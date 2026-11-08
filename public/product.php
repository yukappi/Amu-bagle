<?php
require_once __DIR__ . '/../includes/init.php';

// which bagel? (product.php?id=2). not a number -> 0
$id = (int)($_GET['id'] ?? 0);

// 1) the bagel and its category name
$stmt = db()->prepare(
    "SELECT p.*, c.name_ja AS category
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1"
);
$stmt->execute([$id]);
$product = $stmt->fetch();

// no such bagel -> show "not found" and stop
if (!$product) {
    http_response_code(404);
    $pageTitle = '見つかりません | あむべ〜ぐる';
    require __DIR__ . '/../includes/templates/header.php';
    echo '<h1>ベーグルが見つかりません</h1>';
    echo '<p><a href="menu.php">メニューにもどる</a></p>';
    require __DIR__ . '/../includes/templates/footer.php';
    exit;
}

// 2) allergens of this bagel
$stmt = db()->prepare(
    "SELECT a.name_ja
     FROM product_allergens pa
     JOIN allergens a ON a.id = pa.allergen_id
     WHERE pa.product_id = ?
     ORDER BY a.sort_order"
);
$stmt->execute([$id]);
$allergens = $stmt->fetchAll();

// 3) upcoming open days that sell this bagel
$stmt = db()->prepare(
    "SELECT od.open_date
     FROM open_day_products odp
     JOIN open_days od ON od.id = odp.open_day_id
     WHERE odp.product_id = ? AND od.open_date >= CURDATE()
     ORDER BY od.open_date"
);
$stmt->execute([$id]);
$days = $stmt->fetchAll();

$pageTitle = $product['name_ja'] . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<p><a href="menu.php">← メニューにもどる</a></p>

<article class="product">
  <?php if ($product['image_path']): ?>
    <img class="product-img" src="<?= h($product['image_path']) ?>" alt="">
  <?php endif; ?>

  <div class="product-info">
    <span class="tag"><?= h($product['category']) ?></span>
    <h1><?= h($product['name_ja']) ?></h1>
    <p class="price"><?= h(number_format($product['price'])) ?> 円</p>
    <?php if ($product['is_sold_out']): ?>
      <p class="soldout">売り切れ</p>
    <?php endif; ?>
    <p><?= h($product['description_ja']) ?></p>

    <h2>アレルギー</h2>
    <?php if ($allergens): ?>
      <ul class="allergen-list">
        <?php foreach ($allergens as $a): ?>
          <li><?= h($a['name_ja']) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>確認中です。くわしくはお店にお問い合わせください。</p>
    <?php endif; ?>

    <h2>買える日</h2>
    <?php if ($days): ?>
      <ul class="day-list">
        <?php foreach ($days as $d): ?>
          <li><?= h(date('n月j日', strtotime($d['open_date']))) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>次のOPEN日の販売はまだ決まっていません。</p>
    <?php endif; ?>
  </div>
</article>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
