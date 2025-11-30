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
        
        // Always allow access to pending dashboard, KYC, and logout
        $allowedPaths = ['/supplier/pending', '/supplier/kyc', '/logout', '/supplier/apply'];
        $isAllowed = false;
        foreach ($allowedPaths as $path) {
            if (strpos($currentPath, $path) !== false) {
                $isAllowed = true;
                break;
            }
        }
        
        // If pending, redirect to pending dashboard (except for allowed paths)
        if ($kycStatus === 'pending' && !$isAllowed) {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'Your supplier account is pending approval. Please wait for admin review.'];
            header('Location: ' . View::url('/supplier/pending'));
            exit;
        }
        
        // If rejected, redirect to KYC page
        if ($kycStatus === 'rejected' && !$isAllowed) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Your supplier application was rejected. Please contact support.'];
            header('Location: ' . View::url('/supplier/kyc'));
            exit;
        }
        
        // If approved, allow access
        if ($kycStatus === 'approved') {
            return true; // All good, continue
        }
        
        return false; // Should not reach here, but just in case
    }
}

