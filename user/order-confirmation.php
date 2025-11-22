<?php


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
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    redirect('dashboard.php');
}

$orderData = $order->getById($orderId, $userId);

if (!$orderData) {
    redirect('dashboard.php');
}

$orderItems = $order->getItems($orderId);

$page_title = 'Order Confirmation';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="card" style="max-width: 800px; margin: 0 auto; text-align: center;">
        <div class="card-body">
            <div style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;">✓</div>
            <h1>Order Confirmed!</h1>
            <p style="font-size: 1.25rem; color: #6b7280; margin-bottom: 2rem;">
                Thank you for your order. We've received your order and will begin processing it shortly.
            </p>

            <div style="background: #f3f4f6; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: left;">
                <p><strong>Order Number:</strong> <?php echo e($orderData['order_number']); ?></p>
                <p><strong>Order Date:</strong> <?php echo formatDateTime($orderData['created_at']); ?></p>
                <p><strong>Total Amount:</strong> <?php echo formatPrice($orderData['total_amount']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $orderData['payment_method'])); ?></p>
    </div>

            <div style="text-align: left; margin-bottom: 2rem;">
                <h3>Order Items:</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="text-align: left; padding: 0.5rem;">Product</th>
                            <th style="text-align: center; padding: 0.5rem;">Quantity</th>
                            <th style="text-align: right; padding: 0.5rem;">Price</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 0.5rem;"><?php echo e($item['product_name']); ?></td>
                                <td style="text-align: center; padding: 0.5rem;"><?php echo $item['quantity']; ?></td>
                                <td style="text-align: right; padding: 0.5rem;"><?php echo formatPrice($item['subtotal']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo SITE_URL; ?>user/order-detail.php?id=<?php echo $orderId; ?>" class="btn btn-primary">View Order Details</a>
                <a href="<?php echo SITE_URL; ?>user/orders.php" class="btn btn-outline">View All Orders</a>
                <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
