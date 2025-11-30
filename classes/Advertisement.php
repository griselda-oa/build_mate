<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Advertisement model for premium supplier ads
 */
class Advertisement extends Model
{
    protected string $table = 'advertisements';
    
    /**
     * Get active advertisements
     */
    public function getActive(): array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            SELECT a.*, p.id as product_id, p.name as product_name, p.slug as product_slug, 
                   p.image_url as product_image, s.business_name as supplier_name
            FROM {$this->table} a
            JOIN products p ON a.product_id = p.id
            JOIN suppliers s ON a.supplier_id = s.id
            WHERE a.status = 'active'
            AND a.start_date <= ?
            AND (a.end_date IS NULL OR a.end_date >= ?)
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$now, $now]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get advertisements by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, p.name as product_name, p.slug as product_slug
            FROM {$this->table} a
            JOIN products p ON a.product_id = p.id
            WHERE a.supplier_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get pending advertisements (for admin approval)
     */
    public function getPending(): array
    {
        $stmt = $this->db->query("
            SELECT a.*, p.name as product_name, p.slug as product_slug,
                   s.business_name as supplier_name, s.plan_type
            FROM {$this->table} a
            JOIN products p ON a.product_id = p.id
            JOIN suppliers s ON a.supplier_id = s.id
            WHERE a.status = 'pending'
            ORDER BY a.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Check if supplier can create ad (must be premium)
     */
    public function canCreateAd(int $supplierId): bool
    {
        $supplierModel = new Supplier();
        return $supplierModel->isPremium($supplierId);
    }
    
    /**
     * Increment impressions
     */
    public function incrementImpressions(int $adId): void
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET impressions = impressions + 1
            WHERE id = ?
        ");
        $stmt->execute([$adId]);
    }
    
    /**
     * Increment clicks
     */
    public function incrementClicks(int $adId): void
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET clicks = clicks + 1
            WHERE id = ?
        ");
        $stmt->execute([$adId]);
    }
    
    /**
     * Auto-expire old ads
     */
    public function expireOldAds(): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET status = 'expired'
            WHERE status = 'active'
            AND end_date < ?
        ");
        $stmt->execute([$now]);
        return $stmt->rowCount();
    }
}
