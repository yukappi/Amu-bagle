<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

// the day, e.g. open_day_edit.php?date=2026-11-20
$date = $_GET['date'] ?? '';
$stmt = db()->prepare('SELECT * FROM open_days WHERE open_date = ?');
$stmt->execute([$date]);
$day = $stmt->fetch();
if (!$day) {
    http_response_code(404);
    exit('この日はOPEN日ではありません。');
}

// all bagels on sale
$products = db()->query('SELECT id, name_ja, is_sold_out FROM products WHERE is_active = 1 ORDER BY id')->fetchAll();

// bagels sold on this day: product_id => max_qty
$stmt = db()->prepare('SELECT product_id, max_qty FROM open_day_products WHERE open_day_id = ?');
$stmt->execute([$day['id']]);
$selling = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// time choices every 30 minutes, 08:00 to 20:00
$times = [];
for ($t = strtotime('08:00'); $t <= strtotime('20:00'); $t += 1800) {
    $times[] = date('H:i', $t);
}

$openTime    = substr($day['open_time'], 0, 5);
$closeTime   = substr($day['close_time'], 0, 5);
$maxItems    = (string)$day['max_items'];
$cutoffValue = date('Y-m-d\TH:i', strtotime($day['cutoff_at']));
$note        = $day['note'] ?? '';
$errors      = [];

// save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $openTime    = $_POST['open_time'] ?? '';
    $closeTime   = $_POST['close_time'] ?? '';
    $maxItems    = trim($_POST['max_items'] ?? '');
    $cutoffValue = $_POST['cutoff_at'] ?? '';
    $note        = trim($_POST['note'] ?? '');
    $picked      = $_POST['products'] ?? [];
    $limits      = $_POST['max_qty'] ?? [];

    if (!in_array($openTime, $times, true) || !in_array($closeTime, $times, true) || $openTime >= $closeTime) {
        $errors[] = '営業時間を正しく選んでください。';
    }
    if (!ctype_digit($maxItems) || (int)$maxItems < 1 || (int)$maxItems > 999) {
        $errors[] = '予約の上限は1〜999の数字で入力してください。';
    }
    $cutoffTs = strtotime($cutoffValue);
    if ($cutoffTs === false || date('Y-m-d H:i', $cutoffTs) >= $date . ' ' . $openTime) {
        $errors[] = '締切は開店時間より前にしてください。';
    }
    if (mb_strlen($note) > 500) {
        $errors[] = 'メモは500文字までです。';
    }

    // only bagels that really exist; empty limit means no limit
    $selling = [];
    foreach ($products as $p) {
        $pid = (int)$p['id'];
        if (isset($picked[$pid])) {
            $limit = trim($limits[$pid] ?? '');
            $selling[$pid] = ctype_digit($limit) && (int)$limit > 0 ? (int)$limit : null;
        }
    }
    if (!$selling) {
        $errors[] = '売るベーグルを1つ以上えらんでください。';
    }

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('UPDATE open_days
                                   SET open_time = ?, close_time = ?, max_items = ?, cutoff_at = ?, note = ?
                                   WHERE id = ?');
            $stmt->execute([$openTime, $closeTime, (int)$maxItems, date('Y-m-d H:i:s', $cutoffTs), $note, $day['id']]);

            // replace the bagel list for this day
            $stmt = $pdo->prepare('DELETE FROM open_day_products WHERE open_day_id = ?');
            $stmt->execute([$day['id']]);
            $stmt = $pdo->prepare('INSERT INTO open_day_products (open_day_id, product_id, max_qty) VALUES (?, ?, ?)');
            foreach ($selling as $pid => $limit) {
                $stmt->execute([$day['id'], $pid, $limit]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        $_SESSION['flash'] = '保存しました。';
        header('Location: open_day_edit.php?date=' . $date);
        exit;
    }
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = 'OPEN日の設定 | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>

<div class="admin-bar">
  <h1><?= h(date('n月j日', strtotime($date))) ?>の設定</h1>
  <a href="open_days.php?ym=<?= h(substr($date, 0, 7)) ?>">カレンダーへもどる</a>
</div>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<?php if ($errors): ?>
  <ul class="error-box">
    <?php foreach ($errors as $e): ?>
      <li><?= h($e) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form class="checkout-form day-form" method="post" action="open_day_edit.php?date=<?= h($date) ?>">
  <?= csrf_field() ?>

  <label>
    開店時間
    <select name="open_time">
      <?php foreach ($times as $t): ?>
        <option value="<?= h($t) ?>" <?= $t === $openTime ? 'selected' : '' ?>><?= h($t) ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    閉店時間
    <select name="close_time">
      <?php foreach ($times as $t): ?>
        <option value="<?= h($t) ?>" <?= $t === $closeTime ? 'selected' : '' ?>><?= h($t) ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    予約の上限（その日の合計の個数）
    <input type="number" name="max_items" min="1" max="999" value="<?= h($maxItems) ?>">
  </label>

  <label>
    予約の締切
    <input type="datetime-local" name="cutoff_at" value="<?= h($cutoffValue) ?>">
  </label>

  <label>
    メモ（お店用）
    <textarea name="note" rows="3"><?= h($note) ?></textarea>
  </label>

  <h2>この日に売るベーグル</h2>
  <p>「1つの上限」が空のときは、上限なしです。</p>
  <table class="cart-table">
    <thead>
      <tr><th>売る</th><th>ベーグル</th><th>1つの上限</th></tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <?php $pid = (int)$p['id']; ?>
        <tr>
          <td>
            <input type="checkbox" name="products[<?= h((string)$pid) ?>]" value="1" <?= array_key_exists($pid, $selling) ? 'checked' : '' ?>>
          </td>
          <td>
            <?= h($p['name_ja']) ?>
            <?php if ($p['is_sold_out']): ?>（売切れ中）<?php endif; ?>
          </td>
          <td>
            <input type="number" name="max_qty[<?= h((string)$pid) ?>]" min="1" max="999" value="<?= h((string)($selling[$pid] ?? '')) ?>">
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <button class="btn" type="submit">保存する</button>
</form>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>
