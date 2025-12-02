<?php

declare(strict_types=1);

namespace App;

/**
 * Simple Router
 */
class Router
{
    private static ?Router $instance = null;
    private array $routes = [];
    private array $middleware = [];
    
    private function __construct() {}
    
    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Add GET route
     */
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Add POST route
     */
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Add route
     */
    private function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Dispatch request
     */
    public function dispatch(): void
    {
        // Start output buffering to prevent headers already sent errors
        if (ob_get_level() === 0) {
            ob_start();
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Dynamically detect and remove base path from URI
        $basePath = $this->getBasePath();
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Ensure URI starts with /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        // Handle /index.php as root
        if ($uri === '/index.php' || strpos($uri, '/index.php') !== false) {
            $uri = '/';
        }
        
        // Remove trailing slash (except for root)
        // But keep trailing slash for routes that might need it
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }
        
        // Check if this is an invoice download route (skip security headers)
        $isInvoiceRoute = preg_match('#/orders/\d+/invoice\.pdf#', $uri);
        
        // Only send security headers if not an invoice download
        if (!$isInvoiceRoute) {
            require_once __DIR__ . '/../settings/csp.php';
            sendSecurityHeaders();
        }
        
        error_log("Router: Dispatching {$method} request for URI: {$uri}");
        
        foreach ($this->routes as $index => $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->convertToRegex($route['path']);
            error_log("Router: Checking route #{$index} '{$route['path']}' -> pattern: {$pattern}");
            
            if (preg_match($pattern, $uri, $matches)) {
                // Extract route parameters
                array_shift($matches);
                $params = array_values($matches);
                
                // Convert numeric parameters to integers for type-hinted methods
                $params = array_map(function($param) {
                    return is_numeric($param) ? (int)$param : $param;
                }, $params);
                
                // Log route matching for debugging
                error_log("Router: ✓ Matched route '{$route['path']}' for URI '{$uri}' with params: " . json_encode($params));
                
                // Execute middleware
                foreach ($route['middleware'] as $middleware) {
                    if (is_string($middleware) && strpos($middleware, ':') !== false) {
                        [$class, $arg] = explode(':', $middleware, 2);
                        if (!class_exists($class)) {
                            error_log("Router: Middleware class not found: {$class}");
                            http_response_code(500);
                            die("Middleware class not found: {$class}");
                        }
                        $middlewareInstance = new $class();
                        if (!call_user_func([$middlewareInstance, 'handle'], $arg)) {
                            return;
                        }
                    } else {
                        if (!class_exists($middleware)) {
                            error_log("Router: Middleware class not found: {$middleware}");
                            http_response_code(500);
                            die("Middleware class not found: {$middleware}");
                        }
                        $middlewareInstance = new $middleware();
                        if (!call_user_func([$middlewareInstance, 'handle'])) {
                            return;
                        }
                    }
                }
                
                // Execute handler
                [$controller, $method] = $route['handler'];
                $controllerInstance = new $controller();
                call_user_func_array([$controllerInstance, $method], $params);
                return;
            }
        }
        
        // 404 Not Found - but if no routes are registered, show error
        if (empty($this->routes)) {
            http_response_code(500);
            die('<h1>Router Error</h1><p>No routes have been registered. Please check that <code>settings/routes.php</code> is loading correctly.</p>');
        }
        
        http_response_code(404);
        $view = new View();
        echo $view->render('Errors/404', [], 'main');
    }
    
    /**
     * Get base path - uses APP_BASE_PATH from .env if set, otherwise auto-detects
     */
    private function getBasePath(): string
    {
        // Check for explicit base path in .env (most reliable for deployment)
        if (isset($_ENV['APP_BASE_PATH']) && !empty($_ENV['APP_BASE_PATH'])) {
            $basePath = $_ENV['APP_BASE_PATH'];
            // Ensure it starts with / and ends with /
            $basePath = '/' . trim($basePath, '/');
            return $basePath === '/' ? '/' : $basePath . '/';
        }
        
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return '/';
        }
        
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        
        // Normalize the path
        if ($scriptPath === '.' || $scriptPath === '\\' || empty($scriptPath)) {
            return '/';
        }
        
        // Ensure it starts with / and ends with /
        $basePath = '/' . trim($scriptPath, '/');
        
        return $basePath === '/' ? '/' : $basePath . '/';
    }
    
    /**
     * Convert route pattern to regex
     */
    private function convertToRegex(string $pattern): string
    {
        // Replace route parameters with capture groups BEFORE escaping
        // This way we don't have to deal with escaped braces
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '___CAPTURE_GROUP___', $pattern);
        
        // Now escape the pattern (this will escape everything except our placeholder)
        $escaped = preg_quote($pattern, '#');
        
        // Replace our placeholder with the actual capture group
        $regex = str_replace('___CAPTURE_GROUP___', '([^/]+)', $escaped);
        
        // Add anchors
        $regex = '#^' . $regex . '$#';
        
        return $regex;
    }
}