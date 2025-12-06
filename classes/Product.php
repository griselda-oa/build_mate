<?php

declare(strict_types=1);

namespace App;

use App\Model;
use App\DB;

/**
 * Product model
 */
class Product extends Model
{
    protected string $table = 'products';
    
    /**
     * Find by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, s.business_name as supplier_name
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.slug = ? LIMIT 1
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Search products
     * @param string $query Search query
     * @param int|null $categoryId Category filter
     * @param int|null $minPrice Minimum price in cents
     * @param int|null $maxPrice Maximum price in cents
     * @param bool $verifiedOnly Only verified products
     * @param int|null $supplierId Filter by supplier ID (for supplier's own products view)
     */
    public function search(string $query = '', ?int $categoryId = null, ?int $minPrice = null, ?int $maxPrice = null, bool $verifiedOnly = false, ?int $supplierId = null): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT p.*, 
                       c.name as category_name, 
                       s.business_name as supplier_name, 
                       s.verified_badge,
                       s.plan_type,
                       s.sentiment_score,
                       s.premium_expires_at,
                       a.id as advertisement_id
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN advertisements a ON a.product_id = p.id 
                    AND a.status = 'active'
                    AND a.start_date <= ?
                    AND (a.end_date IS NULL OR a.end_date >= ?)
                WHERE 1=1";
        $params = [$now, $now];
        
        // Filter by supplier if specified (for supplier's own products)
        if ($supplierId !== null) {
            $sql .= " AND p.supplier_id = ?";
            $params[] = $supplierId;
        }
        
