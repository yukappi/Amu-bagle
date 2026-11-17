<?php
require_once __DIR__ . '/../includes/init.php';

// only show the order this user just made
$orderId = $_SESSION['last_order_id'] ?? 0;

$stmt = db()->prepare(
    "SELECT o.*, od.open_date
     FROM orders o
     JOIN open_days od ON od.id = o.open_day_id
     WHERE o.id = ?"
);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

// no order -> go to top page
if (!$order) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = t('done.title') . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1><?= h(t('done.title')) ?></h1>

<div class="next-open">
  <p><?= h(t('done.code')) ?></p>
  <p class="big"><?= h($order['order_code']) ?></p>
  <p><?= h(t('done.tell')) ?></p>
</div>

<dl class="confirm-list">
  <dt><?= h(t('order.day')) ?></dt> <dd><?= h(format_date($order['open_date'])) ?></dd>
  <dt><?= h(t('order.time')) ?></dt> <dd><?= h(date('G:i', strtotime($order['pickup_time']))) ?></dd>
  <dt><?= h(t('order.name')) ?></dt> <dd><?= h($order['customer_name']) ?></dd>
</dl>

<table class="cart-table">
  <thead>
    <tr><th><?= h(t('order.bagel')) ?></th><th><?= h(t('order.price')) ?></th><th><?= h(t('product.qty')) ?></th><th><?= h(t('order.subtotal')) ?></th></tr>
  </thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= h($it['product_name']) ?></td>
        <td><?= h(price($it['unit_price'])) ?></td>
        <td><?= h((string)$it['quantity']) ?></td>
        <td><?= h(price($it['unit_price'] * $it['quantity'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p class="cart-total"><?= h(sprintf(t('order.total'), $order['total_items'])) ?> <strong><?= h(price($order['total_price'])) ?></strong></p>

<p><a class="btn" href="index.php"><?= h(t('done.back')) ?></a></p>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
