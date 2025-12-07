<?php
/**
 * Emergency Deployment Script
 * This will directly patch the PaymentController on the server
 * Upload this file and run it ONCE, then delete it
 */

// Security check - only run if accessed with secret key
$SECRET_KEY = 'buildmate_deploy_' . date('Ymd');
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die("Access denied. Use: ?key=" . $SECRET_KEY);
}

echo "<h1>Emergency Deployment - PaymentController Fix</h1>";
echo "<p>Secret Key: <code>$SECRET_KEY</code></p><hr>";

$controllerPath = __DIR__ . '/controllers/PaymentController.php';

if (!file_exists($controllerPath)) {
    die("<p style='color:red;'>ERROR: PaymentController.php not found at: $controllerPath</p>");
}

echo "<h2>Step 1: Backup Current File</h2>";
$backupPath = $controllerPath . '.backup.' . date('YmdHis');
if (copy($controllerPath, $backupPath)) {
    echo "<p style='color:green;'>✅ Backup created: $backupPath</p>";
} else {
    die("<p style='color:red;'>❌ Failed to create backup</p>");
}

echo "<h2>Step 2: Read Current File</h2>";
$content = file_get_contents($controllerPath);
$originalSize = strlen($content);
echo "<p>Original file size: $originalSize bytes</p>";

echo "<h2>Step 3: Apply Fixes</h2>";
$fixes = 0;

// Fix 1: show() method
if (strpos($content, 'public function show(int $orderId): void') !== false) {
    $content = str_replace(
        'public function show(int $orderId): void',
        'public function show($orderId): void',
        $content
    );
    
    // Add type casting after the opening brace
    $content = preg_replace(
        '/(public function show\(\$orderId\): void\s*\{)/',
        "$1\n        \$orderId = (int)\$orderId;",
        $content
    );
    
    echo "<p style='color:green;'>✅ Fixed show() method</p>";
    $fixes++;
} else {
    echo "<p style='color:orange;'>⚠️ show() method already fixed or not found</p>";
}

// Fix 2: success() method
if (strpos($content, 'public function success(int $orderId): void') !== false) {
    $content = str_replace(
        'public function success(int $orderId): void',
        'public function success($orderId): void',
        $content
    );
    
    $content = preg_replace(
        '/(public function success\(\$orderId\): void\s*\{)/',
        "$1\n        \$orderId = (int)\$orderId;",
        $content
    );
    
    echo "<p style='color:green;'>✅ Fixed success() method</p>";
    $fixes++;
} else {
    echo "<p style='color:orange;'>⚠️ success() method already fixed or not found</p>";
}

echo "<h2>Step 4: Write Fixed File</h2>";
if ($fixes > 0) {
    $newSize = strlen($content);
    echo "<p>New file size: $newSize bytes</p>";
    
    if (file_put_contents($controllerPath, $content)) {
        echo "<p style='color:green;'>✅ File updated successfully!</p>";
        echo "<p><strong>$fixes fixes applied</strong></p>";
    } else {
        echo "<p style='color:red;'>❌ Failed to write file</p>";
        echo "<p>Restoring backup...</p>";
        copy($backupPath, $controllerPath);
    }
} else {
    echo "<p style='color:orange;'>⚠️ No fixes needed or file already updated</p>";
}

echo "<h2>Step 5: Clear OpCache</h2>";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color:green;'>✅ OpCache cleared</p>";
} else {
    echo "<p style='color:orange;'>⚠️ OpCache not available</p>";
}

echo "<hr>";
echo "<h2>Verification</h2>";
$updatedContent = file_get_contents($controllerPath);
if (strpos($updatedContent, 'public function show($orderId): void') !== false &&
    strpos($updatedContent, '$orderId = (int)$orderId;') !== false) {
    echo "<p style='color:green;'>✅ <strong>SUCCESS!</strong> PaymentController is now fixed</p>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Test checkout: <a href='/build_mate/checkout'>Go to Checkout</a></li>";
    echo "<li>Test payment page: <a href='/build_mate/payment/1'>Test Payment Page</a></li>";
    echo "<li><strong>DELETE THIS FILE (deploy_fix.php) immediately for security!</strong></li>";
    echo "</ol>";
} else {
    echo "<p style='color:red;'>❌ Fix verification failed</p>";
    echo "<p>Manual intervention required</p>";
}

echo "<hr>";
echo "<p><small>Executed: " . date('Y-m-d H:i:s') . "</small></p>";
?>
