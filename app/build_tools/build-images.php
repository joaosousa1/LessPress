<?php
/**
 * LessPress - Image Asset Processor
 * * Analyzes image footprints, generates explicit cache-busting hashes
 * for visual resources (PNG, JPG, SVG, GIF), and saves to production.
 */

define('BASE_PATH', dirname(__DIR__, 2));
$srcImages = BASE_PATH . '/app/src/public_html/assets/images';
$prodImages = BASE_PATH . '/public_html/assets/images';

echo "🖼️ Processing and hashing visual media assets...\n";

if (file_exists($srcImages)) {
    // Scan all supported common web image formats
    $files = array_merge(
        glob("$srcImages/*.{jpg,jpeg,png,gif,svg}", GLOB_BRACE) ?: []
    );

    foreach ($files as $file) {
        $filename = basename($file);
        $hash = substr(md5_file($file), 0, 8);
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $pureName = pathinfo($filename, PATHINFO_FILENAME);
        
        $newFilename = "$pureName.$hash.$extension";
        
        copy($file, "$prodImages/$newFilename");
        echo "   ✓ Image: $filename -> $newFilename\n";
    }
}