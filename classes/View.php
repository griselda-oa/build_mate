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
        
        require $file;
        
        return ob_get_clean();
    }
    
    /**
     * Get base path for URLs
     * Uses APP_BASE_PATH from .env if set, otherwise auto-detects
     */
    public static function basePath(): string
    {
        // Check for explicit base path in .env (most reliable for deployment)
        if (isset($_ENV['APP_BASE_PATH']) && !empty($_ENV['APP_BASE_PATH'])) {
            $basePath = $_ENV['APP_BASE_PATH'];
            // Ensure it starts with / and ends with /
            $basePath = '/' . trim($basePath, '/');
            return $basePath === '/' ? '/' : $basePath . '/';
        }
        
        // Try to detect from SCRIPT_NAME
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
            
            // Normalize the path
            if ($scriptPath === '.' || $scriptPath === '\\' || empty($scriptPath)) {
                return '/';
            }
            
            if ($scriptPath === '/') {
                return '/';
            }
            
            // Ensure it starts with / and ends with /
            $basePath = '/' . trim($scriptPath, '/') . '/';
            return $basePath;
        }
        
        // Default fallback for root deployment
        return '/';
    }
    
    /**
     * Generate asset URL
     */
    public static function asset(string $path): string
    {
        $basePath = self::basePath();
        // Remove leading slash from path if present
        $path = ltrim($path, '/');
        return $basePath . $path;
    }
    
    /**
     * Generate route URL (absolute with base path)
     */
    public static function url(string $path = '/'): string
    {
        $basePath = self::basePath();
        // Remove leading slash from path if present
        $path = ltrim($path, '/');
        if ($path === '') {
            return $basePath;
        }
        return $basePath . $path;
    }
    
    /**
     * Generate relative route URL based on current URL depth
     * Uses ../ notation to navigate from current page to target
     */
    public static function relUrl(string $path = '/'): string
    {
        $depth = $_SERVER['ROUTE_DEPTH'] ?? 0;
        $path = ltrim($path, '/');
        
        if ($depth === 0) {
            // At root level, use ./
            return './' . $path;
        }
        
        // Generate ../ for each level of depth
        $prefix = str_repeat('../', $depth);
        return $prefix . $path;
    }
    
    /**
     * Generate relative asset URL based on current URL depth
     */
    public static function relAsset(string $path): string
    {
        return self::relUrl($path);
    }
    
    /**
     * Generate relative image URL
     * Handles both external URLs and local paths
     */
    public static function relImage(string $path): string
    {
        // If it's already a full URL (http/https), return as is
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        
        return self::relUrl($path);
    }
    
    /**
     * Escape HTML output
     */
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate image URL with base path
     * Handles both absolute paths (starting with /) and relative paths
     */
    public static function image(string $path): string
    {
        // If it's already a full URL (http/https), return as is
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        
        // If it starts with /, it's an absolute path - prepend base path
        if (strpos($path, '/') === 0) {
            $basePath = self::basePath();
            // Remove leading slash from path and ensure base path ends with /
            $path = ltrim($path, '/');
            return $basePath . $path;
        }
        
        // Otherwise, treat as relative path and use asset()
        return self::asset($path);
    }
}