<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
require_auth();
$userId = (int) $_SESSION['user_id'];
$user = current_user();

$statsQuery = db()->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(status = 'completed'), 0) AS completed, COALESCE(SUM(status = 'pending'), 0) AS pending, COALESCE(SUM(status = 'pending' AND deadline < NOW()), 0) AS overdue, COALESCE(SUM(status = 'completed' AND completed_at >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)), 0) AS completed_week FROM tasks WHERE user_id = ?");
$statsQuery->execute([$userId]);
$stats = $statsQuery->fetch();

$weeklyQuery = db()->prepare("SELECT DATE(completed_at) AS completed_day, COUNT(*) AS total FROM tasks WHERE user_id = ? AND status = 'completed' AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(completed_at)");
$weeklyQuery->execute([$userId]);
$weeklyRaw = $weeklyQuery->fetchAll();
$weeklyMap = [];
foreach ($weeklyRaw as $row) $weeklyMap[$row['completed_day']] = (int) $row['total'];
$days = [];
$dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = ['date' => $date, 'label' => $dayNames[(int) date('w', strtotime($date))], 'value' => $weeklyMap[$date] ?? 0, 'today' => $i === 0];
}
$weekMax = max(1, ...array_column($days, 'value'));

$upcomingQuery = db()->prepare("SELECT t.id, t.title, t.description, t.priority, t.status, t.deadline, c.name AS category_name FROM tasks t LEFT JOIN categories c ON c.id = t.category_id AND c.user_id = t.user_id WHERE t.user_id = ? AND t.status = 'pending' ORDER BY (t.deadline < NOW()) DESC, t.deadline ASC, FIELD(t.priority, 'high', 'medium', 'low') LIMIT 5");
$upcomingQuery->execute([$userId]);
$upcomingTasks = $upcomingQuery->fetchAll();

$categoryQuery = db()->prepare('SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC');
$categoryQuery->execute([$userId]);
$categories = $categoryQuery->fetchAll();

$workspaceTotal = (int) $stats['total'];
$workspaceCompleted = (int) $stats['completed'];
$workspacePending = (int) $stats['pending'];
$progress = $workspaceTotal > 0 ? (int) round(($workspaceCompleted / $workspaceTotal) * 100) : 0;
$hour = (int) date('G');
$greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
$firstName = explode(' ', trim((string) $user['name']))[0];
$months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$todayLabel = $dayNames[(int) date('w')] . ', ' . date('j') . ' ' . $months[(int) date('n')] . ' ' . date('Y');
$pageTitle = 'Ringkasan';
$activePage = 'dashboard';
require __DIR__ . '/includes/layout_top.php';
?>
<section class="welcome-row">
    <div><p class="date-line"><?= e($todayLabel) ?></p><h2><?= e($greeting) ?>, <?= e($firstName) ?>.</h2><p><?= (int) $stats['pending'] > 0 ? 'Ada ' . (int) $stats['pending'] . ' tugas aktif. Mari tentukan fokus terbaik hari ini.' : 'Ruang kerja Anda tenang. Waktu yang tepat untuk merencanakan langkah berikutnya.' ?></p></div>
    <a class="text-link" href="<?= e(app_url('tasks.php')) ?>">Lihat semua tugas <?= icon('arrow', 15) ?></a>
</section>

<section class="metric-grid" aria-label="Statistik tugas">
    <article class="metric-card"><span class="metric-icon neutral"><?= icon('check-square', 18) ?></span><div><p>Total tugas</p><strong><?= (int) $stats['total'] ?></strong><small>Seluruh ruang kerja</small></div></article>
    <article class="metric-card"><span class="metric-icon accent"><?= icon('clock', 18) ?></span><div><p>Masih aktif</p><strong><?= (int) $stats['pending'] ?></strong><small>Perlu perhatian</small></div></article>
    <article class="metric-card"><span class="metric-icon success"><?= icon('spark', 18) ?></span><div><p>Selesai pekan ini</p><strong><?= (int) $stats['completed_week'] ?></strong><small>Momentum terjaga</small></div></article>
    <article class="metric-card <?= (int) $stats['overdue'] > 0 ? 'has-risk' : '' ?>"><span class="metric-icon danger"><?= icon('alert', 18) ?></span><div><p>Melewati deadline</p><strong><?= (int) $stats['overdue'] ?></strong><small><?= (int) $stats['overdue'] > 0 ? 'Perlu ditinjau' : 'Semua terkendali' ?></small></div></article>
