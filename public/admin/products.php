<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

// sold out on/off, hide or show again
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int)($_GET['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $sql = [
        'sold_out' => 'UPDATE products SET is_sold_out = 1 WHERE id = ?',
        'in_stock' => 'UPDATE products SET is_sold_out = 0 WHERE id = ?',
        'hide'     => 'UPDATE products SET is_active = 0 WHERE id = ?',
        'show'     => 'UPDATE products SET is_active = 1 WHERE id = ?',
    ];
    if (isset($sql[$action])) {
        $stmt = db()->prepare($sql[$action]);
        $stmt->execute([$id]);
        $_SESSION['flash'] = '変更しました。';
    }
    header('Location: products.php');
    exit;
}

// all bagels, hidden ones at the bottom
$products = db()->query(
    'SELECT p.id, p.name_ja, p.price, p.image_path, p.is_sold_out, p.is_active, c.name_ja AS category
     FROM products p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.is_active DESC, c.sort_order, p.id'
)->fetchAll();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = '商品 | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
<div class="admin-bar">
  <h1>商品</h1>
  <a class="btn" href="product_edit.php">＋ 新しい商品</a>
</div>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<table class="cart-table">
  <thead>
    <tr><th>写真</th><th>名前</th><th>種類</th><th>値段</th><th>状態</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($products as $p): ?>
      <tr class="<?= $p['is_active'] ? '' : 'is-hidden' ?>">
        <td>
          <?php if ($p['image_path']): ?>
            <img class="thumb" src="<?= h(url($p['image_path'])) ?>" alt="">
          <?php endif; ?>
        </td>
        <td><a href="product_edit.php?id=<?= h((string)$p['id']) ?>"><?= h($p['name_ja']) ?></a></td>
        <td><?= h($p['category']) ?></td>
        <td><?= h(number_format($p['price'])) ?> 円</td>
        <td>
          <?php if (!$p['is_active']): ?>
            非表示
          <?php elseif ($p['is_sold_out']): ?>
            <span class="status status-cancelled">売切れ</span>
          <?php else: ?>
            販売中
          <?php endif; ?>
        </td>
        <td>
          <form class="row-buttons" method="post" action="products.php?id=<?= h((string)$p['id']) ?>">
            <?= csrf_field() ?>
            <?php if ($p['is_active']): ?>
              <?php if ($p['is_sold_out']): ?>
                <button type="submit" name="action" value="in_stock">売切れを解除</button>
              <?php else: ?>
                <button type="submit" name="action" value="sold_out">売切れにする</button>
              <?php endif; ?>
              <button type="submit" name="action" value="hide">非表示にする</button>
            <?php else: ?>
              <button type="submit" name="action" value="show">もう一度表示する</button>
            <?php endif; ?>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>
