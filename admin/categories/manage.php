<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Category.php';

if (!isAdmin()) {
    redirect('admin/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$category = new Category();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $categoryId = (int)$_GET['delete'];
    
    // Get category to delete image
    $categoryData = $category->getById($categoryId);
    
    // Delete category
    $result = $category->delete($categoryId);
    
    if ($result['success']) {
        // Delete category image if it exists
        if ($categoryData && !empty($categoryData['image'])) {
            deleteFile($categoryData['image']);
            }
        setFlashMessage('success', 'Category deleted successfully!');
    } else {
        setFlashMessage('error', $result['error']);
    }
    
    redirect('admin/categories/manage.php');
}

// Get categories
$categories = $category->getAll();

$page_title = 'Manage Categories';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Manage Categories</h1>
        <a href="<?php echo SITE_URL; ?>admin/categories/add.php" class="btn btn-primary">Add New Category</a>
    </div>

    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($categories)): ?>
        <div class="alert alert-info">
            <p>No categories found. <a href="<?php echo SITE_URL; ?>admin/categories/add.php">Add your first category</a></p>
        </div>
    <?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb;">
                    <th style="text-align: left; padding: 1rem;">Image</th>
                    <th style="text-align: left; padding: 1rem;">Category Name</th>
                    <th style="text-align: left; padding: 1rem;">Description</th>
                    <th style="text-align: center; padding: 1rem;">Status</th>
                    <th style="text-align: center; padding: 1rem;">Actions</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 1rem;">
                            <?php if (!empty($cat['image'])): ?>
                                <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($cat['image']); ?>" 
                                     alt="<?php echo e($cat['name']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <span style="color: #9ca3af;">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <strong><?php echo e($cat['name']); ?></strong>
                        </td>
                        <td style="padding: 1rem;">
                            <?php echo e(substr($cat['description'] ?? '', 0, 50)); ?><?php echo strlen($cat['description'] ?? '') > 50 ? '...' : ''; ?>
                        </td>
                        <td style="text-align: center; padding: 1rem;">
                            <span style="padding: 0.25rem 0.75rem; border-radius: 0.25rem; background: <?php echo $cat['status'] === 'active' ? '#dcfce7' : '#fee2e2'; ?>;">
                                <?php echo ucfirst($cat['status']); ?>
                            </span>
                        </td>
                        <td style="text-align: center; padding: 1rem;">
                            <a href="<?php echo SITE_URL; ?>admin/categories/edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-small btn-primary">Edit</a>
                            <a href="?delete=<?php echo $cat['id']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this category? This will also delete all products in this category.');" 
                               class="btn btn-small" style="background: #ef4444; color: white;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
