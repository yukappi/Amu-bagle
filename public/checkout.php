<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/reservation.php';

// 0) empty cart -> back to the cart page
$cart = $_SESSION['cart'] ?? ['open_day_id' => null, 'items' => []];
if (!$cart['items']) {
    header('Location: cart.php');
    exit;
}

// 1) read the cart from the DB (same as cart.php)
$ids   = array_keys($cart['items']);
$marks = implode(',', array_fill(0, count($ids), '?'));
$stmt  = db()->prepare(
    "SELECT id, name_ja, name_en, price
     FROM products
     WHERE id IN ($marks) AND is_active = 1"
);
$stmt->execute($ids);

$rows  = [];
$total = 0;
$count = 0;
foreach ($stmt->fetchAll() as $p) {
    $qty      = $cart['items'][$p['id']];
    $subtotal = $p['price'] * $qty;
    $rows[]   = ['id' => $p['id'], 'name' => localized($p, 'name'), 'price' => $p['price'], 'qty' => $qty, 'subtotal' => $subtotal];
    $total += $subtotal;
    $count += $qty;
}

// the open day (date, opening hours)
$stmt = db()->prepare("SELECT open_date, open_time, close_time FROM open_days WHERE id = ?");
$stmt->execute([$cart['open_day_id']]);
$openDay = $stmt->fetch();

// 2) pickup times: every 30 minutes, from open to close
// 11:00, 11:30, ... 15:30
$times = [];
$t   = strtotime($openDay['open_time']);
$end = strtotime($openDay['close_time']);
while ($t < $end) {
    $times[] = date('H:i', $t);
    $t += 30 * 60;
}

// 3) the form
// what the customer typed (kept in the session so "back" keeps it)
$form = $_SESSION['checkout'] ?? ['name' => '', 'phone' => '', 'email' => '', 'time' => '', 'note' => ''];
$errors = [];
$ruleErrors = [];
$step   = 'input';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'confirm') {
        $form = [
            'name'  => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'time'  => $_POST['time'] ?? '',
            'note'  => trim($_POST['note'] ?? ''),
        ];
        // full-width numbers and dashes -> normal ones
        $form['phone'] = str_replace(['ー', '−', '‐'], '-', mb_convert_kana($form['phone'], 'a'));

        // check each field
        if ($form['name'] === '') {
            $errors['name'] = t('error.name');
        } elseif (mb_strlen($form['name']) > 100) {
            $errors['name'] = t('error.name_long');
        }

        if (!preg_match('/^[0-9-]{10,13}$/', $form['phone'])) {
            $errors['phone'] = t('error.phone');
        }

        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = t('error.email');
        }

        if (!in_array($form['time'], $times, true)) {
            $errors['time'] = t('error.time');
        }

        if (mb_strlen($form['note']) > 500) {
            $errors['note'] = t('error.note');
        }

        $_SESSION['checkout'] = $form;
        if (!$errors) {
            $step = 'confirm';
        }
        $_SESSION['checkout_ok'] = !$errors;
    }

    if ($action === 'place') {
        // must come from the confirm screen
        if (empty($_SESSION['checkout_ok'])) {
            header('Location: checkout.php');
            exit;
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $ruleErrors = reservation_errors($cart, true);

            if (!$ruleErrors) {
                // order code like AB-261118-001
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE open_day_id = ?');
                $stmt->execute([$cart['open_day_id']]);
                $no   = (int)$stmt->fetchColumn() + 1;
                $code = 'AB-' . date('ymd', strtotime($openDay['open_date'])) . '-' . sprintf('%03d', $no);

                // save the order
                $stmt = $pdo->prepare(
                    "INSERT INTO orders
                       (order_code, open_day_id, customer_name, phone, email, pickup_time, total_items, total_price, note)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $code,
                    $cart['open_day_id'],
                    $form['name'],
                    $form['phone'],
                    $form['email'],
                    $form['time'],
                    $count,
                    $total,
                    $form['note'] === '' ? null : $form['note'],
                ]);
                $orderId = (int)$pdo->lastInsertId();

                // save the items (copy name and price too)
                $stmt = $pdo->prepare(
                    "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price)
                     VALUES (?, ?, ?, ?, ?)"
                );
                foreach ($rows as $r) {
                    $stmt->execute([$orderId, $r['id'], $r['name'], $r['qty'], $r['price']]);
                }

                $pdo->commit();

                // empty the cart and go to the complete page
                $_SESSION['last_order_id'] = $orderId;
                unset($_SESSION['cart'], $_SESSION['checkout'], $_SESSION['checkout_ok']);
                header('Location: complete.php');
                exit;
            }

            $pdo->rollBack();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $step = 'confirm';
    }
}

