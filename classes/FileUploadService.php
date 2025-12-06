<?php

declare(strict_types=1);

namespace App;

use App\Validator;
use App\Security;

/**
 * File upload service
 * Handles file upload business logic
 */
class FileUploadService
{
    private string $uploadPath;
    private array $allowedTypes;
    private int $maxSize;
    
    public function __construct(string $uploadPath, array $allowedTypes, int $maxSize)
    {
        $this->uploadPath = $uploadPath;
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
        
        // Create directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0777, true)) {
                throw new \RuntimeException("Failed to create upload directory: {$this->uploadPath}. Please check parent directory permissions.");
            }
            // Set permissions explicitly after creation
            chmod($this->uploadPath, 0777);
        }
        
        // Ensure directory is writable - try to fix permissions if not writable
        if (!is_writable($this->uploadPath)) {
            // Try to make it writable
            @chmod($this->uploadPath, 0777);
            
            // Check again
            if (!is_writable($this->uploadPath)) {
                throw new \RuntimeException("Upload directory is not writable: {$this->uploadPath}. Please run: chmod -R 777 " . dirname($this->uploadPath));
            }
        }
    }
    
    /**
     * Process and upload a file
     */
    public function upload(array $file): array
    {
        $errors = [];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
            return ['success' => false, 'errors' => [$errorMsg]];
        }
        
        // Validate file
        $validation = Validator::file($file, $this->allowedTypes, $this->maxSize);
        
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        
        // Generate unique filename
        $filename = Security::randomFilename($validation['ext']);
        $filePath = $this->uploadPath . '/' . $filename;
        
        // Ensure upload path exists and is writable
        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0775, true)) {
                return ['success' => false, 'errors' => ['Upload directory does not exist and could not be created']];
            }
        }
        
        if (!is_writable($this->uploadPath)) {
            return ['success' => false, 'errors' => ['Upload directory is not writable. Please check permissions.']];
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $lastError = error_get_last();
            $errorMsg = 'Failed to save file';
            if ($lastError && isset($lastError['message'])) {
                $errorMsg .= ': ' . $lastError['message'];
            }
            return ['success' => false, 'errors' => [$errorMsg]];
        }
        
        // Set file permissions to ensure it's readable
        @chmod($filePath, 0644);
        
        return [
            'success' => true,
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $filePath
        ];
    }
    
    /**
     * Process multiple file uploads
     */
    public function uploadMultiple(array $files): array
    {
        $results = [];
        $errors = [];
        
        foreach ($files as $key => $file) {
            if (isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $result = $this->upload($file);
                if ($result['success']) {
                    $results[$key] = $result;
                } else {
                    $errors = array_merge($errors, $result['errors']);
                }
            }
        }
        
        return [
            'success' => empty($errors),
            'results' => $results,
            'errors' => $errors
        ];
    }
}



