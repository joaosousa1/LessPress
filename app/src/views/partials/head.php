<?php
// app/src/views/partials/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CONFIGURATION: Global Site Identity
$siteName = "LessPress";

// 2. ENVIRONMENT & URL RESOLUTION ENGINE
$siteUrl = "https://" . $_SERVER['HTTP_HOST'];

// 3. DYNAMIC TITLE PACK
// If $pageTitle is set (e.g., "Contactos"), it creates "Contactos | Empresa XPTO"
// If not set, it uses a generic main title
$seoTitle = isset($pageTitle)
    ? htmlspecialchars($pageTitle) . " | " . $siteName
    : "Simple CMS | " . $siteName;

// 4. DYNAMIC SEO DESCRIPTION FALLBACKS
// If the controller specifies a description, use it. 
// If not, fall back to the GENERAL site description (ideal for the Home Page)
$seoDescription = isset($pageDescription)
    ? htmlspecialchars($pageDescription)
    : "A " . $siteName . "text about page or company";

$seoKeywords = isset($pageKeywords) ? htmlspecialchars($pageKeywords) : "php, security, framework, xpto";
$seoImage    = isset($pageImage) ? htmlspecialchars($pageImage) : "/assets/images/default-share.jpg";
$seoUrl      = $siteUrl . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title><?php echo $seoTitle; ?></title>

    <meta name="description" content="<?php echo $seoDescription; ?>">
    <meta name="keywords" content="<?php echo $seoKeywords; ?>">
    <meta name="author" content="CoreSystem Engineering">
    <link rel="canonical" href="<?php echo $seoUrl; ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $seoUrl; ?>">
    <meta property="og:title" content="<?php echo $seoTitle; ?>">
    <meta property="og:description" content="<?php echo $seoDescription; ?>">
    <meta property="og:image" content="<?php echo $siteUrl . $seoImage; ?>">
    <meta property="og:site_name" content="CoreSystem">
    <meta property="og:locale" content="en_US">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $seoUrl; ?>">
    <meta name="twitter:title" content="<?php echo $seoTitle; ?>">
    <meta name="twitter:description" content="<?php echo $seoDescription; ?>">
    <meta name="twitter:image" content="<?php echo $siteUrl . $seoImage; ?>">

    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

    <link rel="stylesheet" href="/assets/css/pico.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<style>
    #menu>ul li {
        display: inline-flex;
        list-style-type: none;
    }
</style>