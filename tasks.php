<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
require_auth();
$userId = (int) $_SESSION['user_id'];

$statusInput = (string) ($_GET['status'] ?? 'all');
$priorityInput = (string) ($_GET['priority'] ?? 'all');
$sortInput = (string) ($_GET['sort'] ?? 'deadline');

$status = in_array($statusInput, ['all', 'pending', 'completed'], true) ? $statusInput : 'all';
$priority = in_array($priorityInput, ['all', 'low', 'medium', 'high'], true) ? $priorityInput : 'all';
$sort = in_array($sortInput, ['deadline', 'deadline_desc', 'priority', 'newest'], true) ? $sortInput : 'deadline';
$search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
$categoryId = filter_var($_GET['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

$where = ['t.user_id = ?'];
$params = [$userId];
if ($status !== 'all') { $where[] = 't.status = ?'; $params[] = $status; }
if ($priority !== 'all') { $where[] = 't.priority = ?'; $params[] = $priority; }
if ($categoryId !== null) { $where[] = 't.category_id = ?'; $params[] = $categoryId; }
if ($search !== '') { $where[] = 't.title LIKE ?'; $params[] = '%' . $search . '%'; }
$orderMap = [
    'deadline' => "t.status ASC, t.deadline ASC",
    'deadline_desc' => "t.status ASC, t.deadline DESC",
    'priority' => "t.status ASC, FIELD(t.priority, 'high', 'medium', 'low'), t.deadline ASC",
    'newest' => "t.status ASC, t.created_at DESC",
];
$orderClause = $orderMap[$sort] ?? $orderMap['deadline'];
$sql = "SELECT t.id, t.title, t.description, t.priority, t.status, t.deadline, t.created_at, c.name AS category_name FROM tasks t LEFT JOIN categories c ON c.id = t.category_id AND c.user_id = t.user_id WHERE " . implode(' AND ', $where) . ' ORDER BY ' . $orderClause . ' LIMIT 200';
$taskQuery = db()->prepare($sql);
$taskQuery->execute($params);
$tasks = $taskQuery->fetchAll();

$categoryQuery = db()->prepare('SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC');
$categoryQuery->execute([$userId]);
$categories = $categoryQuery->fetchAll();

$countQuery = db()->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(status = 'pending'), 0) AS pending, COALESCE(SUM(status = 'completed'), 0) AS completed FROM tasks WHERE user_id = ?");
$countQuery->execute([$userId]);
$counts = $countQuery->fetch();
$totalTasks = (int) $counts['total'];
$completedTasks = (int) $counts['completed'];
$pendingTasks = (int) $counts['pending'];
$completionProgress = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;
$hasFilters = $status !== 'all' || $priority !== 'all' || $categoryId !== null || $search !== '';
$pageTitle = 'Daftar Tugas';
$activePage = 'tasks';
require __DIR__ . '/includes/layout_top.php';
?>
<section class="task-page-intro">
    <div><h2>Semua pekerjaan, satu arah.</h2><p>Temukan, prioritaskan, lalu selesaikan tanpa kehilangan konteks.</p></div>
    <div class="task-progress-summary" data-progress-root>
        <div class="mini-orbit" data-progress-orbit style="--progress:<?= $completionProgress ?>" role="img" aria-label="<?= $completionProgress ?> persen tugas selesai"><div class="mini-orbit-inner"><strong data-progress-value><?= $completionProgress ?>%</strong></div></div>
        <div class="task-progress-copy"><span>Progres seluruh tugas</span><strong data-progress-fraction><?= $completedTasks ?> dari <?= $totalTasks ?> tugas selesai</strong><small><b data-progress-pending><?= $pendingTasks ?></b> tugas masih aktif</small></div>
    </div>
</section>

<section class="task-toolbar panel">
    <form class="filter-form" method="get" action="<?= e(app_url('tasks.php')) ?>" data-filter-form>
        <div class="search-control"><?= icon('search', 17) ?><input name="search" type="search" value="<?= e($search) ?>" placeholder="Cari berdasarkan judul..." aria-label="Cari tugas" data-live-search><kbd>⌘ K</kbd></div>
        <div class="filter-divider"></div>
        <label class="select-control"><span>Status</span><select name="status" data-auto-submit><option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Semua</option><option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Belum selesai</option><option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Selesai</option></select></label>
        <label class="select-control"><span>Prioritas</span><select name="priority" data-auto-submit><option value="all" <?= $priority === 'all' ? 'selected' : '' ?>>Semua</option><option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>Tinggi</option><option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>Sedang</option><option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>Rendah</option></select></label>
        <label class="select-control"><span>Kategori</span><select name="category" data-auto-submit><option value="">Semua</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
        <label class="select-control sort-control"><span>Urutkan</span><select name="sort" data-auto-submit><option value="deadline" <?= $sort === 'deadline' ? 'selected' : '' ?>>Deadline terdekat</option><option value="deadline_desc" <?= $sort === 'deadline_desc' ? 'selected' : '' ?>>Deadline terjauh</option><option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Prioritas tertinggi</option><option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru dibuat</option></select></label>
        <button class="button button-secondary filter-submit" type="submit"><?= icon('filter', 16) ?> Terapkan</button>
    </form>
    <?php if ($hasFilters): ?><div class="active-filter-row"><span><?= count($tasks) ?> hasil ditemukan</span><a href="<?= e(app_url('tasks.php')) ?>"><?= icon('x', 14) ?> Hapus semua filter</a></div><?php endif; ?>
</section>

<section class="task-results">
    <div class="result-heading"><p><strong><?= count($tasks) ?></strong> tugas ditampilkan</p><span>Perubahan status tersimpan otomatis</span></div>
    <?php if ($tasks): ?>
        <div class="task-list"><?php foreach ($tasks as $task) require __DIR__ . '/includes/task_card.php'; ?></div>
    <?php else: ?>
        <div class="empty-state panel">
            <div class="empty-symbol"><span></span><?= icon($hasFilters ? 'search' : 'check-square', 28) ?></div>
            <div><p class="eyebrow"><?= $hasFilters ? 'Tidak ada kecocokan' : 'Ruang kerja siap' ?></p><h3><?= $hasFilters ? 'Tidak ada tugas dengan filter ini.' : 'Belum ada tugas. Tambahkan yang pertama.' ?></h3><p><?= $hasFilters ? 'Ubah kata kunci atau hapus beberapa filter untuk melihat tugas lain.' : 'Mulai dari satu hasil yang ingin Anda capai. Detail lainnya dapat ditambahkan kemudian.' ?></p></div>
            <?php if ($hasFilters): ?><a class="button button-secondary" href="<?= e(app_url('tasks.php')) ?>">Atur Ulang Filter</a><?php else: ?><button class="button button-primary" type="button" data-task-create><?= icon('plus', 16) ?> Tambah Tugas Pertama</button><?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/task_modal.php'; require __DIR__ . '/includes/layout_bottom.php'; ?>
