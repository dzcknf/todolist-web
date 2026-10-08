<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
if (!is_post()) redirect('dashboard.php');
verify_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
session_start();
flash('success', 'Anda telah keluar dengan aman. Sampai jumpa kembali.');
redirect('index.php');
