<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\DB;
use App\Order;
use App\Product;
use App\Invoice;
use App\Delivery;
use App\PaystackService;
use App\EmailService;
use App\Security;

/**
 * Payment Controller - Handles Paystack payment integration
 */
class PaymentController extends Controller
{
    /**
     * Show payment page
     */
    public function show($orderId): void
    {
        $orderId = (int)$orderId;
        $user = $this->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);
        
        // Debug logging
        error_log("PaymentController::show - Order ID: $orderId");
        error_log("PaymentController::show - User ID: " . $user['id']);
        error_log("PaymentController::show - Order found: " . ($order ? 'yes' : 'no'));
        if ($order) {
            error_log("PaymentController::show - Order buyer_id: " . $order['buyer_id']);
            error_log("PaymentController::show - Order status: " . $order['status']);
        }
        
        if (!$order) {
            error_log("PaymentController::show - Order not found, redirecting to /orders");
            $this->setFlash('error', 'Order not found');
            $this->redirect('/orders');
            return;
        }
        
        // Cast both IDs to int for comparison
        if ((int)$order['buyer_id'] !== (int)$user['id']) {
            error_log("PaymentController::show - Unauthorized: buyer_id " . $order['buyer_id'] . " !== user_id " . $user['id']);
            $this->setFlash('error', 'You are not authorized to view this order');
            $this->redirect('/orders');
            return;
        }
        
        if ($order['status'] !== 'pending') {
            error_log("PaymentController::show - Order already processed: " . $order['status']);
            $this->setFlash('info', 'This order has already been processed');
            $this->redirect('/orders/' . $orderId);
            return;
        }
        
        error_log("PaymentController::show - All checks passed, showing payment page");
        
        $paystackService = new PaystackService();
        $publicKey = $paystackService->getPublicKey();
        
        // Ensure we always have a key (fallback for development)
        if (empty($publicKey) || trim($publicKey) === '') {
            $publicKey = 'pk_test_042e80c17c891462d6f0b7f651b48745c184a1b5';
            error_log("PaymentController: Using fallback test public key");
        }
        
        // Debug logging
        error_log("PaymentController: Paystack public key length: " . strlen($publicKey));
        
