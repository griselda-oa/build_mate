<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * Order model
 */
class Order extends Model
{
    protected string $table = 'orders';
    
    /**
     * Get order with items
     */
    public function getWithItems(int $id): ?array
    {
        // Check which payment columns exist
        $columnsStmt = $this->db->query("SHOW COLUMNS FROM {$this->table}");
        $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $paymentFields = '';
        if (in_array('payment_reference', $columns) && in_array('payment_method', $columns)) {
            $paymentFields = ', o.payment_reference, o.payment_method';
        } elseif (in_array('payment_reference', $columns)) {
            $paymentFields = ', o.payment_reference';
        } elseif (in_array('payment_method', $columns)) {
            $paymentFields = ', o.payment_method';
        }
        
        // Check for payment_confirmed_at timestamp
        $timestampFields = '';
        if (in_array('payment_confirmed_at', $columns)) {
            $timestampFields = ', o.payment_confirmed_at';
        }
        if (in_array('order_placed_at', $columns)) {
            $timestampFields .= ', o.order_placed_at';
        }
        
        $stmt = $this->db->prepare("
            SELECT o.*{$paymentFields}{$timestampFields}, u.name as buyer_name, u.email as buyer_email
            FROM {$this->table} o
            JOIN users u ON o.buyer_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            return null;
        }
        
        $order['items'] = (new OrderItem())->getByOrderId($id);
        return $order;
    }
    
    /**
     * Get orders by buyer
     */
    public function getByBuyer(int $buyerId): array
    {
        // Check which columns exist
        $columnsStmt = $this->db->query("SHOW COLUMNS FROM {$this->table}");
        $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $paymentFields = '';
        if (in_array('payment_reference', $columns) && in_array('payment_method', $columns)) {
            $paymentFields = ', o.payment_reference, o.payment_method';
        } elseif (in_array('payment_reference', $columns)) {
            $paymentFields = ', o.payment_reference';
        } elseif (in_array('payment_method', $columns)) {
            $paymentFields = ', o.payment_method';
        }
        
        $stmt = $this->db->prepare("
            SELECT o.*{$paymentFields},
                   COUNT(oi.id) as item_count,
                   SUM(oi.quantity * oi.price_cents) as calculated_total
            FROM {$this->table} o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.buyer_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$buyerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get orders by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT o.*, 
                   COUNT(DISTINCT oi.id) as item_count
            FROM {$this->table} o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            WHERE p.supplier_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all orders for admin
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT o.*, u.name as buyer_name, u.email as buyer_email,
                   COUNT(oi.id) as item_count
            FROM {$this->table} o
            JOIN users u ON o.buyer_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get order statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status != 'cancelled' THEN total_cents ELSE 0 END) as total_gmv
            FROM {$this->table}
        ");
        return $stmt->fetch() ?: ['total_orders' => 0, 'total_gmv' => 0];
    }
    
    /**
     * Calculate cart total from cart items
     */
    public function calculateCartTotal(array $cartItems, Product $productModel): int
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $product = $productModel->find($item['product_id']);
            if ($product) {
                $total += $product['price_cents'] * $item['qty'];
            }
        }
        return $total;
    }
    
    /**
     * Create order from cart
     */
    public function createFromCart(int $buyerId, array $cartItems, array $address, Product $productModel): int
    {
        $this->db->beginTransaction();
        
        try {
            // Calculate total and validate stock
            $total = 0;
            
            foreach ($cartItems as $item) {
                $product = $productModel->find($item['product_id']);
                if (!$product || $product['stock'] < $item['qty']) {
                    throw new \Exception('Insufficient stock for ' . ($product['name'] ?? 'product'));
                }
                $total += $product['price_cents'] * $item['qty'];
            }
            
            // Build delivery address string
            $deliveryAddress = implode(', ', array_filter([
                $address['street'] ?? '',
                $address['city'] ?? '',
                $address['region'] ?? '',
                $address['country'] ?? 'Ghana'
            ]));
            
            // Store full address as JSON for delivery system
            $addressJson = json_encode([
                'street' => $address['street'] ?? '',
                'city' => $address['city'] ?? '',
                'region' => $address['region'] ?? '',
                'country' => $address['country'] ?? 'Ghana',
                'phone' => $address['phone'] ?? '',
                'delivery_lat' => $address['delivery_lat'] ?? null,
                'delivery_lng' => $address['delivery_lng'] ?? null
            ]);
            
            // Create order with new delivery fields
            // Only include columns that exist in the database
            // Check status column type first to avoid truncation errors
            $statusValue = 'placed'; // Default status
            try {
                $statusColStmt = $this->db->query("SHOW COLUMNS FROM {$this->table} WHERE Field = 'status'");
                $statusCol = $statusColStmt->fetch();
                if ($statusCol) {
                    $colType = strtolower($statusCol['Type'] ?? '');
                    // If it's an ENUM, check if 'placed' is allowed, otherwise use 'pending'
                    if (strpos($colType, 'enum') !== false) {
                        if (strpos($colType, "'placed'") === false) {
                            $statusValue = 'pending'; // Fallback to 'pending' if ENUM doesn't have 'placed'
                        }
                    } elseif (preg_match('/varchar\((\d+)\)/', $colType, $matches)) {
                        $maxLength = (int)$matches[1];
                        if (strlen($statusValue) > $maxLength) {
                            $statusValue = 'pending'; // Fallback if column too small
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Error checking status column: " . $e->getMessage());
                $statusValue = 'pending'; // Safe fallback
            }
            
            // Generate unique order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $orderData = [
                'order_number' => $orderNumber,
                'buyer_id' => $buyerId,
                'status' => $statusValue,
                'total_cents' => $total,
                'currency' => 'GHS'
            ];
            
            // Check which columns exist in the database
            try {
                $columnsStmt = $this->db->query("SHOW COLUMNS FROM {$this->table}");
                $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
                
                // Use delivery_address if it exists, otherwise use shipping_address
                if (in_array('delivery_address', $columns)) {
                    $orderData['delivery_address'] = $deliveryAddress;
                } elseif (in_array('shipping_address', $columns)) {
                    $orderData['shipping_address'] = $deliveryAddress;
                }
                
                if (in_array('escrow_held', $columns)) {
                    $orderData['escrow_held'] = 0;
                }
                if (in_array('delivery_lat', $columns)) {
                    $orderData['delivery_lat'] = $address['delivery_lat'] ?? null;
                }
                if (in_array('delivery_lng', $columns)) {
                    $orderData['delivery_lng'] = $address['delivery_lng'] ?? null;
                }
                if (in_array('delivery_region', $columns)) {
                    $orderData['delivery_region'] = $address['region'] ?? null;
                }
                if (in_array('delivery_phone', $columns)) {
                    $orderData['delivery_phone'] = $address['phone'] ?? null;
                }
                if (in_array('address_json', $columns)) {
                    $orderData['address_json'] = $addressJson;
                }
            } catch (\Exception $e) {
                // If we can't check columns, try to use shipping_address as fallback
                error_log("Could not check columns: " . $e->getMessage());
                $orderData['shipping_address'] = $deliveryAddress;
            }
            
            $orderId = $this->create($orderData);
            
            // Create order items
            $orderItemModel = new OrderItem();
            foreach ($cartItems as $item) {
                $product = $productModel->find($item['product_id']);
                $orderItemModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'price_cents' => $product['price_cents']
                ]);
            }
            
            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get recent orders
     */
    public function getRecent(int $limit = 10): array
    {
        return array_slice($this->getAll(), 0, $limit);
    }
}

