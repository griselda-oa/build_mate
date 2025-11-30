<?php

declare(strict_types=1);

namespace App;

/**
 * Base Controller
 */
abstract class Controller
{
    protected View $view;
    
    public function __construct()
    {
        $this->view = new View();
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    
    /**
     * Get and clear flash message
     */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Get current user
     */
    protected function user(): ?array
    {
        // Normalize legacy flat session keys and ensure numeric id is an int
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
        } else {
            $legacyUserId = $_SESSION['user_id'] ?? null;
            $legacyUsername = $_SESSION['username'] ?? null;
            $legacyEmail = $_SESSION['email'] ?? null;
            $legacyRole = $_SESSION['role'] ?? null;

            if ($legacyUserId || $legacyUsername) {
                $user = [
                    'id' => is_numeric($legacyUserId) ? (int)$legacyUserId : null,
                    'name' => $legacyUsername,
                    'email' => $legacyEmail,
                    'role' => $legacyRole ?? 'buyer'
                ];
                // Persist normalized form back to session for future requests
                $_SESSION['user'] = $user;
            } else {
                return null;
            }
        }

        if (isset($user['id']) && is_numeric($user['id'])) {
            $user['id'] = (int)$user['id'];
            // keep session in sync
            $_SESSION['user']['id'] = $user['id'];
        }

        return $user;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user']);
    }
    
    /**
     * Redirect back to previous page
     */
    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/build_mate/';
        $this->redirect($referer);
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

