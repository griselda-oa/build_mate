<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Product;
use App\Category;
use App\Review;
use App\Waitlist;
use App\Wishlist;
use App\Order;
use App\OrderItem;
use App\Auth;
use App\Response;

/**
 * Product controller
 */
class ProductController extends Controller
{
    /**
     * Catalog page
     * For buyers/guests: Shows all products from all approved suppliers
     * For suppliers: Shows only their own products (My Products view)
     */
    public function catalog(): void
    {
        $user = $this->user();
        $supplierId = null;
        $isSupplierView = false;
        
        // If user is a supplier, filter to show only their products
        if ($user && $user['role'] === 'supplier') {
            try {
                $supplierModel = new \App\Supplier();
                $supplier = $supplierModel->findByUserId($user['id']);
                if ($supplier) {
                    $supplierId = (int)$supplier['id'];
                    $isSupplierView = true;
                }
            } catch (\Exception $e) {
                error_log("Error fetching supplier: " . $e->getMessage());
            }
        }
        
        try {
            $productModel = new Product();
            $categoryModel = new Category();
            
            // Use premium ranking for product listing
            
            $query = trim($_GET['q'] ?? '');
            $categoryId = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
            
            // Only apply price filters if they're actually set (not default slider values)
            $minPriceInput = !empty($_GET['min']) ? (float)$_GET['min'] : null;
            $maxPriceInput = !empty($_GET['max']) ? (float)$_GET['max'] : null;
            
            // Get price range first (filtered by supplier if supplier view)
            $priceRange = $productModel->getPriceRange($categoryId, $supplierId);
            
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
            
            // Get sponsored products (advertisements) for display at top
            $sponsoredProducts = [];
            if (!$isSupplierView) {
                try {
                    $sponsoredProducts = $productModel->getSponsoredProducts(6);
                } catch (\Exception $e) {
                    error_log("Error fetching sponsored products: " . $e->getMessage());
                }
            }
            
            // Use premium ranking for public catalog, regular search for supplier view
            if ($isSupplierView) {
                $products = $productModel->search($query, $categoryId, $minPrice, $maxPrice, $verifiedOnly, $supplierId);
            } else {
                // Use premium ranking: Active Ads > Premium > High Sentiment > Freemium > Newest
                // Exclude advertised products from regular list (they're shown in sponsored section)
                try {
                    $allProducts = $productModel->getRanked($query, $categoryId, $minPrice, $maxPrice, $verifiedOnly);
                    $sponsoredProductIds = array_column($sponsoredProducts, 'id');
                    $products = array_filter($allProducts, function($product) use ($sponsoredProductIds) {
                        return !in_array($product['id'], $sponsoredProductIds);
                    });
                    $products = array_values($products); // Re-index array
                } catch (\Exception $e) {
                    // Fallback to regular search if getRanked fails (e.g., if premium tables don't exist)
                    error_log("getRanked failed, using search fallback: " . $e->getMessage());
                    $products = $productModel->search($query, $categoryId, $minPrice, $maxPrice, $verifiedOnly);
                }
            }
            $categories = $categoryModel->findAll('name ASC');
        } catch (\Exception $e) {
            // Database connection failed - show catalog without data
            $products = [];
            $categories = [];
            $priceRange = ['min' => 0, 'max' => 100000];
            $query = $_GET['q'] ?? '';
            $categoryId = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
            $minPrice = !empty($_GET['min']) ? (int)($_GET['min'] * 100) : null;
            $maxPrice = !empty($_GET['max']) ? (int)($_GET['max'] * 100) : null;
            $verifiedOnly = isset($_GET['verified']);
        }
        
        echo $this->view->render('Product/catalog', [
            'products' => $products,
            'sponsored_products' => $sponsoredProducts ?? [],
            'sponsoredProducts' => $sponsoredProducts ?? [], // Also pass with camelCase for compatibility
            'categories' => $categories,
            'price_range' => $priceRange,
            'is_supplier_view' => $isSupplierView,
            'filters' => [
                'query' => $query,
                'category_id' => $categoryId,
                'min_price' => $minPrice ? $minPrice / 100 : ($priceRange['min'] / 100),
                'max_price' => $maxPrice ? $maxPrice / 100 : ($priceRange['max'] / 100),
                'verified_only' => $verifiedOnly
            ]
        ]);
    }
    
    /**
     * Product detail page
     */
    public function show(string $slug): void
    {
        $productModel = new Product();
        $product = $productModel->findBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            echo $this->view->render('Errors/404', [], 'main');
            return;
        }
        
