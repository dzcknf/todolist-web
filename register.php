<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
require_guest();
$flashes = pull_flash();
$errors = pull_form_errors();
$old = pull_form_old();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#f6f7f9">
    <title>Buat Akun — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body class="auth-body">
<main class="auth-shell">
    <section class="auth-story auth-story-register">
        <a class="brand brand-auth" href="<?= e(app_url()) ?>"><span class="brand-mark"><span></span><span></span></span><span class="brand-name">ToDoList</span></a>
        <div class="story-copy"><p class="eyebrow light">Mulai dengan arah yang jelas</p><h1>Satu ruang kerja.<br><span>Lebih sedikit beban pikiran.</span></h1><p>Bangun sistem kerja yang dapat dipercaya—dari tugas pertama hingga progres yang terukur.</p></div>
        <div class="register-proof"><div class="proof-line"><span><?= icon('check-square', 17) ?></span><p><strong>Prioritas yang selalu terlihat</strong><small>Ketahui apa yang perlu dikerjakan berikutnya.</small></p></div><div class="proof-line"><span><?= icon('calendar', 17) ?></span><p><strong>Deadline tanpa kejutan</strong><small>Dapatkan sinyal dini sebelum pekerjaan terlambat.</small></p></div><div class="proof-line"><span><?= icon('spark', 17) ?></span><p><strong>Progres yang terasa nyata</strong><small>Pantau ritme tujuh hari secara sederhana.</small></p></div></div>
        <p class="story-foot">Data setiap akun dipisahkan dan diamankan di tingkat query.</p>
    </section>
    <section class="auth-panel">
        <div class="auth-form-wrap register-form-wrap">
            <div class="auth-heading"><p class="eyebrow">Akun baru</p><h2>Bangun ruang kerja Anda</h2><p>Gratis untuk penggunaan lokal. Selesai dalam satu menit.</p></div>
            <?php foreach ($flashes as $flash): ?><div class="auth-alert auth-alert-<?= e($flash['type']) ?>" role="alert"><?= icon('alert', 17) ?><span><?= e($flash['message']) ?></span></div><?php endforeach; ?>
            <form class="auth-form" method="post" action="<?= e(app_url('actions/register.php')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" type="text" autocomplete="name" maxlength="100" value="<?= e($old['name'] ?? '') ?>" placeholder="Nama Anda" required><?php if (isset($errors['name'])): ?><span class="field-error visible"><?= e($errors['name']) ?></span><?php endif; ?></div>
                <div class="field"><label for="email">Alamat email</label><input id="email" name="email" type="email" autocomplete="email" maxlength="190" value="<?= e($old['email'] ?? '') ?>" placeholder="nama@perusahaan.com" required><?php if (isset($errors['email'])): ?><span class="field-error visible"><?= e($errors['email']) ?></span><?php endif; ?></div>
                <div class="field"><label for="password">Password</label><div class="input-password"><input id="password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" data-password-strength required><button type="button" data-password-toggle aria-label="Tampilkan password"><?= icon('eye', 17) ?></button></div><div class="strength-meter" aria-hidden="true"><i></i><i></i><i></i><i></i></div><small class="field-hint" data-strength-copy>Gunakan huruf besar, huruf kecil, dan angka.</small><?php if (isset($errors['password'])): ?><span class="field-error visible"><?= e($errors['password']) ?></span><?php endif; ?></div>
                <div class="field"><label for="password_confirmation">Konfirmasi password</label><div class="input-password"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi password" required><button type="button" data-password-toggle aria-label="Tampilkan password"><?= icon('eye', 17) ?></button></div><?php if (isset($errors['password_confirmation'])): ?><span class="field-error visible"><?= e($errors['password_confirmation']) ?></span><?php endif; ?></div>
                <button class="button button-primary button-block button-tall" type="submit">Buat Ruang Kerja <?= icon('arrow', 17) ?></button>
            </form>
            <p class="auth-switch">Sudah memiliki akun? <a href="<?= e(app_url('index.php')) ?>">Masuk di sini</a></p>
        </div>
    </section>
</main>
<script>window.Focuslist = { baseUrl: <?= json_encode(APP_BASE_URL) ?> };</script><script src="<?= e(asset_url('assets/js/app.js')) ?>" defer></script>
</body></html>
