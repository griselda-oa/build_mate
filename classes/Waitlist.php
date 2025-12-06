<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Waitlist model
 */
class Waitlist extends Model
{
    protected string $table = 'waitlist';
    
    /**
     * Check if table exists
     */
    private function tableExists(): bool
    {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Add user to waitlist
     */
    public function addToWaitlist(int $userId, int $productId): bool
    {
        if (!$this->tableExists()) {
            error_log("Waitlist table does not exist. Please run migration.");
            return false;
        }
        
        try {
            // Check if already in waitlist
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            if ($stmt->fetch()) {
                return false; // Already in waitlist
            }
            
            return $this->create([
                'user_id' => $userId,
                'product_id' => $productId,
                'notified' => 0
            ]) > 0;
        } catch (\PDOException $e) {
            error_log("Waitlist add error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove from waitlist
     */
    public function removeFromWaitlist(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
    
    /**
     * Check if user is in waitlist
     */
    public function isInWaitlist(int $userId, int $productId): bool
    {
        if (!$this->tableExists()) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            error_log("Waitlist check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get waitlist for product
     */
    public function getByProduct(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT w.*, u.name as user_name, u.email as user_email
            FROM {$this->table} w
            JOIN users u ON w.user_id = u.id
            WHERE w.product_id = ? AND w.notified = 0
            ORDER BY w.created_at ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark as notified
     */
    public function markNotified(int $id): bool
    {
        return $this->update($id, [
            'notified' => 1,
            'notified_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Notify all users when product is back in stock
     */
    public function notifyUsersForProduct(int $productId): int
    {
        $waitlist = $this->getByProduct($productId);
        $notified = 0;
        
        foreach ($waitlist as $item) {
            // In a real app, send email notification here
            // For now, just mark as notified
            $this->markNotified($item['id']);
            $notified++;
        }
        
        return $notified;
    }
}

