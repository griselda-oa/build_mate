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
            
            // Check if audit_logs table exists
            $tableCheck = $db->query("SHOW TABLES LIKE 'audit_logs'");
            if ($tableCheck->rowCount() === 0) {
                error_log("Security::log() - audit_logs table does not exist");
                return;
            }
            
            // If user_id is provided, verify it exists in users table
            if ($userId !== null) {
                $userCheck = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
                $userCheck->execute([$userId]);
                if ($userCheck->rowCount() === 0) {
                    // User doesn't exist, set to NULL to avoid foreign key constraint violation
                    error_log("Security::log() - User ID {$userId} does not exist, setting to NULL");
                    $userId = null;
                }
            }
            
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
            // Log the error but don't break the application
            error_log("Security::log() failed: " . $e->getMessage());
        } catch (\Exception $e) {
            // Log any other errors
            error_log("Security::log() error: " . $e->getMessage());
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
