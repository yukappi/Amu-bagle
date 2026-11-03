<?php
require_once __DIR__ . '/../includes/init.php';

// Ask MySQL for all shops (SQL sent from PHP)
$shops = db()->query('SELECT * FROM shops ORDER BY sort_order')->fetchAll();

$pageTitle = 'お店紹介 | あむべ〜ぐる';
require __DIR__ . '/../includes/templates/header.php';
?>

<h1>お店紹介</h1>

<section class="about-shop">
    <h2>あむべ〜ぐる</h2>
    <p>福岡県中間市の小さなベーグル屋さんです。OPEN日は不定期で、ほぼ週1回です。</p>
    <p>住所：福岡県中間市鍋山町2-14</p>
    <p>営業時間：11:00〜16:00（OPEN日のみ）</p>
</section>

<section class="shops">
    <h2>ベーグルが買えるお店</h2>
    <ul class="shop-list">
        <?php foreach ($shops as $shop): ?>
            <li class="shop-card">
                <h3><?= h($shop['name_ja']) ?></h3>
                <p class="shop-days"><?= h($shop['days_ja']) ?></p>
                <p><?= h($shop['description_ja']) ?></p>
                <p>住所：<?= h($shop['address'] ?? '確認中') ?></p>
                <p>営業時間：<?= h($shop['hours'] ?? '確認中') ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>