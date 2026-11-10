<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$statuses = [
    'received'  => '受付',
    'ready'     => '準備完了',
    'picked_up' => '受渡済',
    'cancelled' => 'キャンセル',
];

$id = (int)($_GET['id'] ?? 0);

// change the status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $new = $_POST['status'] ?? '';
    if (isset($statuses[$new])) {
        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$new, $id]);
        $_SESSION['flash'] = '状態を「' . $statuses[$new] . '」にしました。';
    }
    header('Location: order.php?id=' . $id);
    exit;
}

// the order
$stmt = db()->prepare(
    "SELECT o.*, od.open_date
     FROM orders o
     JOIN open_days od ON od.id = o.open_day_id
     WHERE o.id = ?"
);
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    exit('注文が見つかりません。');
}

$stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = $order['order_code'] . ' | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
<p><a href="index.php">← 注文一覧にもどる</a></p>

<h1><?= h($order['order_code']) ?></h1>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<p>
  今の状態：
  <span class="status status-<?= h($order['status']) ?>"><?= h($statuses[$order['status']]) ?></span>
</p>

<form class="status-buttons" method="post" action="order.php?id=<?= h((string)$order['id']) ?>">
  <?= csrf_field() ?>
  <?php foreach ($statuses as $key => $label): ?>
    <?php if ($key !== $order['status']): ?>
      <button type="submit" name="status" value="<?= h($key) ?>">「<?= h($label) ?>」にする</button>
    <?php endif; ?>
  <?php endforeach; ?>
</form>

<dl class="confirm-list">
  <dt>受け取り日</dt>     <dd><?= h(date('n月j日', strtotime($order['open_date']))) ?></dd>
  <dt>来店時間</dt>       <dd><?= h(date('G:i', strtotime($order['pickup_time']))) ?></dd>
  <dt>お名前</dt>         <dd><?= h($order['customer_name']) ?></dd>
  <dt>電話番号</dt>       <dd><?= h($order['phone']) ?></dd>
  <dt>メールアドレス</dt> <dd><?= h($order['email']) ?></dd>
  <dt>ご要望</dt>         <dd><?= $order['note'] === null ? 'なし' : nl2br(h($order['note'])) ?></dd>
  <dt>注文した日時</dt>   <dd><?= h(date('n月j日 G:i', strtotime($order['created_at']))) ?></dd>
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

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>