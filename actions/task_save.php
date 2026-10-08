<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_auth();
if (!is_post()) redirect('tasks.php');
verify_csrf();

$userId = (int) $_SESSION['user_id'];
$taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
[$errors, $data] = validate_task_input($_POST);
if ($errors !== []) action_response(false, 'Periksa kembali detail tugas yang ditandai.', $errors, 'tasks.php', 422);

try {
    db()->beginTransaction();
    $categoryId = find_or_create_category($userId, $data['category']);

    if ($taskId !== null) {
        $owned = db()->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
        $owned->execute([$taskId, $userId]);
        if (!$owned->fetch()) {
            db()->rollBack();
            action_response(false, 'Tugas tidak ditemukan atau bukan milik akun Anda.', [], 'tasks.php', 404);
        }
        $update = db()->prepare('UPDATE tasks SET title = ?, description = ?, category_id = ?, priority = ?, deadline = ? WHERE id = ? AND user_id = ?');
        $update->execute([$data['title'], $data['description'] ?: null, $categoryId, $data['priority'], $data['deadline'], $taskId, $userId]);
        $message = 'Perubahan tugas berhasil disimpan.';
    } else {
        $insert = db()->prepare('INSERT INTO tasks (user_id, category_id, title, description, priority, deadline) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute([$userId, $categoryId, $data['title'], $data['description'] ?: null, $data['priority'], $data['deadline']]);
        $taskId = (int) db()->lastInsertId();
        $message = 'Tugas baru ditambahkan ke ruang kerja.';
    }

    db()->commit();
    action_response(true, $message, [], 'tasks.php', 200, ['task_id' => $taskId]);
} catch (PDOException $exception) {
    if (db()->inTransaction()) db()->rollBack();
    error_log('Task save failed: ' . $exception->getMessage());
    action_response(false, 'Tugas belum dapat disimpan. Coba kembali beberapa saat lagi.', [], 'tasks.php', 500);
}
