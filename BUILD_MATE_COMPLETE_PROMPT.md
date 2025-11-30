# 🚀 BUILD MATE - COMPLETE DEVELOPMENT PROMPT FOR CURSOR AI

## PROJECT OVERVIEW

Build a complete, production-ready e-commerce marketplace for building and construction materials in Ghana. This is a multi-role B2B/B2C platform with advanced features including AI sentiment analysis, premium subscriptions, and comprehensive logistics management.

---

## 📋 TECH STACK & ARCHITECTURE

**Backend:**
- PHP 8.2+ with MVC architecture
- MySQL/MariaDB database
- Existing Router system (`classes/Router.php`)
- Existing middleware system (AuthMiddleware, RoleMiddleware, CsrfMiddleware)
- Existing Model base class (`classes/Model.php`)

**Frontend:**
- HTML5 + CSS3 (extend existing `assets/css/main.css`)
- Heavy, interactive JavaScript (NOT minimal - rich UX required)
- Bootstrap 5.3.0 (already included)
- Bootstrap Icons (already included)
- Chart.js for analytics dashboards

**Payments:**
- Paystack integration (initialize, callback, webhook, verification)
- Escrow-like fund holding system

**AI/ML:**
- Python Flask microservice for sentiment analysis
- Hugging Face transformers (distilbert-base-uncased-finetuned-sst-2-english)
- RESTful API communication between PHP and Python

