<?php

declare(strict_types=1);

namespace App;

/**
 * HTTP Response helper
 */
class Response
{
    /**
     * Send file download
     */
    public static function download(string $filePath, string $filename): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found');
        }
        
        // Clear ALL output buffering
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Remove any existing headers that might interfere
        if (headers_sent($file, $line)) {
            error_log("Headers already sent in {$file} on line {$line}. Cannot send download headers.");
            // Try to send as redirect to file instead
            header('Location: ' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath));
            exit;
        }
        
        // Set headers for download
        header('Content-Type: application/pdf', true);
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"', true);
        header('Content-Length: ' . filesize($filePath), true);
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0', true);
        header('Pragma: public', true);
        header('Expires: 0', true);
        
        // Remove security headers that might interfere
        header_remove('X-Frame-Options');
        header_remove('Content-Security-Policy');
        
        // Output the file
        readfile($filePath);
        exit;
    }
    
    /**
     * Send file inline
     */
    public static function inline(string $filePath, string $mimeType): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found');
        }
        
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }
    
    /**
     * Send JSON response
     */
    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

