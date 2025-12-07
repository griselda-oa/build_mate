<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Order;
use App\DB;
use App\Response;
use App\Security;

/**
 * Admin Order Management Controller
 */
class AdminOrderController extends Controller
{
    /**
     * List all orders
     */
    public function index(): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'admin') {
            $this->redirect('/login');
            return;
        }
        
        require_once __DIR__ . '/../includes/order_functions.php';
        
        $orderModel = new Order();
        $orders = $orderModel->getAll();
        
        // Get status filter
        $statusFilter = $_GET['status'] ?? null;
        if ($statusFilter) {
            $orders = array_filter($orders, function($order) use ($statusFilter) {
                $status = $order['current_status'] ?? $order['status'] ?? 'pending';
                return $status === $statusFilter;
            });
        }
        
        echo $this->view->render('Admin/orders', [
            'orders' => $orders,
            'statusFilter' => $statusFilter
        ]);
    }
    
    /**
     * Update order status
     */
    public function updateStatus(): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        require_once __DIR__ . '/../includes/order_functions.php';
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? 'Status updated by admin';
        
        if (!$orderId || !$newStatus) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
            return;
        }
        
        // Validate status transition - Admin can ONLY update to out_for_delivery or delivered
        // Admin CANNOT mark orders as "processing" - only suppliers can do that
        $validStatuses = ['out_for_delivery', 'delivered'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Admins cannot mark orders as processing. Only suppliers can do that.'], 400);
            return;
        }
        
        // Check that order is already in processing or beyond before admin can update
        $orderModel = new Order();
        $currentOrder = $orderModel->find($orderId);
        if (!$currentOrder) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }
        
        $currentStatus = $currentOrder['current_status'] ?? $currentOrder['status'] ?? 'placed';
        $statusField = $currentOrder['status'] ?? 'placed';
        
        // Map 'shipped' (ENUM value) to 'out_for_delivery' for validation
        $statusForValidation = $currentStatus;
        if ($statusForValidation === 'shipped') {
            $statusForValidation = 'out_for_delivery';
        }
        if ($statusField === 'shipped') {
            $statusField = 'out_for_delivery';
        }
        
        // Admin can update orders that are:
        // 1. Paid (payment successful) - can mark as out_for_delivery
        // 2. Processing or beyond - can mark as out_for_delivery or delivered
        $paidStatuses = ['paid', 'paid_escrow', 'paid_paystack_secure', 'payment_confirmed'];
        $processingStatuses = ['processing', 'out_for_delivery', 'delivered', 'shipped'];
        
        $isPaid = in_array($statusForValidation, $paidStatuses) ||
                  in_array($statusField, $paidStatuses) ||
                  in_array($currentStatus, $paidStatuses) ||
                  in_array($currentOrder['status'] ?? '', $paidStatuses) ||
                  !empty($currentOrder['payment_reference']);
        
        $isInProcessing = in_array($statusForValidation, $processingStatuses) ||
                         in_array($statusField, $processingStatuses) ||
                         in_array($currentStatus, $processingStatuses) ||
                         in_array($currentOrder['status'] ?? '', $processingStatuses);
        
        // Allow if order is paid OR in processing/beyond
        if (!$isPaid && !$isInProcessing) {
            error_log("Validation failed - currentStatus: {$currentStatus}, statusField: {$statusField}");
            $this->json([
                'success' => false, 
                'message' => 'Order must be paid first before admin can update status. Current status: ' . $currentStatus
            ], 400);
            return;
        }
        
        // Additional check: If marking as "delivered", order must be out_for_delivery first
        $isOutForDelivery = in_array($statusForValidation, ['out_for_delivery', 'delivered']) ||
                           in_array($statusField, ['shipped', 'delivered']) ||
                           in_array($currentStatus, ['out_for_delivery', 'delivered']);
        
        if ($newStatus === 'delivered' && !$isOutForDelivery) {
            $this->json([
                'success' => false,
                'message' => 'Order must be "Out for Delivery" before it can be marked as "Delivered"'
            ], 400);
            return;
        }
        
        // Use the proper updateOrderStatus function that handles current_status and timestamps
        try {
            error_log("=== AdminOrderController::updateStatus START ===");
            error_log("Order ID: {$orderId}");
            error_log("New Status: {$newStatus}");
            error_log("User ID: " . $user['id']);
            error_log("Notes: {$notes}");
            
            $success = updateOrderStatus($orderId, $newStatus, $user['id'], $notes);
            
            error_log("updateOrderStatus returned: " . ($success ? 'true' : 'false'));
            
            if ($success) {
                // Log the status change
                Security::log('order_status_updated', $user['id'], [
                    'order_id' => $orderId,
                    'new_status' => $newStatus,
                    'updated_by' => 'admin'
                ]);
                
                error_log("=== AdminOrderController::updateStatus SUCCESS ===");
                $this->json([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'new_status' => $newStatus
                ]);
            } else {
                error_log("=== AdminOrderController::updateStatus FAILED ===");
                error_log("updateOrderStatus returned false for order #{$orderId}, status: {$newStatus}");
                $this->json([
                    'success' => false, 
                    'message' => 'Failed to update order status. Please check server logs for details.'
                ], 500);
            }
        } catch (\PDOException $e) {
            error_log("=== AdminOrderController::updateStatus PDO EXCEPTION ===");
            error_log("Message: " . $e->getMessage());
            error_log("Code: " . $e->getCode());
            error_log("File: " . $e->getFile());
            error_log("Line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json([
                'success' => false, 
                'message' => 'Database error: ' . $e->getMessage() . ' (Check server logs for details)'
            ], 500);
        } catch (\Exception $e) {
            error_log("=== AdminOrderController::updateStatus EXCEPTION ===");
            error_log("Message: " . $e->getMessage());
            error_log("File: " . $e->getFile());
            error_log("Line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage() . ' (Check server logs for details)'
            ], 500);
        }
    }
    
    /**
     * View order details (admin)
     */
    public function show(int $id): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'admin') {
            $this->redirect('/login');
            return;
        }
        
        try {
            require_once __DIR__ . '/../includes/order_functions.php';
            
            error_log("AdminOrderController::show - Called with Order ID: {$id} (type: " . gettype($id) . ")");
            
            // Ensure $id is an integer
            $id = (int)$id;
            if ($id <= 0) {
                error_log("AdminOrderController::show - Invalid order ID: {$id}");
                $this->setFlash('error', 'Invalid order ID');
                $this->redirect('/admin/orders');
                return;
            }
            
            $order = getOrderDetails($id);
            
            if (!$order) {
                error_log("AdminOrderController::show - Order not found for ID: {$id}");
                error_log("AdminOrderController::show - Attempting to find order using Order model directly");
                
                // Try to get order directly from model as fallback
                $orderModel = new Order();
                $order = $orderModel->find($id);
                
                if (!$order) {
                    error_log("AdminOrderController::show - Order not found even with direct model lookup");
                    $this->setFlash('error', 'Order not found');
                    $this->redirect('/admin/orders');
                    return;
                }
                
                // If we got order from model, add items
                $orderItemModel = new \App\OrderItem();
                $order['items'] = $orderItemModel->getByOrderId($id);
                
                // Map status
                require_once __DIR__ . '/../includes/order_functions.php';
                if (isset($order['status'])) {
                    $order['status'] = mapStatusFromDatabase($order['status']);
                }
                if (isset($order['current_status'])) {
                    $order['current_status'] = mapStatusFromDatabase($order['current_status']);
                }
            }
            
            error_log("AdminOrderController::show - Order found: " . json_encode(['id' => $order['id'], 'status' => $order['status'] ?? 'N/A']));
            
            $timeline = getOrderTimeline($order);
            $nextStatuses = getNextStatuses($order['current_status'] ?? $order['status'] ?? 'pending');
            
            error_log("AdminOrderController::show - Rendering view");
            
            echo $this->view->render('Admin/order-details', [
                'order' => $order,
                'timeline' => $timeline,
                'nextStatuses' => $nextStatuses,
                'flash' => $this->getFlash()
            ]);
            
            error_log("AdminOrderController::show - View rendered successfully");
            return; // Explicit return to prevent any further execution
        } catch (\Throwable $e) {
            error_log("AdminOrderController::show - ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            $this->setFlash('error', 'Error loading order details: ' . $e->getMessage());
            $this->redirect('/admin/orders');
            return;
        }
    }
    
    /**
     * Delete pending order (Admin only)
     */
    public function delete($id): void
    {
        $id = (int)$id;
        $user = $this->user();
        
        if (!$user || $user['role'] !== 'admin') {
            $this->setFlash('error', 'Unauthorized access');
            $this->redirect('/login');
            return;
        }
        
        $orderModel = new Order();
        $order = $orderModel->find($id);
        
        if (!$order) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('/admin/orders');
            return;
        }
        
        // Only allow deletion of pending orders
        if ($order['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending orders can be deleted. This order is: ' . $order['status']);
            $this->redirect('/admin/orders');
            return;
        }
        
        try {
            $db = DB::getInstance();
            $db->beginTransaction();
            
            // Delete order items first (foreign key constraint)
            $stmt = $db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);
            
            // Delete the order
            $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            
            Security::log('order_deleted', $user['id'], ['order_id' => $id]);
            $this->setFlash('success', 'Pending order deleted successfully');
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error deleting order: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete order: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/orders');
    }
}
