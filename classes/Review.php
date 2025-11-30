<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Review model
 */
class Review extends Model
{
    protected string $table = 'reviews';
    
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
     * Get reviews by product
     */
    public function getByProduct(int $productId): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as buyer_name, u.email as buyer_email,
                       o.id as order_number, o.created_at as order_date
                FROM {$this->table} r
                JOIN users u ON r.buyer_id = u.id
                JOIN orders o ON r.order_id = o.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Reviews table error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get reviews by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as buyer_name, u.email as buyer_email,
                       p.name as product_name, p.slug as product_slug,
                       o.id as order_number, o.created_at as order_date
                FROM {$this->table} r
                JOIN users u ON r.buyer_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                JOIN orders o ON r.order_id = o.id
                WHERE r.supplier_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$supplierId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Reviews by supplier error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get product rating statistics
     */
    public function getProductStats(int $productId): array
    {
        if (!$this->tableExists()) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0.0,
                'five_star' => 0,
                'four_star' => 0,
                'three_star' => 0,
                'two_star' => 0,
                'one_star' => 0
            ];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
                FROM {$this->table}
                WHERE product_id = ?
            ");
            $stmt->execute([$productId]);
            $result = $stmt->fetch();
            
            return [
                'total_reviews' => (int)($result['total_reviews'] ?? 0),
                'average_rating' => round((float)($result['average_rating'] ?? 0), 1),
                'five_star' => (int)($result['five_star'] ?? 0),
                'four_star' => (int)($result['four_star'] ?? 0),
                'three_star' => (int)($result['three_star'] ?? 0),
                'two_star' => (int)($result['two_star'] ?? 0),
                'one_star' => (int)($result['one_star'] ?? 0)
            ];
        } catch (\PDOException $e) {
            error_log("Reviews stats error: " . $e->getMessage());
            return [
                'total_reviews' => 0,
                'average_rating' => 0.0,
                'five_star' => 0,
                'four_star' => 0,
                'three_star' => 0,
                'two_star' => 0,
                'one_star' => 0
            ];
        }
    }
    
    /**
     * Check if user has purchased product
     * Updated to use new status values and payment_reference
     */
    public function hasPurchasedProduct(int $userId, int $productId): bool
    {
        // Check which columns exist in orders table
        $columnsStmt = $this->db->query("SHOW COLUMNS FROM orders");
        $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
        $hasPaymentRef = in_array('payment_reference', $columns);
        $hasPaymentMethod = in_array('payment_method', $columns);
        
        // Build query conditionally based on available columns
        // Only allow reviews for DELIVERED orders (user requirement)
        $conditions = ["o.status = 'delivered'"];
        
        if ($hasPaymentRef) {
            $conditions[] = "o.payment_reference IS NOT NULL";
        }
        if ($hasPaymentMethod) {
            $conditions[] = "o.payment_method IS NOT NULL";
        }
        
        $whereClause = "(" . implode(" OR ", $conditions) . ")";
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.buyer_id = ? 
            AND oi.product_id = ?
            AND {$whereClause}
        ");
        $stmt->execute([$userId, $productId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0) > 0;
    }
    
    /**
     * Check if user has purchased from supplier
     * Updated to use new status values: 'paid', 'processing', 'out_for_delivery', 'delivered'
     */
    public function hasPurchasedFromSupplier(int $userId, int $supplierId): bool
    {
        // Check which columns exist in orders table
        $columnsStmt = $this->db->query("SHOW COLUMNS FROM orders");
        $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
        $hasPaymentRef = in_array('payment_reference', $columns);
        $hasPaymentMethod = in_array('payment_method', $columns);
        
        // Build query conditionally based on available columns
        // Only allow reviews for DELIVERED orders (user requirement)
        $conditions = ["status = 'delivered'"];
        
        if ($hasPaymentRef) {
            $conditions[] = "payment_reference IS NOT NULL";
        }
        if ($hasPaymentMethod) {
            $conditions[] = "payment_method IS NOT NULL";
        }
        
        $whereClause = "(" . implode(" OR ", $conditions) . ")";
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM orders
            WHERE buyer_id = ? 
            AND supplier_id = ?
            AND {$whereClause}
        ");
        $stmt->execute([$userId, $supplierId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0) > 0;
    }
    
    /**
     * Check if user has already reviewed this product
     */
    public function hasReviewedProduct(int $userId, int $productId): bool
    {
        if (!$this->tableExists()) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE buyer_id = ? AND product_id = ?
            ");
            $stmt->execute([$userId, $productId]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0) > 0;
        } catch (\PDOException $e) {
            error_log("Check reviewed error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order ID for product purchase
     * Updated to use new status values
     */
    public function getOrderIdForProduct(int $userId, int $productId): ?int
    {
        // Check which columns exist in orders table
        $columnsStmt = $this->db->query("SHOW COLUMNS FROM orders");
        $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
        $hasPaymentRef = in_array('payment_reference', $columns);
        $hasPaymentMethod = in_array('payment_method', $columns);
        
        // Build query conditionally based on available columns
        // Only allow reviews for DELIVERED orders (user requirement)
        $conditions = ["o.status = 'delivered'"];
        
        if ($hasPaymentRef) {
            $conditions[] = "o.payment_reference IS NOT NULL";
        }
        if ($hasPaymentMethod) {
            $conditions[] = "o.payment_method IS NOT NULL";
        }
        
        $whereClause = "(" . implode(" OR ", $conditions) . ")";
        
        $stmt = $this->db->prepare("
            SELECT o.id
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.buyer_id = ? 
            AND oi.product_id = ?
            AND {$whereClause}
            ORDER BY o.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId, $productId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['id'] : null;
    }
    
    /**
     * Get order ID for supplier purchase (for supplier reviews)
     */
    public function getOrderIdForSupplier(int $userId, int $supplierId): ?int
    {
        // Check which columns exist in orders table
        $columnsStmt = $this->db->query("SHOW COLUMNS FROM orders");
        $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
        $hasPaymentRef = in_array('payment_reference', $columns);
        $hasPaymentMethod = in_array('payment_method', $columns);
        
        // Build query conditionally based on available columns
        // Only allow reviews for DELIVERED orders (user requirement)
        $conditions = ["status = 'delivered'"];
        
        if ($hasPaymentRef) {
            $conditions[] = "payment_reference IS NOT NULL";
        }
        if ($hasPaymentMethod) {
            $conditions[] = "payment_method IS NOT NULL";
        }
        
        $whereClause = "(" . implode(" OR ", $conditions) . ")";
        
        $stmt = $this->db->prepare("
            SELECT id
            FROM orders
            WHERE buyer_id = ? 
            AND supplier_id = ?
            AND {$whereClause}
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId, $supplierId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['id'] : null;
    }
    
    /**
     * Get supplier ID from product
     */
    public function getSupplierIdFromProduct(int $productId): ?int
    {
        $stmt = $this->db->prepare("SELECT supplier_id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['supplier_id'] : null;
    }
    
    /**
     * Get sentiment statistics for a product
     */
    public function getSentimentStats(int $productId): array
    {
        if (!$this->tableExists()) {
            return [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'average_score' => 0.500
            ];
        }
        
        try {
            // Check if sentiment columns exist
            $columnsStmt = $this->db->query("SHOW COLUMNS FROM {$this->table}");
            $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
            $hasSentiment = in_array('sentiment_label', $columns) && in_array('sentiment_score', $columns);
            
            if (!$hasSentiment) {
                return [
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0,
                    'average_score' => 0.500
                ];
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(CASE WHEN sentiment_label = 'positive' THEN 1 END) as positive,
                    COUNT(CASE WHEN sentiment_label = 'neutral' THEN 1 END) as neutral,
                    COUNT(CASE WHEN sentiment_label = 'negative' THEN 1 END) as negative,
                    AVG(sentiment_score) as average_score
                FROM {$this->table}
                WHERE product_id = ?
            ");
            $stmt->execute([$productId]);
            $result = $stmt->fetch();
            
            return [
                'positive' => (int)($result['positive'] ?? 0),
                'neutral' => (int)($result['neutral'] ?? 0),
                'negative' => (int)($result['negative'] ?? 0),
                'average_score' => round((float)($result['average_score'] ?? 0.500), 3)
            ];
        } catch (\PDOException $e) {
            error_log("Sentiment stats error: " . $e->getMessage());
            return [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'average_score' => 0.500
            ];
        }
    }
}

