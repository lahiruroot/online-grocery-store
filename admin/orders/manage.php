<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/constants.php';
require_once '../../config/functions.php';
require_once '../../config/db.php';

// Get database connection
$conn = getDbConnection();

// Validate connection
if (!$conn || !($conn instanceof mysqli)) {
    die("Database connection failed. Please check your database configuration.");
}

if (!isAdmin()) {
    redirect('../index.php');
}

$page_title = 'Manage Orders';

// Handle status update
if (isset($_GET['update_status'])) {
    $order_id = (int)$_GET['order_id'] ?? 0;
    $status = sanitize($_GET['update_status'] ?? '');
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
}

// Get orders
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$orders_result = $conn->query($sql);

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <h1>Manage Orders</h1>

    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
            <tr>
                <th style="padding: 1rem; text-align: left;">Order ID</th>
                <th style="padding: 1rem; text-align: left;">Total</th>
                <th style="padding: 1rem; text-align: left;">Status</th>
                <th style="padding: 1rem; text-align: left;">Date</th>
                <th style="padding: 1rem; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($order['order_number']); ?></td>
                    <td style="padding: 1rem;"><?php echo formatPrice($order['total_amount']); ?></td>
                    <td style="padding: 1rem;">
                        <span style="background-color: #dbeafe; color: #0c4a6e; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem;">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;"><?php echo formatDate($order['created_at']); ?></td>
                    <td style="padding: 1rem; text-align: center;">
                        <select style="padding: 0.5rem;" onchange="window.location.href='manage.php?order_id=<?php echo $order['id']; ?>&update_status=' + this.value">
                            <option value="">Update Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
