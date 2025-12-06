<?php

declare(strict_types=1);

namespace App;

use App\View;

/**
 * Middleware to check supplier status and block access if pending/rejected
 */
class SupplierStatusMiddleware
{
    /**
     * Check if supplier is approved, redirect if not
     * Returns false to stop execution if redirecting
     */
    public function handle(): bool
    {
        // Get user from session
        if (!isset($_SESSION['user'])) {
            header('Location: ' . View::url('/login'));
            exit;
        }
        
        $user = $_SESSION['user'];
        
        // Only check for supplier role
        if ($user['role'] !== 'supplier') {
            return true; // Not a supplier, let other middleware handle
        }
        
        $supplierModel = new Supplier();
        $supplier = $supplierModel->findByUserId($user['id']);
        
        // Debug logging
        error_log("SupplierStatusMiddleware - User ID: " . $user['id']);
        error_log("SupplierStatusMiddleware - Supplier found: " . ($supplier ? 'YES' : 'NO'));
        if ($supplier) {
            error_log("SupplierStatusMiddleware - KYC Status: " . var_export($supplier['kyc_status'], true));
            error_log("SupplierStatusMiddleware - Business Name: " . ($supplier['business_name'] ?? 'NULL'));
        }
        error_log("SupplierStatusMiddleware - Current Path: " . ($_SERVER['REQUEST_URI'] ?? 'UNKNOWN'));
        
        if (!$supplier) {
            // No supplier profile yet, allow access to KYC/apply pages
            $currentPath = $_SERVER['REQUEST_URI'] ?? '';
            $allowedPaths = ['/supplier/apply', '/supplier/kyc', '/supplier/pending'];
            
            $isAllowed = false;
            foreach ($allowedPaths as $path) {
                if (strpos($currentPath, $path) !== false) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                header('Location: ' . View::url('/supplier/kyc'));
                exit;
            }
            return true;
        }
        
        // Check KYC status
        $kycStatus = $supplier['kyc_status'] ?? 'pending';
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        
        error_log("SupplierStatusMiddleware - KYC Status type: " . gettype($kycStatus));
        error_log("SupplierStatusMiddleware - KYC Status value: '" . $kycStatus . "'");
        error_log("SupplierStatusMiddleware - Comparison result (approved): " . var_export($kycStatus === 'approved', true));
        
        // Always allow access to pending dashboard, KYC, and logout
        $allowedPaths = ['/supplier/pending', '/supplier/kyc', '/logout', '/supplier/apply'];
        $isAllowed = false;
        foreach ($allowedPaths as $path) {
            if (strpos($currentPath, $path) !== false) {
                $isAllowed = true;
                break;
            }
        }
        
        error_log("SupplierStatusMiddleware - Is allowed path: " . var_export($isAllowed, true));
        
        // If approved, allow access (check this FIRST before other conditions)
        if ($kycStatus === 'approved') {
            error_log("SupplierStatusMiddleware - Status is approved, allowing access");
            return true; // All good, continue
        }
        
        // If pending, redirect to pending dashboard (except for allowed paths)
        if ($kycStatus === 'pending' && !$isAllowed) {
            error_log("SupplierStatusMiddleware - Status is pending, redirecting");
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Your supplier account is pending approval. Please wait for admin review.'];
            header('Location: ' . View::url('/supplier/pending'));
            exit;
        }
        
        // If rejected, redirect to KYC page
        if ($kycStatus === 'rejected' && !$isAllowed) {
            error_log("SupplierStatusMiddleware - Status is rejected, redirecting");
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Your supplier application was rejected. Please contact support.'];
            header('Location: ' . View::url('/supplier/kyc'));
            exit;
        }
        
        // If status is NULL or anything else, redirect to KYC
        if (!$isAllowed) {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Please complete your KYC application.'];
            header('Location: ' . View::url('/supplier/kyc'));
            exit;
        }
        
        return true; // Allow access to allowed paths
    }
}
