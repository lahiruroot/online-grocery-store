<?php
/**
 * User Orders Page
 * List all user orders
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
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$result = $order->getUserOrders($userId, $page, ITEMS_PER_PAGE);
$orders = $result['orders'];
$totalPages = $result['pages'];

$page_title = 'My Orders';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>My Orders</h1>

    <?php if (empty($orders)): ?>
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
                <?php foreach ($orders as $ord): ?>
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
                            <a href="<?php echo SITE_URL; ?>user/order-detail.php?id=<?php echo $ord['id']; ?>" class="btn btn-small btn-primary">View Details</a>
                            </td>
                        </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination mt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-link active"><?php echo $i; ?></span>
        <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