**Existing Codebase:**
- Location: `/Applications/XAMPP/xamppfiles/htdocs/build_mate/`
- Existing classes: Router, Model, Controller, View, DB, Validator, Security
- Existing CSS: `assets/css/main.css` (extend, don't replace)
- Existing structure: Controllers, Models, Views, Middleware

---

## 🎯 USER ROLES & PERMISSIONS

### 1. ADMIN (Full Platform Control)
**Capabilities:**
- Approve/reject supplier applications (status: pending → approved/rejected)
- Approve/reject logistics company registrations
- Manage product categories globally
- Monitor all orders, deliveries, and disputes
- View supplier performance & sentiment reports
- Suspend/downgrade non-performing suppliers
- Approve premium plans and advertisement campaigns
- Access comprehensive analytics dashboard
- Manage platform settings and fees

**Routes:**
- `/admin/dashboard` - Main admin dashboard
- `/admin/suppliers` - Supplier management
- `/admin/suppliers/{id}/approve` - Approve supplier
- `/admin/suppliers/{id}/reject` - Reject supplier
- `/admin/suppliers/{id}/suspend` - Suspend supplier
- `/admin/logistics` - Logistics companies management
- `/admin/ads` - Ad campaign approvals
- `/admin/orders` - All orders overview
- `/admin/performance` - Performance reports
- `/admin/analytics` - Platform analytics

### 2. SUPPLIERS (Vendors)
**Registration Flow:**
1. Apply via `/supplier/apply` (public)
2. Submit KYC documents via `/supplier/kyc`
3. Status set to `pending` in database
4. **Middleware blocks access** to all supplier CRUD until approved
5. After admin approval → status = `approved` → full access unlocked

**While Pending:**
- Can only access: `/supplier/pending`, `/supplier/kyc`, `/supplier/apply`
- **Cannot access:** `/supplier/dashboard`, `/supplier/products`, `/supplier/orders`
- Middleware: `SupplierStatusMiddleware` enforces this

**After Approval:**
- Full CRUD for products (create, edit, delete, manage stock)
- Set pricing, discounts, product variations
- View and manage orders assigned to them
- Coordinate with logistics partners
- Respond to customer messages/reviews
- Opt into premium plans
- Create ad campaigns (requires premium)
- View analytics and sentiment reports

**Routes:**
- `/supplier/apply` - Application form (public)
- `/supplier/pending` - Pending dashboard (when status = pending)
- `/supplier/dashboard` - Main dashboard (requires approved)
- `/supplier/products` - Product management (requires approved)
- `/supplier/products/create` - Create product (requires approved)
- `/supplier/products/{id}/edit` - Edit product (requires approved)
- `/supplier/orders` - View orders (requires approved)
- `/supplier/premium` - Premium plans (requires approved)
- `/supplier/ads` - Ad campaigns (requires premium plan)

### 3. BUYERS (Customers)
**Capabilities:**
- Browse, search, filter products with advanced filters
- Add to cart, manage cart items dynamically
- Multi-supplier checkout with logistics selection per supplier
- Pay securely via Paystack
- Real-time order tracking with status updates
- Rate suppliers and write reviews (feeds AI sentiment analysis)
- View order history and delivery details

**Routes:**
- `/` - Homepage with product listings
- `/catalog` - Product catalog with filters
- `/product/{slug}` - Product detail page
- `/cart` - Shopping cart
- `/checkout` - Checkout process
- `/orders` - Order history
- `/orders/{id}` - Order details
- `/orders/{id}/track` - Order tracking
- `/orders/{id}/review` - Submit review

### 4. LOGISTICS PARTNERS (Delivery Services)
**Two Types:**
1. **Build Mate Internal Logistics** (default, admin-configured)
2. **External Logistics Companies** (apply, get approved by admin)

**Capabilities:**
- View assigned shipments
- Update delivery statuses: `pending_pickup` → `picked_up` → `in_transit` → `delivered` / `failed`
- Access delivery details (addresses, contact info, order contents)
- See performance metrics (delivery success rate, average time)
- Define service areas (regions/cities they serve)
- Real-time status updates trigger notifications

**Routes:**
- `/logistics/apply` - Application form
- `/logistics/dashboard` - Main dashboard
- `/logistics/shipments` - View all shipments
- `/logistics/shipments/{id}` - Shipment details
- `/logistics/shipments/{id}/update-status` - Update status

---

## 🗄️ DATABASE SCHEMA

### Core Tables

```sql
-- Users table (already exists, extend if needed)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table (extend existing)
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS description TEXT;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS avg_rating DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS avg_sentiment_score DECIMAL(4,3) DEFAULT 0.000;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS performance_status ENUM('good', 'warning_level_1', 'warning_level_2', 'at_risk', 'suspended') DEFAULT 'good';
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS visibility_weight DECIMAL(3,2) DEFAULT 1.00;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS review_count INT UNSIGNED DEFAULT 0;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS total_sales DECIMAL(12,2) DEFAULT 0.00;

-- Buyers table
CREATE TABLE IF NOT EXISTS buyers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    phone VARCHAR(20),
    default_address TEXT,
    preferred_payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logistics Companies table
CREATE TABLE IF NOT EXISTS logistics_companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(255),
    regions_served JSON,
    status ENUM('pending', 'approved', 'suspended', 'rejected') DEFAULT 'pending',
    rating DECIMAL(3,2) DEFAULT 0.00,
    delivery_success_rate DECIMAL(5,2) DEFAULT 0.00,
    avg_delivery_time_hours DECIMAL(5,2) DEFAULT 0.00,
    total_deliveries INT UNSIGNED DEFAULT 0,
    successful_deliveries INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table (extend existing)
ALTER TABLE products ADD COLUMN IF NOT EXISTS images JSON;
ALTER TABLE products ADD COLUMN IF NOT EXISTS boost_score INT DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_sponsored TINYINT(1) DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS sold_count INT UNSIGNED DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS view_count INT UNSIGNED DEFAULT 0;

-- Orders table (extend existing)
ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_reference VARCHAR(50) UNIQUE;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100);
ALTER TABLE orders ADD COLUMN IF NOT EXISTS funds_released TINYINT(1) DEFAULT 0;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS funds_released_at TIMESTAMP NULL;

-- Shipments table (NEW - critical for logistics)
CREATE TABLE IF NOT EXISTS shipments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    logistics_company_id INT UNSIGNED,
    tracking_code VARCHAR(100) UNIQUE,
    delivery_fee DECIMAL(10,2) NOT NULL,
    status ENUM('pending_pickup', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending_pickup',
    pickup_address TEXT,
    delivery_address TEXT NOT NULL,
    pickup_contact_name VARCHAR(255),
    pickup_contact_phone VARCHAR(20),
    delivery_contact_name VARCHAR(255),
    delivery_contact_phone VARCHAR(20),
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    failure_reason TEXT,
    delivery_proof_url VARCHAR(500),
    status_history JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_company_id) REFERENCES logistics_companies(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_logistics_company_id (logistics_company_id),
    INDEX idx_tracking_code (tracking_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table (extend existing kyc_documents or create new)
CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    sentiment_label ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral',
    sentiment_score DECIMAL(4,3) DEFAULT 0.000,
    is_verified_purchase TINYINT(1) DEFAULT 1,
    helpful_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_order_id (order_id),
    INDEX idx_rating (rating),
    INDEX idx_sentiment_score (sentiment_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'paystack',
    transaction_reference VARCHAR(100) UNIQUE,
    paystack_reference VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    verified_at TIMESTAMP NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction_reference (transaction_reference),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plans table
CREATE TABLE IF NOT EXISTS plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT UNSIGNED NOT NULL,
    benefits JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Plans table
CREATE TABLE IF NOT EXISTS supplier_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    payment_reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ad Campaigns table
CREATE TABLE IF NOT EXISTS ad_campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    campaign_name VARCHAR(255) NOT NULL,
    product_ids JSON,
    banner_image_url VARCHAR(500),
    budget DECIMAL(10,2),
    clicks INT UNSIGNED DEFAULT 0,
    impressions INT UNSIGNED DEFAULT 0,
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'approved', 'active', 'paused', 'rejected', 'expired') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500),
    read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_read (read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Performance Warnings table
CREATE TABLE IF NOT EXISTS performance_warnings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    warning_level TINYINT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    threshold_violated JSON,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment Status History table (for detailed tracking)
CREATE TABLE IF NOT EXISTS shipment_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🏗️ PHP MVC ARCHITECTURE

### Controllers Structure

**File: `controllers/AdminController.php`**

```php
<?php
declare(strict_types=1);

namespace App;

use App\Controller;

class AdminController extends Controller
{
    // Supplier Management
    public function suppliers(): void
    {
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAllWithUsers();
        
        echo $this->view->render('Admin/suppliers', [
            'suppliers' => $suppliers,
            'title' => 'Supplier Management'
        ]);
    }
    
    public function approveSupplier(int $id): void
    {
        $supplierModel = new Supplier();
        $supplier = $supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', 'Supplier not found');
            $this->redirect('/build_mate/admin/suppliers');
            return;
        }
        
        $supplierModel->update($id, [
            'kyc_status' => 'approved',
            'verified_badge' => 1
        ]);
        
        // Send notification to supplier
        Notification::create([
            'user_id' => $supplier['user_id'],
            'type' => 'supplier_approved',
            'title' => 'Application Approved!',
            'message' => 'Your supplier account has been approved. You can now start listing products.',
            'link' => '/build_mate/supplier/dashboard'
        ]);
        
        Security::log('supplier_approved', $this->user()['id'], ['supplier_id' => $id]);
        $this->setFlash('success', 'Supplier approved successfully');
        $this->redirect('/build_mate/admin/suppliers');
    }
    
    public function rejectSupplier(int $id): void
    {
        // Implementation
    }
    
    public function suspendSupplier(int $id): void
    {
        // Implementation
    }
    
    // Logistics Management
    public function logistics(): void
    {
        // Implementation
    }
    
    public function approveLogisticsCompany(int $id): void
    {
        // Implementation
    }
    
    // Ad Campaign Approval
    public function ads(): void
    {
        // Implementation
    }
    
    public function approveAdCampaign(int $id): void
    {
        // Implementation
    }
    
    // Analytics & Reports
    public function dashboard(): void
    {
        // Implementation
    }
    
    public function performance(): void
    {
        // Implementation
    }
}
```

**File: `controllers/SupplierController.php`** (Extend existing)

Add methods:
- `createProduct()` - Already exists, ensure middleware protection
- `updateProduct()` - Already exists
- `deleteProduct()` - Already exists
- `viewOrders()` - Already exists
- `confirmDispatch()` - New method
- `viewPremiumPlans()` - New method
- `subscribeToPlan()` - New method
- `createAdCampaign()` - New method
- `getSalesDashboard()` - New method (AJAX endpoint)

**File: `controllers/BuyerController.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Controller;

