<?php
/**
 * Order Detail Page
 * Show order details and items
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Order.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$order = new Order();
$userId = getCurrentUserId();
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$orderData = $order->getById($orderId, $userId);

if (!$orderData) {
    redirect('orders.php');
}

$orderItems = $order->getItems($orderId);

$page_title = 'Order Details';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Order Details</h1>
    <p><strong>Order Number:</strong> <?php echo e($orderData['order_number']); ?></p>
    <p><strong>Order Date:</strong> <?php echo formatDateTime($orderData['created_at']); ?></p>
    <p><strong>Status:</strong> <?php echo ucfirst($orderData['status']); ?></p>
    <p><strong>Payment Status:</strong> <?php echo ucfirst($orderData['payment_status']); ?></p>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
        <!-- Order Items -->
        <div>
            <h2>Order Items</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="text-align: left; padding: 1rem;">Product</th>
                        <th style="text-align: center; padding: 1rem;">Quantity</th>
                        <th style="text-align: right; padding: 1rem;">Price</th>
                        <th style="text-align: right; padding: 1rem;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($item['product_image']); ?>" 
                                             alt="<?php echo e($item['product_name']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div>
                                        <?php if (!empty($item['product_slug'])): ?>
                                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $item['product_id']; ?>">
                                                <strong><?php echo e($item['product_name']); ?></strong>
                                            </a>
                                        <?php else: ?>
                                            <strong><?php echo e($item['product_name']); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center; padding: 1rem;">
                                <?php echo $item['quantity']; ?>
                            </td>
                            <td style="text-align: right; padding: 1rem;">
                                <?php echo formatPrice($item['price']); ?>
                            </td>
                            <td style="text-align: right; padding: 1rem;">
                                <?php echo formatPrice($item['subtotal']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="card">
                <div class="card-body">
                    <h3>Order Summary</h3>
                    <div style="margin: 1rem 0; padding: 1rem 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($orderData['subtotal']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Tax:</span>
                            <span><?php echo formatPrice($orderData['tax_amount']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Shipping:</span>
                            <span><?php echo formatPrice($orderData['shipping_amount']); ?></span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: bold; margin-top: 1rem;">
                        <span>Total:</span>
                        <span><?php echo formatPrice($orderData['total_amount']); ?></span>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h3>Shipping Address</h3>
                    <p style="white-space: pre-line;"><?php echo e($orderData['shipping_address']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 2rem;">
        <a href="<?php echo SITE_URL; ?>user/orders.php" class="btn btn-outline">Back to Orders</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

