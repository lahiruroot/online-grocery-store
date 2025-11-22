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

$user_id = getCurrentUserId();
$page_title = 'Add Blog';
$error = '';
$success = '';

// Handle blog creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = sanitize($_POST['status'] ?? 'draft');
    $slug = generateSlug($title);

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        $sql = "INSERT INTO blogs (title, content, author_id, status, slug) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiss", $title, $content, $user_id, $status, $slug);

        if ($stmt->execute()) {
            $success = 'Blog created successfully!';
        } else {
            $error = 'Failed to create blog';
        }
    }
}

require_once '../../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>Add New Blog</h1>

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
                    <input type="text" name="title" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Content*</label>
                    <textarea name="content" required class="form-control" style="min-height: 300px;"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Create Blog</button>
                    <a href="<?php echo SITE_URL; ?>admin/blogs/manage.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
