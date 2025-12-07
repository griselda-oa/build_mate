<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Order Item model
 */
class OrderItem extends Model
{
    protected string $table = 'order_items';
    
    /**
     * Get items by order ID
     */
    public function getByOrderId(int $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT oi.*, oi.quantity as qty, p.name as product_name, p.slug as product_slug, p.image_url as image_path
            FROM {$this->table} oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}

