<?php

declare(strict_types=1);

namespace App;

use App\Auth;
use App\View;

/**
 * Require authentication
 */
class AuthMiddleware
{
    public function handle(): bool
    {
        // (debug log removed)
        
        if (!Auth::check()) {
            error_log("AuthMiddleware - Auth::check() returned false, redirecting to login");
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . View::url('.views/login'));
            exit;
        }
        
        error_log("AuthMiddleware - Auth::check() returned true, allowing access");
        return true;
    }
}

