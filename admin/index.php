<?php
/**
 * Admin Dashboard
 * Main admin panel
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/User.php';

// Check if admin
if (!isAdmin()) {
    redirect('index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$product = new Product();
$order = new Order();
$user = new User();

// Get stats
$stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$totalProducts = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$totalCustomers = $stmt->fetch()['total'];

// Get recent orders
$recentOrders = $order->getAll([], 1, 5);
$recentOrdersList = $recentOrders['orders'];

$page_title = 'Admin Dashboard';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Admin Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-3 mt-4" style="gap: 1rem;">
        <div class="card">
            <div class="card-body text-center">
                <h2><?php echo $totalProducts; ?></h2>
                <p>Active Products</p>
                <a href="<?php echo SITE_URL; ?>admin/products/manage.php" class="btn btn-primary btn-small">Manage</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <h2><?php echo $totalOrders; ?></h2>
                <p>Total Orders</p>
                <a href="<?php echo SITE_URL; ?>admin/orders/manage.php" class="btn btn-primary btn-small">Manage</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <h2><?php echo $totalCustomers; ?></h2>
                <p>Customers</p>
                <a href="<?php echo SITE_URL; ?>admin/users/manage.php" class="btn btn-primary btn-small">Manage</a>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="mt-4">
                <h2>Recent Orders</h2>
        <?php if (empty($recentOrdersList)): ?>
            <p>No orders yet.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="text-align: left; padding: 1rem;">Order Number</th>
                        <th style="text-align: left; padding: 1rem;">Customer</th>
                        <th style="text-align: right; padding: 1rem;">Total</th>
                        <th style="text-align: center; padding: 1rem;">Status</th>
                        <th style="text-align: center; padding: 1rem;">Action</th>
                    </tr>
                </thead>
                    <tbody>
                    <?php foreach ($recentOrdersList as $ord): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 1rem;"><?php echo e($ord['order_number']); ?></td>
                            <td style="padding: 1rem;"><?php echo e($ord['user_name'] ?? 'N/A'); ?></td>
                            <td style="text-align: right; padding: 1rem;"><?php echo formatPrice($ord['total_amount']); ?></td>
                            <td style="text-align: center; padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 0.25rem; background: #f3f4f6;">
                                    <?php echo ucfirst($ord['status']); ?>
                                    </span>
                                </td>
                            <td style="text-align: center; padding: 1rem;">
                                <a href="<?php echo SITE_URL; ?>admin/orders/manage.php?view=<?php echo $ord['id']; ?>" class="btn btn-small btn-primary">View</a>
                            </td>
                            </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="mt-4">
        <h2>Quick Links</h2>
        <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
            <a href="<?php echo SITE_URL; ?>admin/products/manage.php" class="btn btn-outline">Manage Products</a>
            <a href="<?php echo SITE_URL; ?>admin/products/add.php" class="btn btn-outline">Add Product</a>
            <a href="<?php echo SITE_URL; ?>admin/categories/manage.php" class="btn btn-outline">Manage Categories</a>
            <a href="<?php echo SITE_URL; ?>admin/orders/manage.php" class="btn btn-outline">Manage Orders</a>
            <a href="<?php echo SITE_URL; ?>admin/users/manage.php" class="btn btn-outline">Manage Users</a>
            <a href="<?php echo SITE_URL; ?>admin/reviews/manage.php" class="btn btn-outline">Manage Reviews</a>
            <a href="<?php echo SITE_URL; ?>admin/blogs/manage.php" class="btn btn-outline">Manage Blogs</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
