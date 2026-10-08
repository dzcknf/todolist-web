<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_guest();
if (!is_post()) redirect('index.php');
verify_csrf();

$email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Masukkan alamat email yang valid.';
if ($password === '') $errors['password'] = 'Password wajib diisi.';

if ($errors === []) {
    $statement = db()->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
    $statement->execute([$email]);
    $user = $statement->fetch();
    $valid = $user && password_verify($password, (string) $user['password_hash']);
    if (!$valid) {
        $errors['email'] = 'Email atau password tidak sesuai.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $update = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $update->execute([(int) $user['id']]);
        flash('success', 'Selamat datang kembali. Ruang kerja Anda sudah siap.');
        redirect('dashboard.php');
    }
}

store_form_state($errors, ['email' => $email]);
flash('error', 'Kami belum dapat memverifikasi akun Anda. Periksa kembali data di bawah.');
redirect('index.php');
