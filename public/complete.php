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

$pageTitle = 'ご予約ありがとうございました | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1>ご予約ありがとうございました！</h1>

<div class="next-open">
  <p>予約番号</p>
  <p class="big"><?= h($order['order_code']) ?></p>
  <p>お店で予約番号をお伝えください。</p>
</div>

<dl class="confirm-list">
  <dt>受け取り日</dt> <dd><?= h(date('n月j日', strtotime($order['open_date']))) ?></dd>
  <dt>来店時間</dt>   <dd><?= h(date('G:i', strtotime($order['pickup_time']))) ?></dd>
  <dt>お名前</dt>     <dd><?= h($order['customer_name']) ?></dd>
</dl>

<table class="cart-table">
  <thead>
    <tr><th>ベーグル</th><th>値段</th><th>個数</th><th>小計</th></tr>
  </thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= h($it['product_name']) ?></td>
        <td><?= h(number_format($it['unit_price'])) ?> 円</td>
        <td><?= h((string)$it['quantity']) ?></td>
        <td><?= h(number_format($it['unit_price'] * $it['quantity'])) ?> 円</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p class="cart-total">合計 <?= h((string)$order['total_items']) ?> 個　<strong><?= h(number_format($order['total_price'])) ?> 円</strong></p>

<p><a class="btn" href="index.php">トップへもどる</a></p>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