</section>

<section class="insight-grid">
    <article class="panel focus-panel">
        <div class="panel-heading"><div><p class="eyebrow">Focus Orbit</p><h3>Progres seluruh tugas</h3></div><span class="quiet-badge">Diperbarui langsung</span></div>
        <div class="focus-body" data-progress-root>
            <div class="orbit" data-progress-orbit style="--progress:<?= $progress ?>" role="img" aria-label="<?= $progress ?> persen tugas selesai"><div class="orbit-inner"><small>Diselesaikan</small><strong data-progress-value><?= $progress ?>%</strong><span data-progress-fraction><?= $workspaceCompleted ?> dari <?= $workspaceTotal ?> tugas</span></div></div>
            <div class="focus-summary"><p data-progress-message><?= $workspaceTotal === 0 ? 'Belum ada tugas. Tambahkan fokus pertama Anda.' : ($progress === 100 ? 'Seluruh tugas sudah selesai. Kerja yang baik.' : 'Pertahankan ritme, satu tugas dalam satu waktu.') ?></p><div class="focus-stat"><span><i class="dot success"></i><b data-progress-completed><?= $workspaceCompleted ?></b> selesai</span><span><i class="dot pending"></i><b data-progress-pending><?= $workspacePending ?></b> tersisa</span></div><button class="button button-secondary" type="button" data-task-create><?= icon('plus', 16) ?> Rencanakan Tugas</button></div>
        </div>
    </article>
    <article class="panel rhythm-panel">
        <div class="panel-heading"><div><p class="eyebrow">7 hari terakhir</p><h3>Ritme penyelesaian</h3></div><span class="rhythm-total"><?= array_sum(array_column($days, 'value')) ?> <small>tugas</small></span></div>
        <div class="rhythm-chart" role="img" aria-label="Grafik tugas selesai tujuh hari terakhir">
            <?php foreach ($days as $day): ?><div class="rhythm-day <?= $day['today'] ? 'is-today' : '' ?>"><div class="bar-track"><i style="height:<?= max($day['value'] > 0 ? 12 : 3, (int) round(($day['value'] / $weekMax) * 100)) ?>%"><span><?= $day['value'] ?></span></i></div><small><?= e($day['label']) ?></small></div><?php endforeach; ?>
        </div>
        <p class="chart-note"><?= icon('spark', 14) ?> Konsistensi kecil membentuk progres yang dapat diandalkan.</p>
    </article>
</section>

<section class="panel upcoming-panel">
    <div class="panel-heading"><div><p class="eyebrow">Urutan berikutnya</p><h3>Fokus terdekat</h3></div><a class="text-link" href="<?= e(app_url('tasks.php?status=pending&sort=deadline')) ?>">Kelola tugas <?= icon('chevron', 14) ?></a></div>
    <?php if ($upcomingTasks): ?>
        <div class="task-list compact-list"><?php foreach ($upcomingTasks as $task) require __DIR__ . '/includes/task_card.php'; ?></div>
    <?php else: ?>
        <div class="empty-state compact-empty"><div class="empty-symbol"><span></span><?= icon('check-square', 24) ?></div><div><h3>Tidak ada tugas aktif.</h3><p>Ruang kerja Anda bersih. Tambahkan fokus baru saat siap.</p></div><button class="button button-secondary" type="button" data-task-create><?= icon('plus', 16) ?> Tambah Tugas</button></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/task_modal.php'; require __DIR__ . '/includes/layout_bottom.php'; ?>
