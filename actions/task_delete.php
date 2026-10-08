<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_auth();
if (!is_post()) redirect('tasks.php');
verify_csrf();

$userId = (int) $_SESSION['user_id'];
$taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$taskId) action_response(false, 'Tugas tidak valid.', [], 'tasks.php', 422);

$delete = db()->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$delete->execute([(int) $taskId, $userId]);
if ($delete->rowCount() === 0) action_response(false, 'Tugas tidak ditemukan atau bukan milik akun Anda.', [], 'tasks.php', 404);
action_response(true, 'Tugas dihapus dari ruang kerja.', [], 'tasks.php');
