<?php

declare(strict_types=1);

return [
    'app_name' => $_ENV['APP_NAME'] ?? 'Build Mate Ghana',
    'app_env' => $_ENV['APP_ENV'] ?? 'local',
    'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost/build_mate',
    
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['DB_PORT'] ?? 3307), // XAMPP uses 3307 on macOS
        'name' => $_ENV['DB_NAME'] ?? 'buildmate_db',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],
    
    'payment' => [
        'mode' => $_ENV['PAYMENT_MODE'] ?? 'mock',
        'paystack_public_key' => $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '',
        'paystack_secret_key' => $_ENV['PAYSTACK_SECRET_KEY'] ?? '',
        'flutterwave_public_key' => $_ENV['FLUTTERWAVE_PUBLIC_KEY'] ?? '',
        'flutterwave_secret_key' => $_ENV['FLUTTERWAVE_SECRET_KEY'] ?? '',
    ],
    
    'google_maps' => [
        'api_key' => $_ENV['GOOGLE_MAPS_API_KEY'] ?? '',
    ],
    
    'email' => [
        'from' => $_ENV['EMAIL_FROM'] ?? 'noreply@buildmate.com',
        'from_name' => $_ENV['EMAIL_FROM_NAME'] ?? 'Build Mate Ghana',
        'smtp_host' => $_ENV['SMTP_HOST'] ?? '',
        'smtp_port' => (int)($_ENV['SMTP_PORT'] ?? 587),
        'smtp_user' => $_ENV['SMTP_USER'] ?? '',
        'smtp_pass' => $_ENV['SMTP_PASS'] ?? '',
        'smtp_encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
    ],
    
    'currency' => [
        'default' => $_ENV['DEFAULT_CURRENCY'] ?? 'GHS',
        'usd_to_ghs_rate' => (float)($_ENV['USD_TO_GHS_RATE'] ?? 12.5),
    ],
    
    'security' => [
        'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 1800),
        'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
        'login_lockout_time' => (int)($_ENV['LOGIN_LOCKOUT_TIME'] ?? 900),
        'admin_email' => $_ENV['ADMIN_EMAIL'] ?? '', // Leave empty to allow any admin user, or set specific email
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
    
    'ai' => [
        'openai_api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        'model' => $_ENV['OPENAI_MODEL'] ?? 'gpt-3.5-turbo',
        'max_tokens' => (int)($_ENV['OPENAI_MAX_TOKENS'] ?? 500),
        'temperature' => (float)($_ENV['OPENAI_TEMPERATURE'] ?? 0.7),
    ],
    
    'session' => [
        'path' => __DIR__ . '/../storage/sessions',
    ],
];

