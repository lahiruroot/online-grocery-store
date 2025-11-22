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

$page_title = 'Manage Blogs';

// Handle delete
if (isset($_GET['delete'])) {
    $blog_id = (int)$_GET['delete'];

    // Get blog image to delete it
    $sql = "SELECT image FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();

    // Delete blog
    $sql = "DELETE FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);

    if ($stmt->execute()) {
        // Delete blog image if it exists
        if ($blog && $blog['image']) {
            $image_path = UPLOADS_PATH . $blog['image'];
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
        }
        $_SESSION['message'] = 'Blog deleted successfully!';
    } else {
        $_SESSION['message'] = 'Failed to delete blog: ' . $conn->error;
    }

    // Redirect to prevent resubmission
    redirect('admin/blogs/manage.php');
}

// Improved: Fetch blogs and their correct price (if price is in 'blogs' or a joined 'products' table)
$sql = "SELECT b.*, u.name as author_name" .
       (columnExists($conn, 'blogs', 'price') ? ", b.price" : "") .
       (tableExists($conn, 'products') && columnExists($conn, 'products', 'price') ?
            ", p.price as product_price" : ""
        ) .
       " FROM blogs b 
         JOIN users u ON b.author_id = u.id " .
       (tableExists($conn, 'products') && columnExists($conn, 'products', 'price') && columnExists($conn, 'blogs', 'product_id') ?
           " LEFT JOIN products p ON b.product_id = p.id " : ""
        ) .
       " ORDER BY b.created_at DESC";

function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '{$table}'");
    return $result && $result->num_rows > 0;
}
function columnExists($conn, $table, $col) {
    $result = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
    return $result && $result->num_rows > 0;
}

$blogs_result = $conn->query($sql);

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="flex-between mb-3">
        <h1>Manage Blogs</h1>
        <a href="<?php echo SITE_URL; ?>admin/blogs/add.php" class="btn btn-primary">Add New Blog</a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
            <tr>
                <th style="padding: 1rem; text-align: left;">Title</th>
                <th style="padding: 1rem; text-align: left;">Author</th>
                <?php
                // Show price column if available
                if (columnExists($conn, 'blogs', 'price') || (tableExists($conn, 'products') && columnExists($conn, 'products', 'price') && columnExists($conn, 'blogs', 'product_id'))) {
                    echo '<th style="padding: 1rem; text-align: left;">Price</th>';
                }
                ?>
                <th style="padding: 1rem; text-align: left;">Status</th>
                <th style="padding: 1rem; text-align: left;">Date</th>
                <th style="padding: 1rem; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($blog = $blogs_result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($blog['title']); ?></td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($blog['author_name']); ?></td>
                    <?php
                    // Output correct price
                    if (isset($blog['price'])) {
                        // Price in blogs table
                        $show_price = $blog['price'];
                    } elseif (isset($blog['product_price'])) {
                        // Price from related product
                        $show_price = $blog['product_price'];
                    } else {
                        $show_price = null;
                    }
                    if (columnExists($conn, 'blogs', 'price') || (tableExists($conn, 'products') && columnExists($conn, 'products', 'price') && columnExists($conn, 'blogs', 'product_id'))) {
                        echo '<td style="padding: 1rem;">';
                        echo ($show_price !== null && $show_price !== '') ? '$' . number_format((float)$show_price, 2) : '-';
                        echo '</td>';
                    }
                    ?>
                    <td style="padding: 1rem;">
                        <span style="background-color: <?php echo $blog['status'] === 'published' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $blog['status'] === 'published' ? '#15803d' : '#991b1b'; ?>; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem;">
                            <?php echo ucfirst($blog['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;"><?php echo formatDate($blog['created_at']); ?></td>
                    <td style="padding: 1rem; text-align: center;">
                        <a href="<?php echo SITE_URL; ?>admin/blogs/edit.php?id=<?php echo $blog['id']; ?>" class="btn btn-secondary btn-small">Edit</a>
                        <a href="<?php echo SITE_URL; ?>admin/blogs/manage.php?delete=<?php echo $blog['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this blog? This action cannot be undone.')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
