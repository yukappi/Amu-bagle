    <?php
require_once __DIR__ . '/../includes/init.php';
$pdo = db();   // get the DB connection

// next open day
$sql = "SELECT id, open_date, open_time, close_time, cutoff_at
        FROM open_days
        WHERE open_date >= CURDATE()
        ORDER BY open_date
        LIMIT 1";
$next = $pdo->query($sql)->fetch();

// how long until the order deadline
$leftText = '';
if ($next) {    
    $now    = new DateTime();
    $cutoff = new DateTime($next['cutoff_at']);
    if ($now < $cutoff) {
        $diff = $now->diff($cutoff);
        $leftText = "予約の締切まで あと {$diff->days}日 {$diff->h}時間";
    } else {
        $leftText = 'この日の予約は締め切りました';
    }
}

// 3 random bagels for that day
$picks = [];
if ($next) {
    $stmt = $pdo->prepare(
        "SELECT p.id, p.name_ja, p.price, p.image_path
         FROM open_day_products odp
         JOIN products p ON p.id = odp.product_id
         WHERE odp.open_day_id = ? AND p.is_active = 1
         ORDER BY RAND()
         LIMIT 3"
    );
    $stmt->execute([$next['id']]);
    $picks = $stmt->fetchAll();
}

// menu for that day not set yet -> just use any active bagels
if (!$picks) {
    $picks = $pdo->query(
        "SELECT id, name_ja, price, image_path
         FROM products
         WHERE is_active = 1
         ORDER BY RAND()
         LIMIT 3"
    )->fetchAll();
}

$pageTitle = 'あむべ〜ぐる | ベーグルの予約';
require __DIR__ . '/../includes/templates/header.php';
?>

<section class="next-open">
  <h2>次のOPEN日</h2>
  <?php if ($next): ?>
    <p class="big"><?= h(date('n月j日', strtotime($next['open_date']))) ?></p>
    <!-- 11:00:00 -> 11:00 -->
    <p><?= h(substr($next['open_time'], 0, 5)) ?>〜<?= h(substr($next['close_time'], 0, 5)) ?></p>
    <p><?= h($leftText) ?></p>
    <a class="btn" href="menu.php">メニューを見る</a>
  <?php else: ?>
    <p>次のOPEN日はまだ決まっていません。</p>
  <?php endif; ?>
</section>

<section class="picks">
  <h2>おすすめベーグル</h2>
  <div class="pick-list">
    <?php foreach ($picks as $p): ?>
      <a class="pick" href="product.php?id=<?= h($p['id']) ?>">
        <?php if ($p['image_path']): ?>
          <img src="<?= h($p['image_path']) ?>" alt="<?= h($p['name_ja']) ?>">
        <?php endif; ?>
        <h3><?= h($p['name_ja']) ?></h3>
        <p><?= h(number_format($p['price'])) ?> 円</p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>