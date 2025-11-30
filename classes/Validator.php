<?php

declare(strict_types=1);

namespace App;

/**
 * Input validator
 */
class Validator
{
    /**
     * Validate email
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone (basic)
     */
    public static function phone(string $phone): bool
    {
        return preg_match('/^\+?[0-9]{10,15}$/', $phone) === 1;
    }
    
    /**
     * Validate password strength
     */
    public static function password(string $password): bool
    {
        return strlen($password) >= 8;
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitize(string $input, int $maxLength = 1000): string
    {
        $input = trim($input);
        if (strlen($input) > $maxLength) {
            $input = substr($input, 0, $maxLength);
        }
        return $input;
    }
    
    /**
     * Validate file upload
     * @param array $file The $_FILES array element
     * @param array $allowedTypes Array of allowed MIME types (e.g., ['image/jpeg', 'image/png'])
     * @param int $maxSize Maximum file size in bytes
     */
    public static function file(array $file, array $allowedTypes, int $maxSize): array
    {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed (' . round($maxSize / 1024 / 1024, 2) . 'MB)';
        }
        
        // Get file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check MIME type using finfo
        $mimeType = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }
        
        // Fallback to $_FILES['type'] if finfo is not available
        if (!$mimeType && isset($file['type'])) {
            $mimeType = $file['type'];
        }
        
        // Map extensions to MIME types for validation
        $extensionToMime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf'
        ];
        
        // Check if extension is valid
        $expectedMime = $extensionToMime[$ext] ?? null;
        
        // Validate: Check if MIME type is in allowed list AND matches expected MIME for extension
        $isValidMime = false;
        if ($mimeType && in_array($mimeType, $allowedTypes)) {
            // If we have expected MIME, verify it matches
            if ($expectedMime) {
                // Accept both 'image/jpeg' and 'image/jpg' for jpg/jpeg files
                if ($mimeType === $expectedMime || 
                    ($ext === 'jpg' && $mimeType === 'image/jpeg') ||
                    ($ext === 'jpeg' && $mimeType === 'image/jpeg')) {
                    $isValidMime = true;
                }
            } else {
                // No expected MIME mapping, just check if it's in allowed types
                $isValidMime = true;
            }
        }
        
        if (!$isValidMime) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes) . 
                       ($mimeType ? " (Detected: {$mimeType})" : '') . 
                       ($ext ? " (Extension: .{$ext})" : '');
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime' => $mimeType,
            'ext' => $ext
        ];
    }
    
    /**
     * Normalize email
     */
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
    
    /**
     * Normalize phone
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+233' . ltrim($phone, '0');
        }
        return $phone;
    }
}

