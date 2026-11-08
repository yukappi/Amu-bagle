<?php
require_once __DIR__ . '/../includes/init.php';

// ===== The cart lives in the session =====
// $_SESSION['cart'] = ['open_day_id' => 1, 'items' => [商品id => 個数, ...]]
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = ['open_day_id' => null, 'items' => []];
}
$cart = &$_SESSION['cart'];   // & = 「同じもの」を指す。$cart を変えるとセッションも変わる

const MAX_QTY = 20;            // 1つの商品は20個まで

// ===== 1) A form was sent (POST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action    = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = (int)($_POST['qty'] ?? 0);

    if ($action === 'add') {
        $openDayId = (int)($_POST['open_day_id'] ?? 0);

        // 1回の予約は「1つの受け取り日」だけ
        if ($cart['items'] && $cart['open_day_id'] !== $openDayId) {
            $_SESSION['flash'] = 'カートには、ちがう受け取り日の商品が入っています。先にカートを空にしてください。';
        } elseif ($productId > 0 && $qty > 0) {
            $cart['open_day_id'] = $openDayId;
            $now = $cart['items'][$productId] ?? 0;
            $cart['items'][$productId] = min($now + $qty, MAX_QTY);
            $_SESSION['flash'] = 'カートに入れました。';
        }
    }

    if ($action === 'update' && isset($cart['items'][$productId])) {
        if ($qty <= 0) {
            unset($cart['items'][$productId]);       // 0個 = 消す
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

    // PRG: POSTのあとは、もう一度 GET で開き直す（再読み込みで二重に入らないように）
    header('Location: cart.php');
    exit;
}

// ===== 2) Show the cart (GET) =====

// one-time message (出したら消す)
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$rows    = [];
$total   = 0;
$count   = 0;
$openDay = null;

if ($cart['items']) {
    // 名前と値段は、いつもDBから読む（セッションには id と個数だけ）
    $ids = array_keys($cart['items']);
    $marks = implode(',', array_fill(0, count($ids), '?'));   // 3個なら "?,?,?"
    $stmt = db()->prepare(
        "SELECT id, name_ja, price
         FROM products
         WHERE id IN ($marks) AND is_active = 1"
    );
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $p) {
        $qty      = $cart['items'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $rows[]   = ['id' => $p['id'], 'name' => $p['name_ja'], 'price' => $p['price'],
                     'qty' => $qty, 'subtotal' => $subtotal];
        $total += $subtotal;
        $count += $qty;
    }

    // 受け取り日
    $stmt = db()->prepare("SELECT open_date, cutoff_at FROM open_days WHERE id = ?");
    $stmt->execute([$cart['open_day_id']]);
    $openDay = $stmt->fetch();
}

$pageTitle = 'カート | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1>カート</h1>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<?php if (!$rows): ?>
  <p>カートは空です。</p>
  <p><a class="btn" href="menu.php">メニューを見る</a></p>
<?php else: ?>

  <?php if ($openDay): ?>
    <p class="cart-day">
      受け取り日：<strong><?= h(date('n月j日', strtotime($openDay['open_date']))) ?></strong>
      （ご予約は <?= h(date('n月j日 G:i', strtotime($openDay['cutoff_at']))) ?> まで）
    </p>
  <?php endif; ?>

  <table class="cart-table">
    <thead>
      <tr><th>ベーグル</th><th>値段</th><th>個数</th><th>小計</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><a href="product.php?id=<?= h((string)$r['id']) ?>"><?= h($r['name']) ?></a></td>
          <td><?= h(number_format($r['price'])) ?> 円</td>
          <td>
            <form method="post" action="cart.php" class="qty-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="product_id" value="<?= h((string)$r['id']) ?>">
              <input type="number" name="qty" value="<?= h((string)$r['qty']) ?>" min="0" max="20">
              <button type="submit">変更</button>
            </form>
          </td>
          <td><?= h(number_format($r['subtotal'])) ?> 円</td>
          <td>
            <form method="post" action="cart.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?= h((string)$r['id']) ?>">
              <button type="submit">削除</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="cart-total">合計 <?= h((string)$count) ?> 個　<strong><?= h(number_format($total)) ?> 円</strong></p>

  <?php if ($count < 4): ?>
    <p class="notice">4個以上からのご予約をお願いしています。</p>
  <?php endif; ?>

  <div class="cart-actions">
    <a href="menu.php">← 買い物を続ける</a>
    <form method="post" action="cart.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="clear">
      <button type="submit">カートを空にする</button>
    </form>
    <a class="btn" href="checkout.php">予約へ進む</a>
  </div>

<?php endif; ?>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
