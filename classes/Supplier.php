<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Supplier model
 */
class Supplier extends Model
{
    protected string $table = 'suppliers';
    
    /**
     * Find by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get all with user info
     */
    public function getAllWithUsers(): array
    {
        $stmt = $this->db->query("
            SELECT s.*, u.name, u.email
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get supplier statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_suppliers,
                COUNT(CASE WHEN verified_badge = 1 THEN 1 END) as verified_suppliers,
                COUNT(CASE WHEN kyc_status = 'pending' THEN 1 END) as pending_kyc
            FROM {$this->table}
        ");
            return $stmt->fetch() ?: ['total_suppliers' => 0, 'verified_suppliers' => 0, 'pending_kyc' => 0];
    }
    
    /**
     * Get recent products
     */
    public function getRecentProducts(int $supplierId, int $limit = 5): array
    {
        $productModel = new Product();
        $products = $productModel->getBySupplier($supplierId);
        return array_slice($products, 0, $limit);
    }
    
    /**
     * Get recent orders
     */
    public function getRecentOrders(int $supplierId, int $limit = 5): array
    {
        $orderModel = new Order();
        $orders = $orderModel->getBySupplier($supplierId);
        return array_slice($orders, 0, $limit);
    }
    
    /**
     * Check if supplier has active premium plan
     */
    public function isPremium(int $supplierId): bool
    {
        $stmt = $this->db->prepare("
            SELECT plan_type, premium_expires_at
            FROM {$this->table}
            WHERE id = ?
        ");
        $stmt->execute([$supplierId]);
        $supplier = $stmt->fetch();
        
        if (!$supplier || $supplier['plan_type'] !== 'premium') {
            return false;
        }
        
        // Check if premium hasn't expired
        if ($supplier['premium_expires_at']) {
            $expiresAt = strtotime($supplier['premium_expires_at']);
            return $expiresAt > time();
        }
        
        return false;
    }
    
    /**
     * Upgrade supplier to premium
     */
    public function upgradeToPremium(int $supplierId, int $days = 30): bool
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        return $this->update($supplierId, [
            'plan_type' => 'premium',
            'premium_expires_at' => $expiresAt
        ]);
    }
    
    /**
     * Downgrade supplier to freemium
     */
    public function downgradeToFreemium(int $supplierId): bool
    {
        return $this->update($supplierId, [
            'plan_type' => 'freemium',
            'premium_expires_at' => null
        ]);
    }
    
    /**
     * Auto-expire premium plans
     */
    public function expirePremiumPlans(): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET plan_type = 'freemium', premium_expires_at = NULL
            WHERE plan_type = 'premium'
            AND premium_expires_at < ?
        ");
        $stmt->execute([$now]);
        return $stmt->rowCount();
    }
    
    /**
     * Update sentiment score for supplier
     */
    public function updateSentimentScore(int $supplierId, float $score): bool
    {
        // Clamp score between 0 and 1
        $score = max(0.0, min(1.0, $score));
        return $this->update($supplierId, ['sentiment_score' => $score]);
    }
    
    /**
     * Increment performance warnings
     */
    public function incrementWarnings(int $supplierId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET performance_warnings = performance_warnings + 1
            WHERE id = ?
        ");
        $stmt->execute([$supplierId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get premium suppliers
     */
    public function getPremiumSuppliers(): array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->query("
            SELECT s.*, u.name, u.email
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.plan_type = 'premium'
            AND (s.premium_expires_at IS NULL OR s.premium_expires_at > ?)
            ORDER BY s.sentiment_score DESC, s.created_at DESC
        ");
        $stmt->execute([$now]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get suppliers expiring soon (within 7 days)
     */
    public function getExpiringSoon(int $days = 7): array
    {
        $now = date('Y-m-d H:i:s');
        $future = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $stmt = $this->db->prepare("
            SELECT s.*, u.name, u.email
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.plan_type = 'premium'
            AND s.premium_expires_at BETWEEN ? AND ?
            ORDER BY s.premium_expires_at ASC
        ");
        $stmt->execute([$now, $future]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get low sentiment suppliers (for admin review)
     */
    public function getLowSentimentSuppliers(float $threshold = 0.4): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name, u.email
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.sentiment_score < ?
            ORDER BY s.sentiment_score ASC, s.performance_warnings DESC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get supplier statistics
     */
    public function getSupplierStats(int $supplierId): array
    {
        $productModel = new Product();
        $orderModel = new Order();
        
        $products = $productModel->getBySupplier($supplierId);
        $orders = $orderModel->getBySupplier($supplierId);
        
        // Count pending orders - orders waiting for supplier action
        // This includes: 'placed' (waiting for payment), 'pending' (old status), and 'paid' (waiting for supplier to process)
        $pendingOrders = count(array_filter($orders, function($o) {
            $status = strtolower($o['status'] ?? '');
            // Pending = orders that need supplier attention (placed/pending = waiting for payment, paid = waiting to process)
            return in_array($status, ['placed', 'pending', 'paid']);
        }));
        
        return [
            'total_products' => count($products),
            'total_orders' => count($orders),
            'pending_orders' => $pendingOrders
        ];
    }
}

