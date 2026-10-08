        </main>
    </div>
</div>
<nav class="mobile-nav" aria-label="Navigasi seluler">
    <a class="<?= $activePage === 'dashboard' ? 'is-active' : '' ?>" href="<?= e(app_url('dashboard.php')) ?>"><?= icon('grid', 19) ?><span>Ringkasan</span></a>
    <a class="<?= $activePage === 'tasks' ? 'is-active' : '' ?>" href="<?= e(app_url('tasks.php')) ?>"><?= icon('check-square', 19) ?><span>Tugas</span></a>
    <?php if (in_array($activePage, ['dashboard', 'tasks'], true)): ?>
        <button type="button" data-task-create class="mobile-add" aria-label="Tambah tugas"><?= icon('plus', 21) ?></button>
    <?php else: ?>
        <span class="mobile-nav-spacer" aria-hidden="true"></span>
    <?php endif; ?>
    <a class="<?= $activePage === 'profile' ? 'is-active' : '' ?>" href="<?= e(app_url('profile.php')) ?>"><?= icon('settings', 19) ?><span>Pengaturan</span></a>
    <form method="post" action="<?= e(app_url('actions/logout.php')) ?>"><?= csrf_field() ?><button type="submit"><?= icon('logout', 19) ?><span>Keluar</span></button></form>
</nav>
<script>window.Focuslist = { baseUrl: <?= json_encode(APP_BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?> };</script>
<script src="<?= e(asset_url('assets/js/app.js')) ?>" defer></script>
</body>
</html>
