<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

// no id = new bagel, e.g. product_edit.php?id=3 to edit
$id = (int)($_GET['id'] ?? 0);

$categories = db()->query('SELECT id, name_ja FROM categories ORDER BY sort_order, id')->fetchAll();
$allergens  = db()->query('SELECT id, name_ja FROM allergens ORDER BY sort_order, id')->fetchAll();

// start with empty values, then load the bagel if we are editing
$p = [
    'category_id'    => '',
    'name_ja'        => '',
    'name_en'        => '',
    'description_ja' => '',
    'description_en' => '',
    'price'          => '',
    'image_path'     => null,
];
$checked = [];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) {
        http_response_code(404);
        exit('商品が見つかりません。');
    }
    $stmt = db()->prepare('SELECT allergen_id FROM product_allergens WHERE product_id = ?');
    $stmt->execute([$id]);
    $checked = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $p['category_id']    = (int)($_POST['category_id'] ?? 0);
    $p['name_ja']        = trim($_POST['name_ja'] ?? '');
    $p['name_en']        = trim($_POST['name_en'] ?? '');
    $p['description_ja'] = trim($_POST['description_ja'] ?? '');
    $p['description_en'] = trim($_POST['description_en'] ?? '');
    $p['price']          = trim($_POST['price'] ?? '');
    $checked             = array_map('intval', $_POST['allergens'] ?? []);

    if (!in_array($p['category_id'], array_map('intval', array_column($categories, 'id')), true)) {
        $errors[] = '種類をえらんでください。';
    }
    if ($p['name_ja'] === '' || mb_strlen($p['name_ja']) > 100) {
        $errors[] = '名前を100文字までで入力してください。';
    }
    if (mb_strlen($p['name_en']) > 100) {
        $errors[] = '英語の名前は100文字までです。';
    }
    if (!ctype_digit($p['price']) || (int)$p['price'] < 1 || (int)$p['price'] > 99999) {
        $errors[] = '値段は1〜99999の数字で入力してください。';
    }
    // only allergens that really exist
    $checked = array_values(array_intersect($checked, array_map('intval', array_column($allergens, 'id'))));

    // photo (optional)
    $newImage = null;
    $file = $_FILES['image'] ?? null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $info  = $file['error'] === UPLOAD_ERR_OK ? getimagesize($file['tmp_name']) : false;
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
            $errors[] = '写真は2MBまでにしてください。';
        } elseif (!$info || !isset($types[$info['mime']])) {
            $errors[] = '写真は JPG / PNG / WebP にしてください。';
        } else {
            // random file name so nobody can guess or overwrite it
            $name = bin2hex(random_bytes(8)) . '.' . $types[$info['mime']];
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/products/' . $name)) {
                $newImage = 'uploads/products/' . $name;
            } else {
                $errors[] = '写真を保存できませんでした。';
            }
        }
    }

    if (!$errors) {
        if ($newImage) {
            $p['image_path'] = $newImage;
        }
        $values = [
            $p['category_id'], $p['name_ja'], $p['name_en'],
            $p['description_ja'], $p['description_en'], (int)$p['price'], $p['image_path'],
        ];

        $pdo = db();
        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE products
                                       SET category_id = ?, name_ja = ?, name_en = ?, description_ja = ?,
                                           description_en = ?, price = ?, image_path = ?
                                       WHERE id = ?');
                $stmt->execute([...$values, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO products
                                       (category_id, name_ja, name_en, description_ja, description_en, price, image_path)
                                       VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute($values);
                $id = (int)$pdo->lastInsertId();
            }

            // replace the allergen list
            $stmt = $pdo->prepare('DELETE FROM product_allergens WHERE product_id = ?');
            $stmt->execute([$id]);
            $stmt = $pdo->prepare('INSERT INTO product_allergens (product_id, allergen_id) VALUES (?, ?)');
            foreach ($checked as $aid) {
                $stmt->execute([$id, $aid]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        $_SESSION['flash'] = '「' . $p['name_ja'] . '」を保存しました。';
        header('Location: products.php');
        exit;
    }
}

$pageTitle = ($id > 0 ? '商品の編集' : '新しい商品') . ' | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
<div class="admin-bar">
  <h1><?= $id > 0 ? '商品の編集' : '新しい商品' ?></h1>
  <a href="products.php">商品一覧へもどる</a>
</div>

<?php if ($errors): ?>
  <ul class="error-box">
    <?php foreach ($errors as $e): ?>
      <li><?= h($e) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form class="checkout-form" method="post" enctype="multipart/form-data"
      action="product_edit.php<?= $id > 0 ? '?id=' . h((string)$id) : '' ?>">
  <?= csrf_field() ?>

  <label>
    種類
    <select name="category_id">
      <option value="">えらんでください</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= h((string)$c['id']) ?>" <?= (int)$c['id'] === (int)$p['category_id'] ? 'selected' : '' ?>><?= h($c['name_ja']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    名前
    <input type="text" name="name_ja" maxlength="100" value="<?= h($p['name_ja']) ?>">
  </label>

  <label>
    名前（英語）
    <input type="text" name="name_en" maxlength="100" value="<?= h($p['name_en']) ?>">
  </label>

  <label>
    値段（円）
    <input type="number" name="price" min="1" max="99999" value="<?= h((string)$p['price']) ?>">
  </label>

  <label>
    説明
    <textarea name="description_ja" rows="3"><?= h($p['description_ja']) ?></textarea>
  </label>

  <label>
    説明（英語）
    <textarea name="description_en" rows="3"><?= h($p['description_en']) ?></textarea>
  </label>

  <label>
    写真（JPG / PNG / WebP、2MBまで）
    <?php if ($p['image_path']): ?>
      <img class="thumb" src="<?= h(url($p['image_path'])) ?>" alt="">
    <?php endif; ?>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  </label>

  <fieldset class="check-list">
    <legend>アレルギー</legend>
    <?php foreach ($allergens as $a): ?>
      <label>
        <input type="checkbox" name="allergens[]" value="<?= h((string)$a['id']) ?>" <?= in_array((int)$a['id'], $checked, true) ? 'checked' : '' ?>>
        <?= h($a['name_ja']) ?>
      </label>
    <?php endforeach; ?>
  </fieldset>

  <button class="btn" type="submit">保存する</button>
</form>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>
