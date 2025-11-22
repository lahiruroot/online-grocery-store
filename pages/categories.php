<?php
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
$extra_css = 'categories-section.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
<div class="categories-header">
        <h1>Shop by Category</h1>
        <p>Browse our wide selection of fresh categories</p>
    </div>

    
    <?php if (empty($categories)): ?>
        <div class="alert alert-info mt-4">
            <p>No categories available.</p>
                </div>
    <?php else: ?>
        <div class="grid grid-cols-4 mt-4">
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo SITE_URL; ?>pages/category-products.php?id=<?php echo $cat['id']; ?>" class="category-card">
                <div class="category-card-image-wrapper">
                    <?php if (!empty($cat['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($cat['image']); ?>" 
                             alt="<?php echo e($cat['name']); ?>" 
                             class="category-card-image"
                             loading="lazy">
                    <?php else: ?>
                        <div class="category-card-placeholder">
                            <span></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="category-card-body">
                    <h3 class="category-card-title"><?php echo e($cat['name']); ?></h3>
                    <?php if (!empty($cat['description'])): ?>
                        <p class="category-card-description"><?php echo e($cat['description']); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
