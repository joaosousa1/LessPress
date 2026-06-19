<?php
/**
 * LessPress - Reference Engine & Compiler Core
 */

define('BASE_PATH', dirname(__DIR__, 2));
$prodAssets = BASE_PATH . '/public_html/assets';
$srcViews = BASE_PATH . '/app/src/views';
$distViews = BASE_PATH . '/app/dist-views/views'; // Kept the aligned path from our previous fix

echo "🔄 Resolving cross-reference pipelines into view layers...\n";

$replacements = [];

/**
 * Strips the 8-character hash from production filenames to discover their original dev names
 */
function buildTranslationMap($dir, $assetType, &$map) {
    if (!file_exists($dir)) return;
    
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        
        // Robust Regex: Finds a dot, followed by exactly 8 hex characters, followed by the extension
        // Example: logo.b87a1c.png -> logo.png
        // Example: style.f3b2a1.css -> style.css
        $originalName = preg_replace('/\.([a-f0-9]{8})\./', '.', $file);
        
        $map["/assets/$assetType/$originalName"] = "/assets/$assetType/$file";
    }
}

// Map production assets to build the dictionary using the unified single-line cleaner
buildTranslationMap("$prodAssets/css", 'css', $replacements);
buildTranslationMap("$prodAssets/js", 'js', $replacements);
buildTranslationMap("$prodAssets/images", 'images', $replacements);

// --- DEBUG PACK (Optional) ---
// Uncomment the lines below if you want to see exactly what the dictionary is translating:
// echo "Loaded Translations:\n";
// print_r($replacements);
// ------------------------------

/**
 * Compiles view layers recursively applying the translation dictionary
 */
function compileTemplates($src, $dist, $dictionary) {
    foreach (scandir($src) as $item) {
        if ($item === '.' || $item === '..') continue;
        $srcPath = "$src/$item";
        $distPath = "$dist/$item";
        
        if (is_dir($srcPath)) {
            if (!file_exists($distPath)) mkdir($distPath, 0755, true);
            compileTemplates($srcPath, $distPath, $dictionary);
        } else {
            $content = file_get_contents($srcPath);
            $content = str_replace(array_keys($dictionary), array_values($dictionary), $content);
            file_put_contents($distPath, $content);
            echo "   ✓ Compiled Template: app/dist-views/views/" . str_replace(BASE_PATH . '/app/dist-views/views/', '', $distPath) . "\n";
        }
    }
}

// 1. Process application layouts/views
if (file_exists($srcViews)) {
    compileTemplates($srcViews, $distViews, $replacements);
}

// 2. Process web server entry points (index.php, install.php, etc)
$srcPublicHtml = BASE_PATH . '/app/src/public_html';
$destPublicHtml = BASE_PATH . '/public_html';

foreach (scandir($srcPublicHtml) as $item) {
    if ($item === '.' || $item === '..' || $item === 'assets') continue;
    $srcFile = "$srcPublicHtml/$item";
    $destFile = "$destPublicHtml/$item";
    
    if (is_file($srcFile)) {
        $content = file_get_contents($srcFile);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        file_put_contents($destFile, $content);
        echo "   ✓ Provisioned Web Core: public_html/$item\n";
    }
}