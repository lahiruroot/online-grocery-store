<?php
// Start session first
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

$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog
$sql = "SELECT b.*, u.name as author_name FROM blogs b JOIN users u ON b.author_id = u.id WHERE b.id = ? AND b.status = 'published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$blog_result = $stmt->get_result();

if ($blog_result->num_rows === 0) {
    redirect('blogs.php');
}

$blog = $blog_result->fetch_assoc();
$page_title = htmlspecialchars($blog['title']);

require_once '../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <article>
        <?php if ($blog['image']): ?>
            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo htmlspecialchars($blog['image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" style="width: 100%; height: 400px; object-fit: cover; border-radius: 0.5rem; margin-bottom: 2rem;">
        <?php endif; ?>

        <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
        <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 2rem;">
            By <strong><?php echo htmlspecialchars($blog['author_name']); ?></strong> on <?php echo formatDate($blog['created_at']); ?>
        </p>

        <div style="line-height: 1.8; color: #374151; margin-bottom: 2rem;">
            <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
        </div>

        <a href="blogs.php" class="btn btn-outline">Back to Blog</a>
    </article>
</div>

<?php require_once '../includes/footer.php'; ?>
