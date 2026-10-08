<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_auth();
if (!is_post()) redirect('tasks.php');
verify_csrf();

$userId = (int) $_SESSION['user_id'];
$taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$taskId) action_response(false, 'Tugas tidak valid.', [], 'tasks.php', 422);

$find = db()->prepare('SELECT status FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
$find->execute([(int) $taskId, $userId]);
$task = $find->fetch();
if (!$task) action_response(false, 'Tugas tidak ditemukan atau bukan milik akun Anda.', [], 'tasks.php', 404);

$newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
$completedAt = $newStatus === 'completed' ? date('Y-m-d H:i:s') : null;
$update = db()->prepare('UPDATE tasks SET status = ?, completed_at = ? WHERE id = ? AND user_id = ?');
$update->execute([$newStatus, $completedAt, (int) $taskId, $userId]);

$statsQuery = db()->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(status = 'completed'), 0) AS completed, COALESCE(SUM(status = 'pending'), 0) AS pending FROM tasks WHERE user_id = ?");
$statsQuery->execute([$userId]);
$stats = $statsQuery->fetch();
$total = (int) $stats['total'];
$completed = (int) $stats['completed'];
$pending = (int) $stats['pending'];
$progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

$message = $newStatus === 'completed' ? 'Tugas ditandai selesai.' : 'Tugas dikembalikan ke daftar aktif.';
action_response(true, $message, [], 'tasks.php', 200, [
    'status' => $newStatus,
    'status_label' => status_label($newStatus),
    'stats' => [
        'total' => $total,
        'completed' => $completed,
        'pending' => $pending,
        'progress' => $progress,
    ],
]);
