<?php

declare(strict_types=1);

return [
    'app_name' => $_ENV['APP_NAME'] ?? 'Build Mate Ghana',
    'app_env' => $_ENV['APP_ENV'] ?? 'local',
    'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost/build_mate',
    
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306), // Default MySQL port (3306 for server, 3307 for XAMPP)
        'name' => $_ENV['DB_NAME'] ?? 'buildmate_db',
        'user' => $_ENV['DB_USER'] ?? 'griselda.owusu', // Server username
        'pass' => $_ENV['DB_PASS'] ?? '', // Will be set in .env
    ],
    
    'payment' => [
        'mode' => $_ENV['PAYMENT_MODE'] ?? 'mock',
        'paystack_public_key' => $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '',
        'paystack_secret_key' => $_ENV['PAYSTACK_SECRET_KEY'] ?? '',
        'flutterwave_public_key' => $_ENV['FLUTTERWAVE_PUBLIC_KEY'] ?? '',
        'flutterwave_secret_key' => $_ENV['FLUTTERWAVE_SECRET_KEY'] ?? '',
    ],
    
    'currency' => [
        'default' => $_ENV['DEFAULT_CURRENCY'] ?? 'GHS',
        'usd_to_ghs_rate' => (float)($_ENV['USD_TO_GHS_RATE'] ?? 12.5),
    ],
    
    'security' => [
        'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 1800),
        'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
        'login_lockout_time' => (int)($_ENV['LOGIN_LOCKOUT_TIME'] ?? 900),
        'admin_email' => $_ENV['ADMIN_EMAIL'] ?? 'admin@buildmate.com',
    ],
    
    'uploads' => [
        'max_size' => (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880),
        'allowed_types' => explode(',', $_ENV['ALLOWED_UPLOAD_TYPES'] ?? 'jpg,jpeg,png,pdf'),
        'path' => __DIR__ . '/../storage/uploads',
    ],
    
    'invoices' => [
        'path' => __DIR__ . '/../storage/invoices',
    ],
    
    'logs' => [
        'path' => __DIR__ . '/../storage/logs',
    ],
    
    // Session configuration — used by index.php when present
    'session' => [
        // Relative path to store PHP session files for this project
        'path' => __DIR__ . '/../storage/sessions',
    ],
];

