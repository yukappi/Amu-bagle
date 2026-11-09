<?php
// Check the reservation rules. Returns a list of error messages (empty = OK)
// $lock = true locks the open day row (only when placing the order)
function reservation_errors(array $cart, bool $lock = false): array
{
    $errors = [];

    if (!$cart['items']) {
        return ['カートが空です。'];
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
        return ['受け取り日が見つかりません。'];
    }

    // deadline
    if ($day['cutoff_at'] <= date('Y-m-d H:i:s')) {
        $errors[] = 'この日のご予約は締め切りました。';
    }

    // check each item
    $stmt = db()->prepare(
        "SELECT p.name_ja, p.is_sold_out, p.is_active, odp.product_id AS on_menu, odp.max_qty
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
            $errors[] = '販売していない商品が入っています。';
        } elseif ($p['on_menu'] === null) {
            $errors[] = $p['name_ja'] . 'はこの日は販売していません。';
        } elseif ($p['is_sold_out']) {
            $errors[] = $p['name_ja'] . 'は売り切れです。';
        } elseif ($p['max_qty'] !== null && $qty > $p['max_qty']) {
            $errors[] = $p['name_ja'] . 'は' . $p['max_qty'] . '個までです。';
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
        $errors[] = 'この日はあと' . $left . '個までしか予約できません。';
    }

    return $errors;
}
