<?php
/**
 * Products Page
 * Display all products with pagination and filters
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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>All Products</h1>

    <!-- Filters -->
    <div class="filters mb-4" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div>
                <select name="category" class="form-control" style="min-width: 200px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search products..." class="form-control" style="min-width: 250px;">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($categoryId > 0 || !empty($search)): ?>
                <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            <p>No products found. Try adjusting your filters.</p>
        </div>
    <?php else: ?>
    <div class="grid grid-cols-4">
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
                    <a href="?page=<?php echo $page - 1; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
            <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
