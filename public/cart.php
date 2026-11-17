<?php
require_once __DIR__ . '/../includes/init.php';

// The cart is kept in the session:
// $_SESSION['cart'] = ['open_day_id' => 1, 'items' => [product id => qty, ...]]
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = ['open_day_id' => null, 'items' => []];
}
$cart = &$_SESSION['cart'];   // & means changing $cart also changes the session

const MAX_QTY = 20;            // max 20 of one bagel

// 1) a form was sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action    = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = (int)($_POST['qty'] ?? 0);

    if ($action === 'add') {
        $openDayId = (int)($_POST['open_day_id'] ?? 0);

        // one order = one pickup day only
        if ($cart['items'] && $cart['open_day_id'] !== $openDayId) {
            $_SESSION['flash'] = t('cart.other_day');
        } elseif ($productId > 0 && $qty > 0) {
            $cart['open_day_id'] = $openDayId;
            $now = $cart['items'][$productId] ?? 0;
            $cart['items'][$productId] = min($now + $qty, MAX_QTY);
            $_SESSION['flash'] = t('cart.added');
        }
    }

    if ($action === 'update' && isset($cart['items'][$productId])) {
        if ($qty <= 0) {
            unset($cart['items'][$productId]);       // 0 = remove it
        } else {
            $cart['items'][$productId] = min($qty, MAX_QTY);
        }
    }

    if ($action === 'remove') {
        unset($cart['items'][$productId]);
    }

    if ($action === 'clear' || !$cart['items']) {
        $cart = ['open_day_id' => null, 'items' => []];
    }

    // PRG: open the page again with GET so reloading doesn't add twice
    header('Location: cart.php');
    exit;
}

// 2) show the cart

// one-time message
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$rows    = [];
$total   = 0;
$count   = 0;
$openDay = null;

if ($cart['items']) {
    // names and prices always come from the DB (the session only has ids and qty)
    $ids = array_keys($cart['items']);
    $marks = implode(',', array_fill(0, count($ids), '?'));   // 3 items -> "?,?,?"
    $stmt = db()->prepare(
        "SELECT id, name_ja, name_en, price
         FROM products
         WHERE id IN ($marks) AND is_active = 1"
    );
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $p) {
        $qty      = $cart['items'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $rows[]   = ['id' => $p['id'], 'name' => localized($p, 'name'), 'price' => $p['price'],
                     'qty' => $qty, 'subtotal' => $subtotal];
        $total += $subtotal;
        $count += $qty;
    }

    // pickup day
    $stmt = db()->prepare("SELECT open_date, cutoff_at FROM open_days WHERE id = ?");
    $stmt->execute([$cart['open_day_id']]);
    $openDay = $stmt->fetch();
}

$pageTitle = t('cart.title') . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1><?= h(t('cart.title')) ?></h1>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<?php if (!$rows): ?>
  <p><?= h(t('cart.empty')) ?></p>
  <p><a class="btn" href="menu.php"><?= h(t('home.see_menu')) ?></a></p>
<?php else: ?>

  <?php if ($openDay): ?>
    <p class="cart-day">
      <?= h(t('order.day')) ?>: <strong><?= h(format_date($openDay['open_date'])) ?></strong>
      <?= h(sprintf(t('cart.until'), format_date($openDay['cutoff_at']) . ' ' . date('G:i', strtotime($openDay['cutoff_at'])))) ?>
    </p>
  <?php endif; ?>

  <table class="cart-table">
    <thead>
      <tr>
        <th><?= h(t('order.bagel')) ?></th>
        <th><?= h(t('order.price')) ?></th>
        <th><?= h(t('product.qty')) ?></th>
        <th><?= h(t('order.subtotal')) ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><a href="product.php?id=<?= h((string)$r['id']) ?>"><?= h($r['name']) ?></a></td>
          <td><?= h(price($r['price'])) ?></td>
          <td>
            <form method="post" action="cart.php" class="qty-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="product_id" value="<?= h((string)$r['id']) ?>">
              <input type="number" name="qty" value="<?= h((string)$r['qty']) ?>" min="0" max="20">
              <button type="submit"><?= h(t('cart.change')) ?></button>
            </form>
          </td>
          <td><?= h(price($r['subtotal'])) ?></td>
          <td>
            <form method="post" action="cart.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?= h((string)$r['id']) ?>">
              <button type="submit"><?= h(t('cart.remove')) ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="cart-total"><?= h(sprintf(t('order.total'), $count)) ?>　<strong><?= h(price($total)) ?></strong></p>

  <?php if ($count < 4): ?>
    <p class="notice"><?= h(t('cart.min4')) ?></p>
  <?php endif; ?>

  <div class="cart-actions">
    <a href="menu.php"><?= h(t('cart.continue')) ?></a>
    <form method="post" action="cart.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="clear">
      <button type="submit"><?= h(t('cart.clear')) ?></button>
    </form>
    <a class="btn" href="checkout.php"><?= h(t('cart.checkout')) ?></a>
  </div>

<?php endif; ?>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
