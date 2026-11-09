<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$pageTitle = '管理画面 | あむべ〜ぐる';
require __DIR__ . '/../../includes/templates/header.php';
?>

<h1>管理画面</h1>
<p>ログインしました。ここに注文の一覧を作ります。</p>

<form method="post" action="logout.php">
  <?= csrf_field() ?>
  <button class="btn" type="submit">ログアウト</button>
</form>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>