        echo $this->view->render('Payment/show', [
            'order' => $order,
            'paystack_public_key' => $publicKey,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Initialize Paystack payment
     */
    public function initialize(): void
    {
        // Get order_id from JSON body (AJAX) or POST data (form)
        $orderId = 0;
        if (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = (int)($input['order_id'] ?? 0);
        } else {
            $orderId = (int)($_POST['order_id'] ?? 0);
        }
        
        $user = $this->user();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);
        
        if (!$order || $order['buyer_id'] !== $user['id']) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }
        
        if ($order['status'] !== 'pending') {
            $this->json(['success' => false, 'message' => 'Order already processed'], 400);
            return;
        }
        
        $paystackService = new PaystackService();
        $userModel = new \App\User();
        $buyer = $userModel->find($user['id']);
        
        // Use View::url() to get the correct base URL dynamically
        $baseUrl = \App\View::basePath();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appUrl = $protocol . '://' . $host . rtrim($baseUrl, '/');
        
        error_log("PaymentController::initialize - Callback URL: " . $appUrl . '/payment/callback');
        
        // Prepare payment data
        $paymentData = [
            'email' => $buyer['email'],
            'amount' => $order['total_cents'], // Amount in kobo (cents)
            'currency' => $order['currency'] ?? 'GHS',
            'reference' => 'BM-' . $orderId . '-' . time(),
            'callback_url' => $appUrl . '/payment/callback',
            'metadata' => [
                'order_id' => $orderId,
                'buyer_id' => $user['id'],
                'buyer_name' => $buyer['name'] ?? ''
            ]
        ];
        
        try {
            $response = $paystackService->initializeTransaction($paymentData);
            
            if ($response['status']) {
                // Store reference in session for verification
                $_SESSION['payment_reference'] = $response['data']['reference'];
                $_SESSION['payment_order_id'] = $orderId;
                
                $this->json([
                    'success' => true,
                    'authorization_url' => $response['data']['authorization_url'],
                    'reference' => $response['data']['reference']
                ]);
            } else {
                $this->json(['success' => false, 'message' => $response['message'] ?? 'Payment initialization failed'], 400);
            }
        } catch (\Exception $e) {
            error_log("Paystack initialization error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Payment initialization failed. Please try again.'], 500);
        }
    }
    
    /**
     * Handle Paystack callback
     */
    public function callback(): void
    {
        // Debug logging
        error_log("PaymentController::callback() called");
        error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));
        error_log("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));
        error_log("GET params: " . print_r($_GET, true));
        error_log("POST params: " . print_r($_POST, true));
        
        $reference = $_GET['reference'] ?? $_POST['reference'] ?? '';
        
        if (empty($reference)) {
            error_log("PaymentController::callback() - No reference found");
            $this->setFlash('error', 'Invalid payment reference');
            $this->redirect('/orders');
            return;
        }
        
        error_log("PaymentController::callback() - Reference: " . $reference);
        
        $paystackService = new PaystackService();
        
        try {
            $verification = $paystackService->verifyTransaction($reference);
            
            if ($verification['status'] && $verification['data']['status'] === 'success') {
                $orderId = (int)($verification['data']['metadata']['order_id'] ?? 0);
                
                if (!$orderId) {
                    // Try to get from session
                    $orderId = $_SESSION['payment_order_id'] ?? 0;
                }
                
                if ($orderId) {
                    $this->processPaymentSuccess($orderId, $reference, $verification['data']);
                } else {
                    $this->setFlash('error', 'Order ID not found');
                    $this->redirect('/orders');
                }
            } else {
                $this->setFlash('error', 'Payment verification failed');
                $this->redirect('/orders');
            }
        } catch (\Exception $e) {
            error_log("Paystack callback error: " . $e->getMessage());
            $this->setFlash('error', 'Payment processing error: ' . $e->getMessage());
            $this->redirect('/orders');
        }
    }
    
