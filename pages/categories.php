<?php
/**
 * Categories Page
 * Display all categories
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Category.php';

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$category = new Category();
$categories = $category->getAll('active');

$page_title = 'Categories';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Shop by Category</h1>
    
    <?php if (empty($categories)): ?>
        <div class="alert alert-info mt-4">
            <p>No categories available.</p>
                </div>
    <?php else: ?>
        <div class="grid grid-cols-4 mt-4">
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo SITE_URL; ?>pages/category-products.php?id=<?php echo $cat['id']; ?>" class="card">
                    <div class="card-body text-center">
                        <?php if (!empty($cat['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($cat['image']); ?>" 
                                 alt="<?php echo e($cat['name']); ?>" 
                                 style="max-width: 150px; height: auto; margin: 0 auto 1rem;">
                        <?php endif; ?>
                        <h3><?php echo e($cat['name']); ?></h3>
                        <p class="card-text"><?php echo e(substr($cat['description'] ?? '', 0, 100)); ?><?php echo strlen($cat['description'] ?? '') > 100 ? '...' : ''; ?></p>
            </div>
                </a>
            <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
