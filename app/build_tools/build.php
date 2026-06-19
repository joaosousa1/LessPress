<?php
/**
 * LessPress - Master Build Orchestrator
 */

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_HTML_DIR', BASE_PATH . '/public_html');
define('DIST_VIEWS_DIR', BASE_PATH . '/app/dist-views');

$scripts = [
    'build-styles.php',
    'build-scripts.php',
    'build-images.php',
    'build-robots.php',
    'build-sitemap.php',
    'build-refs.php',
    'build-manifest.php'
];

echo "🧹 [1/3] Cleaning up previous build artifacts (Keeping root directories)...\n";

/**
 * Clears ONLY the contents of a directory without deleting the directory itself.
 * This prevents breaking Docker volume mounts (Inode mismatch).
 */
function clearDirectoryContents($dir) {
    if (!file_exists($dir) || !is_dir($dir)) return;

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            // Delete subdirectory contents and the subfolder itself
            clearDirectoryContents($path);
            rmdir($path);
        } else {
            // Delete file
            unlink($path);
        }
    }
}

// Ensure base directories exist before cleaning contents
if (!file_exists(PUBLIC_HTML_DIR)) mkdir(PUBLIC_HTML_DIR, 0755, true);
if (!file_exists(DIST_VIEWS_DIR)) mkdir(DIST_VIEWS_DIR, 0755, true);

// Clear inner contents without destroying the root folders linked to Docker
clearDirectoryContents(PUBLIC_HTML_DIR);
clearDirectoryContents(DIST_VIEWS_DIR);

echo "   ✓ Staging environments cleared smoothly.\n";

echo "\n📁 [2/3] Provisioning fresh production sub-structures...\n";
// Create only the asset sub-folders inside public_html (since public_html already exists)
if (!file_exists(PUBLIC_HTML_DIR . '/assets/css')) mkdir(PUBLIC_HTML_DIR . '/assets/css', 0755, true);
if (!file_exists(PUBLIC_HTML_DIR . '/assets/js')) mkdir(PUBLIC_HTML_DIR . '/assets/js', 0755, true);
if (!file_exists(PUBLIC_HTML_DIR . '/assets/images')) mkdir(PUBLIC_HTML_DIR . '/assets/images', 0755, true);
echo "   ✓ Asset distribution paths established.\n";

echo "\n🚀 [3/3] Launching modular compilation steps...\n";

foreach ($scripts as $script) {
    $scriptPath = __DIR__ . '/' . $script;
    if (file_exists($scriptPath)) {
        echo "\n--------------------------------------------------\n";
        echo "📦 Step: $script\n";
        echo "--------------------------------------------------\n";
        
        passthru("php " . escapeshellarg($scriptPath), $returnCode);
        
        if ($returnCode !== 0) {
            die("\n❌ Build pipeline aborted due to failure in: $script\n");
        }
    } else {
        echo "⚠️ Skipping optional or missing component: $script\n";
    }
}

echo "\n✨ LESSPRESS PRODUCTION BUILD READY FOR DEPLOYMENT! ✨\n";