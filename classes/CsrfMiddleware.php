<?php

declare(strict_types=1);

namespace App;

use App\Csrf;

/**
 * Verify CSRF token
 */
class CsrfMiddleware
{
    public function handle(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = '';
            
            // Check for token in headers first (X-CSRF-TOKEN) - most common for AJAX
            if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
            // Check for token in POST data (form submissions)
            elseif (!empty($_POST['csrf_token'])) {
                $token = $_POST['csrf_token'];
            }
            // Check for token in JSON body (AJAX requests)
            elseif (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
                $token = $input['csrf_token'] ?? $input['_token'] ?? '';
            }
            
            if (empty($token) || !Csrf::verify($token)) {
                // For AJAX/JSON requests, return JSON error
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                    exit;
                }
                // For regular requests, show error page
                http_response_code(403);
                $view = new \App\View();
                echo $view->render('Errors/403', ['message' => 'Invalid CSRF token'], 'main');
                exit;
            }
        }
        return true;
    }
}

