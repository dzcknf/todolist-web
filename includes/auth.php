<?php
declare(strict_types=1);

function is_authenticated(): bool
{
    return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
}

function require_auth(): void
{
    if (!is_authenticated()) {
        flash('error', 'Masuk terlebih dahulu untuk membuka ruang kerja Anda.');
        redirect('index.php');
    }
}

function require_guest(): void
{
    if (is_authenticated()) {
        redirect('dashboard.php');
    }
}

function current_user(): ?array
{
    static $user = false;

    if (!is_authenticated()) {
        return null;
    }

    if ($user !== false) {
        return $user ?: null;
    }

    $statement = db()->prepare('SELECT id, name, email, created_at FROM users WHERE id = ? LIMIT 1');
    $statement->execute([$_SESSION['user_id']]);
    $user = $statement->fetch() ?: null;

    if ($user === null) {
        $_SESSION = [];
        session_destroy();
    }

    return $user;
}
