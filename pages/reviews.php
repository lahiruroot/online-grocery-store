<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/constants.php';
require_once '../config/functions.php';
require_once '../config/db.php';

// Get database connection
$conn = getDbConnection();

// Validate connection
if (!$conn || !($conn instanceof mysqli)) {
    die("Database connection failed. Please check your database configuration.");
}
$page_title = 'Customer Reviews';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * REVIEWS_PER_PAGE;

// Get total reviews
$sql = "SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'";
$count_result = $conn->query($sql);
$count_row = $count_result->fetch_assoc();
$total_pages = ceil($count_row['count'] / REVIEWS_PER_PAGE);

// Get reviews with product info
$sql = "SELECT r.*, u.name, p.name as product_name, p.id as product_id FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        JOIN products p ON r.product_id = p.id 
        WHERE r.status = 'approved' 
        ORDER BY r.created_at DESC 
        LIMIT $offset, " . REVIEWS_PER_PAGE;
$reviews_result = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h1>Customer Reviews</h1>

    <div class="grid grid-cols-1">
        <?php while ($review = $reviews_result->fetch_assoc()): ?>
            <div class="card mb-2">
                <div class="card-body">
                    <div class="flex-between mb-2">
                        <div>
                            <strong><?php echo htmlspecialchars($review['name']); ?></strong>
                            <p style="color: #6b7280; font-size: 0.875rem;">on <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $review['product_id']; ?>"><?php echo htmlspecialchars($review['product_name']); ?></a></p>
                        </div>
                        <div style="text-align: right;">
                            <p><?php echo getStarRating($review['rating']); ?></p>
                            <small style="color: #6b7280;"><?php echo formatDate($review['created_at']); ?></small>
                        </div>
                    </div>
                    <p><?php echo htmlspecialchars($review['comment']); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="flex-center mt-4">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?> btn-small">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
