<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

// is this name already used by another allergen?
function name_taken(string $name, int $id): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM allergens WHERE name_ja = ? AND id <> ?');
    $stmt->execute([$name, $id]);
    return (int)$stmt->fetchColumn() > 0;
}

// add, save or delete one allergen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int)($_GET['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $nameJa = trim($_POST['name_ja'] ?? '');
    $nameEn = trim($_POST['name_en'] ?? '');
    $sort   = (int)($_POST['sort_order'] ?? 0);

    if ($action === 'delete') {
        // an allergen that a bagel still uses can't be deleted
        $stmt = db()->prepare('SELECT COUNT(*) FROM product_allergens WHERE allergen_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $_SESSION['flash'] = 'このアレルギーが付いたベーグルがあるので削除できません。';
        } else {
            $stmt = db()->prepare('DELETE FROM allergens WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['flash'] = '削除しました。';
        }
    } elseif ($nameJa === '' || mb_strlen($nameJa) > 100 || mb_strlen($nameEn) > 100) {
        $_SESSION['flash'] = '名前を100文字までで入力してください。';
    } elseif (name_taken($nameJa, $id)) {
        $_SESSION['flash'] = '「' . $nameJa . '」はもうあります。';
    } elseif ($action === 'add') {
        $stmt = db()->prepare('INSERT INTO allergens (name_ja, name_en, sort_order) VALUES (?, ?, ?)');
        $stmt->execute([$nameJa, $nameEn, $sort]);
        $_SESSION['flash'] = '「' . $nameJa . '」を追加しました。';
    } elseif ($action === 'save') {
        $stmt = db()->prepare('UPDATE allergens SET name_ja = ?, name_en = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$nameJa, $nameEn, $sort, $id]);
        $_SESSION['flash'] = '「' . $nameJa . '」を保存しました。';
    }
    header('Location: allergens.php');
    exit;
}

// allergens with how many bagels use each one
$rows = db()->query(
    'SELECT a.id, a.name_ja, a.name_en, a.sort_order, COUNT(pa.product_id) AS used
     FROM allergens a
     LEFT JOIN product_allergens pa ON pa.allergen_id = a.id
     GROUP BY a.id, a.name_ja, a.name_en, a.sort_order
     ORDER BY a.sort_order, a.id'
)->fetchAll();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = 'アレルギー | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
<h1>アレルギー</h1>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<p>並び順は小さい数字が先です。使われているアレルギーは削除できません。</p>

<div class="edit-row edit-head">
  <span>名前</span><span>英語</span><span>並び順</span><span>ベーグル</span>
</div>

<?php foreach ($rows as $r): ?>
  <form class="edit-row" method="post" action="allergens.php?id=<?= h((string)$r['id']) ?>">
    <?= csrf_field() ?>
    <input type="text" name="name_ja" maxlength="100" value="<?= h($r['name_ja']) ?>">
    <input type="text" name="name_en" maxlength="100" value="<?= h($r['name_en']) ?>">
    <input type="number" name="sort_order" value="<?= h((string)$r['sort_order']) ?>">
    <span><?= h((string)$r['used']) ?>個</span>
    <button type="submit" name="action" value="save">保存</button>
    <?php if ((int)$r['used'] === 0): ?>
      <button type="submit" name="action" value="delete">削除</button>
    <?php endif; ?>
  </form>
<?php endforeach; ?>

<h2>新しいアレルギー</h2>
<form class="edit-row" method="post" action="allergens.php">
  <?= csrf_field() ?>
  <input type="text" name="name_ja" maxlength="100" placeholder="名前">
  <input type="text" name="name_en" maxlength="100" placeholder="English">
  <input type="number" name="sort_order" value="0">
  <span></span>
  <button type="submit" name="action" value="add">追加</button>
</form>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>
