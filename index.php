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
    <title>Masuk — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body class="auth-body">
<main class="auth-shell">
    <section class="auth-story">
        <a class="brand brand-auth" href="<?= e(app_url()) ?>"><span class="brand-mark"><span></span><span></span></span><span class="brand-name">ToDoList</span></a>
        <div class="story-copy">
            <p class="eyebrow light"></p>
            <h1>Sistem Informasi Berbasis<br><span>"ToDoList"</span></h1>
            <p>Satukan prioritas, deadline, dan ritme kerja dalam ruang yang tenang—tanpa distraksi yang tidak perlu.</p>
        </div>
        <div class="orbit-showcase" aria-label="Contoh progress harian 72 persen">
            <div class="orbit orbit-large" style="--progress:72"><div class="orbit-inner"><small>Fokus hari ini</small><strong>72%</strong><span>6 dari 8 selesai</span></div></div>
            <div class="week-pulse"><div><span>Sen</span><i style="--level:42%"></i></div><div><span>Sel</span><i style="--level:72%"></i></div><div><span>Rab</span><i style="--level:58%"></i></div><div><span>Kam</span><i style="--level:88%"></i></div><div class="is-today"><span>Jum</span><i style="--level:64%"></i></div><div><span>Sab</span><i style="--level:22%"></i></div><div><span>Min</span><i style="--level:34%"></i></div></div>
        </div>
        <p class="story-foot">Dirancang untuk fokus pribadi dan standar kerja profesional.</p>
    </section>
    <section class="auth-panel">
        <div class="auth-form-wrap">
            <div class="auth-heading"><p class="eyebrow">Selamat datang kembali</p><h2>Masuk ke ruang kerja</h2><p>Lanjutkan dari tempat terakhir Anda berhenti.</p></div>
            <?php foreach ($flashes as $flash): ?><div class="auth-alert auth-alert-<?= e($flash['type']) ?>" role="alert"><?= icon($flash['type'] === 'error' ? 'alert' : 'check-square', 17) ?><span><?= e($flash['message']) ?></span></div><?php endforeach; ?>
            <form class="auth-form" method="post" action="<?= e(app_url('actions/login.php')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field"><label for="email">Alamat email</label><div class="input-icon"><?= icon('mail', 17) ?><input id="email" name="email" type="email" autocomplete="email" value="<?= e($old['email'] ?? '') ?>" placeholder="nama@perusahaan.com" required></div><?php if (isset($errors['email'])): ?><span class="field-error visible"><?= e($errors['email']) ?></span><?php endif; ?></div>
                <div class="field"><div class="label-row"><label for="password">Password</label></div><div class="input-icon input-password"><?= icon('lock', 17) ?><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password" required><button type="button" data-password-toggle aria-label="Tampilkan password"><?= icon('eye', 17) ?></button></div><?php if (isset($errors['password'])): ?><span class="field-error visible"><?= e($errors['password']) ?></span><?php endif; ?></div>
                <button class="button button-primary button-block button-tall" type="submit">Masuk ke ToDoList <?= icon('arrow', 17) ?></button>
            </form>
            <p class="auth-switch">Belum memiliki akun? <a href="<?= e(app_url('register.php')) ?>">Buat akun sekarang</a></p>
            <p class="auth-security"><?= icon('lock', 14) ?> Kredensial Anda dienkripsi dan tidak pernah ditampilkan.</p>
        </div>
    </section>
</main>
<script>window.Focuslist = { baseUrl: <?= json_encode(APP_BASE_URL) ?> };</script><script src="<?= e(asset_url('assets/js/app.js')) ?>" defer></script>
</body></html>
