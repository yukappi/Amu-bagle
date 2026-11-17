<?php
require_once __DIR__ . '/../includes/init.php';

// Ask MySQL for all shops (SQL sent from PHP)
$shops = db()->query('SELECT * FROM shops ORDER BY sort_order')->fetchAll();

$pageTitle = t('nav.about') . ' | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1><?= h(t('nav.about')) ?></h1>

<section class="about-shop">
    <h2>あむべ〜ぐる</h2>
    <p><?= h(t('about.intro')) ?></p>
    <p><?= h(t('about.address')) ?><?= h(t('about.our_address')) ?></p>
    <p><?= h(t('about.hours')) ?><?= h(t('about.our_hours')) ?></p>
</section>

<section class="shops" id="shops">
    <h2><?= h(t('about.shops')) ?></h2>
    <ul class="shop-list">
        <?php foreach ($shops as $shop): ?>
            <li class="shop-card">
                <h3><?= h(localized($shop, 'name')) ?></h3>
                <p class="shop-days"><?= h(localized($shop, 'days')) ?></p>
                <p><?= h(localized($shop, 'description')) ?></p>
                <p><?= h(t('about.address')) ?><?= h($shop['address'] ?? t('about.unknown')) ?></p>
                <p><?= h(t('about.hours')) ?><?= h($shop['hours'] ?? t('about.unknown')) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>