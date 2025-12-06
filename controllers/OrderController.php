<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\DB;
use App\Order;
use App\Product;
use App\Invoice;
use App\Delivery;
use App\Security;

/**
 * Order controller
 */
class OrderController extends Controller
{
    /**
     * Checkout page
     */
    public function checkout(): void
    {
        $user = $this->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        // Prevent suppliers and admins from accessing checkout (only buyers can purchase)
        if ($user['role'] === 'supplier') {
            $this->setFlash('error', 'Suppliers cannot purchase products. Please create a buyer account to make purchases.');
            $this->redirect('/catalog');
            return;
        }
        if ($user['role'] === 'admin') {
            $this->setFlash('error', 'Admins cannot purchase products. Please use a buyer account to make purchases.');
            $this->redirect('/catalog');
            return;
        }
        
        $cart = $_SESSION['cart'] ?? [];
        
        if (empty($cart)) {
            $this->setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
            return;
        }
        
        $products = \App\Cart::getItemsWithProducts($cart);
        $total = \App\Cart::calculateTotal($cart);
        
        echo $this->view->render('Order/checkout', [
            'products' => $products,
            'total' => $total,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Process checkout
     */
    public function processCheckout(): void
    {
        // Debug: Log that we're in processCheckout
        error_log("=== CHECKOUT PROCESS START ===");
        error_log("OrderController::processCheckout() called");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("SESSION cart: " . print_r($_SESSION['cart'] ?? [], true));
        
        $user = $this->user();
        if (!$user) {
            error_log("User not authenticated, redirecting to login");
            $this->redirect('/login');
            return;
        }
        
        // Prevent suppliers and admins from processing checkout (only buyers can purchase)
        if ($user['role'] === 'supplier') {
            error_log("Supplier attempted checkout, redirecting");
            $this->setFlash('error', 'Suppliers cannot purchase products. Please create a buyer account to make purchases.');
            $this->redirect('/catalog');
            return;
        }
        if ($user['role'] === 'admin') {
            error_log("Admin attempted checkout, redirecting");
            $this->setFlash('error', 'Admins cannot purchase products. Please use a buyer account to make purchases.');
            $this->redirect('/catalog');
            return;
        }
        
        $cart = $_SESSION['cart'] ?? [];
        
        if (empty($cart)) {
            error_log("Cart is empty, redirecting to cart");
            $this->setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
            return;
        }
        
        // Validate delivery address
        $address = [
            'street' => trim($_POST['street'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'region' => trim($_POST['region'] ?? ''),
            'country' => trim($_POST['country'] ?? 'Ghana'),
            'phone' => trim($_POST['phone'] ?? ($user['phone'] ?? '')),
            'delivery_lat' => !empty($_POST['delivery_lat']) ? (float)$_POST['delivery_lat'] : null,
            'delivery_lng' => !empty($_POST['delivery_lng']) ? (float)$_POST['delivery_lng'] : null
        ];
        
        error_log("Address data: " . print_r($address, true));
        
        // Validate region (only Greater Accra and Ashanti Region allowed)
        $allowedRegions = ['Greater Accra', 'Ashanti Region'];
        if (empty($address['region']) || !in_array($address['region'], $allowedRegions)) {
            error_log("Invalid region: " . $address['region']);
            $this->setFlash('error', 'We currently only deliver to Greater Accra and Ashanti Region. Stay tuned for expansion!');
            $this->redirect('/checkout');
            return;
        }
        
        if (empty($address['street']) || empty($address['city']) || empty($address['phone'])) {
            error_log("Missing required fields - street: " . ($address['street'] ? 'yes' : 'no') . ", city: " . ($address['city'] ? 'yes' : 'no') . ", phone: " . ($address['phone'] ? 'yes' : 'no'));
            $this->setFlash('error', 'Please provide complete delivery address and phone number');
            $this->redirect('/checkout');
            return;
        }
        
        // Coordinates are optional (map removed) - set defaults if not provided
        if (empty($address['delivery_lat']) || empty($address['delivery_lng'])) {
            // Set default coordinates for Greater Accra or Ashanti Region
            if ($address['region'] === 'Greater Accra') {
                $address['delivery_lat'] = 5.6037; // Accra center
                $address['delivery_lng'] = -0.1870;
            } elseif ($address['region'] === 'Ashanti Region') {
                $address['delivery_lat'] = 6.6885; // Kumasi center
                $address['delivery_lng'] = -1.6244;
            } else {
                $address['delivery_lat'] = 5.6037; // Default to Accra
                $address['delivery_lng'] = -0.1870;
            }
        }
        
        try {
            error_log("=== CREATING ORDER ===");
            $orderModel = new Order();
            $productModel = new Product();
            
            error_log("Cart items: " . print_r($cart, true));
            error_log("User ID: " . $user['id']);
            error_log("Address: " . print_r($address, true));
            
            // Create order from cart using model method
            $orderId = $orderModel->createFromCart($user['id'], $cart, $address, $productModel);
            
            error_log("✅ Order created successfully with ID: " . $orderId);
            
            // CLEAR CART IMMEDIATELY after order is created
            // This prevents cart from persisting if user navigates away or payment fails
            $_SESSION['cart'] = [];
            error_log("🛒 Cart cleared after order creation. Cart is now: " . (empty($_SESSION['cart']) ? 'EMPTY ✓' : 'NOT EMPTY ✗'));
            
            // Store order ID for payment
            $_SESSION['pending_order_id'] = $orderId;
            
            // Force session save
            session_write_close();
            session_start();
            
            // Clear output buffer before redirect
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Redirect to payment page
            error_log("🔄 Redirecting to payment page: /payment/" . $orderId);
            $this->redirect('/payment/' . $orderId);
            return;
        } catch (\Exception $e) {
            error_log("❌ ERROR in processCheckout: " . $e->getMessage());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->setFlash('error', 'Error processing checkout: ' . $e->getMessage());
            
            // Clear output buffer before redirect
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            $this->redirect('/checkout');
            return;
        }
    }
    
    /**
     * Confirm checkout and process payment
     */
    public function confirmCheckout(): void
    {
        $orderId = $_SESSION['pending_order_id'] ?? null;
        
        if (!$orderId) {
            $this->redirect('/checkout');
        }
        
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);
        
        if (!$order || $order['buyer_id'] !== $this->user()['id']) {
            $this->setFlash('error', 'Invalid order');
            $this->redirect('/orders');
        }
        
        // Process payment (mock or sandbox)
        $config = require __DIR__ . '/../settings/config.php';
        $paymentMode = $config['payment']['mode'];
        
        if ($paymentMode === 'mock') {
            // Process Paystack secure payment
            // Funds held by Paystack until delivery status = 'delivered'
            $paymentRef = 'MOCK-' . bin2hex(random_bytes(8));
        } else {
            // Sandbox payment would be handled here
            $paymentRef = 'SANDBOX-' . bin2hex(random_bytes(8));
        }
        
        $db = DB::getInstance();
        $db->beginTransaction();
        
        try {
            // Update order status
            $orderModel->update($orderId, [
                'status' => 'paid_escrow',
                'escrow_held' => 1,
                'payment_reference' => $paymentRef
            ]);
            
            // Decrement stock when payment is confirmed (paid_escrow status)
            // This happens for mock payments - real Paystack payments decrement in PaymentController
            $productModel = new Product();
            error_log("OrderController::confirmCheckout - Decrementing stock for order #{$orderId}");
            error_log("Order items: " . json_encode($order['items']));
            
            foreach ($order['items'] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                // OrderItem returns both 'quantity' and 'qty' (qty is alias), use quantity first, fallback to qty
                $quantity = (int)($item['quantity'] ?? $item['qty'] ?? 1);
                
                if ($productId > 0 && $quantity > 0) {
                    error_log("Decrementing stock: Product ID {$productId}, Quantity {$quantity}");
                    $success = $productModel->decrementStock($productId, $quantity);
                    if (!$success) {
                        error_log("✗ ERROR: Failed to decrement stock for product {$productId} by {$quantity} in OrderController::confirmCheckout");
                        throw new \Exception("Failed to decrement stock for product {$productId}. Please check product availability.");
                    } else {
                        error_log("✓ Stock decremented successfully in confirmCheckout: Product {$productId}, Quantity {$quantity}");
                    }
                } else {
                    error_log("✗ ERROR: Invalid product_id ({$productId}) or quantity ({$quantity}) in order item: " . json_encode($item));
                    throw new \Exception("Invalid order item data");
                }
            }
            
            // Generate invoice
            $invoiceModel = new Invoice();
            $invoiceModel->generatePdf($orderId, $orderModel);
            
            // Create delivery record
            // Funds held by Paystack until delivery status = 'delivered'
            $deliveryModel = new Delivery();
            $deliveryData = [
                'delivery_lat' => $order['delivery_lat'] ?? null,
                'delivery_lng' => $order['delivery_lng'] ?? null,
                'street' => explode(',', $order['delivery_address'] ?? '')[0] ?? '',
                'city' => $order['delivery_address'] ?? '',
                'region' => $order['delivery_region'] ?? '',
                'phone' => $order['delivery_phone'] ?? ''
            ];
            $deliveryModel->createFromOrder($orderId, $deliveryData);
            
            $db->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            unset($_SESSION['pending_order_id']);
            
            Security::log('order_created', $this->user()['id'], ['order_id' => $orderId]);
            
            $this->setFlash('success', 'Order placed successfully! Payment held securely by Paystack.');
            $this->redirect('/orders/' . $orderId);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->setFlash('error', 'Payment processing failed: ' . $e->getMessage());
            $this->redirect('/orders');
        }
    }
    
    /**
     * Track delivery for an order
     */
    public function trackDelivery($orderId): void
    {
        $orderId = (int)$orderId;
        $user = $this->user();
        if (!$user) {
            $this->redirect('/login');
        }
        
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);
        
        if (!$order || $order['buyer_id'] !== $user['id']) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('/orders');
        }
        
        // Get delivery record for confirmation form
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findByOrderId($orderId);
        
        // If delivery doesn't exist but order is paid, try to create it
        if (!$delivery && in_array($order['status'], ['paid', 'processing', 'out_for_delivery', 'delivered'])) {
            try {
                // Get address from order
                $address = json_decode($order['address_json'] ?? '{}', true);
                if (empty($address) && !empty($order['delivery_address'])) {
                    $parts = explode(', ', $order['delivery_address']);
                    $address = [
                        'street' => $parts[0] ?? '',
                        'city' => $parts[1] ?? '',
                        'region' => $parts[2] ?? $order['delivery_region'] ?? '',
                        'country' => $parts[3] ?? 'Ghana',
                        'phone' => $order['delivery_phone'] ?? '',
                        'delivery_lat' => $order['delivery_lat'] ?? null,
                        'delivery_lng' => $order['delivery_lng'] ?? null
                    ];
                }
                
                if (!empty($address['region'])) {
                    $deliveryData = [
                        'delivery_lat' => $address['delivery_lat'] ?? null,
                        'delivery_lng' => $address['delivery_lng'] ?? null,
                        'street' => $address['street'] ?? '',
                        'city' => $address['city'] ?? '',
                        'region' => $address['region'] ?? '',
                        'phone' => $address['phone'] ?? ''
                    ];
                    $deliveryId = $deliveryModel->createFromOrder($orderId, $deliveryData);
                    $delivery = $deliveryModel->findByOrderId($orderId);
                    error_log("Created delivery record {$deliveryId} for order {$orderId}");
                }
            } catch (\Exception $e) {
                error_log("Failed to create delivery record: " . $e->getMessage());
                // Continue without delivery - page will show "preparing" message
            }
        }
        
        echo $this->view->render('Order/track-delivery', [
            'order' => $order,
            'delivery' => $delivery,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * List orders
     */
    public function index(): void
    {
        $user = $this->user();
        require_once __DIR__ . '/../includes/order_functions.php';
        
        $statusFilter = $_GET['status'] ?? null;
        $orders = getUserOrders($user['id'], $statusFilter);
        
        // Mark orders as paid if they have payment info (even if status is still pending)
        foreach ($orders as &$order) {
            $order['has_payment'] = !empty($order['payment_reference']) || !empty($order['payment_method']);
        }
        unset($order);
        
        echo $this->view->render('Order/index', [
            'orders' => $orders,
            'statusFilter' => $statusFilter
        ]);
    }
    
    /**
     * Show order details
     */
    public function show($id): void
    {
        $id = (int)$id; // Cast to int
        $user = $this->user();
        $orderModel = new Order();
        $order = $orderModel->getWithItems($id);
        
        if (!$order) {
            http_response_code(404);
            echo $this->view->render('Errors/404', [], 'main');
            return;
        }
        
        // Check if user is the buyer OR a supplier who owns products in this order
        $isBuyer = $order['buyer_id'] === $user['id'];
        $isSupplier = false;
        
        if (!$isBuyer && $user['role'] === 'supplier') {
            // Check if this supplier has products in this order
            $supplierModel = new \App\Supplier();
            $supplier = $supplierModel->findByUserId($user['id']);
            
            if ($supplier) {
                $db = \App\DB::getInstance();
                $stmt = $db->prepare("
                    SELECT COUNT(*) as count
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ? AND p.supplier_id = ?
                ");
                $stmt->execute([$id, $supplier['id']]);
                $result = $stmt->fetch();
                $isSupplier = (int)($result['count'] ?? 0) > 0;
            }
        }
        
        if (!$isBuyer && !$isSupplier) {
            http_response_code(404);
            echo $this->view->render('Errors/404', [], 'main');
            return;
        }
        
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findByOrderId($id);
        
        // Load order tracking functions
        require_once __DIR__ . '/../includes/order_functions.php';
        
        // Get delivery record for confirmation form
        $delivery = $deliveryModel->findByOrderId($id);
        
        // Pass user info for role-based actions
        echo $this->view->render('Order/show', [
            'order' => $order,
            'delivery' => $delivery,
            'user' => $user,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Get order status (AJAX endpoint for auto-refresh)
     */
    public function getStatus($id): void
    {
        $id = (int)$id;
        $user = $this->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        require_once __DIR__ . '/../includes/order_functions.php';
        
        $order = getOrderDetails($id);
        
        if (!$order) {
            $this->json(['error' => 'Order not found'], 404);
            return;
        }
        
        // Check if user is the buyer
        if ($order['buyer_id'] !== $user['id']) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }
        
        $status = $order['current_status'] ?? $order['status'] ?? 'placed';
        
        $this->json([
            'status' => mapStatusFromDatabase($status),
            'order_id' => $id
        ]);
    }
    
    /**
     * Confirm delivery with delivery code (Buyer only)
     */
    public function confirmDelivery($id): void
    {
        $id = (int)$id;
        // Check if AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$isAjax) {
            // Allow POST requests too (for form submissions)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                Response::json(['success' => false, 'message' => 'Invalid request'], 400);
                return;
            }
        }
        
        $user = $this->user();
        $input = json_decode(file_get_contents('php://input'), true);
        $deliveryCode = $input['delivery_code'] ?? '';
        
        $orderModel = new Order();
        $order = $orderModel->find($id);
        
        if (!$order || $order['buyer_id'] !== $user['id']) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Order not found'], 404);
            } else {
                $this->setFlash('error', 'Order not found');
                $this->redirect('/orders');
            }
            return;
        }
        
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findByOrderId($id);
        
        if (!$delivery) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Delivery record not found'], 404);
            } else {
                $this->setFlash('error', 'Delivery record not found');
                $this->redirect('/orders');
            }
            return;
        }
        
        // Verify delivery code
        if (empty($deliveryCode) || $delivery['delivery_code'] !== $deliveryCode) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Invalid delivery code'], 400);
            } else {
                $this->setFlash('error', 'Invalid delivery code');
                $this->redirect('/orders/' . $id . '/track-delivery');
            }
            return;
        }
        
        // Confirm delivery
        $success = $deliveryModel->confirmByBuyer($delivery['id'], $deliveryCode);
        
        if ($success) {
            Security::log('delivery_confirmed_by_buyer', $user['id'], [
                'order_id' => $id,
                'delivery_id' => $delivery['id']
            ]);
            
            if ($isAjax) {
                Response::json([
                    'success' => true,
                    'message' => 'Delivery confirmed! Payment has been released to supplier.'
                ]);
            } else {
                $this->setFlash('success', 'Delivery confirmed! Payment has been released to supplier.');
                $this->redirect('/orders/' . $id);
            }
        } else {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Failed to confirm delivery'], 500);
            } else {
                $this->setFlash('error', 'Failed to confirm delivery');
                $this->redirect('/orders/' . $id . '/track-delivery');
            }
        }
    }
    
    /**
     * Dispute order
     */
    public function dispute($id): void
    {
        $id = (int)$id;
        $user = $this->user();
        $orderModel = new Order();
        $order = $orderModel->find($id);
        
        if (!$order || $order['buyer_id'] !== $user['id']) {
            $this->setFlash('error', 'Invalid order');
            $this->redirect('/orders');
        }
        
        $issueText = $_POST['issue_text'] ?? '';
        
        if (empty($issueText)) {
            $this->setFlash('error', 'Please describe the issue');
            $this->redirect('/orders/' . $id);
        }
        
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findByOrderId($id);
        
        if ($delivery) {
            $deliveryModel->update($delivery['id'], [
                'issue_text' => $issueText
            ]);
        }
        
        Security::log('order_disputed', $user['id'], ['order_id' => $id, 'issue' => $issueText]);
        $this->setFlash('success', 'Dispute submitted. Admin will review.');
        $this->redirect('/orders/' . $id);
    }
    
    /**
     * Generate and download invoice PDF
     */
    public function invoice($id): void
    {
        $id = (int)$id;
        // Clear ALL output buffering immediately to prevent any output before headers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $user = $this->user();
        $orderModel = new Order();
        $order = $orderModel->getWithItems($id);
        
        if (!$order || $order['buyer_id'] !== $user['id']) {
            http_response_code(404);
            header('Content-Type: text/plain');
            die('Invoice not found');
        }
        
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->findByOrderId($id);
        
        if (!$invoice) {
            // Generate if doesn't exist
            try {
                $orderModel = new Order();
                $filename = $invoiceModel->generatePdf($id, $orderModel);
                $invoice = $invoiceModel->findByOrderId($id);
                
                // If still null, use the filename from generation
                if (!$invoice && $filename) {
                    $invoice = ['file_path' => $filename];
                }
            } catch (\Exception $e) {
                error_log("Failed to generate invoice: " . $e->getMessage());
                // Can't use flash or redirect here - headers might be sent
                http_response_code(500);
                header('Content-Type: text/plain');
                die('Failed to generate invoice: ' . $e->getMessage());
            }
        }
        
        if (!$invoice || empty($invoice['file_path'])) {
            http_response_code(404);
            header('Content-Type: text/plain');
            die('Invoice not found');
        }
        
        $config = require __DIR__ . '/../settings/config.php';
        $invoicePath = $config['invoices']['path'] ?? __DIR__ . '/../storage/invoices';
        $filePath = $invoicePath . '/' . basename($invoice['file_path']);
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            header('Content-Type: text/plain');
            die('Invoice file not found');
        }
        
        // Clear any remaining output buffering
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Use Response class for download
        $invoiceNo = $invoice['invoice_no'] ?? 'INV-' . $id;
        Response::download($filePath, $invoiceNo . '.pdf');
    }
    
}