class BuyerController extends Controller
{
    public function catalog(): void
    {
        // Implementation with filters
    }
    
    public function viewProduct(string $slug): void
    {
        // Implementation
    }
    
    public function addToCart(int $productId): void
    {
        // Implementation
    }
    
    public function viewCart(): void
    {
        // Implementation
    }
    
    public function checkout(): void
    {
        // Implementation
    }
    
    public function processCheckout(): void
    {
        // Implementation
    }
    
    public function viewOrders(): void
    {
        // Implementation
    }
    
    public function trackOrder(int $orderId): void
    {
        // Implementation
    }
    
    public function submitReview(int $orderId): void
    {
        // Implementation with AI sentiment analysis
    }
}
```

**File: `controllers/LogisticsController.php`** (Extend existing)

Add methods:
- `showApplication()` - New
- `submitApplication()` - New
- `viewShipments()` - New
- `updateShipmentStatus()` - New
- `markAsPickedUp()` - New
- `markAsDelivered()` - New

**File: `controllers/PaymentController.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Controller;

class PaymentController extends Controller
{
    public function startPayment(int $orderId): void
    {
        // Initialize Paystack transaction
    }
    
    public function handlePaystackCallback(): void
    {
        // Process redirect after payment
    }
    
    public function handlePaystackWebhook(): void
    {
        // Verify webhook from Paystack
    }
    
