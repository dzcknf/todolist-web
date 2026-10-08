<?php
declare(strict_types=1);

function app_url(string $path = ''): string
{
    return APP_BASE_URL . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function asset_url(string $path): string
{
    $cleanPath = ltrim($path, '/');
    $fullPath = dirname(__DIR__) . '/' . $cleanPath;
    $version = is_file($fullPath) ? (string) filemtime($fullPath) : '1';
    return app_url($cleanPath) . '?v=' . rawurlencode($version);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        action_response(false, 'Sesi formulir berakhir. Muat ulang halaman lalu coba lagi.', [], 'tasks.php', 419);
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($messages) ? $messages : [];
}

function store_form_state(array $errors, array $old): void
{
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = $old;
}

function pull_form_errors(): array
{
    $errors = $_SESSION['form_errors'] ?? [];
    unset($_SESSION['form_errors']);
    return is_array($errors) ? $errors : [];
}

function pull_form_old(): array
{
    $old = $_SESSION['form_old'] ?? [];
    unset($_SESSION['form_old']);
    return is_array($old) ? $old : [];
}

function wants_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_contains($accept, 'application/json') || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function action_response(bool $success, string $message, array $errors = [], string $fallback = 'dashboard.php', int $status = 200, array $extra = []): never
{
    if (wants_json()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message, 'errors' => $errors], $extra), JSON_UNESCAPED_UNICODE);
        exit;
    }

    flash($success ? 'success' : 'error', $message);
    if (!$success && $errors !== []) {
        store_form_state($errors, $_POST);
    }
    redirect($fallback);
}

function user_initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials !== '' ? $initials : 'U';
}

function password_errors(string $password): array
{
    $errors = [];
    if (mb_strlen($password) < 8) $errors[] = 'minimal 8 karakter';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'satu huruf besar';
    if (!preg_match('/[a-z]/', $password)) $errors[] = 'satu huruf kecil';
    if (!preg_match('/\d/', $password)) $errors[] = 'satu angka';
    return $errors;
}

function validate_task_input(array $input): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));
    $priority = (string) ($input['priority'] ?? 'medium');
    $deadlineRaw = trim((string) ($input['deadline'] ?? ''));
    $errors = [];

    if ($title === '') $errors['title'] = 'Judul tugas wajib diisi.';
    elseif (mb_strlen($title) > 160) $errors['title'] = 'Judul maksimal 160 karakter.';
    if (mb_strlen($description) > 2000) $errors['description'] = 'Deskripsi maksimal 2.000 karakter.';
    if ($category === '') $errors['category'] = 'Pilih atau tulis kategori.';
    elseif (mb_strlen($category) > 60) $errors['category'] = 'Nama kategori maksimal 60 karakter.';
    if (!in_array($priority, ['low', 'medium', 'high'], true)) $errors['priority'] = 'Prioritas tidak valid.';

    $deadline = null;
    if ($deadlineRaw !== '') {
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $deadlineRaw);
        $dateErrors = DateTime::getLastErrors();
        if (!$date || (is_array($dateErrors) && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            $errors['deadline'] = 'Format deadline tidak valid.';
        } else {
            $deadline = $date->format('Y-m-d H:i:s');
        }
    } else {
        $errors['deadline'] = 'Tentukan deadline agar tugas dapat diprioritaskan.';
    }

    return [$errors, [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'priority' => $priority,
        'deadline' => $deadline,
    ]];
}

function find_or_create_category(int $userId, string $name): int
{
    $find = db()->prepare('SELECT id FROM categories WHERE user_id = ? AND name = ? LIMIT 1');
    $find->execute([$userId, $name]);
    $existing = $find->fetchColumn();
    if ($existing !== false) return (int) $existing;

    try {
        $insert = db()->prepare('INSERT INTO categories (user_id, name) VALUES (?, ?)');
        $insert->execute([$userId, $name]);
        return (int) db()->lastInsertId();
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() !== '23000') throw $exception;
        $find->execute([$userId, $name]);
        return (int) $find->fetchColumn();
    }
}

function format_deadline(?string $deadline): string
{
    if (!$deadline) return 'Tanpa deadline';
    $date = new DateTime($deadline);
    $today = new DateTime('today');
    $tomorrow = new DateTime('tomorrow');
    if ($date->format('Y-m-d') === $today->format('Y-m-d')) return 'Hari ini, ' . $date->format('H:i');
    if ($date->format('Y-m-d') === $tomorrow->format('Y-m-d')) return 'Besok, ' . $date->format('H:i');
    return $date->format('d M Y, H:i');
}

function deadline_state(?string $deadline, string $status): string
{
    if (!$deadline || $status === 'completed') return 'normal';
    $timestamp = strtotime($deadline);
    if ($timestamp < time()) return 'overdue';
    if ($timestamp <= time() + 86400) return 'soon';
    return 'normal';
}

function priority_label(string $priority): string
{
    return ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'][$priority] ?? 'Sedang';
}

function status_label(string $status): string
{
    return $status === 'completed' ? 'Selesai' : 'Belum selesai';
}

function task_json(array $task): string
{
    return (string) json_encode([
        'id' => (int) $task['id'],
        'title' => $task['title'],
        'description' => $task['description'] ?? '',
        'category' => $task['category_name'] ?? '',
        'priority' => $task['priority'],
        'deadline' => $task['deadline'] ? date('Y-m-d\TH:i', strtotime($task['deadline'])) : '',
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}

function icon(string $name, int $size = 18): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'check-square' => '<path d="m9 11 2 2 4-4"/><rect x="3" y="3" width="18" height="18" rx="3"/>',
        'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'more' => '<circle cx="5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1" fill="currentColor" stroke="none"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v5M14 11v5"/>',
        'x' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3M15 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6v-.2h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>',
        'chevron' => '<path d="m9 18 6-6-6-6"/>',
        'spark' => '<path d="m12 3 1.4 4.3L18 9l-4.6 1.7L12 15l-1.4-4.3L6 9l4.6-1.7Z"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
        'alert' => '<path d="M12 9v4M12 17h.01"/><path d="M10.3 3.7 2.6 18a2 2 0 0 0 1.8 3h15.2a2 2 0 0 0 1.8-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    ];
    $content = $paths[$name] ?? $paths['spark'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $content . '</svg>';
}
