<?php
// Common header for every page
// Before including this file, set $pageTitle on the page
// how many bagels are in the cart (for the badge)
$cartCount = array_sum($_SESSION['cart']['items'] ?? []);
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
<div class="deco" aria-hidden="true">
    <img class="deco-whitecat" src="<?= h(url('assets/img/whitecat.webp')) ?>" alt="">
    <img class="deco-mentai" src="<?= h(url('assets/img/mentai.webp')) ?>" alt="">
    <img class="deco-browncat" src="<?= h(url('assets/img/browncat.webp')) ?>" alt="">
    <img class="deco-navycat" src="<?= h(url('assets/img/navycat.webp')) ?>" alt="">
    <img class="deco-oreo" src="<?= h(url('assets/img/oreo.webp')) ?>" alt="">
    <img class="deco-bike" src="<?= h(url('assets/img/bike.webp')) ?>" alt="">
</div>
    
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= h(url('index.php')) ?>">
            <img src="<?= h(url('assets/img/logo.png')) ?>" alt="" width="640" height="409">
            あむべ〜ぐる
        </a>
        <nav class="site-nav">
            <ul>
                <li><a href="<?= h(url('index.php')) ?>"<?= current_page('index.php') ?>><?= h(t('nav.top')) ?></a></li>
                <li><a href="<?= h(url('about.php')) ?>"<?= current_page('about.php') ?>><?= h(t('nav.about')) ?></a></li>
                <li><a href="<?= h(url('menu.php')) ?>"<?= current_page('menu.php') ?>><?= h(t('nav.menu')) ?></a></li>
                <li><a class="cart-link" href="<?= h(url('cart.php')) ?>"<?= current_page('cart.php') ?>><?= h(t('nav.cart')) ?><?php if ($cartCount > 0): ?> <span class="badge"><?= h((string)$cartCount) ?></span><?php endif; ?></a></li>
                <li><a href="<?= h(url('admin/index.php')) ?>"><?= h(t('nav.admin')) ?></a></li>
                <li><?php if (current_lang() === 'en'): ?>
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

