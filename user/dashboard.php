<?php

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/Wishlist.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to access dashboard');
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$order = new Order();
$cart = new Cart();
$wishlist = new Wishlist();

$userId = getCurrentUserId();

// Get recent orders
$ordersResult = $order->getUserOrders($userId, 1, 5);
$recentOrders = $ordersResult['orders'];

// Get cart count
$cartCount = $cart->getCount($userId);

// Get wishlist count
$wishlistCount = $wishlist->getCount($userId);

$page_title = 'Dashboard';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Welcome, <?php echo e($_SESSION['user_name']); ?>!</h1>

    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?> mt-4">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Quick Stats -->
    <div class="grid grid-cols-3 mt-4" style="gap: 1rem;">
        <div class="card">
            <div class="card-body text-center">
                <h3><?php echo $cartCount; ?></h3>
                <p>Items in Cart</p>
                <a href="<?php echo SITE_URL; ?>cart/view-cart.php" class="btn btn-primary btn-small">View Cart</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <h3><?php echo count($recentOrders); ?></h3>
                <p>Recent Orders</p>
                <a href="<?php echo SITE_URL; ?>user/orders.php" class="btn btn-primary btn-small">View Orders</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <h3><?php echo $wishlistCount; ?></h3>
                <p>Wishlist Items</p>
                <a href="<?php echo SITE_URL; ?>user/wishlist.php" class="btn btn-primary btn-small">View Wishlist</a>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="mt-4">
        <h2>Recent Orders</h2>

        <?php
        // Only get the 5 most recent orders, sort by newest first
        $limitOrders = array_slice(
            (!empty($recentOrders) ? array_values(
                // sort by 'created_at' descending (most recent first)
                usort($recentOrders, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                }) ? $recentOrders : $recentOrders
            ) : []),
            0,
            5
        );
        ?>

        <?php if (empty($limitOrders)): ?>
            <div class="alert alert-info mt-4">
                <p>You have no orders yet. <a href="<?php echo SITE_URL; ?>pages/products.php">Start Shopping</a></p>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="text-align: left; padding: 1rem;">Order Number</th>
                        <th style="text-align: left; padding: 1rem;">Date</th>
                        <th style="text-align: right; padding: 1rem;">Total</th>
                        <th style="text-align: center; padding: 1rem;">Status</th>
                        <th style="text-align: center; padding: 1rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($limitOrders as $ord): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 1rem;">
                                <strong><?php echo e($ord['order_number']); ?></strong>
                            </td>
                            <td style="padding: 1rem;">
                                <?php echo formatDate($ord['created_at']); ?>
                            </td>
                            <td style="text-align: right; padding: 1rem;">
                                <?php echo formatPrice($ord['total_amount']); ?>
                            </td>
                            <td style="text-align: center; padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 0.25rem; background: #f3f4f6; color: #374151;">
                                    <?php echo ucfirst($ord['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: center; padding: 1rem;">
                                <a href="<?php echo SITE_URL; ?>user/order-detail.php?id=<?php echo $ord['id']; ?>" class="btn btn-small btn-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (isAdmin()): ?>
                <div style="margin-top: 1rem;">
                    <a href="<?php echo SITE_URL; ?>admin/orders/manage.php" class="btn btn-outline">View All Orders</a>
                </div>
            <?php else: ?>
                <div style="margin-top: 1rem;">
                    <a href="<?php echo SITE_URL; ?>user/orders.php" class="btn btn-outline">View My Orders</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    <!-- Quick Links -->
    <div class="mt-4">
        <h2>Quick Links</h2>
        <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
            <a href="<?php echo SITE_URL; ?>user/profile.php" class="btn btn-outline">Edit Profile</a>
            <a href="<?php echo SITE_URL; ?>user/orders.php" class="btn btn-outline">My Orders</a>
            <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-outline">Continue Shopping</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
