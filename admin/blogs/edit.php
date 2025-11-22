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

$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page_title = 'Edit Blog';
$error = '';
$success = '';

// Get blog
$sql = "SELECT * FROM blogs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if (!$blog) {
    redirect('admin/blogs/manage.php');
}

// Handle blog update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = sanitize($_POST['status'] ?? 'draft');

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        $sql = "UPDATE blogs SET title = ?, content = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $title, $content, $status, $blog_id);

        if ($stmt->execute()) {
            $success = 'Blog updated successfully!';
            // Refresh blog data
            $sql = "SELECT * FROM blogs WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $blog_id);
            $stmt->execute();
            $blog = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Failed to update blog';
        }
    }
}

require_once '../../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>Edit Blog</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Blog Title*</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Content*</label>
                    <textarea name="content" required class="form-control" style="min-height: 300px;"><?php echo htmlspecialchars($blog['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?php echo ($blog['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo ($blog['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Update Blog</button>
                    <a href="<?php echo SITE_URL; ?>admin/blogs/manage.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
