<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
require_auth();
$user = current_user();
$pageTitle = 'Pengaturan Akun';
$activePage = 'profile';
require __DIR__ . '/includes/layout_top.php';
?>
<section class="settings-intro"><div><h2>Profil & keamanan</h2><p>Kelola identitas dan kredensial untuk ruang kerja pribadi Anda.</p></div><span class="security-state"><?= icon('lock', 15) ?> Sesi terlindungi</span></section>
<div class="settings-layout">
    <aside class="settings-summary panel">
        <div class="profile-avatar-large"><?= e(user_initials((string) $user['name'])) ?><span></span></div>
        <h3><?= e($user['name']) ?></h3><p><?= e($user['email']) ?></p>
        <div class="account-meta"><span>Akun dibuat</span><strong><?= e(date('d M Y', strtotime($user['created_at']))) ?></strong></div>
        <div class="privacy-note"><?= icon('spark', 16) ?><p><strong>Data tetap privat.</strong><br>Setiap task selalu diakses bersama identitas akun Anda.</p></div>
    </aside>
    <div class="settings-forms">
        <section class="panel settings-card">
            <div class="settings-heading"><span class="settings-icon"><?= icon('user', 19) ?></span><div><h3>Informasi pribadi</h3><p>Nama ini ditampilkan di seluruh ruang kerja Anda.</p></div></div>
            <form class="settings-form" method="post" action="<?= e(app_url('actions/profile_update.php')) ?>">
                <?= csrf_field() ?>
                <div class="field"><label for="profileName">Nama lengkap</label><input id="profileName" name="name" type="text" maxlength="100" autocomplete="name" value="<?= e($user['name']) ?>" required></div>
                <div class="field"><label for="profileEmail">Alamat email</label><input id="profileEmail" name="email" type="email" maxlength="190" autocomplete="email" value="<?= e($user['email']) ?>" required><small class="field-hint">Digunakan sebagai identitas saat masuk.</small></div>
                <div class="settings-actions"><button class="button button-primary" type="submit">Simpan Perubahan</button></div>
            </form>
        </section>
        <section class="panel settings-card">
            <div class="settings-heading"><span class="settings-icon"><?= icon('lock', 19) ?></span><div><h3>Ubah password</h3><p>Gunakan password unik yang tidak dipakai di layanan lain.</p></div></div>
            <form class="settings-form" method="post" action="<?= e(app_url('actions/password_update.php')) ?>">
                <?= csrf_field() ?>
                <div class="field"><label for="currentPassword">Password saat ini</label><div class="input-password"><input id="currentPassword" name="current_password" type="password" autocomplete="current-password" required><button type="button" data-password-toggle aria-label="Tampilkan password"><?= icon('eye', 17) ?></button></div></div>
                <div class="settings-field-row"><div class="field"><label for="newPassword">Password baru</label><div class="input-password"><input id="newPassword" name="new_password" type="password" autocomplete="new-password" data-password-strength required><button type="button" data-password-toggle aria-label="Tampilkan password"><?= icon('eye', 17) ?></button></div></div><div class="field"><label for="confirmPassword">Ulangi password baru</label><div class="input-password"><input id="confirmPassword" name="password_confirmation" type="password" autocomplete="new-password" required><button type="button" data-password-toggle aria-label="Tampilkan password"><?= icon('eye', 17) ?></button></div></div></div>
                <div class="settings-actions"><p><?= icon('alert', 14) ?> Minimal 8 karakter, huruf besar, kecil, dan angka.</p><button class="button button-secondary" type="submit">Perbarui Password</button></div>
            </form>
        </section>
        <section class="panel danger-card"><div><h3>Keluar dari perangkat ini</h3><p>Sesi lokal akan diakhiri. Data Anda tetap tersimpan dengan aman.</p></div><form method="post" action="<?= e(app_url('actions/logout.php')) ?>"><?= csrf_field() ?><button class="button button-danger-quiet" type="submit"><?= icon('logout', 16) ?> Keluar dengan Aman</button></form></section>
    </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
