<?php
/**
 * LessPress - Robots.txt Generator
 * * Dynamically constructs the production robots.txt file during the build phase,
 * protecting private virtual RBAC routes from search engine crawlers.
 */

define('BASE_PATH', dirname(__DIR__, 2));
$robotsFile = BASE_PATH . '/public_html/robots.txt';

echo "🤖 Generating production robots.txt...\n";

// Capture the dynamic host for the absolute sitemap reference
$siteUrl = "https://" . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com');

$content = "# LessPress Automated Robots.txt\n";
$content .= "User-agent: *\n";

// 1. Block Private Virtual Routes (Managed by the Router and restricted by RBAC)
// These exist as URLs, so we must explicitly tell bots not to crawl them.
$content .= "Disallow: /admin/\n";
$content .= "Disallow: /admin\n";
$content .= "Disallow: /editor/\n";
$content .= "Disallow: /editor\n";
$content .= "Disallow: /user/settings\n";

// 2. Block Core Authentication and Setup Entry Points
$content .= "Disallow: /login\n";
$content .= "Disallow: /install.php\n";

// 3. Explicitly allow everything else (Public areas)
$content .= "Allow: /\n\n";

// 4. Dynamic Sitemap Reference
$content .= "Sitemap: $siteUrl/sitemap.xml\n";

file_put_contents($robotsFile, $content);
echo "   ✓ Robots.txt successfully generated at: /public_html/robots.txt\n";