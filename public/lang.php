<?php
require_once __DIR__ . '/../includes/init.php';

// save the language for one year
$lang = ($_GET['l'] ?? '') === 'en' ? 'en' : 'ja';
setcookie('lang', $lang, [
    'expires'  => time() + 60 * 60 * 24 * 365,
    'path'     => '/',
    'samesite' => 'Lax',
]);

// go back to the page we came from (only pages inside this site)
$back  = $_SERVER['HTTP_REFERER'] ?? '';
$path  = parse_url($back, PHP_URL_PATH) ?? '';
$query = parse_url($back, PHP_URL_QUERY);
if (!str_starts_with($path, BASE_URL)) {
    $path  = url('index.php');
    $query = null;
}
header('Location: ' . $path . ($query ? '?' . $query : ''));
exit;