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
        
        // Normalize allowedTypes - convert extensions to MIME types if needed
        $normalizedAllowedTypes = [];
        foreach ($allowedTypes as $type) {
            $type = strtolower(trim($type));
            // If it's an extension, convert to MIME type
            if (isset($extensionToMime[$type])) {
                $normalizedAllowedTypes[] = $extensionToMime[$type];
            } else {
                // Assume it's already a MIME type
                $normalizedAllowedTypes[] = $type;
            }
        }
        $normalizedAllowedTypes = array_unique($normalizedAllowedTypes);
        
        // Check if extension is valid
        $expectedMime = $extensionToMime[$ext] ?? null;
        
        // Validate: Check both extension and MIME type
        $isValid = false;
        
        // First, check if extension is allowed
        if ($expectedMime && in_array($expectedMime, $normalizedAllowedTypes)) {
            // Extension maps to an allowed MIME type
            if ($mimeType) {
                // If we have a detected MIME type, verify it matches the expected MIME
                // Be flexible with jpg/jpeg variations
                if ($mimeType === $expectedMime || 
                    ($ext === 'jpg' && $mimeType === 'image/jpeg') ||
                    ($ext === 'jpeg' && $mimeType === 'image/jpeg')) {
                    $isValid = true;
                } else {
                    // MIME type doesn't match extension - this could be a security issue
                    // (e.g., Word doc renamed to .pdf)
                    // Only accept if the detected MIME is also in the allowed list
                    // AND we're confident it's safe (same category)
                    $mimeCategory = explode('/', $mimeType)[0] ?? '';
                    $expectedCategory = explode('/', $expectedMime)[0] ?? '';
                    if (in_array($mimeType, $normalizedAllowedTypes) && $mimeCategory === $expectedCategory) {
                        $isValid = true;
                    }
                }
            } else {
                // No MIME type detected, but extension is valid - accept it
                $isValid = true;
            }
        } else if ($mimeType && in_array($mimeType, $normalizedAllowedTypes)) {
            // Extension not in allowed list, but MIME type is
            // This is less secure, but accept it if MIME type is clearly valid
            $isValid = true;
        }
        
        if (!$isValid) {
            // Build user-friendly allowed list
            $allowedExtensions = [];
            foreach ($normalizedAllowedTypes as $mime) {
                $ext = array_search($mime, $extensionToMime);
                if ($ext) {
                    $allowedExtensions[] = $ext;
                }
            }
            $allowedList = implode(', ', array_unique($allowedExtensions));
            
            $errors[] = 'Invalid file type. Allowed: ' . $allowedList . 
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
