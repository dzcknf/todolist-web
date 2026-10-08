<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
require_auth();
if (!is_post()) redirect('profile.php');
verify_csrf();

$userId = (int) $_SESSION['user_id'];
$current = (string) ($_POST['current_password'] ?? '');
$new = (string) ($_POST['new_password'] ?? '');
$confirmation = (string) ($_POST['password_confirmation'] ?? '');

$find = db()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
$find->execute([$userId]);
$hash = $find->fetchColumn();
if (!$hash || !password_verify($current, (string) $hash)) action_response(false, 'Password saat ini tidak sesuai.', [], 'profile.php', 422);
$needs = password_errors($new);
if ($needs !== []) action_response(false, 'Password baru perlu ' . implode(', ', $needs) . '.', [], 'profile.php', 422);
if ($new !== $confirmation) action_response(false, 'Konfirmasi password baru belum sama.', [], 'profile.php', 422);
if (password_verify($new, (string) $hash)) action_response(false, 'Password baru harus berbeda dari password saat ini.', [], 'profile.php', 422);

$update = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$update->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
session_regenerate_id(true);
flash('success', 'Password berhasil diperbarui. Sesi Anda tetap aman.');
redirect('profile.php');
