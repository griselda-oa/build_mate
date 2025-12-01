<?php

declare(strict_types=1);

use App\Router;
use App\AuthController;
use App\HomeController;
use App\ProductController;
use App\CartController;
use App\OrderController;
use App\PaymentController;
use App\SupplierController;
use App\LogisticsController;
use App\AdminController;
use App\AdminOrderController;
use App\ChatController;
use App\PremiumController;
use App\AdvertisementController;
use App\AuthMiddleware;
use App\RoleMiddleware;
use App\CsrfMiddleware;
use App\RateLimitMiddleware;
use App\SupplierStatusMiddleware;

// Ensure SupplierStatusMiddleware is loaded (class is in classes/ directory)
require_once __DIR__ . '/../classes/SupplierStatusMiddleware.php';

$router = Router::getInstance();

// Public routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/contact', [HomeController::class, 'contact']);
$router->post('/contact', [HomeController::class, 'contact'], [CsrfMiddleware::class]);
$router->get('/catalog', [ProductController::class, 'catalog']);
$router->get('/product/{slug}', [ProductController::class, 'show']);
$router->get('/products/featured', [ProductController::class, 'featured']);
$router->get('/products/sponsored', [ProductController::class, 'sponsored']);
$router->get('/wishlist', [ProductController::class, 'wishlist'], [AuthMiddleware::class]);
$router->post('/product/review', [ProductController::class, 'submitReview'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/product/waitlist/add', [ProductController::class, 'addToWaitlist'], [AuthMiddleware::class]);
$router->post('/product/waitlist/remove', [ProductController::class, 'removeFromWaitlist'], [AuthMiddleware::class]);
$router->post('/product/wishlist/add', [ProductController::class, 'addToWishlist'], [AuthMiddleware::class]);
$router->post('/product/wishlist/remove', [ProductController::class, 'removeFromWishlist'], [AuthMiddleware::class]);

// Buyer dashboard (authenticated)
$router->get('/dashboard', [HomeController::class, 'dashboard'], [AuthMiddleware::class]);

// Auth routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->get('/login.php', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class, RateLimitMiddleware::class]);
$router->post('/login.php', [AuthController::class, 'login'], [CsrfMiddleware::class, RateLimitMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->get('/register.php', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class, RateLimitMiddleware::class]);
$router->post('/register.php', [AuthController::class, 'register'], [CsrfMiddleware::class, RateLimitMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);
$router->post('/logout.php', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// Cart routes
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add/{id}', [CartController::class, 'add'], [CsrfMiddleware::class]);
$router->post('/cart/update', [CartController::class, 'update'], [CsrfMiddleware::class]);
$router->post('/cart/remove/{id}', [CartController::class, 'remove'], [CsrfMiddleware::class]);

// Checkout routes
$router->get('/checkout', [OrderController::class, 'checkout'], [AuthMiddleware::class]);
$router->post('/checkout', [OrderController::class, 'processCheckout'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Payment routes (specific routes first to avoid matching /payment/{id})
$router->post('/payment/initialize', [PaymentController::class, 'initialize'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/payment/callback', [PaymentController::class, 'callback']); // No auth required - Paystack redirects here (MUST be before /payment/{id})
$router->get('/payment/success/{id}', [PaymentController::class, 'success']); // No auth required - payment already verified
$router->get('/payment/{id}', [PaymentController::class, 'show'], [AuthMiddleware::class]); // Must be last to avoid matching /payment/callback
$router->get('/payment/mock-callback', [PaymentController::class, 'mockCallback'], [AuthMiddleware::class]);

// Order routes (buyer)
$router->get('/orders', [OrderController::class, 'index'], [AuthMiddleware::class]);
$router->get('/orders/{id}/status', [OrderController::class, 'getStatus'], [AuthMiddleware::class]); // Must be before /orders/{id}
$router->get('/orders/{id}', [OrderController::class, 'show'], [AuthMiddleware::class]);
$router->get('/orders/{id}/track-delivery', [OrderController::class, 'trackDelivery'], [AuthMiddleware::class]);
$router->post('/orders/{id}/confirm-delivery', [OrderController::class, 'confirmDelivery'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/orders/{id}/dispute', [OrderController::class, 'dispute'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/orders/{id}/invoice.pdf', [OrderController::class, 'invoice'], [AuthMiddleware::class]);

// Supplier routes
$router->get('/supplier/apply', [SupplierController::class, 'apply']);
$router->get('/supplier/pending', [SupplierController::class, 'pending']);
$router->get('/supplier/dashboard', [SupplierController::class, 'dashboard'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class]);
$router->get('/supplier/kyc', [SupplierController::class, 'kyc'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier']);
$router->post('/supplier/kyc', [SupplierController::class, 'submitKyc'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', CsrfMiddleware::class]);
$router->get('/supplier/products', [SupplierController::class, 'products'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class]);
$router->post('/supplier/products', [SupplierController::class, 'createProduct'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);
$router->post('/supplier/products/{id}/update', [SupplierController::class, 'updateProduct'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);
$router->post('/supplier/products/{id}/delete', [SupplierController::class, 'deleteProduct'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);
$router->get('/supplier/orders', [SupplierController::class, 'orders'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class]);
$router->post('/supplier/orders/{id}/accept', [SupplierController::class, 'acceptOrder'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);
$router->post('/supplier/orders/{id}/dispatch', [SupplierController::class, 'dispatchOrder'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);
$router->post('/supplier/orders/{id}/mark-ready', [SupplierController::class, 'markReadyForPickup'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);
$router->post('/supplier/orders/{id}/update-status', [SupplierController::class, 'updateOrderStatus'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', SupplierStatusMiddleware::class, CsrfMiddleware::class]);

// Premium subscription routes
$router->get('/supplier/premium/upgrade', [PremiumController::class, 'upgrade'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier']);
$router->post('/supplier/premium/initialize', [PremiumController::class, 'initializePayment'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', CsrfMiddleware::class]);
$router->get('/supplier/premium/callback', [PremiumController::class, 'paymentCallback'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier']);
$router->get('/supplier/premium/status', [PremiumController::class, 'status'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier']);

// Advertisement routes
$router->get('/supplier/advertisements', [AdvertisementController::class, 'index'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier']);
$router->get('/supplier/advertisements/create', [AdvertisementController::class, 'create'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier']);
$router->post('/supplier/advertisements/create', [AdvertisementController::class, 'submit'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', CsrfMiddleware::class]);
$router->post('/supplier/advertisements/payment/initialize', [AdvertisementController::class, 'initializePayment'], [AuthMiddleware::class, RoleMiddleware::class . ':supplier', CsrfMiddleware::class]);
$router->get('/supplier/advertisements/payment/callback', [AdvertisementController::class, 'paymentCallback']); // No auth required for callback

// Logistics routes
$router->get('/logistics/dashboard', [LogisticsController::class, 'dashboard'], [AuthMiddleware::class, RoleMiddleware::class . ':logistics']);
$router->get('/logistics/assignments', [LogisticsController::class, 'assignments'], [AuthMiddleware::class, RoleMiddleware::class . ':logistics']);
$router->post('/logistics/orders/{id}/start', [LogisticsController::class, 'startDelivery'], [AuthMiddleware::class, RoleMiddleware::class . ':logistics', CsrfMiddleware::class]);
$router->post('/logistics/orders/{id}/in-transit', [LogisticsController::class, 'markInTransit'], [AuthMiddleware::class, RoleMiddleware::class . ':logistics', CsrfMiddleware::class]);
$router->post('/logistics/orders/{id}/delivered', [LogisticsController::class, 'markDelivered'], [AuthMiddleware::class, RoleMiddleware::class . ':logistics', CsrfMiddleware::class]);

// Admin routes
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
// More specific route must come first
$router->get('/admin/suppliers/{id}', [AdminController::class, 'viewSupplier'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get('/admin/suppliers', [AdminController::class, 'suppliers'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/admin/suppliers/{id}/approve', [AdminController::class, 'approveSupplier'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->post('/admin/suppliers/{id}/reject', [AdminController::class, 'rejectSupplier'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->post('/admin/suppliers/{id}/delete', [AdminController::class, 'deleteSupplier'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'users'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get('/admin/products', [AdminController::class, 'products'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
// More specific route must come first to avoid matching /admin/orders before /admin/orders/{id}
$router->get('/admin/orders/{id}', [AdminOrderController::class, 'show'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get('/admin/orders', [AdminOrderController::class, 'index'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/admin/orders/{id}/update-status', [AdminOrderController::class, 'updateStatus'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
// Admin Logistics routes
$router->get('/admin/logistics', [AdminController::class, 'logistics'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/admin/update-delivery-status', [AdminController::class, 'updateDeliveryStatus'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->post('/admin/mark-delivered-with-photo', [AdminController::class, 'markDeliveredWithPhoto'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->get('/admin/chat', [AdminController::class, 'chat'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get('/admin/chat/sessions', [AdminController::class, 'chatSessions'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->get('/admin/chat/session/{session_id}', [AdminController::class, 'chatSessionDetail'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
// Admin premium and advertisement management
$router->get('/admin/premium', [AdminController::class, 'premium'], [AuthMiddleware::class, RoleMiddleware::class . ':admin']);
$router->post('/admin/premium/{id}/downgrade', [AdminController::class, 'downgradeSupplier'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->post('/admin/advertisements/{id}/approve', [AdminController::class, 'approveAdvertisement'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);
$router->post('/admin/advertisements/{id}/reject', [AdminController::class, 'rejectAdvertisement'], [AuthMiddleware::class, RoleMiddleware::class . ':admin', CsrfMiddleware::class]);

// Chat API routes
$router->post('/api/chat/send', [ChatController::class, 'sendMessage']);
$router->get('/api/chat/history', [ChatController::class, 'getHistory']);

// Public premium product routes
$router->get('/products/featured', [ProductController::class, 'featured']);
$router->get('/products/sponsored', [ProductController::class, 'sponsored']);


