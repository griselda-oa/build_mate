<?php

declare(strict_types=1);

namespace App;

use App\DB;

/**
 * Security helper functions
 */
class Security
{
    /**
     * Log security event
     */
    public static function log(string $action, ?int $userId = null, array $meta = []): void
    {
        try {
            $db = DB::getInstance();
            
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, ip, ua, meta, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                json_encode($meta)
            ]);
        } catch (\PDOException $e) {
            // Log to error log if audit_logs table doesn't exist
            // This prevents the application from crashing if the table is missing
            error_log("Security::log() failed: " . $e->getMessage());
            // Silently fail - don't break the application if audit logging fails
        }
    }
    
    /**
     * Generate secure random filename
     */
    public static function randomFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }
}


    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }
}


    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }
}


    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }
}


    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }
}


    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return substr($filename, 0, 255);
    }
}

