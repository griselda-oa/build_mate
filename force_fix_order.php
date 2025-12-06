<?php
/**
 * Force Fix OrderController - More Aggressive Approach
 */

$SECRET_KEY = 'force_fix_' . date('Ymd');
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die("Access denied. Use: ?key=" . $SECRET_KEY);
}

echo "<h1>Force Fix OrderController</h1>";
echo "<p>Secret Key: <code>$SECRET_KEY</code></p><hr>";

$path = __DIR__ . '/controllers/OrderController.php';

if (!file_exists($path)) {
    die("<p style='color:red;'>ERROR: OrderController.php not found</p>");
}

// Backup
$backup = $path . '.backup.force.' . date('YmdHis');
copy($path, $backup);
echo "<p>✅ Backup: $backup</p>";

// Read file
$content = file_get_contents($path);
echo "<h2>Current File Analysis</h2>";

// Check what we have
if (strpos($content, 'public function show(int $id): void') !== false) {
    echo "<p style='color:red;'>❌ Found: <code>public function show(int \$id): void</code></p>";
    echo "<p>This needs to be fixed!</p>";
} else if (strpos($content, 'public function show($id): void') !== false) {
    echo "<p style='color:green;'>✅ Found: <code>public function show(\$id): void</code></p>";
    
    // Check if casting exists
    if (strpos($content, '$id = (int)$id;') !== false) {
        echo "<p style='color:green;'>✅ Type casting exists</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Type casting missing</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠️ show() method not found in expected format</p>";
}

echo "<hr>";
echo "<h2>Applying Aggressive Fix</h2>";

$changes = 0;

// Method 1: Fix with strict type hint
$patterns = [
    // show() method
    [
        'search' => '/public function show\(int \$id\): void\s*\{/',
        'replace' => "public function show(\$id): void\n    {\n        \$id = (int)\$id;",
        'name' => 'show()'
    ],
    // getStatus() method
    [
        'search' => '/public function getStatus\(int \$id\): void\s*\{/',
        'replace' => "public function getStatus(\$id): void\n    {\n        \$id = (int)\$id;",
        'name' => 'getStatus()'
    ],
    // trackDelivery() method
    [
        'search' => '/public function trackDelivery\(int \$orderId\): void\s*\{/',
        'replace' => "public function trackDelivery(\$orderId): void\n    {\n        \$orderId = (int)\$orderId;",
        'name' => 'trackDelivery()'
    ],
    // confirmDelivery() method
    [
        'search' => '/public function confirmDelivery\(int \$id\): void\s*\{/',
        'replace' => "public function confirmDelivery(\$id): void\n    {\n        \$id = (int)\$id;",
        'name' => 'confirmDelivery()'
    ],
    // dispute() method
    [
        'search' => '/public function dispute\(int \$id\): void\s*\{/',
        'replace' => "public function dispute(\$id): void\n    {\n        \$id = (int)\$id;",
        'name' => 'dispute()'
    ],
    // invoice() method
    [
        'search' => '/public function invoice\(int \$id\): void\s*\{/',
        'replace' => "public function invoice(\$id): void\n    {\n        \$id = (int)\$id;",
        'name' => 'invoice()'
    ]
];

foreach ($patterns as $pattern) {
    $newContent = preg_replace($pattern['search'], $pattern['replace'], $content);
    if ($newContent !== $content) {
        $content = $newContent;
        echo "<p style='color:green;'>✅ Fixed {$pattern['name']}</p>";
        $changes++;
    }
}

if ($changes > 0) {
    file_put_contents($path, $content);
    echo "<hr>";
    echo "<p style='color:green;'><strong>✅ Applied $changes fix(es)</strong></p>";
    
    // Clear OpCache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "<p style='color:green;'>✅ OpCache cleared</p>";
    }
    
    echo "<hr>";
    echo "<h2>✅ SUCCESS!</h2>";
    echo "<p>OrderController.php has been fixed</p>";
} else {
    echo "<hr>";
    echo "<p style='color:orange;'>⚠️ No changes applied</p>";
    echo "<p>The file might already be correct, or the patterns don't match</p>";
    
    // Show a snippet of the show() method
    echo "<h3>Debug: show() method snippet</h3>";
    preg_match('/public function show.*?\{.*?\n.*?\n.*?\n/s', $content, $matches);
    if (!empty($matches[0])) {
        echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Test: <a href='/build_mate/orders'>Go to Orders</a> → Click 'View Details'</li>";
echo "<li>If still 404, check error logs on server</li>";
echo "<li><strong>DELETE THIS FILE immediately!</strong></li>";
echo "</ol>";

echo "<p><small>Executed: " . date('Y-m-d H:i:s') . "</small></p>";
?>
