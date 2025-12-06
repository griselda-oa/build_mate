<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Auth;
use App\User;
use App\Validator;
use App\Security;

/**
 * Authentication controller
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
            return;
        }
        
        // Render login page using view system
        echo $this->view->render('Auth/login', [
            'flash' => $this->getFlash()
        ], 'auth');
    }
    
    /**
     * Process login
     */
    public function login(): void
    {
        $email = Validator::normalizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            error_log("LOGIN - Empty email or password");
            $this->setFlash('error', 'Email and password are required');
            $this->redirect('/login');
            return;
        }
        
        $userModel = new User();

        // Fetch user record so we can check role before attempting a login
        $maybeUser = $userModel->findByEmail($email);

        // Allow admin login - removed restrictive check
        // Admin users can log in if they have the correct credentials

        // Centralized attempt: verifies credentials and performs login on success
        $attempted = Auth::attempt($email, $password);

        if (!$attempted) {
            error_log("LOGIN - Invalid credentials");
            $this->setFlash('error', 'Invalid credentials');
            $this->redirect('/login');
            return;
        }

        // Grab the canonical session user
        $user = Auth::user();
        error_log("LOGIN - User logged in, session: " . print_r($_SESSION['user'], true));
        
        // Verify session is set correctly
        if (!isset($_SESSION['user']) || $_SESSION['user']['id'] !== $user['id']) {
            error_log("LOGIN ERROR - Session not set correctly after login!");
            $this->setFlash('error', 'Login failed - please try again');
            $this->redirect('/login');
            return;
        }
        
        // Log login (don't let this block redirect if it fails)
        try {
            Security::log('login_success', $user['id']);
        } catch (\Exception $e) {
            error_log("LOGIN - Security log failed: " . $e->getMessage());
        }
        
        // Determine redirect based on user role BEFORE cleaning buffer
        $redirect = $_SESSION['redirect_after_login'] ?? null;
        
        if (!$redirect) {
            // Redirect to role-specific dashboard
            switch ($user['role']) {
                case 'supplier':
                    // Check if supplier has filled KYC form
                    $supplierModel = new \App\Supplier();
                    $supplier = $supplierModel->findByUserId($user['id']);
                    
                    // Debug logging
                    error_log("Supplier login - User ID: " . $user['id']);
                    error_log("Supplier found: " . ($supplier ? 'YES' : 'NO'));
                    if ($supplier) {
                        error_log("KYC Status: " . ($supplier['kyc_status'] ?? 'NULL'));
                        error_log("Business Name: " . ($supplier['business_name'] ?? 'NULL'));
                    }
                    
                    if (!$supplier) {
                        // No supplier profile - redirect to KYC form
                        error_log("Redirecting to KYC - No supplier profile");
                        $redirect = '/supplier/kyc';
                    } else {
                        // Check KYC status
                        $kycStatus = $supplier['kyc_status'] ?? NULL;
                        
                        if ($kycStatus === 'approved') {
                            // Approved - go to dashboard
                            error_log("Redirecting to dashboard - Approved");
                            $redirect = '/supplier/dashboard';
                        } elseif ($kycStatus === 'pending') {
                            // KYC submitted but not approved - show pending page
                            error_log("Redirecting to pending - Pending approval");
                            $redirect = '/supplier/pending';
                        } elseif ($kycStatus === 'rejected') {
                            // Rejected - allow to resubmit KYC
                            error_log("Redirecting to KYC - Rejected");
                            $redirect = '/supplier/kyc';
                        } else {
                            // NULL or no KYC submitted yet - redirect to KYC form
                            error_log("Redirecting to KYC - Status is: " . var_export($kycStatus, true));
                            $redirect = '/supplier/kyc';
                        }
                    }
                    break;
                case 'logistics':
                    $redirect = '/logistics/dashboard';
                    break;
                case 'admin':
                    $redirect = '/admin/dashboard';
                    break;
                default:
                    $redirect = '/dashboard'; // Buyer goes to dashboard
                    break;
            }
        }
        
        unset($_SESSION['redirect_after_login']);
        
        // Verify session one more time before redirect
        if (!isset($_SESSION['user']) || $_SESSION['user']['id'] !== $user['id']) {
            error_log("LOGIN ERROR - Session lost before redirect! User ID: " . $user['id']);
            $this->setFlash('error', 'Session error - please try again');
            $this->redirect('/login');
            return;
        }
        
        error_log("LOGIN - Redirecting to: $redirect (role: " . $user['role'] . ", session user: " . ($user['email'] ?? 'NOT SET') . ", session_id: " . session_id() . ")");
        
        // Verify session one final time
        if (!isset($_SESSION['user']) || $_SESSION['user']['id'] !== $user['id']) {
            error_log("LOGIN CRITICAL ERROR - Session lost right before redirect!");
            $this->setFlash('error', 'Session error - please try again');
            $this->redirect('/login');
            return;
        }
        
        // Clean output buffer - session is already written by Auth::login()
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Use redirect method which handles base path automatically
        $this->redirect($redirect);
        return;
    }
    
    /**
     * Show register form
     */
    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
            return;
        }
        
        // Render register page using view system
        echo $this->view->render('Auth/register', [
            'flash' => $this->getFlash()
        ], 'auth');
    }
    
    /**
     * Process registration
     */
    public function register(): void
    {
        $name = Validator::sanitize($_POST['name'] ?? '', 255);
        $email = Validator::normalizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'buyer';
        
        // Validate
        $errors = [];
        
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters';
        }
        
        if (!Validator::email($email)) {
            $errors[] = 'Invalid email address';
        }
        
        if (!Validator::password($password)) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if (!in_array($role, ['buyer', 'supplier', 'logistics'])) {
            $errors[] = 'Invalid role selected';
        }
        
        if (!empty($errors)) {
            $this->setFlash('error', implode(', ', $errors));
            $this->redirect('/register');
        }
        
        $userModel = new User();
        
        // Check if email exists
        if ($userModel->findByEmail($email)) {
            $this->setFlash('error', 'Email already registered');
            $this->redirect('/register');
        }
        
        // Create user
        $userId = $userModel->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
        
        // Create supplier record if role is supplier
        // Don't set kyc_status to 'pending' yet - they need to fill the KYC form first
        if ($role === 'supplier') {
            $supplierModel = new \App\Supplier();
            $supplierModel->create([
                'user_id' => $userId,
                'business_name' => $name,
                'kyc_status' => NULL  // No KYC submitted yet
            ]);
        }
        
        Security::log('user_registered', $userId, ['role' => $role]);
        
        $this->setFlash('success', 'Registration successful. Please login.');
        $this->redirect('/login');
    }
    
    /**
     * Logout
     */
    public function logout(): void
    {
        $userId = Auth::user()['id'] ?? null;
        Security::log('logout', $userId);
        Auth::logout();
        $this->redirect('/');
    }
}
