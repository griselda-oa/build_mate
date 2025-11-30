<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Supplier;
use App\Product;
use App\Category;
use App\KycDocument;
use App\Order;
use App\OrderItem;
use App\Delivery;
use App\FileUploadService;
use App\Validator;
use App\Security;
use App\Response;
use App\PremiumSubscription;
use App\Advertisement;
use App\PaystackService;

/**
 * Supplier controller
 */
class SupplierController extends Controller
{
    /**
     * Supplier application page (public)
     */
    public function apply(): void
    {
        echo $this->view->render('Supplier/apply', [
            'title' => 'Apply to Become a Supplier'
        ]);
    }
    
    /**
     * Supplier pending dashboard (after application submission)
     */
    public function pending(): void
    {
        echo $this->view->render('Supplier/pending', [
            'title' => 'Application Pending Review'
        ]);
    }
    
    /**
     * Supplier dashboard
     */
    public function dashboard(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            // Redirect to KYC/setup page instead of redirecting to the same dashboard page
            $this->redirect('/supplier/kyc');
        }
        
        $products = $supplierModel->getRecentProducts($supplier['id'], 5);
        $orders = $supplierModel->getRecentOrders($supplier['id'], 5);
        $stats = $supplierModel->getSupplierStats($supplier['id']);
        
        // Check premium status
        $isPremium = $supplierModel->isPremium($supplier['id']);
        $expiresAt = $supplier['premium_expires_at'] ?? null;
        $sentimentScore = (float)($supplier['sentiment_score'] ?? 1.0);
        $performanceWarnings = (int)($supplier['performance_warnings'] ?? 0);
        
        // Get active advertisements for banner
        $advertisements = [];
        try {
            $adModel = new \App\Advertisement();
            $advertisements = $adModel->getActive();
            $advertisements = array_slice($advertisements, 0, 5);
        } catch (\Exception $e) {
            error_log("Error fetching advertisements: " . $e->getMessage());
        }
        
