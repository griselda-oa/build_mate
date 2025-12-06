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
     * Dynamically detects the application's base path
     */
    public static function basePath(): string
    {
        // First check if APP_BASE_PATH is set in environment (for production)
        if (!empty($_ENV['APP_BASE_PATH'])) {
            $basePath = $_ENV['APP_BASE_PATH'];
            // Ensure it starts with / and ends with /
            return '/' . trim($basePath, '/') . '/';
        }
        
        // Try to detect from SCRIPT_NAME first (most reliable)
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
     * Generate route URL
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
     * Generate relative URL (alias for url() for backward compatibility)
     */
    public static function relUrl(string $path = '/'): string
    {
        return self::url($path);
    }
    
    /**
     * Generate relative asset URL (alias for asset() for backward compatibility)
     */
    public static function relAsset(string $path): string
    {
        return self::asset($path);
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