<?php
/**
 * LessPress - Sitemap.xml Generator
 * Dynamically maps public application routes into a production-ready XML map.
 */

define('BASE_PATH', dirname(__DIR__, 2));
$sitemapFile = BASE_PATH . '/public_html/sitemap.xml';

echo "🗺️ Generating production sitemap.xml...\n";

// Fallback domain if running from CLI environment without HTTP_HOST
$siteUrl = "https://" . ($_SERVER['HTTP_HOST'] ?? 'empraxpto.com');
$currentDate = date('Y-m-d');

// 1. Define your public routes here (paths accessible to search engines)
$publicRoutes = [
    '/'          => ['priority' => '1.0', 'changefreq' => 'daily'],
    '/home'      => ['priority' => '0.9', 'changefreq' => 'daily'],
    '/contacts'  => ['priority' => '0.7', 'changefreq' => 'monthly'],
    '/products'  => ['priority' => '0.8', 'changefreq' => 'weekly'],
    '/members'   => ['priority' => '0.5', 'changefreq' => 'weekly'] // Public directory if applicable
];

// 2. Start building the XML Structure
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($publicRoutes as $route => $meta) {
    // Clean the route concatenation to avoid double slashes
    $cleanRoute = ($route === '/') ? '' : $route;
    $fullUrl = $siteUrl . $cleanRoute;
    
    $xml .= "    <url>\n";
    $xml .= "        <loc>" . htmlspecialchars($fullUrl) . "</loc>\n";
    $xml .= "        <lastmod>{$currentDate}</lastmod>\n";
    $xml .= "        <changefreq>{$meta['changefreq']}</changefreq>\n";
    $xml .= "        <priority>{$meta['priority']}</priority>\n";
    $xml .= "    </url>\n";
}

$xml .= '</urlset>';

// 3. Save to public root
file_put_contents($sitemapFile, $xml);
echo "   ✓ Sitemap.xml successfully generated at: /public_html/sitemap.xml\n";