        echo $this->view->render('Supplier/dashboard', [
            'advertisements' => $advertisements,
            'supplier' => $supplier,
            'products' => $products,
            'orders' => $orders,
            'stats' => $stats,
            'isPremium' => $isPremium,
            'expiresAt' => $expiresAt,
            'sentimentScore' => $sentimentScore,
            'performanceWarnings' => $performanceWarnings
        ]);
    }
    
    /**
     * KYC page
     */
    public function kyc(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        echo $this->view->render('Supplier/kyc', [
            'supplier' => $supplier,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Submit KYC documents
     */
    public function submitKyc(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/kyc');
        }
        
        $config = require __DIR__ . '/../settings/config.php';
        $uploadService = new FileUploadService(
            $config['uploads']['path'],
            $config['uploads']['allowed_types'],
            $config['uploads']['max_size']
        );
        
        // Process file uploads - only include files that were actually uploaded
        $filesToUpload = [];
        if (isset($_FILES['business_reg']) && $_FILES['business_reg']['error'] !== UPLOAD_ERR_NO_FILE) {
            $filesToUpload['business_reg'] = $_FILES['business_reg'];
        }
        if (isset($_FILES['id_card']) && $_FILES['id_card']['error'] !== UPLOAD_ERR_NO_FILE) {
            $filesToUpload['id_card'] = $_FILES['id_card'];
        }
        if (isset($_FILES['store_photo']) && $_FILES['store_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $filesToUpload['store_photo'] = $_FILES['store_photo'];
        }
        
        if (empty($filesToUpload)) {
            $this->setFlash('error', 'Please upload at least one document');
            $this->redirect('/supplier/kyc');
            return;
        }
        
        try {
            $uploadResult = $uploadService->uploadMultiple($filesToUpload);
            
            if (!$uploadResult['success'] && !empty($uploadResult['errors'])) {
                // Remove duplicate errors and format nicely
                $uniqueErrors = array_unique($uploadResult['errors']);
                $errorMessage = count($uniqueErrors) === 1 
                    ? $uniqueErrors[0] 
                    : implode('. ', $uniqueErrors);
                $this->setFlash('error', $errorMessage);
                $this->redirect('/supplier/kyc');
                return;
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Upload error: ' . $e->getMessage());
            $this->redirect('/supplier/kyc');
            return;
        }
        
        // Only proceed if we have successfully uploaded files
        if (empty($uploadResult['results'])) {
            $this->setFlash('error', 'No files were successfully uploaded. Please try again.');
            $this->redirect('/supplier/kyc');
            return;
        }
        
        // Prepare documents for database
        $documents = [];
        foreach ($uploadResult['results'] as $type => $result) {
            $documents[] = [
                'type' => $type, 
                'path' => $result['filename'],
                'file_name' => $result['original_name'] ?? $result['filename']
            ];
        }
        
        // Update supplier info
        $updateData = [
            'business_name' => Validator::sanitize($_POST['business_name'] ?? $supplier['business_name']),
            'business_registration' => Validator::sanitize($_POST['reg_number'] ?? ''),
            'address' => Validator::sanitize($_POST['address'] ?? ''),
            'kyc_status' => 'pending'
        ];
        
        $supplierModel->update($supplier['id'], $updateData);
        
        // Save documents using model
        if (!empty($documents)) {
            $kycDocumentModel = new KycDocument();
            $kycDocumentModel->createMultiple($supplier['id'], $documents);
        }
        
        Security::log('kyc_submitted', $user['id'], ['supplier_id' => $supplier['id']]);
        $this->setFlash('success', 'Application submitted successfully! Your supplier profile is under review.');
        $this->redirect('/supplier/pending');
    }
    
    /**
     * Products management
     */
    public function products(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/kyc');
        }
        
        $productModel = new Product();
        $products = $productModel->getBySupplier($supplier['id']);
        
        // If supplier is approved, auto-verify any pending products
        $isSupplierApproved = ($supplier['kyc_status'] ?? 'pending') === 'approved';
        if ($isSupplierApproved) {
            $db = \App\DB::getInstance();
            $stmt = $db->prepare("UPDATE products SET verified = 1 WHERE supplier_id = ? AND verified = 0");
            $stmt->execute([$supplier['id']]);
            $productsUpdated = $stmt->rowCount();
            
            // Reload products after update
            if ($productsUpdated > 0) {
                $products = $productModel->getBySupplier($supplier['id']);
            }
        }
        
        $categoryModel = new Category();
        $categories = $categoryModel->findAll('name ASC');
        
        echo $this->view->render('Supplier/products', [
            'products' => $products,
            'categories' => $categories,
            'supplier' => $supplier, // Pass supplier info to view
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Create product
     */
    public function createProduct(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/kyc');
        }
        
        $name = Validator::sanitize($_POST['name'] ?? '', 255);
        $description = Validator::sanitize($_POST['description'] ?? '', 2000);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $priceCents = (int)($_POST['price'] ?? 0) * 100;
        $stock = (int)($_POST['stock'] ?? 0);
        $currency = $_POST['currency'] ?? 'GHS';
        $deliverySize = in_array($_POST['delivery_size'] ?? 'small', ['small', 'large']) ? $_POST['delivery_size'] : 'small';
        
        // Handle image: file upload takes priority over URL
        $imageUrl = null;
        
        // First, try file upload
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $config = require __DIR__ . '/../settings/config.php';
            $uploadPath = $config['uploads']['path'] . '/products';
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            $uploadService = new FileUploadService($uploadPath, $allowedTypes, $maxSize);
            $uploadResult = $uploadService->upload($_FILES['product_image']);
            
            if ($uploadResult['success']) {
                // Generate URL path for uploaded file
                $imageUrl = '/storage/uploads/products/' . $uploadResult['filename'];
                
                // Verify file actually exists
                $fullPath = $uploadPath . '/' . $uploadResult['filename'];
                if (!file_exists($fullPath)) {
                    error_log("ERROR: Uploaded file does not exist at: {$fullPath}");
                    $this->setFlash('error', 'Image was uploaded but file not found. Please try again.');
                    $this->redirect('/supplier/products');
                    return;
                }
                
                error_log("SUCCESS: Product image uploaded to: {$imageUrl} (File: {$fullPath}, Size: " . filesize($fullPath) . " bytes)");
            } else {
                $errorMsg = implode(', ', $uploadResult['errors']);
                error_log("ERROR: Image upload failed: {$errorMsg}");
                $this->setFlash('error', 'Image upload failed: ' . $errorMsg);
                $this->redirect('/supplier/products');
                return;
            }
        } else {
            // Fall back to URL if no file uploaded
            $imageUrlRaw = trim($_POST['image_url'] ?? '');
            
            if (!empty($imageUrlRaw)) {
                // Check column size to determine safe truncation limit
                $maxUrlLength = 450; // Default safe for VARCHAR(500)
                try {
                    $db = \App\DB::getInstance();
                    $stmt = $db->query("SHOW COLUMNS FROM products WHERE Field = 'image_url'");
                    $column = $stmt->fetch();
                    if ($column) {
                        $type = strtolower($column['Type'] ?? '');
                        if (strpos($type, 'varchar') !== false) {
                            preg_match('/varchar\((\d+)\)/', $type, $matches);
                            if (!empty($matches[1])) {
                                $maxUrlLength = (int)$matches[1] - 50; // Leave 50 char buffer
                            }
                        } elseif (strpos($type, 'text') !== false) {
                            $maxUrlLength = 2000; // TEXT can handle much longer URLs
                        }
                    }
                } catch (\Exception $e) {
                    // Use default if check fails
                }
                
                $validatedUrl = filter_var($imageUrlRaw, FILTER_VALIDATE_URL);
                if ($validatedUrl) {
                    // Truncate if needed, but preserve URL structure
                    if (strlen($validatedUrl) > $maxUrlLength) {
                        // Try to preserve file extension if possible
                        $extension = '';
                        $urlWithoutExt = $validatedUrl;
                        if (preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $validatedUrl, $extMatches)) {
                            $extension = $extMatches[0];
                            $urlWithoutExt = substr($validatedUrl, 0, strrpos($validatedUrl, $extension));
                        }
                        $truncated = substr($urlWithoutExt, 0, $maxUrlLength - strlen($extension)) . $extension;
                        $imageUrl = $truncated;
                    } else {
                        $imageUrl = $validatedUrl;
                    }
                } else {
                    // If validation fails, try to clean and truncate anyway
                    $cleaned = trim($imageUrlRaw);
                    if (strlen($cleaned) > 0 && (strpos($cleaned, 'http://') === 0 || strpos($cleaned, 'https://') === 0)) {
                        if (strlen($cleaned) > $maxUrlLength) {
                            $imageUrl = substr($cleaned, 0, $maxUrlLength);
                        } else {
                            $imageUrl = $cleaned;
                        }
                    }
                }
            }
        }
        
        if (empty($name) || $categoryId <= 0 || $priceCents <= 0) {
            $this->setFlash('error', 'Please fill all required fields');
            $this->redirect('/supplier/products');
        }
        
        $productModel = new Product();
        $slug = $productModel->generateSlug($name);
        
        // Auto-verify products if supplier is approved
        // Only pending suppliers need admin approval for products
        $isSupplierApproved = ($supplier['kyc_status'] ?? 'pending') === 'approved';
        $productVerified = $isSupplierApproved ? 1 : 0;
        
        $productData = [
            'supplier_id' => $supplier['id'],
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'image_url' => $imageUrl,
            'price_cents' => $priceCents,
            'currency' => $currency,
            'stock' => $stock,
            'verified' => $productVerified
        ];
        
        // Only add delivery_size if column exists (after migration)
        // Check if column exists by trying to describe the table
        try {
            $db = \App\DB::getInstance();
            $stmt = $db->query("SHOW COLUMNS FROM products LIKE 'delivery_size'");
            if ($stmt->rowCount() > 0) {
                $productData['delivery_size'] = $deliverySize;
            }
        } catch (\Exception $e) {
            // Column doesn't exist yet, skip it
        }
        
        $productModel->create($productData);
        
        Security::log('product_created', $user['id'], ['supplier_id' => $supplier['id']]);
        $this->setFlash('success', 'Product created successfully');
        $this->redirect('/supplier/products');
    }
    
    /**
     * Update product
     */
    public function updateProduct(int $id): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product || $product['supplier_id'] !== $supplier['id']) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('/supplier/products');
        }
        
        // Handle image: file upload takes priority over URL
        $imageUrl = $product['image_url'] ?? null;
        
        // First, try file upload
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $config = require __DIR__ . '/../settings/config.php';
            $uploadPath = $config['uploads']['path'] . '/products';
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            $uploadService = new FileUploadService($uploadPath, $allowedTypes, $maxSize);
            $uploadResult = $uploadService->upload($_FILES['product_image']);
            
            if ($uploadResult['success']) {
                // Generate URL path for uploaded file
                $imageUrl = '/storage/uploads/products/' . $uploadResult['filename'];
                
                // Verify file actually exists
                $fullPath = $uploadPath . '/' . $uploadResult['filename'];
                if (!file_exists($fullPath)) {
                    error_log("ERROR: Uploaded file does not exist at: {$fullPath}");
                    $this->setFlash('error', 'Image was uploaded but file not found. Please try again.');
                    $this->redirect('/supplier/products');
                    return;
                }
                
                error_log("SUCCESS: Product image updated to: {$imageUrl} (File: {$fullPath}, Size: " . filesize($fullPath) . " bytes)");
            } else {
                $errorMsg = implode(', ', $uploadResult['errors']);
                error_log("ERROR: Image upload failed: {$errorMsg}");
                $this->setFlash('error', 'Image upload failed: ' . $errorMsg);
                $this->redirect('/supplier/products');
                return;
            }
        } elseif (!empty($_POST['image_url'])) {
            // Fall back to URL if no file uploaded
            $imageUrlRaw = trim($_POST['image_url']);
            
            // Check column size to determine safe truncation limit
            $maxUrlLength = 450; // Default safe for VARCHAR(500)
            try {
                $db = \App\DB::getInstance();
                $stmt = $db->query("SHOW COLUMNS FROM products WHERE Field = 'image_url'");
                $column = $stmt->fetch();
                if ($column) {
                    $type = strtolower($column['Type'] ?? '');
                    if (strpos($type, 'varchar') !== false) {
                        preg_match('/varchar\((\d+)\)/', $type, $matches);
                        if (!empty($matches[1])) {
                            $maxUrlLength = (int)$matches[1] - 50; // Leave 50 char buffer
                        }
                    } elseif (strpos($type, 'text') !== false) {
                        $maxUrlLength = 2000; // TEXT can handle much longer URLs
                    }
                }
            } catch (\Exception $e) {
                // Use default if check fails
            }
            
            $validatedUrl = filter_var($imageUrlRaw, FILTER_VALIDATE_URL);
            if ($validatedUrl) {
                // Truncate if needed, but preserve URL structure
                if (strlen($validatedUrl) > $maxUrlLength) {
                    // Try to preserve file extension if possible
                    $extension = '';
                    $urlWithoutExt = $validatedUrl;
                    if (preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $validatedUrl, $extMatches)) {
                        $extension = $extMatches[0];
                        $urlWithoutExt = substr($validatedUrl, 0, strrpos($validatedUrl, $extension));
                    }
                    $truncated = substr($urlWithoutExt, 0, $maxUrlLength - strlen($extension)) . $extension;
                    $imageUrl = $truncated;
                } else {
                    $imageUrl = $validatedUrl;
                }
            } else {
                // If validation fails, try to clean and truncate anyway
                $cleaned = trim($imageUrlRaw);
                if (strlen($cleaned) > 0 && (strpos($cleaned, 'http://') === 0 || strpos($cleaned, 'https://') === 0)) {
                    if (strlen($cleaned) > $maxUrlLength) {
                        $imageUrl = substr($cleaned, 0, $maxUrlLength);
                    } else {
                        $imageUrl = $cleaned;
                    }
                }
            }
        }
        
        $deliverySize = in_array($_POST['delivery_size'] ?? ($product['delivery_size'] ?? 'small'), ['small', 'large']) 
            ? ($_POST['delivery_size'] ?? ($product['delivery_size'] ?? 'small')) 
            : 'small';
        
        $updateData = [
            'name' => Validator::sanitize($_POST['name'] ?? $product['name'], 255),
            'description' => Validator::sanitize($_POST['description'] ?? $product['description'], 2000),
            'category_id' => (int)($_POST['category_id'] ?? $product['category_id']),
            'price_cents' => (int)($_POST['price'] ?? ($product['price_cents'] / 100)) * 100,
            'stock' => (int)($_POST['stock'] ?? $product['stock']),
            'currency' => $_POST['currency'] ?? $product['currency'],
            'image_url' => $imageUrl
        ];
        
        // Only add delivery_size if column exists (after migration)
        try {
            $db = \App\DB::getInstance();
            $stmt = $db->query("SHOW COLUMNS FROM products LIKE 'delivery_size'");
            if ($stmt->rowCount() > 0) {
                $updateData['delivery_size'] = $deliverySize;
            }
        } catch (\Exception $e) {
            // Column doesn't exist yet, skip it
        }
        
        $productModel->update($id, $updateData);
        
        Security::log('product_updated', $user['id'], ['product_id' => $id]);
        $this->setFlash('success', 'Product updated successfully');
        $this->redirect('/supplier/products');
    }
    
    /**
     * Delete product
     */
    public function deleteProduct(int $id): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product || $product['supplier_id'] !== $supplier['id']) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('/supplier/products');
        }
        
        $productModel->delete($id);
        
        Security::log('product_deleted', $user['id'], ['product_id' => $id]);
        $this->setFlash('success', 'Product deleted successfully');
        $this->redirect('/supplier/products');
    }
    
    /**
     * Supplier orders
     */
    public function orders(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        $orderModel = new Order();
        $allOrders = $supplier ? $orderModel->getBySupplier($supplier['id']) : [];
        
        // Show all orders (placed, paid, processing, out_for_delivery, delivered)
        // Don't filter out any statuses - suppliers should see all their orders
        $orders = $allOrders;
        
        // Get buyer names and delivery status
        $db = \App\DB::getInstance();
        $deliveryModel = new Delivery();
        foreach ($orders as &$order) {
            $buyerStmt = $db->prepare("SELECT name FROM users WHERE id = ?");
            $buyerStmt->execute([$order['buyer_id']]);
            $buyer = $buyerStmt->fetch();
            $order['buyer_name'] = $buyer['name'] ?? 'N/A';
            
            // Get delivery status
            $delivery = $deliveryModel->findByOrderId($order['id']);
            $order['delivery_status'] = $delivery['status'] ?? 'pending_pickup';
            $order['delivery_id'] = $delivery['id'] ?? null;
            $order['payment_released'] = $order['payment_released'] ?? 0;
        }
        unset($order);
        
        echo $this->view->render('Supplier/orders', [
            'orders' => $orders,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId): void
    {
        // Ensure we always return JSON for this endpoint (it's AJAX-only)
        $isAjax = $this->isAjax();
        
        // Clear any output buffers to prevent JSON corruption
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Set JSON header early
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        
        try {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
                if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Supplier not found'], 403);
            } else {
                $this->setFlash('error', 'Supplier profile not found');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        $orderModel = new Order();
        // Use getWithItems to ensure we get payment fields if they exist
        $order = $orderModel->getWithItems($orderId);
        
        // If getWithItems returns null, try find as fallback
        if (!$order) {
            $order = $orderModel->find($orderId);
        }
        
        // Verify order belongs to this supplier
        $orderItems = (new OrderItem())->getByOrderId($orderId);
        $belongsToSupplier = false;
        foreach ($orderItems as $item) {
            $product = (new Product())->find($item['product_id']);
            if ($product && $product['supplier_id'] === $supplier['id']) {
                $belongsToSupplier = true;
                break;
            }
        }
        
        if (!$order || !$belongsToSupplier) {
                if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Order not found'], 404);
            } else {
                $this->setFlash('error', 'Order not found');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        // Get new status from request
        $newStatus = $_POST['status'] ?? $_GET['status'] ?? null;
        
        if (!$newStatus) {
                if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Status is required'], 400);
            } else {
                $this->setFlash('error', 'Status is required');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        // Validate status transition
        // Suppliers can ONLY: any pre-processing status → processing
        // They CANNOT mark as 'out_for_delivery' or 'delivered' (only admin/logistics can)
            $currentStatus = $order['current_status'] ?? $order['status'] ?? 'placed';
            
            // Map 'shipped' to 'out_for_delivery' for consistency
            if ($currentStatus === 'shipped') {
                $currentStatus = 'out_for_delivery';
            }
        
        // Suppliers cannot mark as out_for_delivery or delivered
        if (in_array($newStatus, ['out_for_delivery', 'delivered'])) {
                if ($isAjax) {
                Response::json([
                    'success' => false, 
                    'message' => 'Suppliers cannot mark orders as "Out for Delivery" or "Delivered". Only admin/logistics can do that.'
                ], 403);
            } else {
                $this->setFlash('error', 'Suppliers cannot mark orders as "Out for Delivery" or "Delivered". Only admin/logistics can do that.');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        // Define pre-processing statuses (any status before processing)
            // Include all possible paid/placed statuses
        $preProcessingStatuses = ['placed', 'pending', 'paid', 'paid_escrow', 'paid_paystack_secure', 'payment_confirmed'];
        
        // Check if transition is allowed
        $isAllowed = false;
        
        // Rule: Suppliers can only go to 'processing' from pre-processing statuses
            // IMPORTANT: If order exists, payment was successful (order wouldn't exist otherwise)
        if ($newStatus === 'processing') {
                // Allow if current status is pre-processing OR if order has payment reference (payment successful)
                if (in_array($currentStatus, $preProcessingStatuses) || !empty($order['payment_reference'])) {
                $isAllowed = true;
            }
        }
        
        if (!$isAllowed) {
            $errorMsg = "Invalid status transition: Cannot change from '{$currentStatus}' to '{$newStatus}'";
            if ($newStatus === 'processing') {
                    $errorMsg .= ". Order must be in a pre-processing status (placed, paid, etc.)";
            }
            
                error_log("SupplierController::updateOrderStatus - Status transition denied. Current: {$currentStatus}, New: {$newStatus}, Payment ref: " . ($order['payment_reference'] ?? 'none'));
                
                if ($isAjax) {
                Response::json([
                    'success' => false, 
                    'message' => $errorMsg
                ], 400);
            } else {
                $this->setFlash('error', $errorMsg);
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
            // Update order status using the proper function that handles current_status and timestamps
            require_once __DIR__ . '/../includes/order_functions.php';
            
            try {
                $success = updateOrderStatus($orderId, $newStatus, $user['id'], 'Status updated by supplier');
            } catch (\Exception $e) {
                error_log("SupplierController::updateOrderStatus - Exception: " . $e->getMessage());
                if ($isAjax) {
                    Response::json([
                        'success' => false, 
                        'message' => 'Failed to update order status: ' . $e->getMessage()
                    ], 500);
                } else {
                    $this->setFlash('error', 'Failed to update order status: ' . $e->getMessage());
                    $this->redirect('/supplier/orders');
                }
                return;
            }
            
            if ($success) {
            // Log the status change
            Security::log('order_status_updated', $user['id'], [
                'order_id' => $orderId,
                'old_status' => $order['status'],
                    'new_status' => $newStatus,
                    'updated_by' => 'supplier'
            ]);
            
                if ($isAjax) {
                Response::json([
                    'success' => true, 
                    'message' => 'Order status updated successfully',
                    'new_status' => $newStatus
                ]);
            } else {
                $this->setFlash('success', 'Order status updated successfully');
                $this->redirect('/supplier/orders');
                }
            } else {
                // Log the failure for debugging
                error_log("SupplierController::updateOrderStatus - updateOrderStatus() returned false for order {$orderId}, status: {$newStatus}");
                
                if ($isAjax) {
                    Response::json([
                        'success' => false, 
                        'message' => 'Failed to update order status. Please check server logs for details.'
                    ], 500);
                } else {
                    $this->setFlash('error', 'Failed to update order status. Please try again or contact support.');
                    $this->redirect('/supplier/orders');
                }
            }
        } catch (\Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            if ($isAjax) {
                Response::json([
                    'success' => false, 
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            } else {
                $this->setFlash('error', 'An error occurred while updating order status');
                $this->redirect('/supplier/orders');
            }
        }
    }
    
    /**
     * Mark order as ready for pickup
     */
    public function markReadyForPickup(int $orderId): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'message' => 'Supplier not found'], 403);
            } else {
                $this->setFlash('error', 'Supplier profile not found');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        $orderModel = new Order();
        $order = $orderModel->find($orderId);
        
        // Verify order belongs to this supplier
        $orderItems = (new OrderItem())->getByOrderId($orderId);
        $belongsToSupplier = false;
        foreach ($orderItems as $item) {
            $product = (new Product())->find($item['product_id']);
            if ($product && $product['supplier_id'] === $supplier['id']) {
                $belongsToSupplier = true;
                break;
            }
        }
        
        if (!$order || !$belongsToSupplier) {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'message' => 'Order not found'], 404);
            } else {
                $this->setFlash('error', 'Order not found');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        // Check if order is paid (accept multiple paid statuses)
        $paidStatuses = ['paid', 'paid_escrow', 'paid_paystack_secure', 'payment_confirmed'];
        if (!in_array($order['status'], $paidStatuses) && empty($order['payment_reference'])) {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'message' => 'Order must be paid before marking ready'], 400);
            } else {
                $this->setFlash('error', 'Order must be paid before marking ready');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        // Get delivery record
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findByOrderId($orderId);
        
        if (!$delivery) {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'message' => 'Delivery record not found'], 404);
            } else {
                $this->setFlash('error', 'Delivery record not found');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        // Check current delivery status must be 'pending_pickup'
        if ($delivery['status'] !== 'pending_pickup') {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'message' => 'Order is not in pending pickup status'], 400);
            } else {
                $this->setFlash('error', 'Order is not in pending pickup status');
                $this->redirect('/supplier/orders');
            }
            return;
        }
        
        $db = \App\DB::getInstance();
        $db->beginTransaction();
        
        try {
            // Update delivery status to 'ready_for_pickup'
            $success = $deliveryModel->updateStatus(
                $delivery['id'],
                'ready_for_pickup',
                $user['id'],
                'supplier',
                'Marked ready for pickup by supplier'
            );
            
            $db->commit();
            
            if ($success) {
                Security::log('delivery_ready_for_pickup', $user['id'], ['order_id' => $orderId, 'delivery_id' => $delivery['id']]);
                
                if ($this->isAjax()) {
                    Response::json(['success' => true, 'message' => 'Order marked as ready for pickup']);
                } else {
                    $this->setFlash('success', 'Order marked as ready for pickup');
                    $this->redirect('/supplier/orders');
                }
            } else {
                $db->rollBack();
                if ($this->isAjax()) {
                    Response::json(['success' => false, 'message' => 'Failed to update status'], 500);
                } else {
                    $this->setFlash('error', 'Failed to update status');
                    $this->redirect('/supplier/orders');
                }
            }
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Mark ready for pickup error: " . $e->getMessage());
            if ($this->isAjax()) {
                Response::json(['success' => false, 'message' => 'An error occurred'], 500);
            } else {
                $this->setFlash('error', 'An error occurred');
                $this->redirect('/supplier/orders');
            }
        }
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Accept order
     */
    public function acceptOrder(int $id): void
    {
        // Order acceptance logic
        $this->setFlash('success', 'Order accepted');
        $this->redirect('/supplier/orders');
    }
    
    /**
     * Dispatch order
     */
    public function dispatchOrder(int $id): void
    {
        $orderModel = new Order();
        $order = $orderModel->find($id);
        
        if ($order && $order['status'] === 'paid_escrow') {
            $orderModel->update($id, ['status' => 'in_transit']);
            Security::log('order_dispatched', $this->user()['id'], ['order_id' => $id]);
            $this->setFlash('success', 'Order dispatched');
        }
        
        $this->redirect('/supplier/orders');
    }
    
    /**
     * Show premium upgrade page
     */
    public function upgrade(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $isPremium = $supplierModel->isPremium($supplier['id']);
        $expiresAt = $supplier['premium_expires_at'] ?? null;
        
        echo $this->view->render('Supplier/upgrade', [
            'supplier' => $supplier,
            'isPremium' => $isPremium,
            'expiresAt' => $expiresAt,
            'sentimentScore' => $supplier['sentiment_score'] ?? 1.0,
            'performanceWarnings' => $supplier['performance_warnings'] ?? 0
        ]);
    }
    
    /**
     * Initiate premium upgrade payment
     */
    public function initiateUpgrade(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            Response::json(['success' => false, 'message' => 'Supplier not found'], 404);
            return;
        }
        
        // Check if already premium and not expired
        if ($supplierModel->isPremium($supplier['id'])) {
            Response::json(['success' => false, 'message' => 'You already have an active premium subscription'], 400);
            return;
        }
        
        // Premium plan: 30 days for 50,000 GHS (500,000 cents)
        $amountCents = 500000; // 50,000 GHS
        $currency = 'GHS';
        $days = 30;
        
        // Generate payment reference
        $paymentReference = 'PREMIUM-' . $supplier['id'] . '-' . time() . '-' . bin2hex(random_bytes(4));
        
        // Create subscription record
        $subscriptionModel = new PremiumSubscription();
        $subscriptionId = $subscriptionModel->createSubscription(
            $supplier['id'],
            $paymentReference,
            $amountCents,
            $currency,
            $days
        );
        
        // Initialize Paystack payment
        $paystackService = new PaystackService();
        $paymentData = $paystackService->initializePayment(
            $amountCents,
            $currency,
            $paymentReference,
            $user['email'],
            $user['name'],
            '/supplier/upgrade/callback',
            [
                'subscription_id' => $subscriptionId,
                'supplier_id' => $supplier['id'],
                'type' => 'premium_upgrade'
            ]
        );
        
        if ($paymentData && isset($paymentData['authorization_url'])) {
            Response::json([
                'success' => true,
                'authorization_url' => $paymentData['authorization_url'],
                'reference' => $paymentReference
            ]);
        } else {
            Response::json(['success' => false, 'message' => 'Failed to initialize payment'], 500);
        }
    }
    
    /**
     * Handle premium upgrade payment callback
     */
    public function upgradeCallback(): void
    {
        $reference = $_GET['reference'] ?? '';
        
        if (empty($reference)) {
            $this->setFlash('error', 'Invalid payment reference');
            $this->redirect('/supplier/upgrade');
            return;
        }
        
        $subscriptionModel = new PremiumSubscription();
        $subscription = $subscriptionModel->findByPaymentReference($reference);
        
        if (!$subscription) {
            $this->setFlash('error', 'Subscription not found');
            $this->redirect('/supplier/upgrade');
            return;
        }
        
        // Verify payment with Paystack
        $paystackService = new PaystackService();
        $verification = $paystackService->verifyPayment($reference);
        
        if ($verification && $verification['status'] === true) {
            // Mark subscription as completed
            $subscriptionModel->markCompleted($subscription['id'], $reference);
            
            // Upgrade supplier to premium
            $supplierModel = new Supplier();
            $supplierModel->upgradeToPremium($subscription['supplier_id'], $subscription['plan_duration_days']);
            
            Security::log('premium_upgraded', $this->user()['id'], [
                'supplier_id' => $subscription['supplier_id'],
                'subscription_id' => $subscription['id']
            ]);
            
            $this->setFlash('success', 'Premium subscription activated successfully! Your products will now appear with priority placement.');
            $this->redirect('/supplier/dashboard');
        } else {
            $this->setFlash('error', 'Payment verification failed. Please contact support if payment was deducted.');
            $this->redirect('/supplier/upgrade');
        }
    }
    
    /**
     * Show premium status and analytics
     */
    public function premiumStatus(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $isPremium = $supplierModel->isPremium($supplier['id']);
        $subscriptionModel = new PremiumSubscription();
        $subscriptions = $subscriptionModel->getBySupplier($supplier['id']);
        
        $adModel = new Advertisement();
        $advertisements = $adModel->getBySupplier($supplier['id']);
        
        echo $this->view->render('Supplier/premium-status', [
            'supplier' => $supplier,
            'isPremium' => $isPremium,
            'subscriptions' => $subscriptions,
            'advertisements' => $advertisements,
            'sentimentScore' => $supplier['sentiment_score'] ?? 1.0,
            'performanceWarnings' => $supplier['performance_warnings'] ?? 0
        ]);
    }
    
    /**
     * Create advertisement (Premium only)
     */
    public function createAd(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            Response::json(['success' => false, 'message' => 'Supplier not found'], 404);
            return;
        }
        
        // Check premium status
        if (!$supplierModel->isPremium($supplier['id'])) {
            Response::json(['success' => false, 'message' => 'Premium subscription required to create advertisements'], 403);
            return;
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $imageUrl = trim($_POST['image_url'] ?? '');
        
        // Validate
        if ($productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid product'], 400);
            return;
        }
        
        // Verify product belongs to supplier
        $productModel = new Product();
        $product = $productModel->find($productId);
        if (!$product || $product['supplier_id'] != $supplier['id']) {
            Response::json(['success' => false, 'message' => 'Product not found or unauthorized'], 403);
            return;
        }
        
        // Create advertisement
        $adModel = new Advertisement();
        $adId = $adModel->create([
            'supplier_id' => $supplier['id'],
            'product_id' => $productId,
            'title' => $title ?: $product['name'],
            'description' => $description,
            'image_url' => $imageUrl ?: $product['image_url'],
            'status' => 'pending', // Requires admin approval
            'start_date' => $startDate ?: date('Y-m-d H:i:s'),
            'end_date' => $endDate ?: null
        ]);
        
        Security::log('ad_created', $user['id'], [
            'supplier_id' => $supplier['id'],
            'product_id' => $productId,
            'ad_id' => $adId
        ]);
        
        Response::json([
            'success' => true,
            'message' => 'Advertisement created successfully. Awaiting admin approval.',
            'ad_id' => $adId
        ]);
    }
    
    /**
     * Get advertisement status
     */
    public function adStatus(): void
    {
        $user = $this->user();
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            Response::json(['success' => false, 'message' => 'Supplier not found'], 404);
            return;
        }
        
        $adModel = new Advertisement();
        $advertisements = $adModel->getBySupplier($supplier['id']);
        
        Response::json([
            'success' => true,
            'advertisements' => $advertisements
        ]);
    }
    
}

