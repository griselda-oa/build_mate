<?php

declare(strict_types=1);

namespace App;

use App\DB;

/**
 * Rate limiting middleware
 */
class RateLimitMiddleware
{
    public function handle(): bool
    {
        $config = require __DIR__ . '/../settings/config.php';
        
        // Skip rate limiting in development/local environment
        if (($config['app_env'] ?? 'local') === 'local' || ($config['app_env'] ?? 'local') === 'development') {
            return true;
        }
        
        $maxAttempts = $config['security']['max_login_attempts'];
        $lockoutTime = $config['security']['login_lockout_time'];
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip . $_SERVER['REQUEST_URI']);
        
        // Check if locked out
        if (isset($_SESSION[$key . '_locked'])) {
            $lockedUntil = $_SESSION[$key . '_locked'];
            if (time() < $lockedUntil) {
                $remaining = $lockedUntil - time();
                http_response_code(429);
                die("Too many requests. Please try again in {$remaining} seconds.");
            } else {
                unset($_SESSION[$key . '_locked']);
                unset($_SESSION[$key . '_attempts']);
            }
        }
        
        // Track attempts
        $attempts = $_SESSION[$key . '_attempts'] ?? 0;
        
        if ($attempts >= $maxAttempts) {
            $_SESSION[$key . '_locked'] = time() + $lockoutTime;
            unset($_SESSION[$key . '_attempts']);
            http_response_code(429);
            die("Too many requests. Account locked for " . ($lockoutTime / 60) . " minutes.");
        }
        
        // Increment on POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION[$key . '_attempts'] = $attempts + 1;
        }
        
        return true;
    }
}

