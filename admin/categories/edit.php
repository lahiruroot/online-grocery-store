<?php
/**
 * Edit Category
 * Admin edit category
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

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId <= 0) {
    redirect('manage.php');
}

// Get category
$categoryData = $category->getById($categoryId);

if (!$categoryData) {
    redirect('manage.php');
}

$error = '';
$success = '';

// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'sort_order' => (int)($_POST['sort_order'] ?? 0)
    ];

    // Validation
    if (empty($data['name'])) {
        $error = 'Category name is required';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if exists
            if (!empty($categoryData['image'])) {
                deleteFile($categoryData['image']);
            }
            
            $uploadResult = uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if (empty($error)) {
            $result = $category->update($categoryId, $data);
            if ($result['success']) {
            $success = 'Category updated successfully!';
            // Refresh category data
                $categoryData = $category->getById($categoryId);
        } else {
                $error = $result['error'];
            }
        }
    }
}

$page_title = 'Edit Category';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>Edit Category</h1>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mt-4"><?php echo e($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Category Name *</label>
            <input type="text" name="name" value="<?php echo e($categoryData['name'] ?? ''); ?>" required class="form-control">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
            <textarea name="description" rows="4" class="form-control"><?php echo e($categoryData['description'] ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Current Image</label>
            <?php if (!empty($categoryData['image'])): ?>
                <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($categoryData['image']); ?>" 
                     alt="<?php echo e($categoryData['name']); ?>" 
                     style="max-width: 200px; height: auto; display: block; margin-bottom: 0.5rem;">
            <?php else: ?>
                <p style="color: #9ca3af;">No image</p>
            <?php endif; ?>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; margin-top: 1rem;">Upload New Image (optional)</label>
            <input type="file" name="image" accept="image/*" class="form-control">
                </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo ($categoryData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($categoryData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sort Order</label>
                <input type="number" name="sort_order" value="<?php echo e($categoryData['sort_order'] ?? 0); ?>" min="0" class="form-control">
            </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <a href="<?php echo SITE_URL; ?>admin/categories/manage.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
