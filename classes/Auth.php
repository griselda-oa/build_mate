<?php

declare(strict_types=1);

namespace App;

use App\User;

/**
 * Centralized authentication service (minimal, secure, backwards-compatible)
 */
class Auth
{
    /**
     * Attempt to authenticate with email/password. Returns true on success.
     */
    public static function attempt(string $email, string $password): bool
    {
        try {
            $logPath = __DIR__ . '/../storage/logs';
            if (!is_dir($logPath)) {
                @mkdir($logPath, 0755, true);
            }
            $readableFile = $logPath . '/auth_debug_readable.log';
            $entry = sprintf("%s - ATTEMPT: email=%s\n", date('c'), $email);
            @file_put_contents($readableFile, $entry, FILE_APPEND | LOCK_EX);
            @chmod($readableFile, 0644);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        try {
            $userModel = new User();
            $user = $userModel->verifyLogin($email, $password);
        } catch (\Throwable $e) {
            error_log('Auth::attempt() - user lookup error: ' . $e->getMessage());
            try {
                $readableFile = __DIR__ . '/../storage/logs/auth_debug_readable.log';
                @file_put_contents($readableFile, sprintf("%s - ATTEMPT_ERROR: email=%s error=%s\n", date('c'), $email, $e->getMessage()), FILE_APPEND | LOCK_EX);
                @chmod($readableFile, 0644);
            } catch (\Throwable $_) {}
            return false;
        }

        if (!$user) {
            try { Security::log('failed_login', null, ['email' => $email]); } catch (\Throwable $_) {}
            try {
                $readableFile = __DIR__ . '/../storage/logs/auth_debug_readable.log';
                @file_put_contents($readableFile, sprintf("%s - ATTEMPT_RESULT: email=%s result=FAIL\n", date('c'), $email), FILE_APPEND | LOCK_EX);
                @chmod($readableFile, 0644);
            } catch (\Throwable $_) {}
            return false;
        }

        self::login($user);

        try { Security::log('login_success', (int)($user['id'] ?? 0)); } catch (\Throwable $_) {}

        try {
            $readableFile = __DIR__ . '/../storage/logs/auth_debug_readable.log';
            @file_put_contents($readableFile, sprintf("%s - ATTEMPT_RESULT: email=%s result=SUCCESS user_id=%s\n", date('c'), $email, $user['id'] ?? ''), FILE_APPEND | LOCK_EX);
            @chmod($readableFile, 0644);
        } catch (\Throwable $_) {}

        return true;
    }

    public static function login(array $user): void
    {
        // Preserve cart and wishlist from before login (if user was browsing)
        $existingCart = $_SESSION['cart'] ?? [];
        $existingWishlist = $_SESSION['wishlist'] ?? [];
        
        $sessionUser = [
            'id' => isset($user['id']) && is_numeric($user['id']) ? (int)$user['id'] : null,
            'email' => $user['email'] ?? '',
            'role' => $user['role'] ?? 'buyer',
            'name' => $user['name'] ?? null,
        ];

        $_SESSION['user'] = $sessionUser;
        $_SESSION['last_activity'] = time();
        @session_regenerate_id(true);

        if (isset($sessionUser['id'])) { $_SESSION['user_id'] = $sessionUser['id']; }
        if (!empty($sessionUser['name'])) { $_SESSION['username'] = $sessionUser['name']; }
        if (!empty($sessionUser['role'])) { $_SESSION['role'] = $sessionUser['role']; }
        
        // Restore cart and wishlist after login (preserve user's shopping progress)
        if (!empty($existingCart)) {
            $_SESSION['cart'] = $existingCart;
        }
        if (!empty($existingWishlist)) {
            $_SESSION['wishlist'] = $existingWishlist;
        }

        @session_write_close();

        try {
            $logPath = __DIR__ . '/../storage/logs';
            if (!is_dir($logPath)) { @mkdir($logPath, 0755, true); }
            $dump = [
                'ts' => date('c'),
                'session_id' => session_id(),
                'session_save_path' => ini_get('session.save_path'),
                'cookie_params' => session_get_cookie_params(),
                'cookie' => $_COOKIE,
                'session' => isset($_SESSION) ? $_SESSION : null,
            ];
            @file_put_contents($logPath . '/auth_debug.log', json_encode($dump, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            error_log('Auth::login() - failed to write auth_debug.log: ' . $e->getMessage());
        }
    }

    public static function logout(): void
    {
        // Preserve cart and other non-auth session data
        $cart = $_SESSION['cart'] ?? [];
        $wishlist = $_SESSION['wishlist'] ?? [];
        $flashMessages = $_SESSION['flash'] ?? [];
        
        // Clear all session data
        $_SESSION = [];
        
        // Restore non-auth data
        if (!empty($cart)) {
            $_SESSION['cart'] = $cart;
        }
        if (!empty($wishlist)) {
            $_SESSION['wishlist'] = $wishlist;
        }
        if (!empty($flashMessages)) {
            $_SESSION['flash'] = $flashMessages;
        }
        
        // Clear session cookie (but keep session alive for cart)
        if (isset($_COOKIE[session_name()])) {
            $cookieParams = session_get_cookie_params();
            // Don't destroy session, just clear auth data
            // Session will continue for cart persistence
        }
        
        // Only clear auth-related session vars, not the entire session
        unset($_SESSION['user']);
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['role']);
        unset($_SESSION['last_activity']);
        
        @session_write_close();
    }

    public static function user(): ?array { return $_SESSION['user'] ?? null; }

    public static function check(): bool
    {
        if (!isset($_SESSION['user'])) { return false; }
        $config = require __DIR__ . '/../settings/config.php';
        $lifetime = $config['security']['session_lifetime'] ?? 1800;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $lifetime) { self::logout(); return false; }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function hasRole(string $role): bool { $user = self::user(); return $user && ($user['role'] ?? null) === $role; }

    public static function isAdmin(): bool
    {
        // Simplified: just check if user has admin role
        return self::hasRole('admin');
    }

    public static function hashPassword(string $password): string { return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]); }

    public static function verifyPassword(string $password, string $hash): bool { return password_verify($password, $hash); }
}


