<?php
/**
 * Home Page
 * Main landing page with featured products and categories
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';
require_once __DIR__ . '/classes/Cart.php';

// Get database connection
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed. Please check your configuration.");
}

$product = new Product();
$category = new Category();
$cart = new Cart();

// Get featured products
$featuredProducts = $product->getFeatured(8);

// Get categories
$categories = $category->getAll('active');

// Get cart count for logged in users
$cartCount = 0;
if (isLoggedIn()) {
    $cartCount = $cart->getCount(getCurrentUserId());
}

$page_title = 'Home';

require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Fresh Groceries Delivered to Your Door</h1>
        <p>Get fresh, quality groceries at unbeatable prices</p>
        <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<section class="categories-section container mt-4">
    <h2>Shop by Category</h2>
    <div class="grid grid-cols-4">
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo SITE_URL; ?>pages/category-products.php?id=<?php echo $cat['id']; ?>" class="card">
                <div class="card-body text-center">
                    <?php if (!empty($cat['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($cat['image']); ?>" alt="<?php echo e($cat['name']); ?>" style="max-width: 100px; height: auto; margin-bottom: 1rem;">
                    <?php endif; ?>
                    <h3><?php echo e($cat['name']); ?></h3>
                    <p class="card-text"><?php echo e(substr($cat['description'] ?? '', 0, 50)); ?><?php echo strlen($cat['description'] ?? '') > 50 ? '...' : ''; ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="products-section container mt-4">
    <h2>Featured Products</h2>
    <div class="grid grid-cols-4">
        <?php foreach ($featuredProducts as $prod): ?>
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
                        if ($displayPrice > 0):
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
</section>

<?php require_once 'includes/footer.php'; ?>
