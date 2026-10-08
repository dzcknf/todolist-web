<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_guest();
if (!is_post()) redirect('register.php');
verify_csrf();

$name = trim((string) ($_POST['name'] ?? ''));
$email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$confirmation = (string) ($_POST['password_confirmation'] ?? '');
$errors = [];

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors['name'] = 'Nama harus terdiri dari 2–100 karakter.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors['email'] = 'Masukkan alamat email yang valid.';
$passwordNeeds = password_errors($password);
if ($passwordNeeds !== []) $errors['password'] = 'Password perlu ' . implode(', ', $passwordNeeds) . '.';
if ($password !== $confirmation) $errors['password_confirmation'] = 'Konfirmasi password belum sama.';

if (!isset($errors['email'])) {
    $check = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) $errors['email'] = 'Email ini sudah terdaftar. Gunakan email lain atau masuk.';
}

if ($errors !== []) {
    store_form_state($errors, ['name' => $name, 'email' => $email]);
    flash('error', 'Beberapa informasi perlu diperbaiki sebelum akun dibuat.');
    redirect('register.php');
}

try {
    db()->beginTransaction();
    $insert = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    $userId = (int) db()->lastInsertId();
    $category = db()->prepare('INSERT INTO categories (user_id, name) VALUES (?, ?)');
    foreach (['Pekerjaan', 'Pribadi', 'Pengembangan'] as $defaultCategory) $category->execute([$userId, $defaultCategory]);
    db()->commit();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    flash('success', 'Ruang kerja berhasil dibuat. Mulai dengan satu tugas penting.');
    redirect('dashboard.php');
} catch (PDOException $exception) {
    if (db()->inTransaction()) db()->rollBack();
    error_log('Registration failed: ' . $exception->getMessage());
    flash('error', 'Akun belum dapat dibuat. Coba kembali beberapa saat lagi.');
    redirect('register.php');
}
