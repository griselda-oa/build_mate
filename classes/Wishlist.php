<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Wishlist model
 */
class Wishlist extends Model
{
    protected string $table = 'wishlist';
    
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
     * Add product to wishlist
     */
    public function addToWishlist(int $userId, int $productId): bool
    {
        if (!$this->tableExists()) {
            error_log("Wishlist table does not exist. Please run migration.");
            return false;
        }
        
        try {
            // Check if already in wishlist
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            if ($stmt->fetch()) {
                return false; // Already in wishlist
            }
            
            return $this->create([
                'user_id' => $userId,
                'product_id' => $productId
            ]) > 0;
        } catch (\PDOException $e) {
            error_log("Wishlist add error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove from wishlist
     */
    public function removeFromWishlist(int $userId, int $productId): bool
    {
        if (!$this->tableExists()) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            return $stmt->execute([$userId, $productId]);
        } catch (\PDOException $e) {
            error_log("Wishlist remove error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if product is in wishlist
     */
    public function isInWishlist(int $userId, int $productId): bool
    {
        if (!$this->tableExists()) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            error_log("Wishlist check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's wishlist
     */
    public function getByUser(int $userId): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, p.name, p.slug, p.price_cents, p.currency, p.image_url, p.stock,
                       c.name as category_name, s.business_name as supplier_name
                FROM {$this->table} w
                JOIN products p ON w.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Wishlist get error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get wishlist count for user
     */
    public function getCount(int $userId): int
    {
        if (!$this->tableExists()) {
            return 0;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\PDOException $e) {
            error_log("Wishlist count error: " . $e->getMessage());
            return 0;
        }
    }
}