        $reviewModel = new Review();
        $waitlistModel = new Waitlist();
        $wishlistModel = new Wishlist();
        
        // Get reviews and stats
        $reviews = $reviewModel->getByProduct($product['id']);
        $reviewStats = $reviewModel->getProductStats($product['id']);
        
        // Check if user can review
        $canReview = false;
        $isInWaitlist = false;
        $isInWishlist = false;
        $hasPurchased = false;
        
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user['id'];
            $supplierId = $product['supplier_id'] ?? 0;
            
            // Check if user has purchased from this supplier (not just the product)
            // This ensures only buyers who purchased from the supplier can review
            $hasPurchasedFromSupplier = $supplierId > 0 ? $reviewModel->hasPurchasedFromSupplier($userId, $supplierId) : false;
            
            // Also check if user has purchased this specific product (for product-specific reviews)
            $hasPurchasedProduct = $reviewModel->hasPurchasedProduct($userId, $product['id']);
            
            // User can review if they purchased from the supplier OR purchased this specific product
            $hasPurchased = $hasPurchasedFromSupplier || $hasPurchasedProduct;
            
            // Check if user can review (must have purchased AND not already reviewed)
            $canReview = $hasPurchased && !$reviewModel->hasReviewedProduct($userId, $product['id']);
            
            // Check if user is in waitlist
            $isInWaitlist = $waitlistModel->isInWaitlist($userId, $product['id']);
            
