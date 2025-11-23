<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Wishlist.php';
require_once __DIR__ . '/../classes/Product.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$wishlist = new Wishlist();
$product = new Product();
$userId = getCurrentUserId();

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $productId = (int)$_POST['product_id'];
    $wishlist->remove($userId, $productId);
    redirect('pages/wishlist.php');
}

$items = $wishlist->getItems($userId);

$page_title = 'My Wishlist';
$extra_css = 'products.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container products-page">
    <div class="products-header">
        <h1>My Wishlist</h1>
        <p>Your saved favorite products</p>
    </div>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <h3>Your Wishlist is Empty</h3>
            <p>Start adding products to your wishlist to save them for later.</p>
            <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid" id="wishlistGrid">
            <?php foreach ($items as $item): ?>
                <?php 
                $displayPrice = $product->getPrice($item);
                $originalPrice = validatePrice($item['price'] ?? 0);
                $discountPrice = validatePrice($item['discount_price'] ?? 0);
                $hasDiscount = $discountPrice > 0 && $discountPrice < $originalPrice && $originalPrice > 0;
                $discountPercent = $hasDiscount ? round((($originalPrice - $discountPrice) / $originalPrice) * 100) : 0;
                ?>
                <div class="product-card">
                    <?php if ($hasDiscount && $discountPercent > 0): ?>
                        <div class="product-card-discount-badge">-<?php echo $discountPercent; ?>%</div>
                    <?php endif; ?>
                    <div class="product-card-image-wrapper">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($item['image']); ?>" 
                                 alt="<?php echo e($item['name']); ?>" 
                                 class="product-card-image"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="product-card-placeholder">
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-card-title"><?php echo e($item['name']); ?></h3>
                        <p class="product-card-description"><?php echo e($item['description'] ?? 'No description available.'); ?></p>
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
                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $item['product_id']; ?>" class="btn" style="background: linear-gradient(135deg,rgb(13, 200, 66) 0%,rgb(21, 133, 51) 100%); color: #fff; border: none; width: 100%; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.15); transition: background 0.2s; font-size: 1rem; display: inline-block; text-align: center;">View</a>
                            <form method="POST" class="remove-wishlist-form" style="display: inline; margin: 0; width: 100%; flex: 1;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" name="remove" class="btn remove-wishlist-btn" style="background: linear-gradient(135deg,rgb(173, 12, 12) 0%,rgb(172, 10, 10) 100%); color: #fff; border: none; width: 100%; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.15); transition: background 0.2s; font-size: 1rem; display: inline-block; text-align: center;">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    'use strict';
    
    // Handle remove from wishlist confirmation
    const removeForms = document.querySelectorAll('.remove-wishlist-form');
    removeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Animate product cards on load
    const wishlistGrid = document.getElementById('wishlistGrid');
    if (wishlistGrid) {
        const cards = wishlistGrid.querySelectorAll('.product-card');
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
    
    // Add click animation to product cards
    if (wishlistGrid) {
        const cards = wishlistGrid.querySelectorAll('.product-card');
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Only animate if clicking on the card itself, not on links or buttons
                if (e.target === card || (e.target.closest('.product-card-body') && !e.target.closest('a') && !e.target.closest('button'))) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
