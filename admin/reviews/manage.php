<?php

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

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$page_title = 'Approve Reviews';

// Handle approval/rejection
if (isset($_GET['approve'])) {
    $review_id = (int)$_GET['approve'];
    $sql = "UPDATE reviews SET status = 'approved' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
}

if (isset($_GET['reject'])) {
    $review_id = (int)$_GET['reject'];
    $sql = "UPDATE reviews SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
}

// Get pending reviews
$sql = "SELECT r.*, u.name, p.name as product_name FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        JOIN products p ON r.product_id = p.id 
        WHERE r.status = 'pending' 
        ORDER BY r.created_at DESC";
$reviews_result = $conn->query($sql);

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <h1>Approve Reviews</h1>

    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
            <tr>
                <th style="padding: 1rem; text-align: left;">Customer</th>
                <th style="padding: 1rem; text-align: left;">Product</th>
                <th style="padding: 1rem; text-align: center;">Rating</th>
                <th style="padding: 1rem; text-align: left;">Comment</th>
                <th style="padding: 1rem; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($review['name']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($review['product_name']); ?></td>
                    <td style="padding: 1rem; text-align: center;"><?php echo getStarRating($review['rating']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars(substr($review['comment'], 0, 50)); ?>...</td>
                    <td style="padding: 1rem; text-align: center;">
                        <a href="manage.php?approve=<?php echo $review['id']; ?>" class="btn btn-primary btn-small">Approve</a>
                        <a href="manage.php?reject=<?php echo $review['id']; ?>" class="btn btn-secondary btn-small">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