        if (!empty($query)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($categoryId !== null) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($minPrice !== null) {
            $sql .= " AND p.price_cents >= ?";
            $params[] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $sql .= " AND p.price_cents <= ?";
            $params[] = $maxPrice;
        }
        
        if ($verifiedOnly) {
            $sql .= " AND p.verified = 1";
        }
        
        // Apply premium ranking logic: Ads > Premium > Sentiment > Freemium > Newest
        $sql .= " ORDER BY 
            CASE 
                WHEN a.id IS NOT NULL THEN 1
                WHEN s.plan_type = 'premium' 
                     AND (s.premium_expires_at IS NULL OR s.premium_expires_at > ?) THEN 2
                ELSE 3
            END,
            s.sentiment_score DESC,
            p.verified DESC,
            p.created_at DESC";
        $params[] = $now;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    
    /**
     * Get ranked products with premium priority (for catalog, search, etc.)
     */
    public function getRanked(string $query = '', ?int $categoryId = null, ?int $minPrice = null, ?int $maxPrice = null, bool $verifiedOnly = false, int $limit = 0): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT p.*, 
                       c.name as category_name, 
                       s.business_name as supplier_name, 
                       s.verified_badge,
                       s.plan_type,
                       s.sentiment_score,
                       a.id as advertisement_id,
                       a.status as ad_status
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN advertisements a ON a.product_id = p.id 
                    AND a.status = 'active'
                    AND a.start_date <= ?
                    AND (a.end_date IS NULL OR a.end_date >= ?)
                WHERE 1=1";
        $params = [$now, $now];
        
        if (!empty($query)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($categoryId !== null) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($minPrice !== null) {
            $sql .= " AND p.price_cents >= ?";
            $params[] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $sql .= " AND p.price_cents <= ?";
            $params[] = $maxPrice;
        }
        
        if ($verifiedOnly) {
            $sql .= " AND p.verified = 1";
        }
        
        // Apply premium ranking logic: Ads > Premium > Sentiment > Freemium > Newest
        $sql .= " ORDER BY 
            CASE 
                WHEN a.id IS NOT NULL THEN 1
                WHEN s.plan_type = 'premium' 
                     AND (s.premium_expires_at IS NULL OR s.premium_expires_at > ?) THEN 2
                ELSE 3
            END,
            s.sentiment_score DESC,
            p.verified DESC,
            p.created_at DESC";
        $params[] = $now;
        
        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get premium supplier products only
     */
    public function getPremiumProducts(int $limit = 12): array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   c.name as category_name, 
                   s.business_name as supplier_name,
                   s.sentiment_score
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            JOIN suppliers s ON p.supplier_id = s.id
            WHERE s.plan_type = 'premium'
            AND (s.premium_expires_at IS NULL OR s.premium_expires_at > ?)
            AND p.verified = 1
            ORDER BY s.sentiment_score DESC, p.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$now, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sponsored products (with active advertisements)
     */
    public function getSponsoredProducts(int $limit = 6): array
    {
        $now = date('Y-m-d H:i:s');
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       c.name as category_name, 
                       s.business_name as supplier_name,
                       a.id as advertisement_id,
                       a.image_url as ad_image,
                       a.title as ad_title,
                       p.slug as slug
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN suppliers s ON p.supplier_id = s.id
                JOIN advertisements a ON a.product_id = p.id
                WHERE a.status = 'active'
                AND a.start_date <= ?
                AND (a.end_date IS NULL OR a.end_date >= ?)
                ORDER BY a.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$now, $now, $limit]);
            $results = $stmt->fetchAll();
            error_log("getSponsoredProducts: Found " . count($results) . " sponsored products");
            return $results;
        } catch (\Exception $e) {
            error_log("getSponsoredProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get price range for products
     * @param int|null $categoryId Category filter
     * @param int|null $supplierId Filter by supplier ID (for supplier's own products view)
     */
    public function getPriceRange(?int $categoryId = null, ?int $supplierId = null): array
    {
        $sql = "SELECT MIN(price_cents) as min_price, MAX(price_cents) as max_price FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($categoryId !== null) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($supplierId !== null) {
            $sql .= " AND supplier_id = ?";
            $params[] = $supplierId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return [
            'min' => (int)($result['min_price'] ?? 0),
            'max' => (int)($result['max_price'] ?? 100000)
        ];
    }
    
    /**
     * Get products by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.supplier_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured products (verified, with premium priority)
     */
    public function getFeatured(int $limit = 8): array
    {
        return $this->getRanked('', null, null, null, true, $limit);
    }
    
    /**
     * Decrement stock
     */
    public function decrementStock(int $id, int $quantity): bool
    {
        try {
            // First, verify the product exists and has enough stock
            $product = $this->find($id);
            if (!$product) {
                error_log("ERROR: Product ID {$id} not found for stock decrement");
                return false;
            }
            
            $currentStock = (int)($product['stock'] ?? 0);
            error_log("Decrementing stock for Product ID {$id}: Current stock = {$currentStock}, Decrementing by = {$quantity}");
            
            // Use atomic update with stock check
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET stock = GREATEST(0, stock - ?) 
                WHERE id = ? AND stock >= ?
            ");
            $result = $stmt->execute([$quantity, $id, $quantity]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Verify the update
                $updatedProduct = $this->find($id);
                $newStock = (int)($updatedProduct['stock'] ?? 0);
                error_log("✓ Stock decremented successfully: Product ID {$id}, Old stock = {$currentStock}, Decremented by = {$quantity}, New stock = {$newStock}");
                return true;
            } else {
                if ($currentStock < $quantity) {
                    error_log("✗ ERROR: Insufficient stock for Product ID {$id}. Current: {$currentStock}, Required: {$quantity}");
                } else {
                    error_log("✗ WARNING: Stock update executed but no rows affected for Product ID {$id}. Product may not exist.");
                }
                return false;
            }
        } catch (\Exception $e) {
            error_log("✗ EXCEPTION in decrementStock for Product ID {$id}: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Generate unique slug from name
     */
    public function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        
        while (true) {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE slug = ?");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Format product for API response
     */
    public function formatForApi(array $product): array
    {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'price_cents' => $product['price_cents'],
            'currency' => $product['currency'],
            'supplier_name' => $product['supplier_name'] ?? '',
            'verified' => (bool)($product['verified'] ?? false)
        ];
    }
    
    /**
     * Format multiple products for API
     */
    public function formatMultipleForApi(array $products): array
    {
        return array_map([$this, 'formatForApi'], $products);
    }
}

