<?php
/**
 * Add Category
 * Admin add new category
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Category.php';

if (!isAdmin()) {
    redirect('index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$category = new Category();

$error = '';
$success = '';
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'sort_order' => (int)($_POST['sort_order'] ?? 0)
    ];
    
    // Validation
    if (empty($formData['name'])) {
        $error = 'Category name is required';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $formData['image'] = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if (empty($error)) {
            $result = $category->create($formData);
            if ($result['success']) {
            $success = 'Category created successfully!';
                $formData = [];
        } else {
                $error = $result['error'];
            }
        }
    }
}

$page_title = 'Add Category';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>Add New Category</h1>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mt-4"><?php echo e($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Category Name *</label>
            <input type="text" name="name" value="<?php echo e($formData['name'] ?? ''); ?>" required class="form-control">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
            <textarea name="description" rows="4" class="form-control"><?php echo e($formData['description'] ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Category Image</label>
            <input type="file" name="image" accept="image/*" class="form-control">
                </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo ($formData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($formData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sort Order</label>
                <input type="number" name="sort_order" value="<?php echo e($formData['sort_order'] ?? 0); ?>" min="0" class="form-control">
            </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Create Category</button>
                    <a href="<?php echo SITE_URL; ?>admin/categories/manage.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
