<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Database connection singleton
 */
class DB
{
    private static ?PDO $instance = null;
    private static array $config;

    /**
     * Get database connection instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$config = require __DIR__ . '/../settings/config.php';
            $db = self::$config['db'];
            
            // For macOS XAMPP, use 127.0.0.1 instead of localhost to force TCP
            // This avoids socket permission issues
            // XAMPP on macOS often uses port 3307 instead of 3306
            $host = $db['host'];
            if ($host === 'localhost') {
                $host = '127.0.0.1';
            }
            
            // Use TCP connection (not socket) to avoid permission issues
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $host,
                $db['port'],
                $db['name']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // Password handling: In production, only use configured password
            // In local/development, try common passwords if empty
            $appEnv = self::$config['app_env'] ?? 'local';
            $passwords = [];
            
            // Always try the configured password first
            if ($db['pass'] !== '') {
                $passwords[] = $db['pass'];
            }
            
            // Only try common passwords in local/development mode
            if ($appEnv === 'local' || $appEnv === 'development') {
                // Then try empty and common passwords for local development
                // Try empty FIRST (most common for XAMPP)
                $passwords = array_merge([''], $passwords, ['root', 'xampp', 'password', 'admin', '123456', 'mysql', '1234', 'test']);
            } else {
                // Production: only try configured password, then empty if not set
                if ($db['pass'] === '') {
                    $passwords[] = '';
                }
            }
            
            // Remove duplicates while preserving order
            $passwords = array_values(array_unique($passwords));
            
            $lastError = null;
            $triedPasswords = [];
            
            foreach ($passwords as $password) {
                try {
                    $pwd = $password === '' ? null : $password;
                    $triedPasswords[] = $password === '' ? '(empty)' : $password;
                    self::$instance = new PDO($dsn, $db['user'], $pwd, $options);
                    // Success! If we used a different password, log it
                    if ($password !== $db['pass']) {
                        error_log("Database connected with password: " . ($password === '' ? '(empty)' : $password) . " (update .env file with: DB_PASS=" . ($password === '' ? '' : $password) . ")");
                    }
                    break;
                } catch (PDOException $e) {
                    $lastError = $e;
                    // Try next password
                    continue;
                }
            }
            
                // If all passwords failed, throw error
                if (self::$instance === null && $lastError !== null) {
                    error_log("Database connection failed: " . $lastError->getMessage());
                    error_log("Tried passwords: " . implode(', ', $triedPasswords));
                    
                    $errorMsg = "Database connection failed. ";
                    if (strpos($lastError->getMessage(), 'Access denied') !== false) {
                        $errorMsg .= "MySQL authentication failed. Please check your database credentials in the .env file.\n\n";
                        $errorMsg .= "Common fixes:\n";
                        $errorMsg .= "1. Verify DB_USER and DB_PASS in your .env file\n";
                        $errorMsg .= "2. For XAMPP, try DB_PASS=(empty) or DB_PASS=root\n";
                        $errorMsg .= "3. Ensure MySQL is running in XAMPP Control Panel\n";
                        $errorMsg .= "4. Check phpMyAdmin to verify your credentials";
                    } elseif (strpos($lastError->getMessage(), 'Permission denied') !== false) {
                        $errorMsg .= "MySQL may not be running. Check XAMPP Control Panel and ensure MySQL is started.";
                    } elseif (strpos($lastError->getMessage(), 'Unknown database') !== false) {
                        $errorMsg .= "Database '{$db['name']}' does not exist. Create it in phpMyAdmin first.";
                    } else {
                        $errorMsg .= "Please ensure MySQL is running and the database '{$db['name']}' exists.";
                    }
                    throw new \RuntimeException($errorMsg, 0, $lastError);
                }
        }
        
        return self::$instance;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

