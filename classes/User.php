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
     * Find by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
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
        
        if (!$user || !Auth::verifyPassword($password, $user['password_hash'])) {
            return null;
        }
        
        // Remove password hash from returned data
        unset($user['password_hash']);
        return $user;
    }
}

