<?php
// Common header for every page
// Before including this file, set $pageTitle on the page
?>
<!DOCTYPE html>
<html lang="<?= h(current_lang()) ?>">
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
                <li><a href="<?= h(url('index.php')) ?>"><?= h(t('nav.top')) ?></a></li>
                <li><a href="<?= h(url('about.php')) ?>"><?= h(t('nav.about')) ?></a></li>
                <li><a href="<?= h(url('menu.php')) ?>"><?= h(t('nav.menu')) ?></a></li>
                <li><a href="<?= h(url('cart.php')) ?>"><?= h(t('nav.cart')) ?></a></li>
                <li><a href="<?= h(url('admin/index.php')) ?>"><?= h(t('nav.admin')) ?></a></li>
                    <?php if (current_lang() === 'en'): ?>
                        <a class="lang-btn" href="<?= h(url('lang.php?l=ja')) ?>">JA</a>
                    <?php else: ?>
                        <a class="lang-btn" href="<?= h(url('lang.php?l=en')) ?>">EN</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>
<?php if (is_admin()): ?>
<nav class="admin-nav">
    <div class="container">
        <span>管理：</span>
        <a href="<?= h(url('admin/index.php')) ?>">注文</a>
        <a href="<?= h(url('admin/open_days.php')) ?>">OPEN日</a>
        <a href="<?= h(url('admin/products.php')) ?>">商品</a>
        <a href="<?= h(url('admin/categories.php')) ?>">種類</a>
        <a href="<?= h(url('admin/allergens.php')) ?>">アレルギー</a>
        <a href="<?= h(url('admin/shops.php')) ?>">卸先</a>
    </div>
</nav>
<?php endif; ?>
<main class="container">
    