            // Check if user is in wishlist
            $isInWishlist = $wishlistModel->isInWishlist($userId, $product['id']);
        }
        
        echo $this->view->render('Product/show', [
            'product' => $product,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'canReview' => $canReview,
            'hasPurchased' => $hasPurchased,
            'isInWaitlist' => $isInWaitlist,
            'isInWishlist' => $isInWishlist,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Submit review
     */
    public function submitReview(): void
    {
        if (!Auth::check()) {
            $this->setFlash('error', 'Please login to submit a review');
            $this->redirect('/login');
            return;
        }
        
        $user = Auth::user();
        $userId = $user['id'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');
        
        if ($productId <= 0 || $rating < 1 || $rating > 5) {
            $this->setFlash('error', 'Invalid review data');
            $this->redirectBack();
            return;
        }
        
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product) {
            $this->setFlash('error', 'Product not found');
            $this->redirectBack();
            return;
        }
        
        $reviewModel = new Review();
        $supplierId = $product['supplier_id'] ?? 0;
        
        // Verify user has purchased from this supplier (not just the product)
        // This ensures only buyers who purchased from the supplier can review
        $hasPurchasedFromSupplier = $supplierId > 0 ? $reviewModel->hasPurchasedFromSupplier($userId, $supplierId) : false;
        $hasPurchasedProduct = $reviewModel->hasPurchasedProduct($userId, $productId);
        
        if (!$hasPurchasedFromSupplier && !$hasPurchasedProduct) {
            $this->setFlash('error', 'You can only review products from suppliers you have purchased from');
            $this->redirectBack();
            return;
        }
        
        // Check if already reviewed
        if ($reviewModel->hasReviewedProduct($userId, $productId)) {
            $this->setFlash('error', 'You have already reviewed this product');
            $this->redirectBack();
            return;
        }
        
        // Get order ID for this purchase (prefer product-specific, fallback to supplier)
        $orderId = $reviewModel->getOrderIdForProduct($userId, $productId);
        if (!$orderId && $supplierId > 0) {
            $orderId = $reviewModel->getOrderIdForSupplier($userId, $supplierId);
        }
        
        if (!$orderId) {
            $this->setFlash('error', 'Could not find your purchase record');
            $this->redirectBack();
            return;
        }
        
        // Ensure supplier ID is set
        if (!$supplierId) {
            $supplierId = $reviewModel->getSupplierIdFromProduct($productId);
        }
        
        if (!$supplierId) {
            $this->setFlash('error', 'Could not determine supplier');
            $this->redirectBack();
            return;
        }
        
        // Analyze sentiment using AI
        $sentimentLabel = 'neutral';
        $sentimentScore = 0.500;
        
        try {
            $openAIService = new \App\OpenAIService();
            $sentimentResult = $openAIService->analyzeSentiment($reviewText, $rating);
            $sentimentLabel = $sentimentResult['label'];
            $sentimentScore = $sentimentResult['score'];
        } catch (\Exception $e) {
            // If AI analysis fails, infer from rating
            error_log("Sentiment analysis failed for review: " . $e->getMessage());
            if ($rating >= 4) {
                $sentimentLabel = 'positive';
                $sentimentScore = 0.750;
            } elseif ($rating == 3) {
                $sentimentLabel = 'neutral';
                $sentimentScore = 0.500;
            } else {
                $sentimentLabel = 'negative';
                $sentimentScore = 0.250;
            }
        }
        
        // Create review with sentiment analysis
        $reviewModel->create([
            'order_id' => $orderId,
            'buyer_id' => $userId,
            'supplier_id' => $supplierId,
            'product_id' => $productId,
            'rating' => $rating,
            'review_text' => $reviewText ?: null,
            'sentiment_label' => $sentimentLabel,
            'sentiment_score' => $sentimentScore,
            'is_verified_purchase' => 1
        ]);
        
        $this->setFlash('success', 'Thank you for your review!');
        $this->redirectBack();
    }
    
    /**
     * Add to waitlist
     */
    public function addToWaitlist(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Please login to join waitlist'], 401);
            return;
        }
        
        $user = Auth::user();
        $userId = $user['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid product'], 400);
            return;
        }
        
        $waitlistModel = new Waitlist();
        
        if ($waitlistModel->addToWaitlist($userId, $productId)) {
            Response::json(['success' => true, 'message' => 'Added to waitlist. We\'ll notify you when this product is back in stock!']);
        } else {
            Response::json(['success' => false, 'message' => 'You are already on the waitlist for this product'], 400);
        }
    }
    
    /**
     * Remove from waitlist
     */
    public function removeFromWaitlist(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Please login'], 401);
            return;
        }
        
        $user = Auth::user();
        $userId = $user['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid product'], 400);
            return;
        }
        
        $waitlistModel = new Waitlist();
        
        if ($waitlistModel->removeFromWaitlist($userId, $productId)) {
            Response::json(['success' => true, 'message' => 'Removed from waitlist']);
        } else {
            Response::json(['success' => false, 'message' => 'Not in waitlist'], 400);
        }
    }
    
    /**
     * Add to wishlist
     */
    public function addToWishlist(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Please login to add to wishlist'], 401);
            return;
        }
        
        $user = Auth::user();
        $userId = $user['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid product'], 400);
            return;
        }
        
        $wishlistModel = new Wishlist();
        
        // Check if table exists first
        try {
            $db = \App\DB::getInstance();
            $stmt = $db->query("SHOW TABLES LIKE 'wishlist'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                Response::json([
                    'success' => false, 
                    'message' => 'Wishlist table does not exist. Please run the migration: <a href="/run_wishlist_migration_web.php" target="_blank">Run Migration</a>'
                ], 500);
                return;
            }
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
            return;
        }
        
        if ($wishlistModel->addToWishlist($userId, $productId)) {
            Response::json(['success' => true, 'message' => 'Added to wishlist!']);
        } else {
            Response::json(['success' => false, 'message' => 'Already in wishlist'], 400);
        }
    }
    
    /**
     * Remove from wishlist
     */
    public function removeFromWishlist(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Please login'], 401);
            return;
        }
        
        $user = Auth::user();
        $userId = $user['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid product'], 400);
            return;
        }
        
        $wishlistModel = new Wishlist();
        
        if ($wishlistModel->removeFromWishlist($userId, $productId)) {
            Response::json(['success' => true, 'message' => 'Removed from wishlist']);
        } else {
            Response::json(['success' => false, 'message' => 'Not in wishlist'], 400);
        }
    }
    
    /**
     * Show wishlist page
     */
    public function wishlist(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }
        
        $user = Auth::user();
        $wishlistModel = new Wishlist();
        
        // Check if table exists
        try {
            $db = \App\DB::getInstance();
            $stmt = $db->query("SHOW TABLES LIKE 'wishlist'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                $this->setFlash('warning', 'Wishlist table does not exist. Please run the migration: <a href="/run_wishlist_migration_web.php" target="_blank">Run Migration</a>');
                $wishlistItems = [];
            } else {
                $wishlistItems = $wishlistModel->getByUser($user['id']);
            }
        } catch (\Exception $e) {
            error_log("Wishlist page error: " . $e->getMessage());
            $this->setFlash('error', 'Error loading wishlist: ' . $e->getMessage());
            $wishlistItems = [];
        }
        
        echo $this->view->render('Product/wishlist', [
            'wishlistItems' => $wishlistItems,
            'flash' => $this->getFlash()
        ]);
    }
}

