<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Advertisement;
use App\Supplier;
use App\Product;
use App\PaystackService;
use App\DB;

/**
 * Advertisement controller for premium suppliers
 */
class AdvertisementController extends Controller
{
    /**
     * Show create advertisement page
     */
    public function create(): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->setFlash('error', 'Only suppliers can create advertisements');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        // Check if supplier is approved (required to create products and ads)
        if (($supplier['kyc_status'] ?? NULL) !== 'approved') {
            $this->setFlash('error', 'Your supplier account must be approved before you can create advertisements. Please complete your KYC application and wait for admin approval.');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        // No premium check needed - each ad requires separate payment
        $productModel = new Product();
        $products = $productModel->getBySupplier($supplier['id']);
        
        // Log for debugging
        error_log("AdvertisementController::create - Supplier ID: {$supplier['id']}, Products found: " . count($products));
        
        // Check if supplier has products
        if (empty($products)) {
            $this->setFlash('warning', 'You need to create at least one product before you can create an advertisement. <a href="' . \App\View::url('/supplier/products') . '">Create a product</a>');
        }
        
        $adModel = new Advertisement();
        $existingAds = $adModel->getBySupplier($supplier['id']);
        
        echo $this->view->render('Supplier/advertisement-create', [
            'supplier' => $supplier,
            'products' => $products ?? [],
            'existingAds' => $existingAds,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Submit advertisement
     */
    public function submit(): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->setFlash('error', 'Only suppliers can create advertisements');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier profile not found');
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        // Check if payment was completed (payment_reference should be in POST)
        $paymentReference = $_POST['payment_reference'] ?? '';
        if (empty($paymentReference)) {
            $this->setFlash('error', 'Payment is required to create an advertisement. Please complete payment first.');
            $this->redirect('/supplier/advertisements/create');
            return;
        }
        
        // Verify payment was successful
        $paystackService = new PaystackService();
        try {
            $verification = $paystackService->verifyTransaction($paymentReference);
            if (!$verification['status'] || $verification['data']['status'] !== 'success') {
                $this->setFlash('error', 'Payment verification failed. Please try again.');
                $this->redirect('/supplier/advertisements/create');
                return;
            }
            
            // Check if this payment reference was already used
            $db = DB::getInstance();
            $checkStmt = $db->prepare("SELECT id FROM advertisements WHERE payment_reference = ?");
            $checkStmt->execute([$paymentReference]);
            if ($checkStmt->fetch()) {
                $this->setFlash('error', 'This payment has already been used to create an advertisement.');
                $this->redirect('/supplier/advertisements/create');
                return;
            }
        } catch (\Exception $e) {
            error_log("Advertisement payment verification error: " . $e->getMessage());
            $this->setFlash('error', 'Payment verification error. Please contact support.');
            $this->redirect('/supplier/advertisements/create');
            return;
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if ($productId <= 0) {
            $this->setFlash('error', 'Please select a product');
            $this->redirectBack();
            return;
        }
        
        // Verify product belongs to supplier
        $productModel = new Product();
        $product = $productModel->find($productId);
        if (!$product || $product['supplier_id'] != $supplier['id']) {
            $this->setFlash('error', 'Invalid product');
            $this->redirectBack();
            return;
        }
        
        // Handle file uploads (images and videos)
        $mediaUrl = null;
        $config = require __DIR__ . '/../settings/config.php';
        $uploadPath = $config['uploads']['path'] . '/advertisements';
        
        if (!empty($_FILES['media_files']['name'][0])) {
            $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $allowedVideoTypes = ['video/mp4', 'video/mov', 'video/quicktime', 'video/webm'];
            $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);
            $maxSize = 10 * 1024 * 1024; // 10MB
            
            // Process first uploaded file
            $file = [
                'name' => $_FILES['media_files']['name'][0],
                'type' => $_FILES['media_files']['type'][0],
                'tmp_name' => $_FILES['media_files']['tmp_name'][0],
                'error' => $_FILES['media_files']['error'][0],
                'size' => $_FILES['media_files']['size'][0]
            ];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                $uploadService = new FileUploadService($uploadPath, $allowedTypes, $maxSize);
                $uploadResult = $uploadService->upload($file);
                
                if ($uploadResult['success']) {
                    $mediaUrl = '/storage/uploads/advertisements/' . $uploadResult['filename'];
                } else {
                    $this->setFlash('error', 'File upload failed: ' . implode(', ', $uploadResult['errors']));
                    $this->redirectBack();
                    return;
                }
            }
        }
        
        // Auto-set dates: Start = today, End = 30 days from today (matching premium subscription)
        $startDate = date('Y-m-d H:i:s'); // Today, now
        $endDate = date('Y-m-d H:i:s', strtotime('+30 days')); // 30 days from today
        
        // Create advertisement (auto-approved, active immediately)
        $adModel = new Advertisement();
        $adId = $adModel->create([
            'supplier_id' => $supplier['id'],
            'product_id' => $productId,
            'title' => $title ?: $product['name'],
            'description' => $description ?: $product['description'],
            'image_url' => $mediaUrl ?: $product['image_url'], // Use uploaded media or product image
            'status' => 'active', // Auto-approved, no admin approval needed
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_reference' => $paymentReference // Store payment reference
        ]);
        
        if ($adId) {
            $this->setFlash('success', 'Advertisement created and activated! It will run for 30 days.');
        } else {
            $this->setFlash('error', 'Failed to create advertisement');
        }
        
        $this->redirect('/supplier/advertisements');
    }
    
    /**
     * List supplier's advertisements
     */
    public function index(): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $adModel = new Advertisement();
        $ads = $adModel->getBySupplier($supplier['id']);
        
        echo $this->view->render('Supplier/advertisements', [
            'supplier' => $supplier,
            'advertisements' => $ads
        ]);
    }
    