$pageTitle = t('checkout.title') . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1><?= h(t($step === 'confirm' ? 'checkout.confirm' : 'checkout.title')) ?></h1>

<p class="cart-day">
  <?= h(t('order.day')) ?>: <strong><?= h(format_date($openDay['open_date'])) ?></strong>
</p>

<!-- the cart (same on both screens) -->
<table class="cart-table">
  <thead>
    <tr>
      <th><?= h(t('order.bagel')) ?></th>
      <th><?= h(t('order.price')) ?></th>
      <th><?= h(t('product.qty')) ?></th>
      <th><?= h(t('order.subtotal')) ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['name']) ?></td>
        <td><?= h(price($r['price'])) ?></td>
        <td><?= h((string)$r['qty']) ?></td>
        <td><?= h(price($r['subtotal'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p class="cart-total"><?= h(sprintf(t('order.total'), $count)) ?>　<strong><?= h(price($total)) ?></strong></p>
<?php if ($count < 4): ?>
  <p class="notice"><?= h(t('cart.min4')) ?></p>
<?php endif; ?>

<?php if ($step === 'input'): ?>
  <!-- input screen -->
  <form class="checkout-form" method="post" action="checkout.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="confirm">

    <label>
      <?= h(t('order.name') . t('checkout.required')) ?>
      <input type="text" name="name" value="<?= h($form['name']) ?>">
    </label>
    <?php if (isset($errors['name'])): ?><p class="error"><?= h($errors['name']) ?></p><?php endif; ?>

    <label>
      <?= h(t('order.phone') . t('checkout.required')) ?>
      <input type="tel" name="phone" value="<?= h($form['phone']) ?>" placeholder="090-1234-5678">
    </label>
    <?php if (isset($errors['phone'])): ?><p class="error"><?= h($errors['phone']) ?></p><?php endif; ?>

    <label>
      <?= h(t('order.email') . t('checkout.required')) ?>
      <input type="email" name="email" value="<?= h($form['email']) ?>">
    </label>
    <?php if (isset($errors['email'])): ?><p class="error"><?= h($errors['email']) ?></p><?php endif; ?>

    <label>
      <?= h(t('order.time') . t('checkout.required')) ?>
      <select name="time">
        <option value=""><?= h(t('checkout.choose')) ?></option>
        <?php foreach ($times as $tm): ?>
          <option value="<?= h($tm) ?>" <?= $tm === $form['time'] ? 'selected' : '' ?>><?= h($tm) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <?php if (isset($errors['time'])): ?><p class="error"><?= h($errors['time']) ?></p><?php endif; ?>

    <label>
      <?= h(t('order.note') . t('checkout.optional')) ?>
      <textarea name="note" rows="3"><?= h($form['note']) ?></textarea>
    </label>
    <?php if (isset($errors['note'])): ?><p class="error"><?= h($errors['note']) ?></p><?php endif; ?>

    <div class="cart-actions">
      <a href="cart.php"><?= h(t('checkout.back_cart')) ?></a>
      <button class="btn" type="submit"><?= h(t('checkout.next')) ?></button>
    </div>
  </form>

<?php else: ?>
  <!-- confirm screen -->
  <?php if ($ruleErrors): ?>
    <ul class="error-box">
      <?php foreach ($ruleErrors as $msg): ?>
        <li><?= h($msg) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <dl class="confirm-list">
    <dt><?= h(t('order.name')) ?></dt>  <dd><?= h($form['name']) ?></dd>
    <dt><?= h(t('order.phone')) ?></dt> <dd><?= h($form['phone']) ?></dd>
    <dt><?= h(t('order.email')) ?></dt> <dd><?= h($form['email']) ?></dd>
    <dt><?= h(t('order.time')) ?></dt>  <dd><?= h($form['time']) ?></dd>
    <dt><?= h(t('order.note')) ?></dt>  <dd><?= $form['note'] === '' ? h(t('checkout.none')) : nl2br(h($form['note'])) ?></dd>
  </dl>

  <div class="cart-actions">
    <form method="post" action="checkout.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="back">
      <button type="submit"><?= h(t('checkout.fix')) ?></button>
    </form>
    <form method="post" action="checkout.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="place">
      <button class="btn" type="submit"><?= h(t('checkout.place')) ?></button>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
