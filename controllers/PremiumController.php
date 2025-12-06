<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Supplier;
use App\Advertisement;
use App\PaystackService;
use App\EmailService;

/**
 * Premium subscription controller
 */
class PremiumController extends Controller
{
    /**
     * Show upgrade page
     */
    public function upgrade(): void
    {
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->setFlash('error', 'Only suppliers can upgrade to premium');
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
        
        $isPremium = $supplierModel->isPremium($supplier['id']);
        $expiresAt = $supplier['premium_expires_at'] ?? null;
        
        echo $this->view->render('Supplier/premium-upgrade', [
            'supplier' => $supplier,
            'isPremium' => $isPremium,
            'expiresAt' => $expiresAt
        ]);
    }
    
    /**
     * Initialize premium payment
     */
    public function initializePayment(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('/supplier/premium/upgrade');
            return;
        }
        
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->json(['success' => false, 'message' => 'Only suppliers can upgrade'], 403);
            return;
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->json(['success' => false, 'message' => 'Supplier profile not found'], 404);
            return;
        }
        
        // Premium plan: 30 days for 250 GHS (25,000 cents)
        $amountCents = 25000; // 250 GHS
        $currency = 'GHS';
        $days = 30;
        
        $paystackService = new PaystackService();
        $reference = 'PREMIUM-' . $supplier['id'] . '-' . time();
        
        $config = require __DIR__ . '/../settings/config.php';
        $appUrl = rtrim($config['app_url'] ?? 'http://localhost/build_mate', '/');
        
        // Prepare payment data for Paystack
        $paymentData = [
            'email' => $user['email'],
            'amount' => $amountCents, // Amount in kobo (cents)
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => $appUrl . '/supplier/premium/callback',
            'metadata' => [
                'supplier_id' => $supplier['id'],
                'plan_type' => 'premium',
                'days' => $days,
                'type' => 'premium_upgrade'
            ]
        ];
        
        try {
            $response = $paystackService->initializeTransaction($paymentData);
            
            if ($response['status']) {
                // Store pending subscription
                $db = \App\DB::getInstance();
                $stmt = $db->prepare("
                    INSERT INTO premium_subscriptions 
                    (supplier_id, payment_reference, amount_cents, currency, plan_duration_days, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([
                    $supplier['id'],
                    $reference,
                    $amountCents,
                    $currency,
                    $days
                ]);
                
                $this->json([
                    'success' => true,
                    'authorization_url' => $response['data']['authorization_url']
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Payment initialization failed'], 400);
            }
        } catch (\Exception $e) {
            error_log("Premium payment error: " . $e->getMessage());
            error_log("Premium payment stack trace: " . $e->getTraceAsString());
            $this->json([
                'success' => false, 
                'message' => 'Payment initialization failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle premium payment callback
     */
    public function paymentCallback(): void
    {
        $reference = $_GET['reference'] ?? '';
        
        if (empty($reference) || !str_starts_with($reference, 'PREMIUM-')) {
            $this->setFlash('error', 'Invalid payment reference');
            $this->redirect('/supplier/premium/upgrade');
            return;
        }
        
        $paystackService = new PaystackService();
        
        try {
            $verification = $paystackService->verifyTransaction($reference);
            
            if ($verification['status'] && $verification['data']['status'] === 'success') {
                // Extract supplier ID from reference
                $parts = explode('-', $reference);
                $supplierId = (int)($parts[1] ?? 0);
                
                if ($supplierId > 0) {
                    $supplierModel = new Supplier();
                    $metadata = $verification['data']['metadata'] ?? [];
                    $days = (int)($metadata['days'] ?? 30);
                    
                    // Upgrade supplier
                    $supplierModel->upgradeToPremium($supplierId, $days);
                    
                    // Update subscription record
                    $db = \App\DB::getInstance();
                    $stmt = $db->prepare("
                        UPDATE premium_subscriptions
                        SET status = 'completed',
                            started_at = NOW(),
                            expires_at = DATE_ADD(NOW(), INTERVAL ? DAY)
                        WHERE payment_reference = ?
                    ");
                    $stmt->execute([$days, $reference]);
                    
                    // Send confirmation email
                    $userModel = new User();
                    $supplier = $supplierModel->find($supplierId);
                    if ($supplier) {
                        $user = $userModel->find($supplier['user_id']);
                        if ($user) {
                            $emailService = new EmailService();
                            $emailService->send($user['email'], 
                                'Premium Subscription Activated',
                                "Your premium subscription has been activated for {$days} days. Thank you for upgrading!"
                            );
                        }
                    }
                    
                    $this->setFlash('success', 'Premium subscription activated successfully!');
                    $this->redirect('/supplier/dashboard');
                    return;
                }
            }
            
            $this->setFlash('error', 'Payment verification failed');
        } catch (\Exception $e) {
            error_log("Premium callback error: " . $e->getMessage());
            $this->setFlash('error', 'Payment processing error');
        }
        
        $this->redirect('/supplier/premium/upgrade');
    }
    
    /**
     * Get premium status (AJAX)
     */
    public function status(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        $user = $this->user();
        if (!$user || $user['role'] !== 'supplier') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        if (!$supplier) {
            $this->json(['success' => false, 'message' => 'Supplier not found'], 404);
            return;
        }
        
        $isPremium = $supplierModel->isPremium($supplier['id']);
        
        $this->json([
            'success' => true,
            'isPremium' => $isPremium,
            'planType' => $supplier['plan_type'] ?? 'freemium',
            'expiresAt' => $supplier['premium_expires_at'] ?? null,
            'sentimentScore' => (float)($supplier['sentiment_score'] ?? 1.0),
            'performanceWarnings' => (int)($supplier['performance_warnings'] ?? 0)
        ]);
    }
}

