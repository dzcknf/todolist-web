<?php
declare(strict_types=1);

const APP_NAME = 'ToDoList';
const APP_TAGLINE = 'Ruang tenang untuk pekerjaan penting.';
const APP_TIMEZONE = 'Asia/Jakarta';

date_default_timezone_set(APP_TIMEZONE);

$appRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? (realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';
$baseUrl = '';
if ($documentRoot !== '' && str_starts_with(str_replace('\\', '/', $appRoot), str_replace('\\', '/', $documentRoot))) {
    $baseUrl = str_replace('\\', '/', substr($appRoot, strlen($documentRoot)));
}
define('APP_BASE_URL', rtrim($baseUrl, '/'));

$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
