<?php

/**
 * Order management helper functions
 */

use App\Order;
use App\DB;
use App\Security;

/**
 * Map status values for database compatibility
 * The database ENUM uses 'shipped' but we use 'out_for_delivery' in the UI
 */
function mapStatusForDatabase(string $status): string
{
    // Map 'out_for_delivery' to 'shipped' for database ENUM compatibility
    if ($status === 'out_for_delivery') {
        return 'shipped';
    }
    return $status;
}

/**
 * Map status values from database to UI
 * Convert 'shipped' back to 'out_for_delivery' for display
 */
function mapStatusFromDatabase(string $status): string
{
    // Map 'shipped' to 'out_for_delivery' for UI display
    if ($status === 'shipped') {
        return 'out_for_delivery';
    }
    return $status;
}

/**
 * Update order status
 * Handles both status and current_status fields, plus timestamps
 */
function updateOrderStatus(int $orderId, string $newStatus, int $userId, string $notes = ''): bool
{
    try {
        $db = DB::getInstance();
        
        // Map status for database compatibility
        $dbStatus = mapStatusForDatabase($newStatus);
        
        // Get current order to check existing status
        $orderModel = new Order();
        $order = $orderModel->find($orderId);
        
        if (!$order) {
            error_log("updateOrderStatus: Order {$orderId} not found");
            return false;
        }
        
        // Determine which timestamp field to update based on status
        // Check which timestamp columns exist first
        $timestampFields = [];
        $existingColumns = [];
        try {
            $colStmt = $db->query("SHOW COLUMNS FROM orders");
            $columns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);
            $existingColumns = array_map('strtolower', $columns);
        } catch (\PDOException $e) {
            error_log("Could not check columns: " . $e->getMessage());
        }
        
        // Only add timestamp fields if the columns exist
        switch ($newStatus) {
            case 'processing':
                if (in_array('processing_started_at', $existingColumns)) {
                    $timestampFields[] = "processing_started_at = NOW()";
                }
                break;
            case 'out_for_delivery':
            case 'shipped':
                if (in_array('shipped_at', $existingColumns)) {
                    $timestampFields[] = "shipped_at = NOW()";
                }
                break;
            case 'delivered':
                if (in_array('delivered_at', $existingColumns)) {
                    $timestampFields[] = "delivered_at = NOW()";
                }
                break;
        }
        
        // Build update query
        $updateFields = ["status = ?"];
        $updateParams = [$dbStatus];
        
        // Update current_status if column exists (check first)
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM orders LIKE 'current_status'");
            if ($checkStmt->rowCount() > 0) {
                $updateFields[] = "current_status = ?";
                $updateParams[] = $newStatus; // Keep original status for current_status (no mapping)
            }
        } catch (\PDOException $e) {
            // Column doesn't exist, skip it
            error_log("current_status column not found, skipping update");
        }
        
        // Add timestamp fields
        if (!empty($timestampFields)) {
            $updateFields = array_merge($updateFields, $timestampFields);
        }
        
        // Add updated_at
        $updateFields[] = "updated_at = NOW()";
        
        $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $updateParams[] = $orderId;
        
        error_log("updateOrderStatus SQL: {$sql}");
        error_log("updateOrderStatus Params: " . json_encode($updateParams));
        
        try {
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($updateParams);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $errorMsg = "PDO Error: " . json_encode($errorInfo) . " | SQL: {$sql} | Params: " . json_encode($updateParams);
                error_log("updateOrderStatus PDO Error: " . $errorMsg);
                throw new \Exception("Database update failed: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            $rowsAffected = $stmt->rowCount();
            error_log("updateOrderStatus: Updated {$rowsAffected} rows for order {$orderId}, status: {$dbStatus}");
            
            if ($rowsAffected === 0) {
                // Check if order actually exists
                $checkOrder = $orderModel->find($orderId);
                if (!$checkOrder) {
                    error_log("updateOrderStatus: Order {$orderId} does not exist");
                    throw new \Exception("Order not found");
                } else {
                    error_log("updateOrderStatus: WARNING - No rows updated for order {$orderId} (status may already be {$dbStatus})");
                    // Don't throw error if status is already set - just return true
                    return true;
                }
            }
        } catch (\PDOException $e) {
            error_log("updateOrderStatus PDOException in execute: " . $e->getMessage());
            error_log("SQL: {$sql}");
            error_log("Params: " . json_encode($updateParams));
            throw $e; // Re-throw to be caught by outer catch
        }
        
        // Log the status change
        Security::log('order_status_updated', $userId, [
            'order_id' => $orderId,
            'old_status' => $order['status'] ?? 'unknown',
            'new_status' => $newStatus,
            'db_status' => $dbStatus,
            'notes' => $notes
        ]);
        
        return true;
        
    } catch (\PDOException $e) {
        $errorMsg = "PDOException: " . $e->getMessage() . " | SQL State: " . $e->getCode();
        error_log("updateOrderStatus PDOException: " . $errorMsg);
        error_log("updateOrderStatus Stack Trace: " . $e->getTraceAsString());
        // Re-throw so controller can handle it
        throw new \Exception("Database error: " . $e->getMessage(), 0, $e);
    } catch (\Exception $e) {
        error_log("updateOrderStatus Exception: " . $e->getMessage());
        error_log("updateOrderStatus Stack Trace: " . $e->getTraceAsString());
        // Re-throw so controller can handle it
        throw $e;
    }
}

