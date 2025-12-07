<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Cart;
use App\Response;

/**
 * Cart controller
 */
class CartController extends Controller
{
    /**
     * Show cart
     */
    public function index(): void
    {
        // Prevent suppliers and admins from accessing cart (only buyers can purchase)
        $user = $this->user();
        if ($user) {
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
        }
        
        $cart = $_SESSION['cart'] ?? [];
        
        try {
            $products = Cart::getItemsWithProducts($cart);
            $total = Cart::calculateTotal($cart);
        } catch (\Exception $e) {
            // Database connection failed - show cart without product details
            $products = [];
            $total = 0;
        }
        
        echo $this->view->render('Cart/index', [
            'products' => $products,
            'total' => $total
        ]);
    }
    
    /**
     * Add to cart
     */
    public function add(int $id): void
    {
        // Prevent suppliers and admins from adding to cart (only buyers can purchase)
        $user = $this->user();
        if ($user) {
            if ($user['role'] === 'supplier') {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $isJson = !empty($_SERVER['CONTENT_TYPE']) && 
                      strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
            
            if ($isAjax || $isJson) {
                Response::json([
                    'success' => false, 
                    'message' => 'Suppliers cannot purchase products. Please create a buyer account to make purchases.'
                ], 403);
                return;
            }
            
            $this->setFlash('error', 'Suppliers cannot purchase products. Please create a buyer account to make purchases.');
            $this->redirect('/catalog');
            return;
            }
            if ($user['role'] === 'admin') {
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                $isJson = !empty($_SERVER['CONTENT_TYPE']) && 
                          strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
                
                if ($isAjax || $isJson) {
                    Response::json([
                        'success' => false, 
                        'message' => 'Admins cannot purchase products. Please use a buyer account to make purchases.'
                    ], 403);
                    return;
                }
                
                $this->setFlash('error', 'Admins cannot purchase products. Please use a buyer account to make purchases.');
                $this->redirect('/catalog');
                return;
            }
        }
        
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isJson = !empty($_SERVER['CONTENT_TYPE']) && 
                  strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        
        try {
            $productModel = new \App\Product();
            $product = $productModel->find($id);
            
            if (!$product) {
                if ($isAjax || $isJson) {
                    Response::json(['success' => false, 'message' => 'Product not found'], 404);
                    return;
                }
                $this->setFlash('error', 'Product not found');
                $this->redirect('/cart');
                return;
            }
        } catch (\Exception $e) {
            if ($isAjax || $isJson) {
                Response::json(['success' => false, 'message' => 'Database connection failed'], 500);
                return;
            }
            $this->setFlash('error', 'Database connection failed. Please set up your database first.');
            $this->redirect('/catalog');
            return;
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Get quantity from POST or JSON
        $qty = 1;
        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);
            $qty = (int)($input['qty'] ?? 1);
        } else {
            $qty = (int)($_POST['qty'] ?? 1);
        }
        
        Cart::addItem($_SESSION['cart'], $id, $qty);
        
        if ($isAjax || $isJson) {
            Response::json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => count($_SESSION['cart'])
            ]);
            return;
        }
        
        $this->setFlash('success', 'Product added to cart');
        $this->redirect('/cart');
    }
    
    /**
     * Update cart item
     */
    public function update(): void
    {
        // Prevent suppliers and admins from updating cart (only buyers can purchase)
        $user = $this->user();
        if ($user) {
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
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 0);
        
        if ($qty <= 0) {
            $this->remove($productId);
            return;
        }
        
        if (isset($_SESSION['cart'])) {
            Cart::updateItem($_SESSION['cart'], $productId, $qty);
        }
        
        $this->redirect('/cart');
    }
    
    /**
     * Remove from cart
     */
    public function remove(int $id): void
    {
        // Prevent suppliers and admins from removing from cart (only buyers can purchase)
        $user = $this->user();
        if ($user) {
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
        }
        
        if (isset($_SESSION['cart'])) {
            Cart::removeItem($_SESSION['cart'], $id);
        }
        
        $this->redirect('/cart');
    }
}

