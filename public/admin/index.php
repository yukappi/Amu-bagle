<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$statuses = [
    'received'  => '受付',
    'ready'     => '準備完了',
    'picked_up' => '受渡済',
    'cancelled' => 'キャンセル',
];

// open days for the filter
$days = db()->query('SELECT id, open_date FROM open_days ORDER BY open_date')->fetchAll();

// filter from the URL, e.g. index.php?day=1&status=received
$dayId  = (int)($_GET['day'] ?? 0);
$status = $_GET['status'] ?? '';
if (!isset($statuses[$status])) {
    $status = '';
}

// orders
$where  = [];
$params = [];
if ($dayId > 0) {
    $where[]  = 'o.open_day_id = ?';
    $params[] = $dayId;
}
if ($status !== '') {
    $where[]  = 'o.status = ?';
    $params[] = $status;
}

$sql = "SELECT o.id, o.order_code, o.customer_name, o.pickup_time,
               o.total_items, o.total_price, o.status, od.open_date
        FROM orders o
        JOIN open_days od ON od.id = o.open_day_id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY od.open_date, o.pickup_time, o.id';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// how many of each bagel to make on that day (no cancelled orders)
$prep = [];
if ($dayId > 0) {
    $stmt = db()->prepare(
        "SELECT oi.product_name, SUM(oi.quantity) AS qty
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE o.open_day_id = ? AND o.status <> 'cancelled'
         GROUP BY oi.product_id, oi.product_name
         ORDER BY qty DESC"
    );
    $stmt->execute([$dayId]);
    $prep = $stmt->fetchAll();
}

$pageTitle = '注文一覧 | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
<div class="admin-bar">
  <h1>注文一覧</h1>
  <form method="post" action="logout.php">
    <?= csrf_field() ?>
    <button type="submit">ログアウト</button>
  </form>
</div>

<form class="filters" method="get" action="index.php">
  <label>
    OPEN日
    <select name="day">
      <option value="0">すべて</option>
      <?php foreach ($days as $d): ?>
        <option value="<?= h((string)$d['id']) ?>" <?= (int)$d['id'] === $dayId ? 'selected' : '' ?>>
          <?= h(date('n月j日', strtotime($d['open_date']))) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>
    状態
    <select name="status">
      <option value="">すべて</option>
      <?php foreach ($statuses as $key => $label): ?>
        <option value="<?= h($key) ?>" <?= $key === $status ? 'selected' : '' ?>><?= h($label) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button class="btn" type="submit">しぼりこむ</button>
</form>

<?php if ($dayId > 0): ?>
  <h2>この日に作る数</h2>
  <?php if ($prep): ?>
    <ul class="day-list">
      <?php foreach ($prep as $p): ?>
        <li><?= h($p['product_name']) ?> × <?= h((string)$p['qty']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>まだ注文はありません。</p>
  <?php endif; ?>
<?php endif; ?>

<h2>注文（<?= h((string)count($orders)) ?>件）</h2>
<?php if ($orders): ?>
  <table class="cart-table">
    <thead>
      <tr><th>予約番号</th><th>受け取り日</th><th>時間</th><th>お名前</th><th>個数</th><th>合計</th><th>状態</th></tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><a href="order.php?id=<?= h((string)$o['id']) ?>"><?= h($o['order_code']) ?></a></td>
          <td><?= h(date('n/j', strtotime($o['open_date']))) ?></td>
          <td><?= h(date('G:i', strtotime($o['pickup_time']))) ?></td>
          <td><?= h($o['customer_name']) ?></td>
          <td><?= h((string)$o['total_items']) ?></td>
          <td><?= h(number_format($o['total_price'])) ?> 円</td>
          <td><span class="status status-<?= h($o['status']) ?>"><?= h($statuses[$o['status']]) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>注文はありません。</p>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>