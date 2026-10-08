<?php
declare(strict_types=1);
$state = deadline_state($task['deadline'], $task['status']);
$isCompleted = $task['status'] === 'completed';
?>
<article class="task-card <?= $isCompleted ? 'is-completed' : '' ?> <?= $state !== 'normal' ? 'is-' . e($state) : '' ?>" data-task-id="<?= (int) $task['id'] ?>">
    <form class="task-check-form js-toggle-task" method="post" action="<?= e(app_url('actions/task_toggle.php')) ?>">
        <?= csrf_field() ?><input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
        <button class="task-checkbox" type="submit" aria-label="<?= $isCompleted ? 'Tandai belum selesai' : 'Tandai selesai' ?>" aria-pressed="<?= $isCompleted ? 'true' : 'false' ?>"><span><?= icon('check-square', 15) ?></span></button>
    </form>
    <div class="task-content">
        <div class="task-title-row"><h3><?= e($task['title']) ?></h3><?php if ($state === 'overdue'): ?><span class="deadline-flag overdue">Terlambat</span><?php elseif ($state === 'soon'): ?><span class="deadline-flag soon">&lt; 24 jam</span><?php endif; ?></div>
        <?php if (!empty($task['description'])): ?><p class="task-description"><?= e($task['description']) ?></p><?php endif; ?>
        <div class="task-meta">
            <span class="category-pill"><i></i><?= e($task['category_name'] ?? 'Tanpa kategori') ?></span>
            <span class="priority-pill priority-<?= e($task['priority']) ?>"><i></i><?= e(priority_label($task['priority'])) ?></span>
            <span class="deadline-copy <?= $state !== 'normal' ? e($state) : '' ?>"><?= icon('calendar', 14) ?><?= e(format_deadline($task['deadline'])) ?></span>
        </div>
    </div>
    <div class="task-actions">
        <button class="icon-button task-edit" type="button" data-task-edit data-task="<?= e(task_json($task)) ?>" aria-label="Edit <?= e($task['title']) ?>" title="Edit tugas"><?= icon('edit', 16) ?></button>
        <form class="js-delete-task" method="post" action="<?= e(app_url('actions/task_delete.php')) ?>">
            <?= csrf_field() ?><input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
            <button class="icon-button task-delete" type="submit" aria-label="Hapus <?= e($task['title']) ?>" title="Hapus tugas"><?= icon('trash', 16) ?></button>
        </form>
    </div>
</article>
