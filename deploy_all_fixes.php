<?php
/**
 * Complete Deployment Script - Fix ALL Controllers
 * Upload and run this ONCE, then delete it
 */

$SECRET_KEY = 'buildmate_deploy_all_' . date('Ymd');
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die("Access denied. Use: ?key=" . $SECRET_KEY);
}

echo "<h1>Complete Deployment - Fix All Controllers</h1>";
echo "<p>Secret Key: <code>$SECRET_KEY</code></p><hr>";

$fixes = [];

// ============================================
// FIX 1: OrderController.php
// ============================================
echo "<h2>Fix 1: OrderController.php</h2>";
$path = __DIR__ . '/controllers/OrderController.php';

if (file_exists($path)) {
    $backup = $path . '.backup.' . date('YmdHis');
    copy($path, $backup);
    echo "<p>✅ Backup: $backup</p>";
    
    $content = file_get_contents($path);
    $changed = false;
    
    // Fix show() method
    if (strpos($content, 'public function show(int $id): void') !== false) {
        $content = str_replace(
            "public function show(int \$id): void\n    {\n        \$user = \$this->user();",
            "public function show(\$id): void\n    {\n        \$id = (int)\$id;\n        \$user = \$this->user();",
            $content
        );
        echo "<p>✅ Fixed show() method</p>";
        $changed = true;
    }
    
    // Fix getStatus() method
    if (strpos($content, 'public function getStatus(int $id): void') !== false) {
        $content = str_replace(
            "public function getStatus(int \$id): void\n    {\n        \$user = \$this->user();",
            "public function getStatus(\$id): void\n    {\n        \$id = (int)\$id;\n        \$user = \$this->user();",
            $content
        );
        echo "<p>✅ Fixed getStatus() method</p>";
        $changed = true;
    }
    
    // Fix trackDelivery() method
    if (strpos($content, 'public function trackDelivery(int $orderId): void') !== false) {
        $content = str_replace(
            "public function trackDelivery(int \$orderId): void\n    {\n        \$user = \$this->user();",
            "public function trackDelivery(\$orderId): void\n    {\n        \$orderId = (int)\$orderId;\n        \$user = \$this->user();",
            $content
        );
        echo "<p>✅ Fixed trackDelivery() method</p>";
        $changed = true;
    }
    
    // Fix confirmDelivery() method
    if (strpos($content, 'public function confirmDelivery(int $id): void') !== false) {
        $content = str_replace(
            "public function confirmDelivery(int \$id): void\n    {",
            "public function confirmDelivery(\$id): void\n    {\n        \$id = (int)\$id;",
            $content
        );
        echo "<p>✅ Fixed confirmDelivery() method</p>";
        $changed = true;
    }
    
    // Fix dispute() method
    if (strpos($content, 'public function dispute(int $id): void') !== false) {
        $content = str_replace(
            "public function dispute(int \$id): void\n    {\n        \$user = \$this->user();",
            "public function dispute(\$id): void\n    {\n        \$id = (int)\$id;\n        \$user = \$this->user();",
            $content
        );
        echo "<p>✅ Fixed dispute() method</p>";
        $changed = true;
    }
    
    // Fix invoice() method
    if (strpos($content, 'public function invoice(int $id): void') !== false) {
        $content = str_replace(
            "public function invoice(int \$id): void\n    {",
            "public function invoice(\$id): void\n    {\n        \$id = (int)\$id;",
            $content
        );
        echo "<p>✅ Fixed invoice() method</p>";
        $changed = true;
    }
    
    if ($changed) {
        file_put_contents($path, $content);
        echo "<p style='color:green;'><strong>✅ OrderController.php updated</strong></p>";
        $fixes[] = 'OrderController.php';
    } else {
        echo "<p style='color:orange;'>⚠️ No changes needed</p>";
    }
} else {
    echo "<p style='color:red;'>❌ File not found</p>";
}

echo "<hr>";

// ============================================
// FIX 2: ProductController.php
// ============================================
echo "<h2>Fix 2: ProductController.php</h2>";
$path = __DIR__ . '/controllers/ProductController.php';

