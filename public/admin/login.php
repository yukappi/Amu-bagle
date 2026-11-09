<?php
require_once __DIR__ . '/../../includes/init.php';
 
// already logged in
if (is_admin()) {
    header('Location: index.php');
    exit;
}
 
$error    = '';
$username = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
 
    // 5 wrong tries -> wait 5 minutes
    if (time() < ($_SESSION['login_locked_until'] ?? 0)) {
        $error = 'ログインに何度も失敗したので、5分ほど待ってからためしてください。';
    } else {
        $stmt = db()->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
 
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);   // new session id after login
            $_SESSION['admin_id'] = $admin['id'];
            unset($_SESSION['login_fails'], $_SESSION['login_locked_until']);
 
            $stmt = db()->prepare('UPDATE admins SET last_login_at = ? WHERE id = ?');
            $stmt->execute([date('Y-m-d H:i:s'), $admin['id']]);
 
            header('Location: index.php');
            exit;
        }
 
        // wrong name or password
        $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
        if ($_SESSION['login_fails'] >= 5) {
            $_SESSION['login_locked_until'] = time() + 5 * 60;
            $_SESSION['login_fails'] = 0;
        }
        $error = 'ユーザー名かパスワードがちがいます。';
    }
}
 
$pageTitle = 'ログイン | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>
 
<h1>管理画面ログイン</h1>
 
<?php if ($error): ?>
  <p class="error-box"><?= h($error) ?></p>
<?php endif; ?>
 
<form class="checkout-form" method="post" action="login.php">
  <?= csrf_field() ?>
 
  <label>
    ユーザー名
    <input type="text" name="username" value="<?= h($username) ?>" autocomplete="username">
  </label>
 
  <label>
    パスワード
    <input type="password" name="password" autocomplete="current-password">
  </label>
 
  <div class="cart-actions">
    <button class="btn" type="submit">ログイン</button>
  </div>
</form>
 
<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>
