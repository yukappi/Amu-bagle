<?php
// Common header for every page
// Before including this file, set $pageTitle on the page
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle ?? 'あむべ〜ぐる') ?></title>
    <link rel="stylesheet" href="<?= h(url('assets/css/base.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= h(url('index.php')) ?>">あむべ〜ぐる</a>
        <nav class="site-nav">
            <ul>
                <li><a href="<?= h(url('about.php')) ?>">お店紹介</a></li>
                <li><a href="<?= h(url('menu.php')) ?>">メニュー</a></li>
                <li><a href="<?= h(url('shops.php')) ?>">取扱店</a></li>
                <li><a href="<?= h(url('cart.php')) ?>">カート</a></li>
            </ul>
        </nav>
    </div>
</header>
<main class="container">
    