    public function verifyTransaction(string $reference): array
    {
        // Server-to-server verification
    }
    
    public function releaseEscrowFunds(int $orderId): void
    {
        // After delivery confirmation
    }
}
```

**File: `controllers/ApiController.php`** (New - for AJAX endpoints)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Controller;

class ApiController extends Controller
{
    public function searchProducts(): void
    {
        // AJAX product search
    }
    
    public function addToCart(): void
    {
        // AJAX add to cart
    }
    
    public function updateCart(): void
    {
        // AJAX update cart
    }
    
    public function getCart(): void
    {
        // AJAX get cart
    }
    
    public function updateLogistics(): void
    {
        // AJAX update logistics selection
    }
    
    public function getSupplierStats(): void
    {
        // AJAX supplier dashboard stats
    }
    
    public function getOrderTracking(int $orderId): void
    {
        // AJAX order tracking data
    }
    
    public function getNotifications(): void
    {
        // AJAX notifications
    }
    
    public function checkNotifications(): void
    {
        // AJAX check for new notifications
    }
}
```

### Models Structure

**File: `classes/Supplier.php`** (Extend existing)

Add methods:
- `getPerformanceMetrics(int $supplierId): array`
- `updateSentimentScore(int $supplierId, float $score): void`
- `checkPerformanceThresholds(int $supplierId): void`

**File: `classes/LogisticsCompany.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class LogisticsCompany extends Model
{
    protected string $table = 'logistics_companies';
    
    public function findByUserId(int $userId): ?array
    {
        // Implementation
    }
    
    public function getAvailableForRoute(string $fromRegion, string $toRegion): array
    {
        // Implementation
    }
    
    public function updatePerformanceMetrics(int $companyId): void
    {
        // Implementation
    }
}
```

**File: `classes/Shipment.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class Shipment extends Model
{
    protected string $table = 'shipments';
    
    public function createForOrder(int $orderId, array $data): int
    {
        // Implementation
    }
    
    public function updateStatus(int $shipmentId, string $status, ?string $notes = null): void
    {
        // Implementation with status history logging
    }
    
    public function getByLogisticsCompany(int $companyId): array
    {
        // Implementation
    }
    
    public function getTrackingInfo(string $trackingCode): ?array
    {
        // Implementation
    }
}
```

**File: `classes/Review.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class Review extends Model
{
    protected string $table = 'reviews';
    
    public function createWithSentiment(array $data): int
    {
        // Implementation with AI sentiment analysis
    }
    
    public function getBySupplier(int $supplierId): array
    {
        // Implementation
    }
    
    public function getSentimentDistribution(int $supplierId): array
    {
        // Implementation
    }
}
```