    /**
     * Process successful payment
     */
    private function processPaymentSuccess(int $orderId, string $reference, array $paymentData): void
    {
        $db = DB::getInstance();
        $db->beginTransaction();
        
        try {
            $orderModel = new Order();
            $order = $orderModel->getWithItems($orderId);
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            // Check if already paid or beyond
            $paidStatuses = ['paid', 'processing', 'out_for_delivery', 'delivered'];
            if (in_array($order['status'], $paidStatuses)) {
                // Already processed
                $db->commit();
                $this->setFlash('info', 'Payment already processed');
                $this->redirect('/orders/' . $orderId);
                return;
            }
            
            // After Paystack payment → set order.status = "paid"
            // Accept both 'pending' and 'placed' as valid initial statuses
            $validInitialStatuses = ['pending', 'placed'];
            if (!in_array($order['status'], $validInitialStatuses)) {
                error_log("PaymentController: Unexpected order status '{$order['status']}' for order {$orderId}");
            }
            
            $updateData = [
                'status' => 'paid'
            ];
            
            error_log("PaymentController: Setting order {$orderId} status from '{$order['status']}' to 'paid'");
            
            // Set current_status for timeline system if column exists
            try {
                $columnsStmt = $db->query("SHOW COLUMNS FROM orders");
                $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
                
                if (in_array('current_status', $columns)) {
                    $updateData['current_status'] = 'payment_confirmed';
                }
                
                // Set payment_confirmed_at timestamp for timeline
                if (in_array('payment_confirmed_at', $columns)) {
                    $updateData['payment_confirmed_at'] = date('Y-m-d H:i:s');
                }
                
                // Set order_placed_at if not already set
                if (in_array('order_placed_at', $columns) && empty($order['order_placed_at'])) {
                    $updateData['order_placed_at'] = $order['created_at'] ?? date('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                error_log("Error checking timeline columns: " . $e->getMessage());
            }
            
            // Check which columns exist before adding them
            try {
                $columnsStmt = $db->query("SHOW COLUMNS FROM orders");
                $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
                
                // Only add payment_reference if column exists
                if (in_array('payment_reference', $columns)) {
                    $updateData['payment_reference'] = $reference;
                } else {
                    error_log("payment_reference column not found. Run migration: /run_payment_columns_migration_web.php");
                }
                
                // Only add payment_method if column exists
                if (in_array('payment_method', $columns)) {
                    $updateData['payment_method'] = 'paystack';
                }
                
                // Only add paystack_secure_held if column exists
                if (in_array('paystack_secure_held', $columns)) {
                    $updateData['paystack_secure_held'] = 1;
                }
            } catch (\Exception $e) {
                error_log("Error checking columns: " . $e->getMessage());
                // Don't fail payment if columns don't exist
            }
            
            // Direct SQL update to ensure it works - UPDATE STATUS AND PAYMENT FIELDS TOGETHER
            // This ensures atomic update and prevents any race conditions
            // Check which payment columns exist first
            $columnsStmt = $db->query("SHOW COLUMNS FROM orders");
            $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
            
            $hasPaymentRef = in_array('payment_reference', $columns);
            $hasPaymentMethod = in_array('payment_method', $columns);
            
            // Build UPDATE query dynamically based on available columns
            $updateFields = ["status = 'paid'"];
            $updateParams = [];
            
            if ($hasPaymentRef) {
                $updateFields[] = "payment_reference = ?";
                $updateParams[] = $reference;
            }
            if ($hasPaymentMethod) {
                $updateFields[] = "payment_method = 'paystack'";
            }
            
            $updateSql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $updateParams[] = $orderId;
            
            $statusUpdateStmt = $db->prepare($updateSql);
            $statusUpdateStmt->execute($updateParams);
            $rowsAffected = $statusUpdateStmt->rowCount();
            error_log("PaymentController: Direct SQL update for order {$orderId}: {$rowsAffected} rows affected, setting status='paid', payment_ref=" . ($hasPaymentRef ? "'{$reference}'" : 'NOT SET (column missing)'));
            
            if ($rowsAffected === 0) {
                error_log("PaymentController: WARNING - No rows updated for order {$orderId}. Order may not exist or status already set.");
            }
            
            // Also use model update for other fields (but NOT status - we already set it)
            unset($updateData['status']); // Remove status since we updated it directly
            if (!empty($updateData)) {
                $orderModel->update($orderId, $updateData);
            }
            
            // Verify the update worked BEFORE committing
            $verifyFields = ['status'];
            if ($hasPaymentRef) $verifyFields[] = 'payment_reference';
            if ($hasPaymentMethod) $verifyFields[] = 'payment_method';
            
            $verifySql = "SELECT " . implode(', ', $verifyFields) . " FROM orders WHERE id = ?";
            $verifyStmt = $db->prepare($verifySql);
            $verifyStmt->execute([$orderId]);
            $verifyOrder = $verifyStmt->fetch();
            
            $logMsg = "PaymentController: Verified order {$orderId} status is now: '{$verifyOrder['status']}'";
            if ($hasPaymentRef) $logMsg .= ", payment_ref: '{$verifyOrder['payment_reference']}'";
            if ($hasPaymentMethod) $logMsg .= ", payment_method: '{$verifyOrder['payment_method']}'";
            error_log($logMsg);
            
            // Ensure status is 'paid' before proceeding
            if ($verifyOrder['status'] !== 'paid') {
                error_log("PaymentController: ERROR - Status update failed! Expected 'paid', got '{$verifyOrder['status']}'. Retrying...");
                // Retry the update with FORCE
                $retryStmt = $db->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
                $retryStmt->execute([$orderId]);
                $retryRows = $retryStmt->rowCount();
                error_log("PaymentController: Retry update affected {$retryRows} rows");
                
                // Verify again after retry
                $verifyStmt->execute([$orderId]);
                $verifyOrder = $verifyStmt->fetch();
                if ($verifyOrder['status'] !== 'paid') {
                    throw new \Exception("CRITICAL: Failed to set order status to 'paid' after multiple attempts. Current status: '{$verifyOrder['status']}'");
                }
            }
            
            // Decrement stock - MUST happen within transaction
            // Only decrement if order status was 'placed' or 'pending' (not already paid)
            // This prevents double-decrementing if stock was already decremented in confirmCheckout
            $productModel = new Product();
            error_log("Order items structure: " . json_encode($order['items']));
            error_log("Order status before payment: " . ($order['status'] ?? 'unknown'));
            
            // Check if stock should be decremented (only if order wasn't already processed)
            $shouldDecrementStock = in_array($order['status'] ?? '', ['placed', 'pending']);
            
            if ($shouldDecrementStock) {
            foreach ($order['items'] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                // Check both 'quantity' and 'qty' fields
                $quantity = (int)($item['quantity'] ?? $item['qty'] ?? 1);
                
                if ($productId > 0 && $quantity > 0) {
                    try {
                        $success = $productModel->decrementStock($productId, $quantity);
                        if ($success) {
                                error_log("✓ Successfully decremented stock for product {$productId} by {$quantity} in PaymentController");
                        } else {
                            error_log("✗ ERROR: Failed to decrement stock for product {$productId} by {$quantity}");
                            throw new \Exception("Failed to decrement stock for product {$productId}");
                        }
                    } catch (\Exception $e) {
                        error_log("EXCEPTION during stock decrement: " . $e->getMessage());
                        throw $e; // Re-throw to rollback transaction
                    }
                } else {
                    error_log("✗ ERROR: Invalid product_id ({$productId}) or quantity ({$quantity}) for order item: " . json_encode($item));
                    throw new \Exception("Invalid order item data");
                }
                }
            } else {
                error_log("⚠ Stock already decremented or order already processed. Skipping stock decrement. Order status: " . ($order['status'] ?? 'unknown'));
            }
            
            // Generate invoice
            $invoiceModel = new Invoice();
            $invoiceModel->generatePdf($orderId, $orderModel);
            
            // Create delivery record
            try {
                $deliveryModel = new Delivery();
                $address = json_decode($order['address_json'] ?? '{}', true);
                $deliveryData = [
                    'delivery_lat' => $address['delivery_lat'] ?? null,
                    'delivery_lng' => $address['delivery_lng'] ?? null,
                    'street' => $address['street'] ?? '',
                    'city' => $address['city'] ?? '',
                    'region' => $address['region'] ?? '',
                    'phone' => $address['phone'] ?? ''
                ];
                $deliveryId = $deliveryModel->createFromOrder($orderId, $deliveryData);
                error_log("Delivery record created successfully: ID {$deliveryId} for Order {$orderId}");
            } catch (\Exception $e) {
                error_log("Failed to create delivery record for Order {$orderId}: " . $e->getMessage());
                // Don't fail the payment if delivery creation fails - it can be created later
            }
            
            $db->commit();
            
            // Clear cart AGAIN after successful payment (in case it was restored somehow)
            // Also clear all payment-related session vars
            $_SESSION['cart'] = [];
            unset($_SESSION['pending_order_id']);
            unset($_SESSION['payment_reference']);
            unset($_SESSION['payment_order_id']);
            
            // Force session save IMMEDIATELY
            session_write_close();
            
            // Reopen session for flash message and redirect
            session_start();
            
            // Verify cart is empty
            $cartIsEmpty = empty($_SESSION['cart']);
            error_log("🛒 Cart cleared after payment for order {$orderId}. Cart is " . ($cartIsEmpty ? 'EMPTY ✓' : 'NOT EMPTY ✗'));
            
            if (!$cartIsEmpty) {
                // Force clear one more time if somehow it's not empty
                $_SESSION['cart'] = [];
                session_write_close();
                session_start();
                error_log("🛒 FORCED cart clear - cart was not empty!");
            }
            
            // Send email notifications
            $emailService = new EmailService();
            $userModel = new \App\User();
            $buyer = $userModel->find($order['buyer_id']);
            
            if ($buyer) {
                // Send order confirmation email
                try {
                    $emailSent = $emailService->sendOrderConfirmation($orderId, $order, $buyer['email'], $buyer['name']);
                    if ($emailSent) {
                        error_log("Order confirmation email sent to: " . $buyer['email']);
                    } else {
                        error_log("Failed to send order confirmation email to: " . $buyer['email']);
                    }
                } catch (\Exception $e) {
                    error_log("Error sending order confirmation email: " . $e->getMessage());
                }
                
                // Send payment confirmation email
                try {
                    $emailSent = $emailService->sendPaymentConfirmation($orderId, $order, $buyer['email'], $buyer['name'], $reference);
                    if ($emailSent) {
                        error_log("Payment confirmation email sent to: " . $buyer['email']);
                    } else {
                        error_log("Failed to send payment confirmation email to: " . $buyer['email']);
                    }
                } catch (\Exception $e) {
                    error_log("Error sending payment confirmation email: " . $e->getMessage());
                }
            } else {
                error_log("Buyer not found for order ID: " . $orderId);
            }
            
            // Update order tracking status - REMOVED: This was overwriting the 'paid' status
            // The status is already set to 'paid' above, so we don't need to call updateOrderStatus
            // require_once __DIR__ . '/../includes/order_functions.php';
            // updateOrderStatus($orderId, 'payment_confirmed', null, 'Payment received via Paystack');
            
            Security::log('payment_success', $order['buyer_id'], [
                'order_id' => $orderId,
                'reference' => $reference
            ]);
            
            $this->setFlash('success', 'Payment successful! Your order is confirmed and delivery tracking is now available.');
            
            // Ensure cart is cleared one final time before redirect
            $_SESSION['cart'] = [];
            
            // Clear output buffer before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Final session save before redirect
            session_write_close();
            session_start();
            
            // Redirect to order detail page (which includes tracking)
            error_log("Redirecting to order detail page: /orders/{$orderId}. Final cart check: " . (empty($_SESSION['cart']) ? 'EMPTY ✓' : 'NOT EMPTY ✗'));
            $this->redirect('/orders/' . $orderId);
            return;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Payment processing error: " . $e->getMessage());
            $this->setFlash('error', 'Payment processing failed: ' . $e->getMessage());
            $this->redirect('/orders');
        }
    }
    
    /**
     * Payment success page
     */
    public function success($orderId): void
    {
        $orderId = (int)$orderId;
        $user = $this->user();
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);
        
        if (!$order) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('/');
            return;
        }
        
        // If user is logged in, verify they own the order
        if ($user && $order['buyer_id'] !== $user['id']) {
            $this->setFlash('error', 'Unauthorized access');
            $this->redirect('/orders');
            return;
        }
        
        echo $this->view->render('Payment/success', [
            'order' => $order,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Mock payment callback for testing
     */
    public function mockCallback(): void
    {
        $reference = $_GET['reference'] ?? 'MOCK-' . bin2hex(random_bytes(8));
        $orderId = $_SESSION['payment_order_id'] ?? 0;
        
        if ($orderId) {
            $this->processPaymentSuccess($orderId, $reference, [
                'status' => 'success',
                'reference' => $reference,
                'amount' => 0,
                'currency' => 'GHS'
            ]);
        } else {
            $this->setFlash('error', 'Order not found');
            $this->redirect('/orders');
        }
    }
}