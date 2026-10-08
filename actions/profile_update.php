<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_auth();
if (!is_post()) redirect('profile.php');
verify_csrf();

$userId = (int) $_SESSION['user_id'];
$name = trim((string) ($_POST['name'] ?? ''));
$email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) action_response(false, 'Nama harus terdiri dari 2–100 karakter.', [], 'profile.php', 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) action_response(false, 'Masukkan alamat email yang valid.', [], 'profile.php', 422);

$duplicate = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
$duplicate->execute([$email, $userId]);
if ($duplicate->fetch()) action_response(false, 'Email tersebut sudah digunakan oleh akun lain.', [], 'profile.php', 409);

$update = db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
$update->execute([$name, $email, $userId]);
flash('success', 'Informasi profil berhasil diperbarui.');
redirect('profile.php');
