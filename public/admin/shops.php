<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$fields = ['name_ja', 'name_en', 'days_ja', 'days_en', 'address', 'hours', 'description_ja', 'description_en', 'url'];

// ?id=2 edits that shop, no id adds a new one
$id = (int)($_GET['id'] ?? 0);

$shop = array_fill_keys($fields, '');
$shop['sort_order'] = 0;
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM shops WHERE id = ?');
    $stmt->execute([$id]);
    $shop = $stmt->fetch();
    if (!$shop) {
        http_response_code(404);
        exit('お店が見つかりません。');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = db()->prepare('DELETE FROM shops WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['flash'] = '削除しました。';
        header('Location: shops.php');
        exit;
    }

    foreach ($fields as $f) {
        $shop[$f] = trim($_POST[$f] ?? '');
    }
    $shop['sort_order'] = (int)($_POST['sort_order'] ?? 0);

    if ($shop['name_ja'] === '' || mb_strlen($shop['name_ja']) > 100) {
        $errors[] = 'お店の名前を100文字までで入力してください。';
    }
    foreach (['name_en', 'days_ja', 'days_en', 'hours'] as $f) {
        if (mb_strlen($shop[$f]) > 100) {
            $errors[] = '短い項目は100文字までです。';
            break;
        }
    }
    if (mb_strlen($shop['address']) > 255) {
        $errors[] = '住所は255文字までです。';
    }
    // only real http(s) links
    if ($shop['url'] !== '' && (!filter_var($shop['url'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#', $shop['url']))) {
        $errors[] = 'URLは https:// から始まる形で入力してください。';
    }

    if (!$errors) {
        $values = [];
        foreach ($fields as $f) {
            // empty optional fields are saved as NULL
            $values[] = in_array($f, ['address', 'hours', 'description_ja', 'description_en', 'url'], true) && $shop[$f] === ''
                ? null
                : $shop[$f];
        }
        $values[] = $shop['sort_order'];

        if ($id > 0) {
            $stmt = db()->prepare('UPDATE shops
                                   SET name_ja = ?, name_en = ?, days_ja = ?, days_en = ?, address = ?,
                                       hours = ?, description_ja = ?, description_en = ?, url = ?, sort_order = ?
                                   WHERE id = ?');
            $stmt->execute([...$values, $id]);
        } else {
            $stmt = db()->prepare('INSERT INTO shops
                                   (name_ja, name_en, days_ja, days_en, address, hours,
                                    description_ja, description_en, url, sort_order)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute($values);
        }
        $_SESSION['flash'] = '「' . $shop['name_ja'] . '」を保存しました。';
        header('Location: shops.php');
        exit;
    }
}

$shops = db()->query('SELECT id, name_ja, days_ja FROM shops ORDER BY sort_order, id')->fetchAll();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = '卸先 | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
<h1>卸先のお店</h1>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<table class="cart-table">
  <thead>
    <tr><th>お店</th><th>曜日</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($shops as $s): ?>
      <tr>
        <td><a href="shops.php?id=<?= h((string)$s['id']) ?>"><?= h($s['name_ja']) ?></a></td>
        <td><?= h($s['days_ja']) ?></td>
        <td>
          <form method="post" action="shops.php?id=<?= h((string)$s['id']) ?>">
            <?= csrf_field() ?>
            <button type="submit" name="action" value="delete">削除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h2><?= $id > 0 ? '「' . h($shop['name_ja']) . '」を編集' : '新しいお店' ?></h2>
<?php if ($id > 0): ?>
  <p><a href="shops.php">編集をやめて、新しいお店を追加する</a></p>
<?php endif; ?>

<?php if ($errors): ?>
  <ul class="error-box">
    <?php foreach ($errors as $e): ?>
      <li><?= h($e) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form class="checkout-form" method="post" action="shops.php<?= $id > 0 ? '?id=' . h((string)$id) : '' ?>">
  <?= csrf_field() ?>
  <label>お店の名前 <input type="text" name="name_ja" maxlength="100" value="<?= h($shop['name_ja']) ?>"></label>
  <label>お店の名前（英語） <input type="text" name="name_en" maxlength="100" value="<?= h($shop['name_en']) ?>"></label>
  <label>ならぶ曜日（例：毎週 木・金） <input type="text" name="days_ja" maxlength="100" value="<?= h($shop['days_ja']) ?>"></label>
  <label>ならぶ曜日（英語） <input type="text" name="days_en" maxlength="100" value="<?= h($shop['days_en']) ?>"></label>
  <label>住所 <input type="text" name="address" maxlength="255" value="<?= h($shop['address']) ?>"></label>
  <label>営業時間 <input type="text" name="hours" maxlength="100" value="<?= h($shop['hours']) ?>"></label>
  <label>紹介文 <textarea name="description_ja" rows="3"><?= h($shop['description_ja']) ?></textarea></label>
  <label>紹介文（英語） <textarea name="description_en" rows="3"><?= h($shop['description_en']) ?></textarea></label>
  <label>URL <input type="url" name="url" maxlength="255" value="<?= h($shop['url']) ?>"></label>
  <label>並び順（小さい数字が先） <input type="number" name="sort_order" value="<?= h((string)$shop['sort_order']) ?>"></label>
  <button class="btn" type="submit">保存する</button>
</form>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>
