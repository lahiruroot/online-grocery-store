<?php
/**
 * Header Template
 * Common header for all pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(dirname(__FILE__)) . '/config/constants.php';
require_once dirname(dirname(__FILE__)) . '/config/functions.php';

// Get cart count for logged in users
$cartCount = 0;
if (isLoggedIn()) {
    require_once dirname(dirname(__FILE__)) . '/classes/Cart.php';
    $cart = new Cart();
    $cartCount = $cart->getCount(getCurrentUserId());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo SITE_URL; ?>">
    <title><?php echo isset($page_title) ? e($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="public/css/<?php echo e($extra_css); ?>">
    <?php endif; ?>
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="<?php echo SITE_URL; ?>" class="navbar-brand">FreshCart</a>
            <ul class="navbar-menu">
                <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>pages/products.php">Products</a></li>
                <li><a href="<?php echo SITE_URL; ?>pages/categories.php">Categories</a></li>
                <li><a href="<?php echo SITE_URL; ?>pages/blogs.php">Blog</a></li>
                <li><a href="<?php echo SITE_URL; ?>pages/about.php">About</a></li>
            </ul>
            <div class="navbar-right">
                <a href="<?php echo SITE_URL; ?>cart/view-cart.php" class="btn btn-outline btn-small">
                    Cart <span class="cart-badge"><?php echo $cartCount; ?></span>
                </a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo SITE_URL; ?>admin/index.php" class="btn btn-outline btn-small" style="background-color: #10b981; color: white;">Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="btn btn-secondary btn-small">Dashboard</a>
                    <a href="<?php echo SITE_URL; ?>auth/logout.php" class="btn btn-secondary btn-small">Logout</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>auth/login.php" class="btn btn-secondary btn-small">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>
