<?php
// Check the reservation rules. Returns a list of error messages (empty = OK)
// $lock = true locks the open day row (only when placing the order)
function reservation_errors(array $cart, bool $lock = false): array
{
    $errors = [];

    if (!$cart['items']) {
        return [t('cart.empty')];
    }

    // open day
    $sql = 'SELECT * FROM open_days WHERE id = ?';
    if ($lock) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = db()->prepare($sql);
    $stmt->execute([$cart['open_day_id']]);
    $day = $stmt->fetch();

    if (!$day) {
        return [t('rule.no_day')];
    }

    // deadline
    if ($day['cutoff_at'] <= date('Y-m-d H:i:s')) {
        $errors[] = t('rule.closed');
    }

    // check each item
    $stmt = db()->prepare(
        "SELECT p.name_ja, p.name_en, p.is_sold_out, p.is_active, odp.product_id AS on_menu, odp.max_qty
         FROM products p
         LEFT JOIN open_day_products odp
           ON odp.product_id = p.id AND odp.open_day_id = ?
         WHERE p.id = ?"
    );
    $count = 0;
    foreach ($cart['items'] as $productId => $qty) {
        $stmt->execute([$cart['open_day_id'], $productId]);
        $p = $stmt->fetch();

        if (!$p || !$p['is_active']) {
            $errors[] = t('rule.not_sold');
        } elseif ($p['on_menu'] === null) {
            $errors[] = sprintf(t('rule.not_day'), localized($p, 'name'));
        } elseif ($p['is_sold_out']) {
            $errors[] = sprintf(t('rule.soldout'), localized($p, 'name'));
        } elseif ($p['max_qty'] !== null && $qty > $p['max_qty']) {
            $errors[] = sprintf(t('rule.max'), localized($p, 'name'), $p['max_qty']);
        }
        $count += $qty;
    }

    // daily limit (not counting cancelled orders)
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(total_items), 0)
         FROM orders
         WHERE open_day_id = ? AND status <> 'cancelled'"
    );
    $stmt->execute([$cart['open_day_id']]);
    $booked = (int)$stmt->fetchColumn();

    if ($booked + $count > $day['max_items']) {
        $left = max($day['max_items'] - $booked, 0);
        $errors[] = sprintf(t('rule.left'), $left);
    }

    return $errors;
}
