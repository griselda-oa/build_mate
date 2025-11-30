<?php

declare(strict_types=1);

namespace App;

/**
 * View renderer
 */
class View
{
    private string $basePath;
    
    public function __construct()
    {
        $this->basePath = __DIR__ . '/../views';
    }
    
    /**
     * Render view with layout
     */
    public function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = $this->renderPartial($view, $data);
        
        if ($layout !== null) {
            $data['content'] = $content;
            return $this->renderPartial("Layouts/{$layout}", $data);
        }
        
        return $content;
    }
    
    /**
     * Render partial view
     */
    public function renderPartial(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        
        // Make View class available in views via variable
        $View = self::class;
        
        ob_start();
        
        // Check if it's a login view (in login/ folder at root)
        if (strpos($view, 'login/') === 0) {
            $file = __DIR__ . '/../' . $view . '.php';
        } elseif (strpos(strtolower($view), 'admin/') === 0) {
            // Admin views are in Admin/ folder (case-insensitive check, but preserve case for path)
            $file = $this->basePath . '/' . $view . '.php';
        } elseif (strpos(strtolower($view), 'layouts/') === 0) {
            // Layouts folder - handle case-insensitive matching
            $layoutName = substr($view, 8); // Remove 'Layouts/'
            // Try capitalized first letter (Auth.php, Main.php, etc.)
            $file = $this->basePath . '/Layouts/' . ucfirst(strtolower($layoutName)) . '.php';
            if (!file_exists($file)) {
                // Try original case
                $file = $this->basePath . '/Layouts/' . $layoutName . '.php';
            }
        } else {
            $file = $this->basePath . '/' . $view . '.php';
        }
        
        if (!file_exists($file)) {
            error_log("View file not found: {$file} (view: {$view})");
            // Try case-insensitive search as last resort
            $dir = dirname($file);
            $basename = basename($file);
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $f) {
                    if (strcasecmp($f, $basename) === 0) {
                        $file = $dir . '/' . $f;
                        error_log("Found view file with case-insensitive match: {$file}");
                        break;
                    }
                }
            }
            if (!file_exists($file)) {
                throw new \RuntimeException("View not found: {$view} (looked for: {$file})");
            }
        }
        
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            error_log("Error rendering view {$view}: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Escape output
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Get base path for assets and links
     * Detects /~username/build_mate or /build_mate dynamically
     */
    public static function basePath(): string
    {
        static $basePath = null;
        
        if ($basePath !== null) {
            return $basePath;
        }
        
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $scriptDir = dirname($scriptName);
        
        // Priority 1: Check REQUEST_URI for server path: /~username/build_mate
        if (preg_match('#^/~[^/]+/build_mate#', $requestUri)) {
            $basePath = preg_replace('#^(/~[^/]+/build_mate).*#', '$1', $requestUri);
        }
        // Priority 2: Check REQUEST_URI for localhost path: /build_mate
        elseif (strpos($requestUri, '/build_mate') === 0) {
            $basePath = '/build_mate';
        }
        // Priority 3: Check SCRIPT_NAME for server path (more reliable for redirects)
        elseif (preg_match('#^/~[^/]+/build_mate#', $scriptName)) {
            $basePath = preg_replace('#^(/~[^/]+/build_mate).*#', '$1', $scriptName);
        }
        // Priority 4: Check SCRIPT_NAME for localhost path
        elseif (strpos($scriptName, '/build_mate') !== false) {
            $basePath = '/build_mate';
        }
        // Priority 5: Use script directory if it contains build_mate
        elseif ($scriptDir !== '/' && $scriptDir !== '.' && strpos($scriptDir, 'build_mate') !== false) {
            $basePath = $scriptDir;
        }
        // Priority 6: Handle server root: /~username (fallback)
        elseif (preg_match('#^/~[^/]+#', $requestUri)) {
            $basePath = preg_replace('#^(/~[^/]+).*#', '$1', $requestUri) . '/build_mate';
        }
        // Priority 7: Use script directory as fallback
        elseif ($scriptDir !== '/' && $scriptDir !== '.') {
            $basePath = $scriptDir;
        }
        // Priority 8: Default fallback
        else {
            $basePath = '/build_mate';
        }
        
        error_log("View::basePath() detected: '{$basePath}' from REQUEST_URI: '{$requestUri}', SCRIPT_NAME: '{$scriptName}'");
        
        return $basePath;
    }
    
    /**
     * Generate asset URL (CSS, JS, images)
     */
    public static function asset(string $path): string
    {
        $base = self::basePath();
        // Remove leading slash from path if present
        $path = ltrim($path, '/');
        return rtrim($base, '/') . '/' . $path;
    }
    
    /**
     * Generate URL for routes
     */
    public static function url(string $path = '/'): string
    {
        $base = self::basePath();
        // Remove leading slash from path if present
        $path = ltrim($path, '/');
        if ($path === '') {
            return rtrim($base, '/') . '/';
        }
        return rtrim($base, '/') . '/' . $path;
    }
}

