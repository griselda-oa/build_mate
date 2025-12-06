<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Supplier;
use App\User;
use App\Product;
use App\Order;
use App\Delivery;
use App\Security;
use App\ChatMessage;
use App\ChatSession;
use App\KycDocument;
use App\Advertisement;

/**
 * Admin controller
 */
class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function dashboard(): void
    {
        $orderModel = new Order();
        $supplierModel = new Supplier();
        $deliveryModel = new Delivery();
        
        // Get statistics from models
        $orderStats = $orderModel->getStats();
        $supplierStats = $supplierModel->getStats();
        $deliveryStats = $deliveryModel->getStats();
        
        // KPIs
        $stats = [
            'total_orders' => $orderStats['total_orders'] ?? 0,
            'total_gmv' => $orderStats['total_gmv'] ?? 0,
            'verified_suppliers' => $supplierStats['verified_suppliers'] ?? 0,
            'pending_kyc' => $supplierStats['pending_kyc'] ?? 0,
            'disputes' => $deliveryStats['disputes'] ?? 0
        ];
        
        // Recent orders
        $recentOrders = $orderModel->getRecent(10) ?? [];
        
        // Get active advertisements for banner
        $advertisements = [];
        try {
            $adModel = new \App\Advertisement();
            $advertisements = $adModel->getActive();
            $advertisements = array_slice($advertisements, 0, 5);
        } catch (\Exception $e) {
            error_log("Error fetching advertisements: " . $e->getMessage());
        }
        
        echo $this->view->render('Admin/dashboard', [
            'advertisements' => $advertisements,
            'stats' => $stats,
            'recentOrders' => $recentOrders
        ]);
    }
    
    /**
     * Suppliers management
     */
    public function suppliers(): void
    {
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAllWithUsers();
        
        echo $this->view->render('Admin/suppliers', [
            'suppliers' => $suppliers,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * View supplier details with documents
     */
    public function viewSupplier(int $id): void
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier not found');
            $this->redirect('/admin/suppliers');
            return;
        }
        
        // Get user info
        $userModel = new User();
        $user = $userModel->find($supplier['user_id']);
        
        // Get KYC documents
        $kycDocumentModel = new KycDocument();
        $documents = $kycDocumentModel->getBySupplier($id);
        
        echo $this->view->render('Admin/supplier_details', [
            'supplier' => $supplier,
            'user' => $user,
            'documents' => $documents,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Approve supplier
     */
    public function approveSupplier(int $id): void
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier not found');
            $this->redirect('/admin/suppliers');
        }
        
        $supplierModel->update($id, [
            'kyc_status' => 'approved',
            'verified_badge' => 1
        ]);
        
        // Auto-verify all pending products for this approved supplier
        $productModel = new Product();
        $db = \App\DB::getInstance();
        $stmt = $db->prepare("UPDATE products SET verified = 1 WHERE supplier_id = ? AND verified = 0");
        $stmt->execute([$id]);
        $productsUpdated = $stmt->rowCount();
        
        Security::log('supplier_approved', $this->user()['id'], [
            'supplier_id' => $id,
            'products_auto_verified' => $productsUpdated
        ]);
        
        $message = 'Supplier approved';
        if ($productsUpdated > 0) {
            $message .= " and {$productsUpdated} product(s) auto-verified";
        }
        $this->setFlash('success', $message);
        $this->redirect('/admin/suppliers');
    }
    
    /**
     * Reject supplier
     */
    public function rejectSupplier(int $id): void
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier not found');
            $this->redirect('/admin/suppliers');
        }
        
        $supplierModel->update($id, [
            'kyc_status' => 'rejected',
            'verified_badge' => 0
        ]);
        
        Security::log('supplier_rejected', $this->user()['id'], ['supplier_id' => $id]);
        $this->setFlash('success', 'Supplier rejected');
        $this->redirect('/admin/suppliers');
    }
    
    /**
     * Delete supplier and all their products
     */
    public function deleteSupplier(int $id): void
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier not found');
            $this->redirect('/admin/suppliers');
            return;
        }
        
        $db = \App\DB::getInstance();
        
        // Check if supplier's products are in any orders
        $checkOrdersStmt = $db->prepare("
            SELECT COUNT(DISTINCT oi.order_id) as order_count, COUNT(oi.id) as item_count
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE p.supplier_id = ?
        ");
        $checkOrdersStmt->execute([$id]);
        $orderCheck = $checkOrdersStmt->fetch();
        $orderCount = (int)($orderCheck['order_count'] ?? 0);
        $itemCount = (int)($orderCheck['item_count'] ?? 0);
        
        if ($orderCount > 0) {
            $this->setFlash('error', "Cannot delete supplier: This supplier has products in {$orderCount} order(s) ({$itemCount} item(s)). Products that are part of orders cannot be deleted to maintain order history integrity.");
            $this->redirect('/admin/suppliers');
            return;
        }
        
        $db->beginTransaction();
        
        try {
            // Get count of products to delete (for logging)
            $productModel = new Product();
            $productsStmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE supplier_id = ?");
            $productsStmt->execute([$id]);
            $productsCount = $productsStmt->fetch()['count'] ?? 0;
            
            // Delete all products belonging to this supplier (only if not in orders)
            // First, delete products that are NOT in any order_items
            $deleteProductsStmt = $db->prepare("
                DELETE p FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                WHERE p.supplier_id = ? AND oi.product_id IS NULL
            ");
            $deleteProductsStmt->execute([$id]);
            $productsDeleted = $deleteProductsStmt->rowCount();
            
            // Check if there are any products left (those in orders)
            $remainingProductsStmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE supplier_id = ?");
            $remainingProductsStmt->execute([$id]);
            $remainingCount = (int)($remainingProductsStmt->fetch()['count'] ?? 0);
            
            if ($remainingCount > 0) {
                $db->rollBack();
                $this->setFlash('error', "Cannot delete supplier: {$remainingCount} product(s) are still referenced in orders and cannot be deleted.");
                $this->redirect('/admin/suppliers');
                return;
            }
            
            // Delete KYC documents
            $kycDocumentModel = new KycDocument();
            $kycStmt = $db->prepare("DELETE FROM kyc_documents WHERE supplier_id = ?");
            $kycStmt->execute([$id]);
            
            // Delete reviews for this supplier's products
            $reviewsStmt = $db->prepare("DELETE FROM reviews WHERE supplier_id = ?");
            $reviewsStmt->execute([$id]);
            
            // Delete the supplier record
            $supplierModel->delete($id);
            
            $db->commit();
            
            Security::log('supplier_deleted', $this->user()['id'], [
                'supplier_id' => $id,
                'products_deleted' => $productsDeleted
            ]);
            
            $this->setFlash('success', "Supplier deleted successfully. {$productsDeleted} product(s) were also removed.");
            $this->redirect('/admin/suppliers');
            
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error deleting supplier: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Provide more user-friendly error message
            $errorMsg = 'Failed to delete supplier';
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $errorMsg = 'Cannot delete supplier: This supplier has products that are part of existing orders. Products in orders cannot be deleted to maintain order history.';
            } else {
                $errorMsg = 'Failed to delete supplier: ' . $e->getMessage();
            }
            
            $this->setFlash('error', $errorMsg);
            $this->redirect('/admin/suppliers');
        }
    }
    
    /**
     * Users management
     */
    public function users(): void
    {
        $userModel = new User();
        $users = $userModel->findAll('created_at DESC');
        
        echo $this->view->render('Admin/users', [
            'users' => $users
        ]);
    }
    
    /**
     * Products management
     */
    public function products(): void
    {
        $productModel = new Product();
        $products = $productModel->findAll('created_at DESC');
        
        echo $this->view->render('Admin/products', [
            'products' => $products
        ]);
    }
    
    /**
     * Orders management
     */
    public function orders(): void
    {
        $orderModel = new Order();
        $orders = $orderModel->getAll();
        
        echo $this->view->render('Admin/orders', [
            'orders' => $orders
        ]);
    }
    
    /**
     * Chat management dashboard
     */
    public function chat(): void
    {
        $chatMessage = new ChatMessage();
        $chatSession = new ChatSession();
        
        $stats = $chatMessage->getStats();
        $activeSessions = $chatSession->getActiveSessions(20);
        
        echo $this->view->render('Admin/chat', [
            'title' => 'Chat Management',
            'stats' => $stats,
            'activeSessions' => $activeSessions,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Get chat sessions (AJAX)
     */
    public function chatSessions(): void
    {
        header('Content-Type: application/json');
        
        $chatSession = new ChatSession();
        $sessions = $chatSession->getActiveSessions(50);
        
        echo json_encode([
            'success' => true,
            'sessions' => $sessions
        ]);
    }
    
    /**
     * Get chat session detail
     */
    public function chatSessionDetail(string $sessionId): void
    {
        $chatMessage = new ChatMessage();
        $chatSession = new ChatSession();
        
        $session = $chatSession->findBySessionId($sessionId);
        $messages = $chatMessage->getSessionMessages($sessionId);
        
        echo $this->view->render('Admin/chat_session', [
            'title' => 'Chat Session Details',
            'session' => $session,
            'messages' => $messages,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Logistics dashboard - Manage deliveries
     */
    public function logistics(): void
    {
        $deliveryModel = new Delivery();
        
        // Get filter parameters
        $status = $_GET['status'] ?? null;
        $region = $_GET['region'] ?? null;
        $vehicleType = $_GET['vehicle_type'] ?? null;
        
        // Get all deliveries with filters
        $deliveries = $deliveryModel->getAll($status, $region, $vehicleType);
        
        // Get supplier names for each delivery
        $db = \App\DB::getInstance();
        foreach ($deliveries as &$delivery) {
            $stmt = $db->prepare("
                SELECT s.business_name, s.id as supplier_id
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                JOIN suppliers s ON p.supplier_id = s.id
                WHERE o.id = ?
                LIMIT 1
            ");
            $stmt->execute([$delivery['order_id']]);
            $supplier = $stmt->fetch();
            $delivery['supplier_name'] = $supplier['business_name'] ?? 'N/A';
            $delivery['supplier_id'] = $supplier['supplier_id'] ?? null;
        }
        unset($delivery);
        
        echo $this->view->render('Admin/logistics', [
            'deliveries' => $deliveries,
            'filters' => [
                'status' => $status,
                'region' => $region,
                'vehicle_type' => $vehicleType
            ],
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Premium management dashboard
     */
    public function premium(): void
    {
        $supplierModel = new Supplier();
        $adModel = new \App\Advertisement();
        
        $premiumSuppliers = $supplierModel->getPremiumSuppliers();
        $expiringSoon = $supplierModel->getExpiringSoon(7);
        $lowSentiment = $supplierModel->getLowSentimentSuppliers(0.4);
        $pendingAds = $adModel->getPending();
        
        echo $this->view->render('Admin/premium', [
            'premiumSuppliers' => $premiumSuppliers,
            'expiringSoon' => $expiringSoon,
            'lowSentiment' => $lowSentiment,
            'pendingAds' => $pendingAds,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Downgrade supplier from premium
     */
    public function downgradeSupplier(int $id): void
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier not found');
            $this->redirect('/admin/premium');
            return;
        }
        
        if ($supplierModel->downgradeToFreemium($id)) {
            Security::log('supplier_downgraded', $this->user()['id'], [
                'supplier_id' => $id,
                'reason' => 'Admin downgrade'
            ]);
            $this->setFlash('success', 'Supplier downgraded to freemium');
        } else {
            $this->setFlash('error', 'Failed to downgrade supplier');
        }
        
        $this->redirect('/admin/premium');
    }
    
    /**
     * Approve advertisement
     */
    public function approveAdvertisement(int $id): void
    {
        $adModel = new \App\Advertisement();
        $ad = $adModel->find($id);
        
        if (!$ad) {
            $this->setFlash('error', 'Advertisement not found');
            $this->redirect('/admin/premium');
            return;
        }
        
        if ($adModel->update($id, ['status' => 'active'])) {
            Security::log('advertisement_approved', $this->user()['id'], [
                'advertisement_id' => $id,
                'supplier_id' => $ad['supplier_id']
            ]);
            $this->setFlash('success', 'Advertisement approved');
        } else {
            $this->setFlash('error', 'Failed to approve advertisement');
        }
        
        $this->redirect('/admin/premium');
    }
    
    /**
     * Reject advertisement
     */
    public function rejectAdvertisement(int $id): void
    {
        $adModel = new \App\Advertisement();
        $ad = $adModel->find($id);
        
        if (!$ad) {
            $this->setFlash('error', 'Advertisement not found');
            $this->redirect('/admin/premium');
            return;
        }
        
        if ($adModel->update($id, ['status' => 'rejected'])) {
            Security::log('advertisement_rejected', $this->user()['id'], [
                'advertisement_id' => $id,
                'supplier_id' => $ad['supplier_id']
            ]);
            $this->setFlash('success', 'Advertisement rejected');
        } else {
            $this->setFlash('error', 'Failed to reject advertisement');
        }
        
        $this->redirect('/admin/premium');
    }
    
    /**
     * Update delivery status (Admin/Logistics only)
     */
    public function updateDeliveryStatus(): void
    {
        if (!$this->isAjax()) {
            Response::json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $deliveryId = (int)($input['delivery_id'] ?? 0);
        $newStatus = $input['status'] ?? '';
        $notes = $input['notes'] ?? '';
        $adminId = $this->user()['id'];
        
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->find($deliveryId);
        
        if (!$delivery) {
            Response::json(['success' => false, 'message' => 'Delivery not found'], 404);
            return;
        }
        
        // Validate status transitions
        $validTransitions = [
            'ready_for_pickup' => ['picked_up'],
            'picked_up' => ['in_transit', 'failed'],
            'in_transit' => ['delivered', 'failed'],
            'failed' => ['picked_up'] // Allow retry
        ];
        
        if (!in_array($newStatus, $validTransitions[$delivery['status']] ?? [])) {
            Response::json(['success' => false, 'message' => 'Invalid status transition'], 400);
            return;
        }
        
        // Generate delivery code if marking as in_transit
        $deliveryCode = null;
        if ($newStatus === 'in_transit') {
            $deliveryCode = $deliveryModel->generateDeliveryCode($deliveryId);
            
            // Send email to buyer with delivery code
            try {
                $orderModel = new Order();
                $order = $orderModel->find($delivery['order_id']);
                if ($order) {
                    $userModel = new User();
                    $buyer = $userModel->find($order['buyer_id']);
                    if ($buyer && !empty($buyer['email'])) {
                        $emailService = new \App\EmailService();
                        $emailService->sendDeliveryCode($delivery['order_id'], $deliveryCode, $buyer['email'], $buyer['name']);
                    }
                }
            } catch (\Exception $e) {
                error_log("Failed to send delivery code email: " . $e->getMessage());
                // Continue anyway - code is generated
            }
        }
        
        // Update status
        $success = $deliveryModel->updateStatus(
            $deliveryId,
            $newStatus,
            $adminId,
            'admin',
            $notes,
            $deliveryCode
        );
        
        if ($success) {
            Security::log('delivery_status_updated', $adminId, [
                'delivery_id' => $deliveryId,
                'old_status' => $delivery['status'],
                'new_status' => $newStatus
            ]);
            
            Response::json([
                'success' => true,
                'message' => 'Status updated successfully',
                'delivery_code' => $deliveryCode
            ]);
        } else {
            Response::json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }
    
    /**
     * Mark delivered with photo (Admin backup method)
     */
    public function markDeliveredWithPhoto(): void
    {
        if (!$this->isAjax()) {
            Response::json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $deliveryId = (int)($_POST['delivery_id'] ?? 0);
        $adminId = $this->user()['id'];
        
        // Validate file upload
        if (!isset($_FILES['delivery_photo']) || $_FILES['delivery_photo']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'Photo required'], 400);
            return;
        }
        
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->find($deliveryId);
        
        if (!$delivery || $delivery['status'] !== 'in_transit') {
            Response::json(['success' => false, 'message' => 'Invalid delivery state'], 400);
            return;
        }
        
        // Upload photo
        $config = require __DIR__ . '/../settings/config.php';
        $uploadDir = $config['uploads']['path'] . '/deliveries/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = uniqid('delivery_') . '_' . basename($_FILES['delivery_photo']['name']);
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($_FILES['delivery_photo']['tmp_name'], $filepath)) {
            Response::json(['success' => false, 'message' => 'Failed to upload photo'], 500);
            return;
        }
        
        // Update delivery
        $success = $deliveryModel->markDeliveredWithPhoto($deliveryId, $filename, $adminId);
        
        if ($success) {
            Security::log('delivery_marked_with_photo', $adminId, ['delivery_id' => $deliveryId]);
            Response::json(['success' => true, 'message' => 'Marked as delivered with photo proof']);
        } else {
            Response::json(['success' => false, 'message' => 'Failed to update delivery'], 500);
        }
    }
    
}

