<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Delivery model
 */
class Delivery extends Model
{
    protected string $table = 'deliveries';
    
    /**
     * Find by order ID
     */
    public function findByOrderId(int $orderId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, o.buyer_id, u.name as buyer_name,
                   lu.name as logistics_user_name, lu.email as logistics_user_email
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON o.buyer_id = u.id
            LEFT JOIN users lu ON d.logistics_user_id = lu.id
            WHERE d.order_id = ? LIMIT 1
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get all deliveries with filters
     */
    public function getAll(?string $status = null, ?string $region = null, ?string $vehicleType = null): array
    {
        $where = [];
        $params = [];
        
        if ($status) {
            $where[] = "d.status = ?";
            $params[] = $status;
        }
        
        if ($region) {
            $where[] = "d.region = ?";
            $params[] = $region;
        }
        
        if ($vehicleType) {
            $where[] = "d.vehicle_type = ?";
            $params[] = $vehicleType;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT d.*, o.buyer_id, u.name as buyer_name, u.email as buyer_email, o.total_cents
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON o.buyer_id = u.id
            {$whereClause}
            ORDER BY d.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get deliveries by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, o.buyer_id, u.name as buyer_name, o.total_cents
            FROM {$this->table} d
            JOIN orders o ON d.order_id = o.id
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN users u ON o.buyer_id = u.id
            WHERE p.supplier_id = ?
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update delivery status
     */
    public function updateStatus(int $deliveryId, string $status, ?int $changedBy = null, ?string $changedByRole = null, ?string $notes = null, ?string $deliveryCode = null): bool
    {
        $db = $this->db;
        $db->beginTransaction();
        
        try {
            // Prepare update data
            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $changedBy
            ];
            
            // Add delivery code if provided
            if ($deliveryCode !== null) {
                $updateData['delivery_code'] = $deliveryCode;
            }
            
            // Add notes if provided
            if ($notes !== null) {
                $updateData['admin_notes'] = $notes;
            }
            
            // Set delivered_at if status is delivered
            if ($status === 'delivered') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            }
            
            // Update delivery status
            $this->update($deliveryId, $updateData);
            
            // Log status change (if history table exists)
            try {
                $historyStmt = $db->prepare("
                    INSERT INTO delivery_status_history 
                    (delivery_id, status, changed_by, changed_by_role, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $historyStmt->execute([
                    $deliveryId,
                    $status,
                    $changedBy,
                    $changedByRole ?? 'system',
                    $notes
                ]);
            } catch (\Exception $e) {
                // History table might not exist, continue anyway
                error_log("Delivery history log error: " . $e->getMessage());
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Delivery status update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate and set delivery code
     */
    public function generateDeliveryCode(int $deliveryId): string
    {
        // Generate 6-digit code
        $code = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE delivery_code = ? AND id != ?");
        $stmt->execute([$code, $deliveryId]);
        while ($stmt->fetch()) {
            $code = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $stmt->execute([$code, $deliveryId]);
        }
        
        // Update delivery with code
        $this->update($deliveryId, ['delivery_code' => $code]);
        
        return $code;
    }
    
    /**
     * Confirm delivery by buyer with code
     */
    public function confirmByBuyer(int $deliveryId, string $code): bool
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE id = ? AND delivery_code = ? AND status = 'in_transit'
        ");
        $stmt->execute([$deliveryId, $code]);
        $delivery = $stmt->fetch();
        
        if (!$delivery) {
            return false;
        }
        
        // Update delivery
        $this->update($deliveryId, [
            'status' => 'delivered',
            'confirmed_by_buyer' => 1,
            'delivered_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Release payment
        $orderStmt = $this->db->prepare("
            UPDATE orders 
            SET payment_released = 1, 
                payment_released_at = NOW() 
            WHERE id = ?
        ");
        $orderStmt->execute([$delivery['order_id']]);
        
        return true;
    }
    
    /**
     * Mark delivered with photo (admin backup method)
     */
    public function markDeliveredWithPhoto(int $deliveryId, string $photoPath, ?int $adminId = null): bool
    {
        $this->update($deliveryId, [
            'status' => 'delivered',
            'delivery_photo' => $photoPath,
            'delivered_at' => date('Y-m-d H:i:s'),
            'confirmed_by_buyer' => 0, // Not confirmed by buyer, admin marked it
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $adminId
        ]);
        
        return true;
    }
    
    /**
     * Get status history for a delivery
     */
    public function getStatusHistory(int $deliveryId): array
    {
        $stmt = $this->db->prepare("
            SELECT h.*, u.name as changed_by_name
            FROM delivery_status_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.delivery_id = ?
            ORDER BY h.created_at ASC
        ");
        $stmt->execute([$deliveryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create delivery record from order
     */
    public function createFromOrder(int $orderId, array $orderData): int
    {
        // Determine vehicle type based on product size_category
        $vehicleType = 'motorbike';
        $stmt = $this->db->prepare("
            SELECT p.size_category
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            if (($product['size_category'] ?? 'small') === 'large') {
                $vehicleType = 'truck';
                break;
            }
        }
        
        return $this->create([
            'order_id' => $orderId,
            'delivery_lat' => $orderData['delivery_lat'],
            'delivery_lng' => $orderData['delivery_lng'],
            'street' => $orderData['street'],
            'city' => $orderData['city'],
            'region' => $orderData['region'],
            'phone' => $orderData['phone'],
            'vehicle_type' => $vehicleType,
            'status' => 'pending_pickup'
        ]);
    }
    
    /**
     * Get delivery statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_deliveries,
                COUNT(CASE WHEN status = 'pending_pickup' THEN 1 END) as pending_pickup,
                COUNT(CASE WHEN status = 'ready_for_pickup' THEN 1 END) as ready_for_pickup,
                COUNT(CASE WHEN status = 'picked_up' THEN 1 END) as picked_up,
                COUNT(CASE WHEN status = 'in_transit' THEN 1 END) as in_transit,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed
            FROM {$this->table}
        ");
        return $stmt->fetch() ?: [];
    }
}
