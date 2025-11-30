<?php

declare(strict_types=1);

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Set custom error handler for database connection errors
set_exception_handler(function ($exception) {
    if ($exception instanceof \RuntimeException && strpos($exception->getMessage(), 'Database connection failed') !== false) {
        // Show friendly database error page
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Connection Error - Build Mate</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .error-box {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    max-width: 600px;
                    width: 100%;
                    padding: 40px;
                    text-align: center;
                }
                .icon { font-size: 4rem; margin-bottom: 20px; }
                h1 { color: #333; margin-bottom: 15px; font-size: 2rem; }
                p { color: #666; line-height: 1.6; margin-bottom: 20px; }
                .btn {
                    background: #667eea;
                    color: white;
                    padding: 15px 30px;
                    border-radius: 8px;
                    text-decoration: none;
                    display: inline-block;
                    font-weight: 600;
                    margin: 10px;
                    transition: all 0.3s;
                }
                .btn:hover {
                    background: #5568d3;
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }
                .error-details {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 8px;
                    margin-top: 20px;
                    text-align: left;
                    font-size: 0.9rem;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="icon">🔌</div>
                <h1>Database Connection Failed</h1>
                <p>Your MySQL password needs to be fixed before the app can work.</p>
                <p><strong>This takes 2 minutes to fix!</strong></p>
                
                <a href="http://localhost/phpmyadmin" target="_blank" class="btn">🔧 Open phpMyAdmin to Fix</a>
                
                <div class="error-details">
                    <h3 style="color: #333; margin-bottom: 15px;">🔧 Fix in 3 Steps:</h3>
                    
                    <div style="background: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #667eea;">
                        <strong>Step 1:</strong> Click the "Open phpMyAdmin" button above
                    </div>
                    
                    <div style="background: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #667eea;">
                        <strong>Step 2:</strong> Try logging in with:
                        <ul style="margin: 10px 0 0 20px; line-height: 2;">
                            <li>Username: <code>root</code></li>
                            <li>Password: Try <code>root</code>, <code>xampp</code>, or leave <strong>blank</strong></li>
                        </ul>
                    </div>
                    
                    <div style="background: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #667eea;">
                        <strong>Step 3:</strong> Once logged in, reset password to empty and restart MySQL
                    </div>
                    
                    <p style="margin-top: 20px; color: #28a745; font-weight: 600;">
                        ✅ After fixing, refresh this page - it should work!
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    // For other errors, show normal error
    throw $exception;
});

// Check if vendor exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('<h1>Error: Composer dependencies not installed</h1><p>Please run: <code>composer install</code> in the project root directory.</p>');
}

try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Manually require new classes to ensure they're loaded (until composer dump-autoload is run)
    if (file_exists(__DIR__ . '/classes/Review.php')) {
        require_once __DIR__ . '/classes/Review.php';
    }
    if (file_exists(__DIR__ . '/classes/Waitlist.php')) {
        require_once __DIR__ . '/classes/Waitlist.php';
    }
    if (file_exists(__DIR__ . '/classes/Wishlist.php')) {
        require_once __DIR__ . '/classes/Wishlist.php';
    }
    if (file_exists(__DIR__ . '/classes/PaystackService.php')) {
        require_once __DIR__ . '/classes/PaystackService.php';
    }
    if (file_exists(__DIR__ . '/classes/EmailService.php')) {
        require_once __DIR__ . '/classes/EmailService.php';
    }
    if (file_exists(__DIR__ . '/controllers/AdminOrderController.php')) {
        require_once __DIR__ . '/controllers/AdminOrderController.php';
    }
    if (file_exists(__DIR__ . '/controllers/PremiumController.php')) {
        require_once __DIR__ . '/controllers/PremiumController.php';
    }
    if (file_exists(__DIR__ . '/controllers/AdvertisementController.php')) {
        require_once __DIR__ . '/controllers/AdvertisementController.php';
    }
    if (file_exists(__DIR__ . '/classes/Advertisement.php')) {
        require_once __DIR__ . '/classes/Advertisement.php';
    }
} catch (\Throwable $e) {
    // If Composer platform check failed (PHP version mismatch), show a friendly message
    $msg = $e->getMessage();
    if (strpos($msg, 'Composer detected issues in your platform') !== false) {
        http_response_code(500);
        $current = phpversion();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>PHP Version Error - Build Mate</title>
            <style>body{font-family:system-ui,Segoe UI,Arial,sans-serif;padding:40px;color:#333}code{background:#f6f8fa;padding:2px 6px;border-radius:4px}</style>
        </head>
        <body>
            <h1>PHP Version Mismatch</h1>
            <p>The application requires <strong>PHP >= 8.2</strong>, but the server is currently running <code><?php echo htmlspecialchars($current); ?></code>.</p>
            <p>Please upgrade your PHP installation or run the app with a PHP 8.2+ runtime. Common options:</p>
            <ul>
                <li>Install a newer <strong>XAMPP</strong> that bundles PHP 8.2+ from <a href="https://www.apachefriends.org">apachefriends.org</a>.</li>
                <li>Install <code>php@8.2</code> via Homebrew and run the built-in server:</li>
            </ul>
            <pre><code>brew update
brew install php@8.2
/opt/homebrew/opt/php@8.2/bin/php -S localhost:8000 -t /Applications/XAMPP/xamppfiles/htdocs/build_mate</code></pre>
            <p>Or run the app in Docker using an image with PHP 8.2+ (recommended for isolation).</p>
            <p>If you intentionally want to run with an older PHP version, you must reinstall Composer dependencies compatible with that version or relax platform checks (not recommended).</p>
            <p>Full error:</p>
            <pre><?php echo htmlspecialchars($msg); ?></pre>
        </body>
        </html>
        <?php
        exit;
    }

    // For other errors rethrow
    throw $e;
}

use Dotenv\Dotenv;
use App\Router;

// Load environment variables (if .env exists)
$dotenv = Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (\Exception $e) {
    // .env file not found, use defaults from config.php
}

// Start output buffering FIRST
if (ob_get_level() === 0) {
    ob_start();
}

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters BEFORE starting session
    // Use session_set_cookie_params() which must be called before session_start()
    // Determine whether we should mark session cookies as Secure.
    // Default APP_ENV to 'local' (not 'production') when unset so local dev doesn't accidentally enable Secure.
    $appEnv = $_ENV['APP_ENV'] ?? 'local';
    $isProduction = $appEnv === 'production';
    // Only set Secure if we're in production AND the current request is HTTPS.
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') ||
               (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    // Detect base path dynamically for session cookie
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $basePath = '';
    if (preg_match('#^/~[^/]+/build_mate#', $requestUri)) {
        $basePath = preg_replace('#^(/~[^/]+/build_mate).*#', '$1', $requestUri);
    } elseif (strpos($requestUri, '/build_mate') === 0) {
        $basePath = '/build_mate';
    } elseif (preg_match('#^/~[^/]+#', $requestUri)) {
        $basePath = preg_replace('#^(/~[^/]+).*#', '$1', $requestUri);
    }
    
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie (expires when browser closes)
        // Use detected base path
        'path' => $basePath ?: '/',
        'domain' => '',
        'secure' => ($isProduction && $isHttps),
        'httponly' => true,
        'samesite' => 'Lax' // Changed from Strict to Lax for redirects
    ]);
    
    // Additional session settings
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', '86400'); // 24 hours
    
    // Use project storage directory for sessions (ensure it exists and is writable)
    // Prefer `settings/core.php` session path if provided (user-created core.php)
    $sessionPath = __DIR__ . '/storage/sessions';
    $coreConfigFile = __DIR__ . '/settings/core.php';
    if (file_exists($coreConfigFile)) {
        try {
            $coreCfg = require $coreConfigFile;
            if (isset($coreCfg['session']['path']) && !empty($coreCfg['session']['path'])) {
                $sessionPath = $coreCfg['session']['path'];
            }
        } catch (\Throwable $e) {
            // Ignore faulty core.php — fall back to default
            error_log('Failed to load settings/core.php: ' . $e->getMessage());
        }
    }
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0755, true);
    }
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        ini_set('session.save_path', $sessionPath);
    }
    // If custom path fails, let PHP use default (XAMPP's temp directory)
    
    session_start();
    
    // Debug: Log session start
    $cookieParams = session_get_cookie_params();
    error_log("SESSION STARTED - ID: " . session_id() . ", Status: " . session_status() . ", Save path: " . ini_get('session.save_path') . ", Cookie path: " . $cookieParams['path']);

    // Compatibility shim: normalize legacy flat session keys into standardized $_SESSION['user']
    if (!isset($_SESSION['user'])) {
        $legacyUserId = $_SESSION['user_id'] ?? null;
        $legacyUsername = $_SESSION['username'] ?? null;
        $legacyEmail = $_SESSION['email'] ?? null;
        $legacyRole = $_SESSION['role'] ?? null;

        if ($legacyUserId || $legacyUsername) {
            // Cast numeric legacy id to int to satisfy typed callers elsewhere
            $castId = null;
            if ($legacyUserId !== null && is_numeric($legacyUserId)) {
                $castId = (int)$legacyUserId;
            }

            $_SESSION['user'] = [
                'id' => $castId,
                'name' => $legacyUsername,
                'email' => $legacyEmail,
                'role' => $legacyRole ?? 'buyer'
            ];
            $_SESSION['last_activity'] = time();
            error_log("SESSION NORMALIZE - Converted legacy session keys to \"user\" array: " . print_r($_SESSION['user'], true));
        }
    }
}

// Load routes and dispatch
try {
    require_once __DIR__ . '/settings/routes.php';
} catch (\Throwable $e) {
    die('<h1>Error Loading Routes</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>');
}

$router = Router::getInstance();
$router->dispatch();
