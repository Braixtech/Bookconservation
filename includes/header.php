<?php
// includes/header.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arewa Conservation Platform - <?php echo $page_title ?? 'Home'; ?></title>
    <meta name="description" content="Centre of Excellence in Historical Documentation and Research for Northern Nigeria">
    <meta name="keywords" content="Arewa House, Conservation, Historical Documentation, Northern Nigeria, Manuscripts, Research">
    
    <!-- SEO Meta Tags -->
    <meta property="og:title" content="Arewa Conservation Platform">
    <meta property="og:description" content="Preserving Northern Nigeria's Historical Heritage">
    <meta property="og:image" content="images/arewa-house.jpg">
    <meta property="og:url" content="https://conservation.arewahouse.org">
    <meta name="twitter:card" content="summary_large_image">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; img-src 'self' data: https:;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://conservation.arewahouse.org">
</head>
<body>
    <!-- Security Badge -->
    <div class="security-badge">
        <i class="bi bi-shield-check"></i>
        <span>Secure Connection</span>
    </div>