**File: `classes/Payment.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class Payment extends Model
{
    protected string $table = 'payments';
    
    public function createPayment(array $data): int
    {
        // Implementation
    }
    
    public function verifyPaystackTransaction(string $reference): array
    {
        // Implementation
    }
    
    public function releaseFunds(int $orderId): void
    {
        // Implementation
    }
}
```

**File: `classes/Plan.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class Plan extends Model
{
    protected string $table = 'plans';
    
    public function getActivePlans(): array
    {
        // Implementation
    }
}
```

**File: `classes/AdCampaign.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class AdCampaign extends Model
{
    protected string $table = 'ad_campaigns';
    
    public function getActiveCampaigns(): array
    {
        // Implementation
    }
    
    public function trackImpression(int $campaignId): void
    {
        // Implementation
    }
    
    public function trackClick(int $campaignId): void
    {
        // Implementation
    }
}
```

**File: `classes/Notification.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Model;

class Notification extends Model
{
    protected string $table = 'notifications';
    
    public static function create(array $data): int
    {
        // Implementation
    }
    
    public function getUnreadForUser(int $userId): array
    {
        // Implementation
    }
    
    public function markAsRead(int $notificationId): void
    {
        // Implementation
    }
}
```

**File: `classes/SentimentAnalyzer.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

class SentimentAnalyzer
{
    private string $serviceUrl;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../settings/config.php';
        $this->serviceUrl = $config['ai']['sentiment_service_url'] ?? 'http://localhost:5000';
    }
    
    public function analyze(string $text): array
    {
        // Call Python microservice
    }
    
    public function processReview(int $orderId, int $supplierId, int $rating, string $reviewText): int
    {
        // Analyze sentiment and save review
    }
    
    public function updateSupplierMetrics(int $supplierId): void
    {
        // Recalculate supplier sentiment averages
    }
    
    private function checkPerformanceThresholds(Supplier $supplier): void
    {
        // Check and send warnings if needed
    }
}
```

### Middleware

**File: `classes/SupplierStatusMiddleware.php`** (Already created, ensure it works)

**File: `classes/PremiumPlanMiddleware.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

class PremiumPlanMiddleware
{
    public function handle(): bool
    {
        // Check if supplier has active premium plan
        // Redirect to premium plans page if not
    }
}
```

---

## 💳 PAYSTACK INTEGRATION

**File: `classes/PaystackService.php`** (New)

```php
<?php
declare(strict_types=1);

namespace App;

class PaystackService
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl = 'https://api.paystack.co';
    
    public function __construct()
    {
        $config = require __DIR__ . '/../settings/config.php';
        $this->secretKey = $config['payment']['paystack_secret_key'];
        $this->publicKey = $config['payment']['paystack_public_key'];
    }
    
    public function initializeTransaction(array $data): array
    {
        // Initialize Paystack transaction
    }
    
    public function verifyTransaction(string $reference): array
    {
        // Verify transaction
    }
    
    public function handleWebhook(array $payload): bool
    {
        // Verify and process webhook
    }
    
    public function transferToSupplier(int $supplierId, float $amount, string $accountNumber): array
    {
        // Transfer funds to supplier (payout)
    }
}
```

---

## 🤖 AI SENTIMENT ANALYSIS

**File: `sentiment_service/sentiment_service.py`** (New - Python microservice)

