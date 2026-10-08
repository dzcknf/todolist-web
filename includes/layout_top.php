<?php
declare(strict_types=1);

$user = current_user();
$pageTitle = $pageTitle ?? 'Ruang kerja';
$activePage = $activePage ?? '';
$flashes = pull_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f9">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true"><span></span><span></span></span>
            <span class="brand-name">ToDoList</span>
        </div>
        <nav class="sidebar-nav" aria-label="Navigasi utama">
            <p class="nav-label">Ruang kerja</p>
            <a class="nav-item <?= $activePage === 'dashboard' ? 'is-active' : '' ?>" href="<?= e(app_url('dashboard.php')) ?>"><?= icon('grid') ?><span>Ringkasan</span></a>
            <a class="nav-item <?= $activePage === 'tasks' ? 'is-active' : '' ?>" href="<?= e(app_url('tasks.php')) ?>"><?= icon('check-square') ?><span>Daftar Tugas</span></a>
            <p class="nav-label nav-label-spaced">Akun</p>
            <a class="nav-item <?= $activePage === 'profile' ? 'is-active' : '' ?>" href="<?= e(app_url('profile.php')) ?>"><?= icon('settings') ?><span>Pengaturan</span></a>
        </nav>
        <div class="sidebar-foot">
            <div class="sidebar-insight">
                <span class="insight-icon"><?= icon('spark', 16) ?></span>
                <p><strong>Jaga ritme.</strong><br>Fokus pada satu hal penting dalam satu waktu.</p>
            </div>
            <div class="user-chip">
                <span class="avatar"><?= e(user_initials((string) $user['name'])) ?></span>
                <span class="user-chip-copy"><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?></small></span>
                <form class="logout-form" method="post" action="<?= e(app_url('actions/logout.php')) ?>">
                    <?= csrf_field() ?>
                    <button class="icon-button" type="submit" title="Keluar" aria-label="Keluar"><?= icon('logout', 17) ?></button>
                </form>
            </div>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-close></div>
    <div class="workspace">
        <header class="topbar">
            <div class="topbar-title">
                <button class="icon-button mobile-menu" type="button" data-sidebar-open aria-label="Buka navigasi"><?= icon('menu', 20) ?></button>
                <div><p class="eyebrow">Workspace pribadi</p><h1><?= e($pageTitle) ?></h1></div>
            </div>
            <?php if (in_array($activePage, ['dashboard', 'tasks'], true)): ?>
                <button class="button button-primary" type="button" data-task-create><?= icon('plus', 17) ?><span>Tambah Tugas</span></button>
            <?php endif; ?>
        </header>
        <main class="main-content">
            <div class="toast-stack" aria-live="polite">
                <?php foreach ($flashes as $flash): ?>
                    <div class="toast toast-<?= e($flash['type']) ?>" role="status">
                        <span class="toast-dot"></span><span><?= e($flash['message']) ?></span>
                        <button type="button" class="toast-close" aria-label="Tutup"><?= icon('x', 15) ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
