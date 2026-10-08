(() => {
    'use strict';

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
    const baseUrl = window.Focuslist?.baseUrl || '';
    const csrf = $('meta[name="csrf-token"]')?.content || '';

    function showToast(message, type = 'success') {
        let stack = $('.toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', 'status');
        const dot = document.createElement('span');
        dot.className = 'toast-dot';
        const copy = document.createElement('span');
        copy.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'toast-close';
        close.setAttribute('aria-label', 'Tutup');
        close.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>';
        toast.append(dot, copy, close);
        stack.appendChild(toast);
        const dismiss = () => {
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 190);
        };
        close.addEventListener('click', dismiss);
        window.setTimeout(dismiss, 4200);
    }

    $$('.toast').forEach((toast) => {
        const dismiss = () => {
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 190);
        };
        $('.toast-close', toast)?.addEventListener('click', dismiss);
        window.setTimeout(dismiss, 4800);
    });

    $$('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement?.querySelector('input');
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    $$('[data-password-strength]').forEach((input) => {
        const field = input.closest('.field');
        const meter = $('.strength-meter', field);
        const copy = $('[data-strength-copy]', field);
        input.addEventListener('input', () => {
            const value = input.value;
            let score = 0;
            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
            if (/\d/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value) || value.length >= 12) score++;
            meter?.setAttribute('data-score', String(score));
            if (copy) copy.textContent = ['', 'Masih lemah—tambahkan variasi.', 'Cukup, tetapi dapat diperkuat.', 'Password kuat.', 'Password sangat kuat.'][score];
        });
    });

    $('[data-sidebar-open]')?.addEventListener('click', () => document.body.classList.add('sidebar-open'));
    $$('[data-sidebar-close]').forEach((element) => element.addEventListener('click', () => document.body.classList.remove('sidebar-open')));

    const taskModal = $('#taskModal');
    const taskForm = taskModal ? $('.js-task-form', taskModal) : null;
    const modalTitle = taskModal ? $('#taskModalTitle', taskModal) : null;
    const submitCopy = taskModal ? $('[data-submit-text]', taskModal) : null;
    const description = taskModal ? $('textarea[name="description"]', taskModal) : null;
    const countCopy = taskModal ? $('[data-char-count]', taskModal) : null;
    let lastFocused = null;

    function setDefaultDeadline() {
        if (!taskForm) return;
        const input = $('input[name="deadline"]', taskForm);
        if (!input) return;
        const now = new Date();
        const minimum = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        input.min = minimum;
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(17, 0, 0, 0);
        input.value = new Date(tomorrow.getTime() - tomorrow.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    }

    function clearTaskErrors() {
        if (!taskForm) return;
        $$('.field-error', taskForm).forEach((error) => { error.textContent = ''; });
        $$('input, textarea', taskForm).forEach((input) => input.classList.remove('is-invalid'));
        const alert = $('.form-alert', taskForm);
        if (alert) { alert.hidden = true; alert.textContent = ''; }
    }

    function openTaskModal(task = null) {
        if (!taskModal || !taskForm) return;
        lastFocused = document.activeElement;
        taskForm.reset();
        clearTaskErrors();
        $('input[name="task_id"]', taskForm).value = task?.id || '';
        $('input[name="title"]', taskForm).value = task?.title || '';
        $('textarea[name="description"]', taskForm).value = task?.description || '';
        $('input[name="category"]', taskForm).value = task?.category || '';
        if (task?.deadline) $('input[name="deadline"]', taskForm).value = task.deadline;
        else setDefaultDeadline();
        const priority = $(`input[name="priority"][value="${task?.priority || 'medium'}"]`, taskForm);
        if (priority) priority.checked = true;
        if (modalTitle) modalTitle.textContent = task ? 'Edit tugas' : 'Tambah tugas';
        if (submitCopy) submitCopy.textContent = task ? 'Simpan Perubahan' : 'Simpan Tugas';
        if (countCopy) countCopy.textContent = String((description?.value || '').length);
        taskModal.classList.add('is-open');
        taskModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => $('input[name="title"]', taskForm)?.focus(), 100);
    }

    function closeTaskModal() {
        if (!taskModal) return;
        taskModal.classList.remove('is-open');
        taskModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (lastFocused instanceof HTMLElement) lastFocused.focus();
    }

    $$('[data-task-create]').forEach((button) => button.addEventListener('click', () => openTaskModal()));
    $$('[data-task-edit]').forEach((button) => button.addEventListener('click', () => {
        try { openTaskModal(JSON.parse(button.dataset.task || '{}')); }
        catch { showToast('Detail tugas tidak dapat dibuka.', 'error'); }
    }));
    $$('[data-modal-close]').forEach((button) => button.addEventListener('click', closeTaskModal));
    description?.addEventListener('input', () => { if (countCopy) countCopy.textContent = String(description.value.length); });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && taskModal?.classList.contains('is-open')) closeTaskModal();
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            const search = $('[data-live-search]');
            if (search) { event.preventDefault(); search.focus(); }
        }
    });

    taskForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearTaskErrors();
        const submit = $('button[type="submit"]', taskForm);
        submit?.classList.add('is-loading');
        if (submit) submit.disabled = true;
        try {
            const response = await fetch(taskForm.action, {
                method: 'POST', body: new FormData(taskForm),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrf }
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                Object.entries(data.errors || {}).forEach(([name, message]) => {
                    const error = $(`[data-error-for="${name}"]`, taskForm);
                    const input = $(`[name="${name}"]`, taskForm);
                    if (error) error.textContent = String(message);
                    input?.classList.add('is-invalid');
                });
                const alert = $('.form-alert', taskForm);
                if (alert) { alert.hidden = false; alert.textContent = data.message || 'Periksa kembali detail tugas.'; }
                return;
            }
            closeTaskModal();
            showToast(data.message);
            window.setTimeout(() => window.location.reload(), 480);
        } catch {
            const alert = $('.form-alert', taskForm);
            if (alert) { alert.hidden = false; alert.textContent = 'Koneksi terputus. Periksa server lokal lalu coba lagi.'; }
        } finally {
            submit?.classList.remove('is-loading');
            if (submit) submit.disabled = false;
        }
    });

    $$('.js-toggle-task').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const card = form.closest('.task-card');
            const button = $('.task-checkbox', form);
            card?.classList.add('is-updating');
            if (button) button.disabled = true;
            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrf } });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message);
                const complete = data.status === 'completed';
                card?.classList.toggle('is-completed', complete);
                button?.setAttribute('aria-pressed', complete ? 'true' : 'false');
                if (data.stats) {
                    $$('[data-progress-orbit]').forEach((orbit) => {
                        orbit.style.setProperty('--progress', String(data.stats.progress));
                        orbit.setAttribute('aria-label', `${data.stats.progress} persen tugas selesai`);
                    });
                    $$('[data-progress-value]').forEach((element) => { element.textContent = `${data.stats.progress}%`; });
                    $$('[data-progress-fraction]').forEach((element) => { element.textContent = `${data.stats.completed} dari ${data.stats.total} tugas selesai`; });
                    $$('[data-progress-completed]').forEach((element) => { element.textContent = String(data.stats.completed); });
                    $$('[data-progress-pending]').forEach((element) => { element.textContent = String(data.stats.pending); });
                    $$('[data-progress-message]').forEach((element) => {
                        element.textContent = data.stats.total === 0
                            ? 'Belum ada tugas. Tambahkan fokus pertama Anda.'
                            : data.stats.progress === 100
                                ? 'Seluruh tugas sudah selesai. Kerja yang baik.'
                                : 'Pertahankan ritme, satu tugas dalam satu waktu.';
                    });
                }
                showToast(`${data.message} Progres kini ${data.stats?.progress ?? 0}%.`);
                window.setTimeout(() => window.location.reload(), 1100);
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Status tugas gagal diperbarui.', 'error');
            } finally {
                card?.classList.remove('is-updating');
                if (button) button.disabled = false;
            }
        });
    });

    function confirmation(title) {
        return new Promise((resolve) => {
            const layer = document.createElement('div');
            layer.className = 'confirm-layer';
            layer.innerHTML = `<div class="confirm-backdrop"></div><section class="confirm-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle"><div class="confirm-icon"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6"/></svg></div><h3 id="confirmTitle">Hapus tugas ini?</h3><p>“${title.replace(/[<>&"']/g, '')}” akan dihapus permanen dari ruang kerja Anda.</p><div class="confirm-actions"><button class="button button-secondary" data-cancel>Batal</button><button class="button button-danger-quiet" data-confirm>Hapus Tugas</button></div></section>`;
            document.body.appendChild(layer);
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => layer.classList.add('is-open'));
            const finish = (result) => {
                layer.classList.remove('is-open');
                document.body.style.overflow = '';
                window.setTimeout(() => layer.remove(), 190);
                resolve(result);
            };
            $('[data-cancel]', layer).addEventListener('click', () => finish(false));
            $('.confirm-backdrop', layer).addEventListener('click', () => finish(false));
            $('[data-confirm]', layer).addEventListener('click', () => finish(true));
            $('[data-confirm]', layer).focus();
        });
    }

    $$('.js-delete-task').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const card = form.closest('.task-card');
            const title = $('.task-title-row h3', card)?.textContent || 'Tugas';
            if (!await confirmation(title)) return;
            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrf } });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message);
                card?.classList.add('is-removing');
                showToast(data.message);
                window.setTimeout(() => { card?.remove(); window.location.reload(); }, 360);
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Tugas gagal dihapus.', 'error');
            }
        });
    });

    const filterForm = $('[data-filter-form]');
    $$('[data-auto-submit]').forEach((select) => select.addEventListener('change', () => filterForm?.requestSubmit()));
    const liveSearch = $('[data-live-search]');
    if (liveSearch && filterForm) {
        let timer;
        liveSearch.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => filterForm.requestSubmit(), 450);
        });
    }
})();