```python
from flask import Flask, request, jsonify
from transformers import pipeline
import logging
import os

app = Flask(__name__)

# Load sentiment analysis model
sentiment_analyzer = pipeline(
    "sentiment-analysis",
    model="distilbert-base-uncased-finetuned-sst-2-english"
)

@app.route('/analyze-sentiment', methods=['POST'])
def analyze_sentiment():
    try:
        data = request.get_json()
        text = data.get('text', '')
        
        if not text or len(text.strip()) < 5:
            return jsonify({'error': 'Text too short for analysis'}), 400
        
        # Run sentiment analysis
        result = sentiment_analyzer(text[:512])[0]
        
        # Convert to standardized format
        label = result['label'].lower()
        score = result['score']
        
        # Normalize score to -1 to 1 range
        if label == 'negative':
            normalized_score = -score
        else:
            normalized_score = score
        
        return jsonify({
            'label': label,
            'score': normalized_score,
            'confidence': score,
            'original_text_length': len(text)
        })
        
    except Exception as e:
        logging.error(f"Sentiment analysis error: {str(e)}")
        return jsonify({'error': 'Analysis failed'}), 500

@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'healthy', 'model': 'distilbert-sst-2'})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
```

**File: `sentiment_service/requirements.txt`** (New)

```
flask==2.3.0
transformers==4.30.0
torch==2.0.0
```

---

## 📱 ROUTING STRUCTURE

**File: `settings/routes.php`** (Extend existing)

Add all routes as specified in the prompt, ensuring middleware is properly applied.

---

## 🎨 FRONTEND JAVASCRIPT

Create comprehensive JavaScript files for:
1. Product filtering & search (`assets/js/product-filter.js`)
2. Cart management (`assets/js/cart.js`)
3. Supplier dashboard (`assets/js/supplier-dashboard.js`)
4. Notifications system (`assets/js/notifications.js`)
5. Order tracking (`assets/js/order-tracking.js`)

All with heavy interactivity, AJAX, real-time updates, and smooth animations.

---

## ✅ IMPLEMENTATION PRIORITIES

**Phase 1: Core Infrastructure**
1. Database schema creation
2. Middleware system (SupplierStatusMiddleware, PremiumPlanMiddleware)
3. Base models (Supplier, LogisticsCompany, Shipment, Review, Payment)
4. Routing structure

**Phase 2: Supplier System**
1. Application form (already done)
2. Pending dashboard (already done)
3. Product CRUD with middleware protection
4. Order management
5. Premium plans integration

**Phase 3: Buyer System**
1. Product catalog with advanced filters
2. Shopping cart with multi-supplier support
3. Checkout with logistics selection
4. Order tracking
5. Review system

**Phase 4: Logistics System**
1. Application form
2. Dashboard with shipments
3. Status update system
4. Performance tracking

**Phase 5: Payment Integration**
1. Paystack initialization
2. Callback handling
3. Webhook verification
4. Escrow fund release

**Phase 6: AI Integration**
1. Python microservice
2. PHP integration
3. Sentiment analysis on reviews
4. Performance monitoring

**Phase 7: Admin Panel**
1. Supplier approval workflow
2. Logistics approval workflow
3. Ad campaign approvals
4. Analytics dashboard

**Phase 8: Premium & Ads**
1. Plan subscription system
2. Ad campaign creation
3. Search ranking with boosts
4. Ad display logic

---

## 🎯 CRITICAL REQUIREMENTS

1. **All supplier CRUD operations MUST be protected by SupplierStatusMiddleware**
2. **All premium features MUST be protected by PremiumPlanMiddleware**
3. **All forms MUST include CSRF protection**
4. **All database queries MUST use prepared statements**
5. **All user input MUST be validated and sanitized**
6. **All file uploads MUST be validated (type, size)**
7. **All AJAX endpoints MUST return JSON**
8. **All sensitive operations MUST be logged**
9. **All error messages MUST be user-friendly**
10. **All code MUST be production-ready (no TODOs, no placeholders)**

---

## 📝 DELIVERABLES CHECKLIST

- [ ] Complete database schema with all tables
- [ ] All controllers with full implementation
- [ ] All models with CRUD and business logic
- [ ] All middleware classes
- [ ] Paystack integration (complete flow)
- [ ] Python sentiment analysis service
- [ ] All JavaScript files (heavy interactivity)
- [ ] All view files (HTML/PHP)
- [ ] CSS extensions (don't break existing styles)
- [ ] Routing configuration
- [ ] Configuration files
- [ ] Documentation/comments

---

**NOW BUILD THE COMPLETE APPLICATION WITH ALL FEATURES FULLY IMPLEMENTED.**



