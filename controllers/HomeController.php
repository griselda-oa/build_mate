<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Product;
use App\Category;

/**
 * Home controller
 */
class HomeController extends Controller
{
    /**
     * Home page
     */
    public function index(): void
    {
        try {
            $productModel = new Product();
            $categoryModel = new Category();
            
            $featured = $productModel->getFeatured(8);
            $categories = $categoryModel->findAll('name ASC');
        } catch (\Exception $e) {
            // Database connection failed - show homepage without data
            $featured = [];
            $categories = [];
        }
        
        echo $this->view->render('Home/index-modern', [
            'featured' => $featured,
            'categories' => $categories,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Buyer dashboard with product catalog, search, and filters
     */
    public function dashboard(): void
    {
        $user = $this->user();
        $recentOrders = [];
        
        // Redirect suppliers to their supplier dashboard
        if ($user && $user['role'] === 'supplier') {
            $this->redirect('/supplier/dashboard');
            return;
        }
        
        // Get recent orders for logged-in buyers
        if ($user && $user['role'] === 'buyer') {
            try {
                $orderModel = new \App\Order();
                $recentOrders = $orderModel->getByBuyer($user['id'], 5);
            } catch (\Exception $e) {
                error_log("Error fetching orders for dashboard: " . $e->getMessage());
            }
        }
        
        // Get active advertisements for banner
        $advertisements = [];
        try {
            $adModel = new \App\Advertisement();
            $advertisements = $adModel->getActive();
            // Limit to 5 for banner
            $advertisements = array_slice($advertisements, 0, 5);
        } catch (\Exception $e) {
            error_log("Error fetching advertisements for dashboard: " . $e->getMessage());
        }
        
        try {
            $productModel = new Product();
            $categoryModel = new Category();
            
            $query = trim($_GET['q'] ?? '');
            $categoryId = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
            
            // Price filters
            $minPriceInput = !empty($_GET['min']) ? (float)$_GET['min'] : null;
            $maxPriceInput = !empty($_GET['max']) ? (float)$_GET['max'] : null;
            
            // Get price range first (no supplier filter - buyers see all products)
            $priceRange = $productModel->getPriceRange($categoryId, null);
            
            // Only filter by price if user explicitly changed from defaults
            $minPrice = null;
            $maxPrice = null;
            if ($minPriceInput !== null && $minPriceInput > ($priceRange['min'] / 100)) {
                $minPrice = (int)($minPriceInput * 100);
            }
            if ($maxPriceInput !== null && $maxPriceInput < ($priceRange['max'] / 100)) {
                $maxPrice = (int)($maxPriceInput * 100);
            }
            
            $verifiedOnly = isset($_GET['verified']);
            
            // Buyers see all products (no supplier filter)
            $products = $productModel->search($query, $categoryId, $minPrice, $maxPrice, $verifiedOnly, null);
            $categories = $categoryModel->findAll('name ASC');
        } catch (\Exception $e) {
            // Database connection failed - show dashboard without data
            $products = [];
            $categories = [];
            $priceRange = ['min' => 0, 'max' => 100000];
            $query = $_GET['q'] ?? '';
            $categoryId = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
            $minPrice = !empty($_GET['min']) ? (int)($_GET['min'] * 100) : null;
            $maxPrice = !empty($_GET['max']) ? (int)($_GET['max'] * 100) : null;
            $verifiedOnly = isset($_GET['verified']);
        }
        
        echo $this->view->render('Home/dashboard', [
            'products' => $products,
            'categories' => $categories,
            'price_range' => $priceRange,
            'recentOrders' => $recentOrders,
            'advertisements' => $advertisements,
            'filters' => [
                'query' => $query,
                'category_id' => $categoryId,
                'min_price' => $minPrice ? $minPrice / 100 : ($priceRange['min'] / 100),
                'max_price' => $maxPrice ? $maxPrice / 100 : ($priceRange['max'] / 100),
                'verified_only' => $verifiedOnly
            ],
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Contact page with live chat
     */
    public function contact(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Basic validation
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                $this->setFlash('error', 'Please fill in all fields.');
                echo $this->view->render('Home/contact', [
                    'title' => 'Contact Us',
                    'flash' => $this->getFlash()
                ]);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid email address.');
                echo $this->view->render('Home/contact', [
                    'title' => 'Contact Us',
                    'flash' => $this->getFlash()
                ]);
                return;
            }
            
            // In a real application, you would:
            // 1. Save to database
            // 2. Send email notification
            // 3. Send auto-reply to user
            
            // For now, just show success message
            $this->setFlash('success', 'Thank you for your message! We\'ll get back to you soon at ' . htmlspecialchars($email) . '.');
        }
        
        echo $this->view->render('Home/contact', [
            'title' => 'Contact Us',
            'flash' => $this->getFlash()
        ]);
    }
}