/**
 * Get order details with user information
 */
function getOrderDetails(int $orderId): ?array
{
    try {
        error_log("getOrderDetails: Looking for order ID: {$orderId}");
        $db = DB::getInstance();
        $stmt = $db->prepare("
            SELECT o.*,
                   u.name as customer_name,
                   u.email as customer_email,
                   u.name as buyer_name,
                   u.email as buyer_email
            FROM orders o
            LEFT JOIN users u ON o.buyer_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($order === false || empty($order)) {
            error_log("getOrderDetails: Order ID {$orderId} not found in database");
            // Try a simpler query without JOIN to see if order exists at all
            $simpleStmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
            $simpleStmt->execute([$orderId]);
            $simpleOrder = $simpleStmt->fetch(\PDO::FETCH_ASSOC);
            if ($simpleOrder) {
                error_log("getOrderDetails: Order exists but JOIN failed. Order data: " . json_encode($simpleOrder));
                // Use the simple order and add user data separately
                $order = $simpleOrder;
                $userStmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
                $userStmt->execute([$order['buyer_id']]);
                $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
                if ($user) {
                    $order['customer_name'] = $user['name'];
                    $order['customer_email'] = $user['email'];
                    $order['buyer_name'] = $user['name'];
                    $order['buyer_email'] = $user['email'];
                }
            } else {
                error_log("getOrderDetails: Order ID {$orderId} does not exist in orders table");
                return null;
            }
        }
        
        error_log("getOrderDetails: Found order ID {$orderId}, status: " . ($order['status'] ?? 'N/A'));
        
        // Get order items
        $orderItemModel = new \App\OrderItem();
        $order['items'] = $orderItemModel->getByOrderId($orderId);
        
        // Map status from database to UI
        if (isset($order['status'])) {
            $order['status'] = mapStatusFromDatabase($order['status']);
        }
        if (isset($order['current_status'])) {
            $order['current_status'] = mapStatusFromDatabase($order['current_status']);
        }
        
        return $order;
    } catch (\PDOException $e) {
        error_log("getOrderDetails PDOException for order ID {$orderId}: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        
        // If JOIN failed, try simpler query
        try {
            $db = DB::getInstance();
            $simpleStmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
            $simpleStmt->execute([$orderId]);
            $order = $simpleStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($order) {
                error_log("getOrderDetails: Retrieved order using simple query");
                // Get user data separately
                $userStmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
                $userStmt->execute([$order['buyer_id']]);
                $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
                if ($user) {
                    $order['customer_name'] = $user['name'];
                    $order['customer_email'] = $user['email'];
                    $order['buyer_name'] = $user['name'];
                    $order['buyer_email'] = $user['email'];
                } else {
                    $order['customer_name'] = 'N/A';
                    $order['customer_email'] = 'N/A';
                    $order['buyer_name'] = 'N/A';
                    $order['buyer_email'] = 'N/A';
                }
                
                // Get order items
                $orderItemModel = new \App\OrderItem();
                $order['items'] = $orderItemModel->getByOrderId($orderId);
                
                // Map status
                if (isset($order['status'])) {
                    $order['status'] = mapStatusFromDatabase($order['status']);
                }
                if (isset($order['current_status'])) {
                    $order['current_status'] = mapStatusFromDatabase($order['current_status']);
                }
                
                return $order;
            }
        } catch (\Exception $fallbackError) {
            error_log("getOrderDetails fallback also failed: " . $fallbackError->getMessage());
        }
        
        return null;
    } catch (\Exception $e) {
        error_log("getOrderDetails error for order ID {$orderId}: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}

/**
 * Get next available statuses for an order
 */
function getNextStatuses(string $currentStatus): array
{
    $statusFlow = [
        'placed' => ['paid', 'processing'],
        'pending' => ['paid', 'processing'],
        'paid' => ['processing'],
        'processing' => ['out_for_delivery'],
        'out_for_delivery' => ['delivered'],
        'shipped' => ['delivered'], // Database value
        'delivered' => []
    ];
    
    // Map current status from database if needed
    $mappedStatus = mapStatusFromDatabase($currentStatus);
    
    return $statusFlow[$mappedStatus] ?? [];
}

/**
 * Get order timeline stages
 * Returns an array of timeline stages with their status
 */
function getOrderTimeline(array $order): array
{
    $status = $order['current_status'] ?? $order['status'] ?? 'placed';
    $mappedStatus = mapStatusFromDatabase($status);
    
    $timeline = [
        'placed' => [
            'status' => 'completed',
            'timestamp' => $order['created_at'] ?? null,
            'label' => 'Order Placed'
        ],
        'paid' => [
            'status' => in_array($mappedStatus, ['paid', 'processing', 'out_for_delivery', 'delivered']) ? 'completed' : 'pending',
            'timestamp' => $order['payment_confirmed_at'] ?? $order['created_at'] ?? null,
            'label' => 'Payment Successful'
        ],
        'processing' => [
            'status' => in_array($mappedStatus, ['processing', 'out_for_delivery', 'delivered']) ? 'completed' : 'pending',
            'timestamp' => $order['processing_started_at'] ?? null,
            'label' => 'Supplier Processing'
        ],
        'out_for_delivery' => [
            'status' => in_array($mappedStatus, ['out_for_delivery', 'delivered']) ? 'completed' : 'pending',
            'timestamp' => $order['out_for_delivery_started_at'] ?? $order['shipped_at'] ?? null,
            'label' => 'Out for Delivery'
        ],
        'delivered' => [
            'status' => ($mappedStatus === 'delivered') ? 'completed' : 'pending',
            'timestamp' => $order['delivered_at'] ?? null,
            'label' => 'Delivered'
        ]
    ];
    
    return $timeline;
}

/**
 * Get user orders with optional status filter
 */
function getUserOrders(int $userId, ?string $statusFilter = null): array
{
    try {
        $db = DB::getInstance();
        
        $whereClause = "WHERE o.buyer_id = ?";
        $params = [$userId];
        
        if ($statusFilter) {
            // Map filter status from UI to database if needed
            $dbStatus = mapStatusForDatabase($statusFilter);
            $whereClause .= " AND (o.status = ? OR o.current_status = ?)";
            $params[] = $dbStatus;
            $params[] = $statusFilter; // Also check current_status with original value
        }
        
        $stmt = $db->prepare("
            SELECT o.*,
                   u.name as buyer_name,
                   u.email as buyer_email,
                   COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN users u ON o.buyer_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            {$whereClause}
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Map status from database to UI for all orders
        foreach ($orders as &$order) {
            if (isset($order['status'])) {
                $order['status'] = mapStatusFromDatabase($order['status']);
            }
            if (isset($order['current_status'])) {
                $order['current_status'] = mapStatusFromDatabase($order['current_status']);
            }
        }
        unset($order);
        
        return $orders;
    } catch (\Exception $e) {
        error_log("getUserOrders error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get status badge class
 */
function getStatusBadge(string $status): string
{
    $badges = [
        'placed' => 'secondary',
        'pending' => 'warning',
        'paid' => 'info',
        'processing' => 'primary',
        'out_for_delivery' => 'warning',
        'shipped' => 'warning',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    
    $mappedStatus = mapStatusFromDatabase($status);
    return $badges[$mappedStatus] ?? 'secondary';
}

/**
 * Get status badge HTML
 */
function getStatusBadgeHtml(string $status): string
{
    $badgeClass = getStatusBadge($status);
    $statusLabels = [
        'placed' => 'Placed',
        'pending' => 'Pending',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'out_for_delivery' => 'Out for Delivery',
        'shipped' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    
    $mappedStatus = mapStatusFromDatabase($status);
    $label = $statusLabels[$mappedStatus] ?? ucfirst($mappedStatus);
    
    return '<span class="badge bg-' . htmlspecialchars($badgeClass) . '">' . htmlspecialchars($label) . '</span>';
}