if (file_exists($path)) {
    $backup = $path . '.backup.' . date('YmdHis');
    copy($path, $backup);
    echo "<p>✅ Backup: $backup</p>";
    
    $content = file_get_contents($path);
    $changed = false;
    
    // Check if productId casting exists
    if (strpos($content, '$productId = (int)$product[\'id\'];') === false) {
        // Find the line with reviews and add casting before it
        $content = preg_replace(
            '/(\/\/ Get reviews and stats\s+\$reviews = \$reviewModel->getByProduct\(\$product\[\'id\'\]\);)/s',
            "// Get reviews and stats\n        \$productId = (int)\$product['id'];\n        \$reviews = \$reviewModel->getByProduct(\$productId);",
            $content
        );
        
        // Replace other occurrences
        $content = str_replace(
            "\$reviewModel->getProductStats(\$product['id'])",
            "\$reviewModel->getProductStats(\$productId)",
            $content
        );
        $content = str_replace(
            "\$reviewModel->hasPurchasedProduct(\$userId, \$product['id'])",
            "\$reviewModel->hasPurchasedProduct(\$userId, \$productId)",
            $content
        );
        $content = str_replace(
            "\$reviewModel->hasReviewedProduct(\$userId, \$product['id'])",
            "\$reviewModel->hasReviewedProduct(\$userId, \$productId)",
            $content
        );
        $content = str_replace(
            "\$waitlistModel->isInWaitlist(\$userId, \$product['id'])",
            "\$waitlistModel->isInWaitlist(\$userId, \$productId)",
            $content
        );
        $content = str_replace(
            "\$wishlistModel->isInWishlist(\$userId, \$product['id'])",
            "\$wishlistModel->isInWishlist(\$userId, \$productId)",
            $content
        );
        
        // Cast userId and supplierId
        $content = preg_replace(
            '/(\$user = Auth::user\(\);\s+\$userId = \$user\[\'id\'\];)/s',
            "\$user = Auth::user();\n            \$userId = (int)\$user['id'];",
            $content
        );
        $content = preg_replace(
            '/(\$supplierId = \$product\[\'supplier_id\'\] \?\? 0;)/s',
            "\$supplierId = (int)(\$product['supplier_id'] ?? 0);",
            $content
        );
        
        echo "<p>✅ Fixed product ID casting</p>";
        $changed = true;
    }
    
    if ($changed) {
        file_put_contents($path, $content);
        echo "<p style='color:green;'><strong>✅ ProductController.php updated</strong></p>";
        $fixes[] = 'ProductController.php';
    } else {
        echo "<p style='color:orange;'>⚠️ No changes needed</p>";
    }
} else {
    echo "<p style='color:red;'>❌ File not found</p>";
}

echo "<hr>";

// ============================================
// Clear OpCache
// ============================================
echo "<h2>Clear OpCache</h2>";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color:green;'>✅ OpCache cleared</p>";
} else {
    echo "<p style='color:orange;'>⚠️ OpCache not available</p>";
}

echo "<hr>";

// ============================================
// Summary
// ============================================
echo "<h2>Deployment Summary</h2>";
if (count($fixes) > 0) {
    echo "<p style='color:green;'><strong>✅ SUCCESS! Fixed " . count($fixes) . " file(s):</strong></p>";
    echo "<ul>";
    foreach ($fixes as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠️ All files were already up to date</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Test order view: <a href='/build_mate/orders'>Go to Orders</a> → Click 'View Details'</li>";
echo "<li>Test checkout: <a href='/build_mate/checkout'>Go to Checkout</a></li>";
echo "<li>Test product page: <a href='/build_mate/catalog'>Browse Products</a></li>";
echo "<li><strong style='color:red;'>DELETE THIS FILE (deploy_all_fixes.php) immediately!</strong></li>";
echo "</ol>";

echo "<hr>";
echo "<p><small>Executed: " . date('Y-m-d H:i:s') . "</small></p>";
?>
