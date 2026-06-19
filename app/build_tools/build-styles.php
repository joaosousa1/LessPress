<?php
/**
 * LessPress - CSS Asset Processor
 * * Strips comments/whitespace from source stylesheets,
 * injects a content-based unique hash, and saves to production.
 */

define('BASE_PATH', dirname(__DIR__, 2));
$srcCss = BASE_PATH . '/app/src/public_html/assets/css';
$prodCss = BASE_PATH . '/public_html/assets/css';

echo "🎨 Minifying and hashing stylesheets...\n";

function minifyCSS($css) {
    $css = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $css);
    $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    '), '', $css);
    return str_replace(array('{ ', ' }', '; ', ': '), array('{', '}', ';', ':'), $css);
}

if (file_exists($srcCss)) {
    foreach (glob("$srcCss/*.css") as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        
        if (strpos($filename, '.min.css') === false) {
            $content = minifyCSS($content);
        }
        
        $hash = substr(md5($content), 0, 8);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $pureName = pathinfo($filename, PATHINFO_FILENAME);
        
        if (strpos($pureName, '.min') !== false) {
            $pureName = str_replace('.min', '', $pureName);
            $newFilename = "$pureName.min.$hash.$extension";
        } else {
            $newFilename = "$pureName.$hash.$extension";
        }
        
        file_put_contents("$prodCss/$newFilename", $content);
        echo "   ✓ CSS: $filename -> $newFilename\n";
    }
}