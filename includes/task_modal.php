<?php
declare(strict_types=1);
$categories = $categories ?? [];
?>
<div class="modal" id="taskModal" aria-hidden="true">
    <div class="modal-backdrop" data-modal-close></div>
    <section class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle">
        <header class="modal-header">
            <div><p class="eyebrow">Atur fokus berikutnya</p><h2 id="taskModalTitle">Tambah tugas</h2></div>
            <button class="icon-button" type="button" data-modal-close aria-label="Tutup modal"><?= icon('x', 19) ?></button>
        </header>
        <form class="task-form js-task-form" method="post" action="<?= e(app_url('actions/task_save.php')) ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="task_id" value="">
            <div class="field field-full">
                <label for="taskTitle">Judul tugas</label>
                <input id="taskTitle" name="title" type="text" maxlength="160" placeholder="Contoh: Finalisasi proposal kuartal dua" required autofocus>
                <span class="field-error" data-error-for="title"></span>
            </div>
            <div class="field field-full">
                <label for="taskDescription">Catatan <span>Opsional</span></label>
                <textarea id="taskDescription" name="description" rows="4" maxlength="2000" placeholder="Tambahkan konteks agar tugas mudah dilanjutkan."></textarea>
                <div class="field-meta"><span class="field-error" data-error-for="description"></span><small><span data-char-count>0</span>/2000</small></div>
            </div>
            <div class="field">
                <label for="taskCategory">Kategori</label>
                <input id="taskCategory" name="category" type="text" list="categoryOptions" maxlength="60" placeholder="Mis. Pekerjaan" required>
                <datalist id="categoryOptions">
                    <?php foreach ($categories as $category): ?><option value="<?= e($category['name']) ?>"></option><?php endforeach; ?>
                </datalist>
                <span class="field-error" data-error-for="category"></span>
            </div>
            <div class="field">
                <label for="taskDeadline">Deadline</label>
                <input id="taskDeadline" name="deadline" type="datetime-local" required>
                <span class="field-error" data-error-for="deadline"></span>
            </div>
            <fieldset class="field field-full priority-picker">
                <legend>Prioritas</legend>
                <label><input type="radio" name="priority" value="low"><span><i class="priority-dot low"></i>Rendah</span></label>
                <label><input type="radio" name="priority" value="medium" checked><span><i class="priority-dot medium"></i>Sedang</span></label>
                <label><input type="radio" name="priority" value="high"><span><i class="priority-dot high"></i>Tinggi</span></label>
                <span class="field-error" data-error-for="priority"></span>
            </fieldset>
            <div class="form-alert" hidden></div>
            <footer class="modal-actions">
                <button class="button button-secondary" type="button" data-modal-close>Batal</button>
                <button class="button button-primary" type="submit"><span data-submit-text>Simpan Tugas</span><span class="button-loader" aria-hidden="true"></span></button>
            </footer>
        </form>
    </section>
</div>
