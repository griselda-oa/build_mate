<?php

declare(strict_types=1);

/**
 * Content Security Policy Headers
 * Configure CSP headers for security
 */

function sendSecurityHeaders(): void
{
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
           "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
           "frame-ancestors 'none';";
    
    header("Content-Security-Policy: $csp");
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // HSTS (document for production HTTPS)
    if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

