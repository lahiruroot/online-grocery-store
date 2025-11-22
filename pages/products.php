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

// Get filters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$filters = [];
if ($categoryId > 0) {
    $filters['category_id'] = $categoryId;
}
if (!empty($search)) {
    $filters['search'] = $search;
}

// Get products
$result = $product->getAll($filters, $page, ITEMS_PER_PAGE);
$products = $result['products'];
$totalPages = $result['pages'];

// Get categories for filter
$categories = $category->getAll('active');

$page_title = 'Products';
$extra_css = 'products.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container products-page">
    <div class="products-header">
        <h1>Our Products</h1>
        <p>Discover fresh groceries delivered to your doorstep</p>
    </div>

    <!-- Enhanced Filters -->
    <div class="filters-container">
        <form method="GET" action="" class="filters-form" id="filterForm">
            <div class="filter-group">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="search">Search Products</label>
                <input type="text" name="search" id="search" value="<?php echo e($search); ?>" placeholder="Search by name..." class="form-control">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <span></span> Filter
                </button>
                <?php if ($categoryId > 0 || !empty($search)): ?>
                    <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <h3>No Products Found</h3>
            <p>Try adjusting your filters to find what you're looking for.</p>
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
                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary btn-small">buy</a>
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
                        <a href="?page=<?php echo $page - 1; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">← Previous</a>
                    <?php endif; ?>
                    
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="?page=1<?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-link" style="border: none; background: transparent; cursor: default;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-link active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-link" style="border: none; background: transparent; cursor: default;">...</span>
                        <?php endif; ?>
                        <a href="?page=<?php echo $totalPages; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">Next →</a>
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
    const filterForm = document.getElementById('filterForm');
    const categorySelect = document.getElementById('category');
    const searchInput = document.getElementById('search');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const productsGrid = document.getElementById('productsGrid');
    
    // Show loading overlay
    function showLoading() {
        if (loadingOverlay) {
            loadingOverlay.classList.add('active');
        }
    }
    
    // Hide loading overlay
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.classList.remove('active');
        }
    }
    
    // Auto-submit form on category change
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (filterForm) {
                showLoading();
                filterForm.submit();
            }
        });
    }
    
    // Debounce search input
    let searchTimeout;
    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Only auto-submit if there's a value or if it was cleared
                if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                    showLoading();
                    filterForm.submit();
                }
            }, 500);
        });
    }
    
    // Add smooth scroll to top on filter submit
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            showLoading();
            // Scroll to top smoothly
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
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
    
    // Hide loading overlay after page load
    window.addEventListener('load', function() {
        hideLoading();
    });
    
    // Add click animation to product cards
    if (productsGrid) {
        const cards = productsGrid.querySelectorAll('.product-card');
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Only animate if clicking on the card itself, not on links
                if (e.target === card || e.target.closest('.product-card-body')) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });
        });
    }
    
    // Smooth pagination link clicks
    const paginationLinks = document.querySelectorAll('.pagination-link:not(.active):not(span)');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            showLoading();
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
