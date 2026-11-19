<?php
require_once __DIR__ . '/../includes/init.php';

// filters from the URL (?day=3&cat=2). not a number -> 0 = no filter
$day = (int)($_GET['day'] ?? 0);
$cat = (int)($_GET['cat'] ?? 0);

// for the select boxes
$categories = db()->query(
    "SELECT id, name_ja, name_en FROM categories ORDER BY sort_order"
)->fetchAll();
$openDays = db()->query(
    "SELECT id, open_date, cutoff_at FROM open_days
     WHERE open_date >= CURDATE()
     ORDER BY open_date"
)->fetchAll();

// find the chosen open day (null if none)
$chosenDay = null;
foreach ($openDays as $d) {
    if ((int)$d['id'] === $day) {
        $chosenDay = $d;
    }
}

// deadline already passed?
$closed = $chosenDay && new DateTime() >= new DateTime($chosenDay['cutoff_at']);

// build the SQL piece by piece
$sql = "SELECT p.id, p.name_ja, p.name_en, p.price, p.image_path, p.is_sold_out,
               c.name_ja AS category_ja, c.name_en AS category_en
        FROM products p
        JOIN categories c ON c.id = p.category_id";
$where  = ["p.is_active = 1"];
$params = [];

if ($chosenDay) {
    // only bagels sold on that day
    $sql .= " JOIN open_day_products odp ON odp.product_id = p.id";
    $where[]  = "odp.open_day_id = ?";
    $params[] = $chosenDay['id'];
}
if ($cat > 0) {
    $where[]  = "p.category_id = ?";
    $params[] = $cat;
}

$sql .= " WHERE " . implode(' AND ', $where) . " ORDER BY c.sort_order, p.id";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = t('menu.title') . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1><?= h(t('menu.title')) ?></h1>

<form class="filters" method="get" action="menu.php">
  <label>
    <?= h(t('filter.day')) ?>
    <select name="day">
      <option value="0"><?= h(t('filter.all')) ?></option>
      <?php foreach ($openDays as $d): ?>
        <option value="<?= h($d['id']) ?>" <?= (int)$d['id'] === $day ? 'selected' : '' ?>>
          <?= h(format_date($d['open_date'])) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    <?= h(t('filter.cat')) ?>
    <select name="cat">
      <option value="0"><?= h(t('filter.all')) ?></option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= h($c['id']) ?>" <?= (int)$c['id'] === $cat ? 'selected' : '' ?>>
          <?= h(localized($c, 'name')) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <button class="btn" type="submit"><?= h(t('filter.button')) ?></button>
</form>

<?php if ($closed): ?>
  <p class="soldout"><?= h(t('menu.closed')) ?></p>
<?php endif; ?>
<?php if (!$products): ?>
  <p><?= h(t('menu.none')) ?></p>
<?php endif; ?>

<div class="pick-list">
  <?php foreach ($products as $p): ?>
    <a class="pick" href="product.php?id=<?= h($p['id']) ?>">
      <?php if ($p['image_path']): ?>
        <img src="<?= h($p['image_path']) ?>" alt="<?= h(localized($p, 'name')) ?>">
      <?php endif; ?>
      <span class="tag"><?= h(localized($p, 'category')) ?></span>
      <h2><?= h(localized($p, 'name')) ?></h2>
      <p><?= h(price($p['price'])) ?></p>
      <?php if ($p['is_sold_out']): ?>
        <p class="soldout"><?= h(t('soldout')) ?></p>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>