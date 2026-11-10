<?php
require_once __DIR__ . '/../../includes/init.php';
require_admin();

$today = date('Y-m-d');

// open or close one day
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $date   = $_GET['date'] ?? '';
    $action = $_POST['action'] ?? '';

    // only real dates from today on
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) !== false && $date >= $today) {
        $label = date('n月j日', strtotime($date));

        if ($action === 'open') {
            // orders close at 21:00 the day before
            $cutoff = date('Y-m-d', strtotime($date . ' -1 day')) . ' 21:00:00';
            $stmt = db()->prepare('INSERT IGNORE INTO open_days (open_date, cutoff_at) VALUES (?, ?)');
            $stmt->execute([$date, $cutoff]);

            // a new day sells every active bagel at first
            if ($stmt->rowCount() === 1) {
                $dayId = (int)db()->lastInsertId();
                $stmt = db()->prepare('INSERT INTO open_day_products (open_day_id, product_id)
                                       SELECT ?, id FROM products WHERE is_active = 1');
                $stmt->execute([$dayId]);
            }
            $_SESSION['flash'] = $label . 'をOPENにしました。';
        }

        if ($action === 'close') {
            // a day with orders can't be closed
            $stmt = db()->prepare('SELECT COUNT(*) FROM orders o
                                   JOIN open_days od ON od.id = o.open_day_id
                                   WHERE od.open_date = ?');
            $stmt->execute([$date]);
            if ((int)$stmt->fetchColumn() > 0) {
                $_SESSION['flash'] = $label . 'は予約が入っているので休みにできません。';
            } else {
                $stmt = db()->prepare('DELETE FROM open_days WHERE open_date = ?');
                $stmt->execute([$date]);
                $_SESSION['flash'] = $label . 'を休みにしました。';
            }
        }
    }
    header('Location: open_days.php?ym=' . substr($date, 0, 7));
    exit;
}

// which month to show, e.g. open_days.php?ym=2026-11
$ym    = $_GET['ym'] ?? '';
$first = strtotime($ym . '-01');
if (!preg_match('/^\d{4}-\d{2}$/', $ym) || $first === false) {
    $first = strtotime(date('Y-m-01'));
}
$prevYm = date('Y-m', strtotime('-1 month', $first));
$nextYm = date('Y-m', strtotime('+1 month', $first));

// open days in this month, with how many bagels are booked
$stmt = db()->prepare(
    "SELECT od.open_date, od.max_items, COALESCE(SUM(o.total_items), 0) AS booked
     FROM open_days od
     LEFT JOIN orders o ON o.open_day_id = od.id AND o.status <> 'cancelled'
     WHERE od.open_date BETWEEN ? AND ?
     GROUP BY od.id, od.open_date, od.max_items"
);
$stmt->execute([date('Y-m-01', $first), date('Y-m-t', $first)]);
$open = [];
foreach ($stmt->fetchAll() as $row) {
    $open[$row['open_date']] = $row;
}

// calendar cells: blanks before the 1st (week starts on Monday), then each day
$cells = array_fill(0, (int)date('N', $first) - 1, null);
for ($d = 1; $d <= (int)date('t', $first); $d++) {
    $cells[] = date('Y-m-', $first) . sprintf('%02d', $d);
}
while (count($cells) % 7 !== 0) {
    $cells[] = null;
}
$weeks = array_chunk($cells, 7);

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = 'OPEN日 | あむべ〜ぐる 管理画面';
require __DIR__ . '/../../includes/templates/header.php';
?>

<div class="admin-bar">
  <h1>OPEN日</h1>
  <a href="index.php">注文一覧へ</a>
</div>

<?php if ($flash): ?>
  <p class="flash"><?= h($flash) ?></p>
<?php endif; ?>

<div class="cal-nav">
  <a href="open_days.php?ym=<?= h($prevYm) ?>">‹ 前の月</a>
  <h2><?= h(date('Y年n月', $first)) ?></h2>
  <a href="open_days.php?ym=<?= h($nextYm) ?>">次の月 ›</a>
</div>

<table class="calendar">
  <thead>
    <tr><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th><th>日</th></tr>
  </thead>
  <tbody>
    <?php foreach ($weeks as $week): ?>
      <tr>
        <?php foreach ($week as $date): ?>
          <?php if ($date === null): ?>
            <td></td>
          <?php else: ?>
            <?php $day = $open[$date] ?? null; ?>
            <td class="<?= $day ? 'is-open' : 'is-closed' ?>">
              <span class="cal-date"><?= h((string)(int)substr($date, 8)) ?></span>
              <?php if ($day): ?>
                <span class="cal-booked">予約 <?= h((string)$day['booked']) ?> / <?= h((string)$day['max_items']) ?></span>
              <?php endif; ?>
              <?php if ($date >= $today): ?>
                <form method="post" action="open_days.php?date=<?= h($date) ?>">
                  <?= csrf_field() ?>
                  <?php if ($day): ?>
                    <button type="submit" name="action" value="close">休みにする</button>
                  <?php else: ?>
                    <button type="submit" name="action" value="open">OPENにする</button>
                  <?php endif; ?>
                </form>
              <?php endif; ?>
            </td>
          <?php endif; ?>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../../includes/templates/footer.php'; ?>