    /**
     * Initialize payment for advertisement
     */
    public function initializePayment(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('/supplier/advertisements/create');
            return;
        }
        
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->json(['success' => false, 'message' => 'Only suppliers can create advertisements'], 403);
            return;
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->json(['success' => false, 'message' => 'Supplier profile not found'], 404);
            return;
        }
        
        // Advertisement payment: 250 GHS (25,000 cents) per ad
        $amountCents = 25000; // 250 GHS
        $currency = 'GHS';
        
        $paystackService = new PaystackService();
        $reference = 'AD-' . $supplier['id'] . '-' . time();
        
        $config = require __DIR__ . '/../settings/config.php';
        // Use View::url() to get the correct base URL dynamically
        $baseUrl = \App\View::basePath();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appUrl = $protocol . '://' . $host . rtrim($baseUrl, '/');
        
        error_log("AdvertisementController::initializePayment - Callback URL: " . $appUrl . '/supplier/advertisements/payment/callback');
        
        // Prepare payment data for Paystack
        $paymentData = [
            'email' => $user['email'],
            'amount' => $amountCents, // Amount in kobo (cents)
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => $appUrl . '/supplier/advertisements/payment/callback',
            'metadata' => [
                'supplier_id' => $supplier['id'],
                'type' => 'advertisement',
                'ad_duration_days' => 30
            ]
        ];
        
        try {
            $response = $paystackService->initializeTransaction($paymentData);
            
            if ($response['status']) {
                $this->json([
                    'success' => true,
                    'authorization_url' => $response['data']['authorization_url'],
                    'reference' => $reference
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Payment initialization failed'], 400);
            }
        } catch (\Exception $e) {
            error_log("Advertisement payment error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Payment error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle Paystack callback for advertisement payment
     */
    public function paymentCallback(): void
    {
        $reference = $_GET['reference'] ?? '';
        
        error_log("AdvertisementController::paymentCallback - Reference: " . $reference);
        error_log("AdvertisementController::paymentCallback - GET params: " . print_r($_GET, true));
        
        if (empty($reference)) {
            error_log("AdvertisementController::paymentCallback - No reference provided");
            $this->setFlash('error', 'Invalid payment reference. No reference provided.');
            $this->redirect('/supplier/advertisements/create');
            return;
        }
        
        // Check if reference starts with AD- (advertisement) or handle other formats
        if (!str_starts_with($reference, 'AD-')) {
            error_log("AdvertisementController::paymentCallback - Reference doesn't start with AD-: " . $reference);
            // Try to extract from query string if it's in a different format
            if (isset($_GET['ref'])) {
                $reference = $_GET['ref'];
            } else {
                $this->setFlash('error', 'Invalid payment reference format.');
                $this->redirect('/supplier/advertisements/create');
                return;
            }
        }
        
        $paystackService = new PaystackService();
        
        try {
            error_log("AdvertisementController::paymentCallback - Verifying transaction: " . $reference);
            $verification = $paystackService->verifyTransaction($reference);
            
            error_log("AdvertisementController::paymentCallback - Verification response: " . json_encode($verification));
            
            if ($verification['status'] && isset($verification['data']) && $verification['data']['status'] === 'success') {
                // Extract supplier ID from reference (format: AD-{supplierId}-{timestamp})
                $parts = explode('-', $reference);
                $supplierId = (int)($parts[1] ?? 0);
                
                error_log("AdvertisementController::paymentCallback - Extracted supplier ID: " . $supplierId);
                
                if ($supplierId > 0) {
                    // Store payment reference in session
                    $_SESSION['ad_payment_reference'] = $reference;
                    $_SESSION['ad_payment_verified'] = true;
                    $_SESSION['ad_payment_amount'] = $verification['data']['amount'] ?? 25000;
                    $_SESSION['ad_payment_timestamp'] = time();
                    
                    error_log("AdvertisementController::paymentCallback - Payment verified successfully for supplier: " . $supplierId);
                    $this->setFlash('success', 'Payment successful! You can now create your advertisement.');
                    $this->redirect('/supplier/advertisements/create?payment=success&ref=' . urlencode($reference));
                    return;
                } else {
                    error_log("AdvertisementController::paymentCallback - Invalid supplier ID extracted from reference");
                    $this->setFlash('error', 'Invalid payment reference. Could not extract supplier information.');
                    $this->redirect('/supplier/advertisements/create');
                    return;
                }
            } else {
                // Log the actual verification response for debugging
                $verificationStatus = $verification['status'] ?? 'unknown';
                $dataStatus = $verification['data']['status'] ?? 'unknown';
                error_log("AdvertisementController::paymentCallback - Verification failed. Status: {$verificationStatus}, Data status: {$dataStatus}");
                error_log("AdvertisementController::paymentCallback - Full response: " . json_encode($verification));
                
                $this->setFlash('error', 'Payment verification failed. Status: ' . ($dataStatus !== 'unknown' ? $dataStatus : 'verification failed') . '. Please try again or contact support.');
                $this->redirect('/supplier/advertisements/create');
            }
        } catch (\Exception $e) {
            error_log("AdvertisementController::paymentCallback - Exception: " . $e->getMessage());
            error_log("AdvertisementController::paymentCallback - Stack trace: " . $e->getTraceAsString());
            $this->setFlash('error', 'Payment verification error: ' . $e->getMessage() . '. Please contact support.');
            $this->redirect('/supplier/advertisements/create');
        }
    }
}

