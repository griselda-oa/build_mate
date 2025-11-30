<?php
/**
 * Script to fix all hardcoded /build_mate/ paths
 * Run: php fix_all_paths.php
 */

$files = [
    // Controllers - replace redirects
    'controllers/ProductController.php',
    'controllers/CartController.php',
    'controllers/PaymentController.php',
    'controllers/SupplierController.php',
    'controllers/AdvertisementController.php',
    'controllers/AdminOrderController.php',
    'controllers/OrderController.php',
    'controllers/LogisticsController.php',
    'controllers/AdminController.php',
    'controllers/PremiumController.php',
    'controllers/HomeController.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace $this->redirect('/build_mate/...') with $this->redirect('/...')
    $content = preg_replace(
        "/\$this->redirect\(['\"]\/build_mate\/([^'\"]+)['\"]\)/",
        "\$this->redirect('/$1')",
        $content
    );
    
    // Replace redirect('/build_mate/...') with redirect('/...')
    $content = preg_replace(
        "/redirect\(['\"]\/build_mate\/([^'\"]+)['\"]\)/",
        "redirect('/$1')",
        $content
    );
    
    // Replace '/build_mate/storage/' with View::asset() or keep as is (storage paths are relative)
    // Actually, storage paths should use View::asset() or be relative
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "Done fixing controllers!\n";

