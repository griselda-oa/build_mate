<?php

declare(strict_types=1);

namespace App;

use App\Model;
use App\DB;
use App\Auth;

/**
 * User model
 */
class User extends Model
{
    protected string $table = 'users';
    
    /**
     * Find by email (case-insensitive)
     */
    public function findByEmail(string $email): ?array
    {
        // Normalize email to lowercase for comparison
        $email = strtolower(trim($email));
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE LOWER(TRIM(email)) = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        
        if (!$result) {
            error_log("findByEmail: No user found for email: {$email}");
            // Try exact match as fallback
            $stmt2 = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
            $stmt2->execute([$email]);
            $result = $stmt2->fetch();
            if ($result) {
                error_log("findByEmail: Found user with exact match (case-sensitive)");
            }
        }
        
        return $result ?: null;
    }
    
    /**
     * Create user with hashed password
     */
    public function createUser(array $data): int
    {
        $data['password_hash'] = Auth::hashPassword($data['password']);
        unset($data['password']);
        return $this->create($data);
    }
    
    /**
     * Verify login credentials
     */
    public function verifyLogin(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            error_log("verifyLogin: User not found for email: {$email}");
            return null;
        }
        
        // Check if password_hash column exists and has value
        if (empty($user['password_hash'])) {
            error_log("verifyLogin: User found but password_hash is empty for email: {$email}");
            return null;
        }
        
        $passwordValid = Auth::verifyPassword($password, $user['password_hash']);
        
        if (!$passwordValid) {
            error_log("verifyLogin: Password verification failed for email: {$email}");
            error_log("verifyLogin: Hash in DB: " . substr($user['password_hash'], 0, 20) . "...");
            // Try to verify with a test hash to see if password_verify is working
            $testHash = Auth::hashPassword('test');
            $testVerify = Auth::verifyPassword('test', $testHash);
            error_log("verifyLogin: password_verify function test: " . ($testVerify ? 'WORKING' : 'NOT WORKING'));
            return null;
        }
        
        return $user;
    }
}