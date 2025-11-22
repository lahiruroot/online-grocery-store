<?php
/**
 * Category Products Page
 * Display products in a specific category
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Category.php';

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$product = new Product();
$category = new Category();

// Get category ID
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId <= 0) {
    redirect('categories.php');
}

// Get category
$categoryData = $category->getById($categoryId);

if (!$categoryData) {
    redirect('categories.php');
}

// Get products
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$result = $product->getAll(['category_id' => $categoryId], $page, ITEMS_PER_PAGE);
$products = $result['products'];
$totalPages = $result['pages'];

$page_title = $categoryData['name'] . ' Products';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1><?php echo e($categoryData['name']); ?></h1>
    
    <?php if (!empty($categoryData['description'])): ?>
        <p><?php echo e($categoryData['description']); ?></p>
    <?php endif; ?>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="alert alert-info mt-4">
            <p>No products found in this category.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-4 mt-4">
            <?php foreach ($products as $prod): ?>
                <div class="card">
                    <?php if (!empty($prod['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($prod['image']); ?>" alt="<?php echo e($prod['name']); ?>" class="card-img">
                    <?php else: ?>
                        <div class="card-img" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                            No Image
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo e($prod['name']); ?></h3>
                        <p class="card-text"><?php echo e(substr($prod['description'] ?? '', 0, 50)); ?><?php echo strlen($prod['description'] ?? '') > 50 ? '...' : ''; ?></p>
                        <div class="price mb-2">
                            <?php 
                            $displayPrice = $product->getPrice($prod);
                            if ((float)$displayPrice > 0):
                            ?>
                                <span class="card-price"><?php echo formatPrice($displayPrice); ?></span>
                                <?php 
                                $originalPrice = validatePrice($prod['price'] ?? 0);
                                $discountPrice = validatePrice($prod['discount_price'] ?? 0);
                                if ($discountPrice > 0 && $discountPrice < $originalPrice && $originalPrice > 0): 
                                ?>
                                    <span class="card-price-old" style="text-decoration: line-through; color: #9ca3af; margin-left: 0.5rem;">
                                        <?php echo formatPrice($originalPrice); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-between">
                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary btn-small">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
    </div>

    <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination mt-4">
                <?php if ($page > 1): ?>
                    <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>" class="pagination-link">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
            <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
