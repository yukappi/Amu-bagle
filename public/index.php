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
        $leftText = sprintf(t('home.left'), $diff->days, $diff->h);
    } else {
        $leftText = t('menu.closed');
    }
}

// 3 random bagels for that day
$picks = [];
if ($next) {
    $stmt = $pdo->prepare(
        "SELECT p.id, p.name_ja, p.name_en, p.price, p.image_path
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
        "SELECT id, name_ja, name_en, price, image_path
         FROM products
         WHERE is_active = 1
         ORDER BY RAND()
         LIMIT 3"
    )->fetchAll();
}

$pageTitle = 'あむべ〜ぐる | ' . t('home.title');
require __DIR__ . '/../includes/templates/header.php';
?>

<section class="hero">
  <div class="hero-text">
    <h1 class="hero-logo"><img src="<?= h(url('assets/img/kana.webp')) ?>" alt="アムベーグル" width="520" height="359"></h1>
    <p class="hero-small"><?= h(t('home.small')) ?></p>
    <p class="hero-catch"><?= h(t('home.catch1')) ?><span><?= h(t('home.catch2')) ?></span></p>
    <p><?= h(t('home.lead')) ?></p>
    <a class="btn" href="menu.php"><?= h(t('home.cta')) ?></a>

    <?php if ($next): ?>
      <p class="hero-next">
        <img class="announce" src="<?= h(url('assets/img/navycat.webp')) ?>" alt="">
        <span class="pill"><?= h(t('home.next')) ?></span>
        <?= h(format_date($next['open_date'])) ?>
        <?= h(substr($next['open_time'], 0, 5)) ?>〜<?= h(substr($next['close_time'], 0, 5)) ?>
      </p>
      <p class="hero-left"><?= h($leftText) ?></p>
    <?php else: ?>
      <p class="hero-left"><?= h(t('home.no_next')) ?></p>
    <?php endif; ?>
  </div>

  <div class="hero-pic">
    <img src="<?= h(url('assets/img/cheer.webp')) ?>" alt="" width="555" height="700">
  </div>
</section>

<section class="picks">
  <h2><img class="h-icon" src="<?= h(url('assets/img/mentai.webp')) ?>" alt=""><?= h(t('home.picks')) ?></h2>
  <div class="pick-list">
    <?php foreach ($picks as $p): ?>
      <a class="pick" href="product.php?id=<?= h($p['id']) ?>">
        <?php if ($p['image_path']): ?>
          <img src="<?= h($p['image_path']) ?>" alt="<?= h(localized($p, 'name')) ?>">
        <?php endif; ?>
        <h3><?= h(localized($p, 'name')) ?></h3>
        <p><?= h(price($p['price'])) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>