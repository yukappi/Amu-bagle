<?php
require_once __DIR__ . '/../includes/init.php';

// which bagel? (product.php?id=2). not a number -> 0
$id = (int)($_GET['id'] ?? 0);

// 1) the bagel and its category name
$stmt = db()->prepare(
    "SELECT p.*, c.name_ja AS category_ja, c.name_en AS category_en
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1"
);
$stmt->execute([$id]);
$product = $stmt->fetch();

// no such bagel -> show "not found" and stop
if (!$product) {
    http_response_code(404);
    $pageTitle = t('product.notfound') . ' | あむべ〜ぐる';
    require __DIR__ . '/../includes/templates/header.php';
    echo '<h1>' . h(t('product.notfound')) . '</h1>';
    echo '<p><a href="menu.php">' . h(t('product.back')) . '</a></p>';
    require __DIR__ . '/../includes/templates/footer.php';
    exit;
}

// 2) allergens of this bagel
$stmt = db()->prepare(
    "SELECT a.name_ja, a.name_en
     FROM product_allergens pa
     JOIN allergens a ON a.id = pa.allergen_id
     WHERE pa.product_id = ?
     ORDER BY a.sort_order"
);
$stmt->execute([$id]);
$allergens = $stmt->fetchAll();

// 3) open days you can still order for (deadline not passed yet)
$stmt = db()->prepare(
    "SELECT od.id, od.open_date
     FROM open_day_products odp
     JOIN open_days od ON od.id = odp.open_day_id
     WHERE odp.product_id = ? AND od.cutoff_at > ?
     ORDER BY od.open_date"
);
$stmt->execute([$id, date('Y-m-d H:i:s')]);
$days = $stmt->fetchAll();

$pageTitle = localized($product, 'name') . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<p><a href="menu.php"><?= h(t('product.back')) ?></a></p>

<article class="product">
  <?php if ($product['image_path']): ?>
    <img class="product-img" src="<?= h($product['image_path']) ?>" alt="">
  <?php endif; ?>

  <div class="product-info">
    <span class="tag"><?= h(localized($product, 'category')) ?></span>
    <h1><?= h(localized($product, 'name')) ?></h1>
    <p class="price"><?= h(price($product['price'])) ?></p>
    <?php if ($product['is_sold_out']): ?>
      <p class="soldout"><?= h(t('soldout')) ?></p>
    <?php endif; ?>
    <p><?= h(localized($product, 'description')) ?></p>

    <h2><?= h(t('product.allergens')) ?></h2>
    <?php if ($allergens): ?>
      <ul class="allergen-list">
        <?php foreach ($allergens as $a): ?>
          <li><?= h(localized($a, 'name')) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p><?= h(t('product.allergens_unknown')) ?></p>
    <?php endif; ?>

    <h2><?= h(t('product.days')) ?></h2>
    <?php if ($days): ?>
      <ul class="day-list">
        <?php foreach ($days as $d): ?>
          <li><?= h(format_date($d['open_date'])) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p><?= h(t('product.no_days')) ?></p>
    <?php endif; ?>
    <?php if ($days && !$product['is_sold_out']): ?>
      <form class="add-cart" method="post" action="cart.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= h($product['id']) ?>">

        <label>
          <?= h(t('product.pickup_day')) ?>
          <select name="open_day_id">
            <?php foreach ($days as $d): ?>
              <option value="<?= h($d['id']) ?>"><?= h(format_date($d['open_date'])) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          <?= h(t('product.qty')) ?>
          <input type="number" name="qty" value="1" min="1" max="20">
        </label>

        <button class="btn" type="submit"><?= h(t('product.add')) ?></button>
      </form>
    <?php endif; ?>


  </div>
</article>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
