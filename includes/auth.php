<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * Admin session + authentication helpers.
 */

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('arail_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_admin(): ?array
{
    admin_session_start();
    return $_SESSION['admin'] ?? null;
}

/** Redirects to the login page when not authenticated. */
function require_admin(): array
{
    $admin = current_admin();
    if ($admin === null) {
        redirect('login.php');
    }
    return $admin;
}

function attempt_admin_login(string $username, string $password): bool
{
    admin_session_start();

    // Very small brute-force guard: max 10 failed tries per 5 minutes.
    $now      = time();
    $attempts = array_values(array_filter(
        $_SESSION['login_attempts'] ?? [],
        static fn(int $t): bool => $t > $now - 300
    ));
    if (count($attempts) >= 10) {
        return false;
    }

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string)$user['password_hash'])) {
        $attempts[] = $now;
        $_SESSION['login_attempts'] = $attempts;
        return false;
    }

    session_regenerate_id(true);
    unset($_SESSION['login_attempts']);
    $_SESSION['admin'] = [
        'id'       => (int)$user['id'],
        'username' => (string)$user['username'],
        'name'     => $user['display_name'] !== '' ? (string)$user['display_name'] : (string)$user['username'],
        'role'     => (string)$user['role'],
    ];

    db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')
        ->execute([(int)$user['id']]);

    return true;
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}
