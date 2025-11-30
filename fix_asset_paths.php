<?php
/**
 * Script to replace hardcoded /build_mate/ paths with dynamic View::asset() and View::url()
 * Run this once to fix all asset paths
 */

$baseDir = __DIR__;
$viewsDir = $baseDir . '/views';

function replaceInFile($filePath) {
    $content = file_get_contents($filePath);
    $original = $content;
    
    // Replace CSS/JS asset paths
    $content = preg_replace(
        '/(href|src)=["\']\/build_mate\/assets\/([^"\']+)["\']/',
        '$1="<?= \\App\\View::asset(\'assets/$2\') ?>"',
        $content
    );
    
    // Replace route URLs (but not assets)
    $content = preg_replace(
        '/href=["\']\/build_mate\/([^"\']+)["\']/',
        'href="<?= \\App\\View::url(\'/$1\') ?>"',
        $content
    );
    
    // Fix action attributes in forms
    $content = preg_replace(
        '/action=["\']\/build_mate\/([^"\']+)["\']/',
        'action="<?= \\App\\View::url(\'/$1\') ?>"',
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        return true;
    }
    
    return false;
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        if (replaceInFile($file->getPathname())) {
            $count++;
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}

echo "\nFixed $count files.\n";

