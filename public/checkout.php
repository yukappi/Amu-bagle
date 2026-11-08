<?php
require_once __DIR__ . '/../includes/init.php';

//  0) Empty cart -> back to the cart page 
$cart = $_SESSION['cart'] ?? ['open_day_id' => null, 'items' => []];
if (!$cart['items']) {
    header('Location: cart.php');
    exit;
}

//  1) Read the cart from the DB (same as cart.php) 
$ids   = array_keys($cart['items']);
$marks = implode(',', array_fill(0, count($ids), '?'));
$stmt  = db()->prepare(
    "SELECT id, name_ja, price
     FROM products
     WHERE id IN ($marks) AND is_active = 1"
);
$stmt->execute($ids);

$rows  = [];
$total = 0;
$count = 0;
foreach ($stmt->fetchAll() as $p) {
    $qty      = $cart['items'][$p['id']];
    $subtotal = $p['price'] * $qty;
    $rows[]   = ['name' => $p['name_ja'], 'price' => $p['price'], 'qty' => $qty, 'subtotal' => $subtotal];
    $total += $subtotal;
    $count += $qty;
}

// the open day (date, opening hours)
$stmt = db()->prepare("SELECT open_date, open_time, close_time FROM open_days WHERE id = ?");
$stmt->execute([$cart['open_day_id']]);
$openDay = $stmt->fetch();

// 2) Pickup times: every 30 minutes, from open to close 
// 11:00, 11:30, ... 15:30
$times = [];
$t   = strtotime($openDay['open_time']);
$end = strtotime($openDay['close_time']);
while ($t < $end) {
    $times[] = date('H:i', $t);
    $t += 30 * 60;                 
}

//  3) The form 
// what the customer typed (kept in the session so "戻る" keeps it)
$form = $_SESSION['checkout'] ?? ['name' => '', 'phone' => '', 'email' => '', 'time' => '', 'note' => ''];
$errors = [];
$step   = 'input';                

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'confirm') {
        
        $form = [
            'name'  => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'time'  => $_POST['time'] ?? '',
            'note'  => trim($_POST['note'] ?? ''),
        ];
        
        $form['phone'] = str_replace(['ー', '−', '‐'], '-', mb_convert_kana($form['phone'], 'a'));

        //  check each field 
        if ($form['name'] === '') {
            $errors['name'] = 'お名前を入れてください。';
        } elseif (mb_strlen($form['name']) > 100) {
            $errors['name'] = 'お名前は100文字までです。';
        }

        if (!preg_match('/^[0-9-]{10,13}$/', $form['phone'])) {
            $errors['phone'] = '電話番号は数字とハイフンで入れてください。（例：090-1234-5678）';
        }

        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'メールアドレスの形がちがいます。';
        }

        if (!in_array($form['time'], $times, true)) {
            $errors['time'] = '来店時間を選んでください。';
        }

        if (mb_strlen($form['note']) > 500) {
            $errors['note'] = 'ご要望は500文字までです。';
        }

        $_SESSION['checkout'] = $form;
        if (!$errors) {
            $step = 'confirm';    
        }
    }

    if ($action === 'place') {
       
        exit('ここは次のステップで作ります。');
    }
}

$pageTitle = 'ご予約 | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1><?= $step === 'confirm' ? 'ご予約内容の確認' : 'ご予約' ?></h1>

<p class="cart-day">
  受け取り日：<strong><?= h(date('n月j日', strtotime($openDay['open_date']))) ?></strong>
</p>

<!-- the cart (same on both screens) -->
<table class="cart-table">
  <thead>
    <tr><th>ベーグル</th><th>値段</th><th>個数</th><th>小計</th></tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['name']) ?></td>
        <td><?= h(number_format($r['price'])) ?> 円</td>
        <td><?= h((string)$r['qty']) ?></td>
        <td><?= h(number_format($r['subtotal'])) ?> 円</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p class="cart-total">合計 <?= h((string)$count) ?> 個　<strong><?= h(number_format($total)) ?> 円</strong></p>
<?php if ($count < 4): ?>
  <p class="notice">4個以上からのご予約をお願いしています。</p>
<?php endif; ?>

<?php if ($step === 'input'): ?>
  <!-- input screen -->
  <form class="checkout-form" method="post" action="checkout.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="confirm">

    <label>
      お名前（必須）
      <input type="text" name="name" value="<?= h($form['name']) ?>">
    </label>
    <?php if (isset($errors['name'])): ?><p class="error"><?= h($errors['name']) ?></p><?php endif; ?>

    <label>
      電話番号（必須）
      <input type="tel" name="phone" value="<?= h($form['phone']) ?>" placeholder="090-1234-5678">
    </label>
    <?php if (isset($errors['phone'])): ?><p class="error"><?= h($errors['phone']) ?></p><?php endif; ?>

    <label>
      メールアドレス（必須）
      <input type="email" name="email" value="<?= h($form['email']) ?>">
    </label>
    <?php if (isset($errors['email'])): ?><p class="error"><?= h($errors['email']) ?></p><?php endif; ?>

    <label>
      来店時間（必須）
      <select name="time">
        <option value="">えらんでください</option>
        <?php foreach ($times as $tm): ?>
          <option value="<?= h($tm) ?>" <?= $tm === $form['time'] ? 'selected' : '' ?>><?= h($tm) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <?php if (isset($errors['time'])): ?><p class="error"><?= h($errors['time']) ?></p><?php endif; ?>

    <label>
      ご要望（なくてもOK）
      <textarea name="note" rows="3"><?= h($form['note']) ?></textarea>
    </label>
    <?php if (isset($errors['note'])): ?><p class="error"><?= h($errors['note']) ?></p><?php endif; ?>

    <div class="cart-actions">
      <a href="cart.php">← カートにもどる</a>
      <button class="btn" type="submit">確認画面へ</button>
    </div>
  </form>

<?php else: ?>
  <!-- confirm screen  -->
  <dl class="confirm-list">
    <dt>お名前</dt>        <dd><?= h($form['name']) ?></dd>
    <dt>電話番号</dt>      <dd><?= h($form['phone']) ?></dd>
    <dt>メールアドレス</dt> <dd><?= h($form['email']) ?></dd>
    <dt>来店時間</dt>      <dd><?= h($form['time']) ?></dd>
    <dt>ご要望</dt>        <dd><?= $form['note'] === '' ? 'なし' : nl2br(h($form['note'])) ?></dd>
  </dl>

  <div class="cart-actions">
    <form method="post" action="checkout.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="back">
      <button type="submit">← 入力を直す</button>
    </form>
    <form method="post" action="checkout.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="place">
      <button class="btn" type="submit">予約を確定する</button>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
