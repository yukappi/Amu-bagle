<?php
require_once __DIR__ . '/../includes/init.php';

// all bagels on sale, with category name
$sql = "SELECT p.id, p.name_ja, p.price, p.image_path, p.is_sold_out,
               c.name_ja AS category
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1
        ORDER BY c.sort_order, p.id";
$products = db()->query($sql)->fetchAll();

$pageTitle = 'メニュー | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1>メニュー</h1>

<div class="pick-list">
  <?php foreach ($products as $p): ?>
    <a class="pick" href="product.php?id=<?= h($p['id']) ?>">
      <?php if ($p['image_path']): ?>
        <img src="<?= h($p['image_path']) ?>" alt="<?= h($p['name_ja']) ?>">
      <?php endif; ?>
      <span class="tag"><?= h($p['category']) ?></span>
      <h3><?= h($p['name_ja']) ?></h3>
      <p><?= h(number_format($p['price'])) ?> Ft</p>
      <?php if ($p['is_sold_out']): ?>
        <p class="soldout">売り切れ</p>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>