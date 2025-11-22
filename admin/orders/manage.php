<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Order.php';

if (!isAdmin()) {
    redirect('index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$order = new Order();

// Handle status update
if (isset($_GET['update_status']) && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $status = sanitize($_GET['update_status']);
    
    if ($order_id > 0 && !empty($status)) {
        $order->updateStatus($order_id, $status);
        redirect('admin/orders/manage.php');
    }
}

// Get orders
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$result = $order->getAll([], $page, ITEMS_PER_PAGE);
$orders = $result['orders'];
$totalPages = $result['pages'];

$page_title = 'Manage Orders';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <h1>Manage Orders</h1>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            <p>No orders found.</p>
        </div>
    <?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
            <tr>
                <th style="padding: 1rem; text-align: left;">Order ID</th>
                <th style="padding: 1rem; text-align: left;">Customer</th>
                <th style="padding: 1rem; text-align: left;">Total</th>
                <th style="padding: 1rem; text-align: left;">Status</th>
                <th style="padding: 1rem; text-align: left;">Date</th>
                <th style="padding: 1rem; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order_item): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($order_item['order_number']); ?></td>
                    <td style="padding: 1rem;">
                        <?php echo htmlspecialchars($order_item['user_name'] ?? 'N/A'); ?>
                        <?php if (!empty($order_item['user_email'])): ?>
                            <br><small style="color: #6b7280;"><?php echo htmlspecialchars($order_item['user_email']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem;"><?php echo formatPrice($order_item['total_amount']); ?></td>
                    <td style="padding: 1rem;">
                        <span style="background-color: #dbeafe; color: #0c4a6e; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem;">
                            <?php echo ucfirst($order_item['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;"><?php echo formatDate($order_item['created_at']); ?></td>
                    <td style="padding: 1rem; text-align: center;">
                        <select style="padding: 0.5rem;" onchange="if(this.value) window.location.href='<?php echo SITE_URL; ?>admin/orders/manage.php?order_id=<?php echo $order_item['id']; ?>&update_status=' + this.value">
                            <option value="">Update Status</option>
                            <option value="pending" <?php echo $order_item['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order_item['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order_item['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order_item['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order_item['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
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

<?php require_once '../../includes/footer.php'; ?>
