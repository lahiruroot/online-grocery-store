<?php
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
$extra_css = 'products.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container products-page">
    <div class="products-header">
        <h1><?php echo e($categoryData['name']); ?></h1>
        <?php if (!empty($categoryData['description'])): ?>
            <p><?php echo e($categoryData['description']); ?></p>
        <?php else: ?>
            <p>Browse our selection of <?php echo e($categoryData['name']); ?> products</p>
        <?php endif; ?>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3>No Products Found</h3>
            <p>There are no products available in this category at the moment.</p>
            <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-primary">View All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $prod): ?>
                <?php 
                $displayPrice = $product->getPrice($prod);
                $originalPrice = validatePrice($prod['price'] ?? 0);
                $discountPrice = validatePrice($prod['discount_price'] ?? 0);
                $hasDiscount = $discountPrice > 0 && $discountPrice < $originalPrice && $originalPrice > 0;
                $discountPercent = $hasDiscount ? round((($originalPrice - $discountPrice) / $originalPrice) * 100) : 0;
                ?>
                <div class="product-card">
                    <?php if ($hasDiscount && $discountPercent > 0): ?>
                        <div class="product-card-discount-badge">-<?php echo $discountPercent; ?>%</div>
                    <?php endif; ?>
                    <div class="product-card-image-wrapper">
                        <?php if (!empty($prod['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($prod['image']); ?>" 
                                 alt="<?php echo e($prod['name']); ?>" 
                                 class="product-card-image"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="product-card-placeholder">
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-card-title"><?php echo e($prod['name']); ?></h3>
                        <p class="product-card-description"><?php echo e($prod['description'] ?? 'No description available.'); ?></p>
                        <div class="product-card-price-wrapper">
                            <?php if ((float)$displayPrice > 0): ?>
                                <span class="product-card-price"><?php echo formatPrice($displayPrice); ?></span>
                                <?php if ($hasDiscount): ?>
                                    <span class="product-card-price-old"><?php echo formatPrice($originalPrice); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="product-card-price">Price on request</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-actions">
                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary btn-small">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Enhanced Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>" class="pagination-link">← Previous</a>
                    <?php endif; ?>
                    
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="?id=<?php echo $categoryId; ?>&page=1" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-link" style="border: none; background: transparent; cursor: default;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-link active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-link" style="border: none; background: transparent; cursor: default;">...</span>
                        <?php endif; ?>
                        <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $totalPages; ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>" class="pagination-link">Next →</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<script>
(function() {
    'use strict';
    
    // DOM Elements
    const loadingOverlay = document.getElementById('loadingOverlay');
    const productsGrid = document.getElementById('productsGrid');
    
    // Hide loading overlay after page load
    window.addEventListener('load', function() {
        if (loadingOverlay) {
            loadingOverlay.classList.remove('active');
        }
    });
    
    // Animate product cards on load
    if (productsGrid) {
        const cards = productsGrid.querySelectorAll('.product-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }
    
    // Smooth pagination link clicks
    const paginationLinks = document.querySelectorAll('.pagination-link:not(.active):not(span)');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (loadingOverlay) {
                loadingOverlay.classList.add('active');
            }
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
