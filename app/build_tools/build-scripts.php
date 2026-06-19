<?php
/**
 * LessPress - JavaScript Asset Processor
 * * Compresses script blocks, appends dynamic content hash tags,
 * and transfers execution packets to production.
 */

define('BASE_PATH', dirname(__DIR__, 2));
$srcJs = BASE_PATH . '/app/src/public_html/assets/js';
$prodJs = BASE_PATH . '/public_html/assets/js';

echo "⚡ Minifying and hashing script components...\n";

function minifyJS($js) {
    // Basic structural compression for standard vanilla components
    $js = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $js); // Remove block comments
    $js = preg_replace('!//.*!', '', $js); // Remove inline comments
    return preg_replace('/\s+/', ' ', $js); // Normalize whitespace
}

if (file_exists($srcJs)) {
    foreach (glob("$srcJs/*.js") as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        
        if (strpos($filename, '.min.js') === false) {
            $content = minifyJS($content);
        }
        
        $hash = substr(md5($content), 0, 8);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $pureName = str_replace('.min', '', pathinfo($filename, PATHINFO_FILENAME));
        
        $newFilename = strpos($filename, '.min.js') !== false 
            ? "$pureName.min.$hash.$extension" 
            : "$pureName.$hash.$extension";
            
        file_put_contents("$prodJs/$newFilename", $content);
        echo "   ✓ JS: $filename -> $newFilename\n";
    }
}