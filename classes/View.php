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
        } else {
            $file = $this->basePath . '/' . $view . '.php';
        }
        
        if (!file_exists($file)) {
            error_log("View file not found: {$file} (view: {$view})");
            throw new \RuntimeException("View not found: {$view} (looked for: {$file})");
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
}

