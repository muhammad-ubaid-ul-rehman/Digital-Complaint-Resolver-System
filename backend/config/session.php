<?php
// ============================================================
//  DCRS — Session Manager
//  File: backend/config/session.php
// ============================================================

require_once __DIR__ . '/database.php';

class Session {

    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,   // set true on HTTPS
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    // ── Login ────────────────────────────────────────────────
    public static function login(array $user): void {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['name']      = $user['name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time']= time();
    }

    // ── Logout ───────────────────────────────────────────────
    public static function logout(): void {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ── Getters ──────────────────────────────────────────────
    public static function isLoggedIn(): bool {
        self::start();
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function userId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string {
        return $_SESSION['role'] ?? null;
    }

    public static function name(): ?string {
        return $_SESSION['name'] ?? null;
    }

    public static function get(string $key): mixed {
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    // ── Flash messages ───────────────────────────────────────
    public static function flash(string $key, string $message): void {
        self::start();
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string {
        self::start();
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    // ── Role Guards ──────────────────────────────────────────
    public static function requireLogin(): void {
        self::start();
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            header('Location: ' . APP_URL . '/unauthorized.php');
            exit;
        }
    }
}
