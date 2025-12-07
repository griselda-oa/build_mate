<?php

declare(strict_types=1);

namespace App;

use App\Auth;

/**
 * Require specific role
 */
class RoleMiddleware
{
    public function handle(string $role): bool
    {
        if (!Auth::check()) {
            header('Location: ' . \App\View::url('/login'));
            exit;
        }
        
        // Admin role check - allow if user has admin role
        if ($role === 'admin') {
            // Just verify the user has admin role, no additional email check needed
            if (!Auth::hasRole('admin')) {
                http_response_code(403);
                $view = new \App\View();
                echo $view->render('Errors/403', [], 'main');
                exit;
            }
        }
        
        if (!Auth::hasRole($role)) {
            http_response_code(403);
            $view = new \App\View();
            echo $view->render('Errors/403', [], 'main');
            exit;
        }
        
        return true;
    }
}
