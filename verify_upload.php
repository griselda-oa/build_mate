<?php
/**
 * Upload Verification Script
 * Upload this file to your production server to verify the fixes are in place
 */

echo "<h1>BuildMate - Upload Verification</h1>";
echo "<p>Checking if type casting fixes are deployed...</p><hr>";

// Check OrderController
$orderControllerPath = __DIR__ . '/controllers/OrderController.php';
if (file_exists($orderControllerPath)) {
    $content = file_get_contents($orderControllerPath);
    
    echo "<h2>✓ OrderController.php exists</h2>";
    
    // Check if the fix is present
    if (strpos($content, 'public function show($id): void') !== false && 
        strpos($content, '$id = (int)$id;') !== false) {
        echo "<p style='color: green;'>✅ <strong>FIXED</strong> - show() method has type casting</p>";
    } else if (strpos($content, 'public function show(int $id): void') !== false) {
        echo "<p style='color: red;'>❌ <strong>NOT FIXED</strong> - show() method still has strict int type hint</p>";
        echo "<p><strong>Action:</strong> Re-upload controllers/OrderController.php</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ <strong>UNKNOWN</strong> - Cannot determine fix status</p>";
    }
    
    echo "<p>Last modified: " . date("Y-m-d H:i:s", filemtime($orderControllerPath)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ OrderController.php NOT FOUND at: $orderControllerPath</p>";
}

echo "<hr>";

// Check PaymentController
$paymentControllerPath = __DIR__ . '/controllers/PaymentController.php';
if (file_exists($paymentControllerPath)) {
    $content = file_get_contents($paymentControllerPath);
    
    echo "<h2>✓ PaymentController.php exists</h2>";
    
    if (strpos($content, 'public function show($orderId): void') !== false && 
        strpos($content, '$orderId = (int)$orderId;') !== false) {
        echo "<p style='color: green;'>✅ <strong>FIXED</strong> - show() method has type casting</p>";
    } else if (strpos($content, 'public function show(int $orderId): void') !== false) {
        echo "<p style='color: red;'>❌ <strong>NOT FIXED</strong> - show() method still has strict int type hint</p>";
        echo "<p><strong>Action:</strong> Re-upload controllers/PaymentController.php</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ <strong>UNKNOWN</strong> - Cannot determine fix status</p>";
    }
    
    echo "<p>Last modified: " . date("Y-m-d H:i:s", filemtime($paymentControllerPath)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ PaymentController.php NOT FOUND at: $paymentControllerPath</p>";
}

echo "<hr>";

// Check ProductController
$productControllerPath = __DIR__ . '/controllers/ProductController.php';
if (file_exists($productControllerPath)) {
    $content = file_get_contents($productControllerPath);
    
    echo "<h2>✓ ProductController.php exists</h2>";
    
    if (strpos($content, '$productId = (int)$product[\'id\'];') !== false) {
        echo "<p style='color: green;'>✅ <strong>FIXED</strong> - Product ID casting present</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>NOT FIXED</strong> - Product ID casting missing</p>";
        echo "<p><strong>Action:</strong> Re-upload controllers/ProductController.php</p>";
    }
    
    echo "<p>Last modified: " . date("Y-m-d H:i:s", filemtime($productControllerPath)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ ProductController.php NOT FOUND at: $productControllerPath</p>";
}

echo "<hr>";

// Check if OpCache is enabled
echo "<h2>PHP OpCache Status</h2>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status && $status['opcache_enabled']) {
        echo "<p style='color: orange;'>⚠️ OpCache is ENABLED - Old code might be cached</p>";
        echo "<p><strong>Action:</strong> Clear OpCache or restart PHP-FPM</p>";
        
        if (function_exists('opcache_reset')) {
            echo "<form method='post'>";
            echo "<button type='submit' name='clear_opcache' style='padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;'>Clear OpCache Now</button>";
            echo "</form>";
            
            if (isset($_POST['clear_opcache'])) {
                opcache_reset();
                echo "<p style='color: green;'>✅ OpCache cleared! Refresh this page.</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ OpCache is disabled or not active</p>";
    }
} else {
    echo "<p>OpCache not available</p>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If any files show ❌ NOT FIXED, re-upload them</li>";
echo "<li>If OpCache is enabled, clear it using the button above</li>";
echo "<li>Test the order view again: <a href='/build_mate/orders'>Go to Orders</a></li>";
echo "<li>After verification, DELETE this file (verify_upload.php) for security</li>";
echo "</ol>";

echo "<hr>";
echo "<p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>";
?>
