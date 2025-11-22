<?php
/**
 * Product Detail Page
 * Display single product with details, reviews, and add to cart
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Review.php';
require_once __DIR__ . '/../classes/Wishlist.php';
require_once __DIR__ . '/../classes/Cart.php';

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$productModel = new Product();
$review = new Review();
$wishlist = new Wishlist();

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    redirect('products.php');
}

// Get product
$product = $productModel->getById($productId);

if (!$product || $product['status'] !== 'active') {
    redirect('products.php');
}

// Get reviews
$reviewsResult = $review->getByProduct($productId, 1, REVIEWS_PER_PAGE);
$reviews = $reviewsResult['reviews'];
$reviewStats = $review->getAverageRating($productId);

// Check if in wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $inWishlist = $wishlist->isInWishlist(getCurrentUserId(), $productId);
}

// Handle add to cart
$cartMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to add items to cart');
        redirect('auth/login.php');
    }

    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $cart = new Cart();
    $result = $cart->addItem(getCurrentUserId(), $productId, $quantity);

    if ($result['success']) {
        $cartMessage = 'Product added to cart successfully!';
    } else {
        $cartMessage = $result['error'];
    }
}

// Handle add to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist']) && isLoggedIn()) {
    if ($inWishlist) {
        $wishlist->remove(getCurrentUserId(), $productId);
    } else {
        $wishlist->add(getCurrentUserId(), $productId);
    }
    redirect('product-detail.php?id=' . $productId);
}

$page_title = $product['name'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Product Image -->
            <div>
            <?php if (!empty($product['image'])): ?>
                <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($product['image']); ?>" 
                     alt="<?php echo e($product['name']); ?>" 
                     style="width: 100%; max-width: 500px; border-radius: 0.5rem;">
            <?php else: ?>
                <div style="width: 100%; height: 400px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; color: #9ca3af;">
                    No Image Available
                </div>
            <?php endif; ?>
            </div>

        <!-- Product Info -->
            <div>
            <h1><?php echo e($product['name']); ?></h1>
            
            <?php if (!empty($product['category_name'])): ?>
                <p><a href="<?php echo SITE_URL; ?>pages/category-products.php?id=<?php echo $product['category_id']; ?>"><?php echo e($product['category_name']); ?></a></p>
            <?php endif; ?>

            <!-- Rating -->
            <?php if ($reviewStats['count'] > 0): ?>
                <div style="margin: 1rem 0;">
                    <?php echo getStarRating($reviewStats['average']); ?>
                    <span>(<?php echo $reviewStats['count']; ?> reviews)</span>
                </div>
            <?php endif; ?>

            <!-- Price -->
            <div style="margin: 1rem 0;">
                <?php 
                $displayPrice = $productModel->getPrice($product);
                // displayPrice is now a string
                ?>
                <span style="font-size: 2rem; font-weight: bold; color: #10b981;">
                    <?php echo formatPrice($displayPrice); ?>
                </span>
                    <?php 
                $originalPrice = validatePrice($product['price'] ?? 0);
                $discountPrice = validatePrice($product['discount_price'] ?? 0);
                if ($discountPrice > 0 && $discountPrice < $originalPrice && $originalPrice > 0): 
                ?>
                    <span style="text-decoration: line-through; color: #9ca3af; margin-left: 1rem;">
                        <?php echo formatPrice($originalPrice); ?>
                    </span>
                    <span style="color: #ef4444; margin-left: 0.5rem;">
                        <?php echo calculateDiscountPercent($originalPrice, $discountPrice); ?>% OFF
                    </span>
                <?php endif; ?>
            </div>

            <!-- Stock Status -->
            <div style="margin: 1rem 0;">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span style="color: #10b981;">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                <?php else: ?>
                    <span style="color: #ef4444;">Out of Stock</span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <?php if (!empty($product['description'])): ?>
                <div style="margin: 1rem 0;">
                    <h3>Description</h3>
                    <p><?php echo nl2br(e($product['description'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" style="margin: 2rem 0;">
                    <?php if ($cartMessage): ?>
                        <div class="alert alert-<?php echo strpos($cartMessage, 'successfully') !== false ? 'success' : 'error'; ?>" style="margin-bottom: 1rem;">
                            <?php echo e($cartMessage); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 80px; padding: 0.5rem;">
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                        
                        <?php if (isLoggedIn()): ?>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="toggle_wishlist" class="btn btn-outline">
                                    <?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <button class="btn btn-primary" disabled>Out of Stock</button>
            <?php endif; ?>
            </div>
        </div>

        <!-- Reviews Section -->
    <div style="margin-top: 3rem;">
        <h2>Reviews</h2>
        
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first to review this product!</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div style="border: 1px solid #e5e7eb; padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <div>
                            <strong><?php echo e($rev['user_name']); ?></strong>
                            <div><?php echo getStarRating($rev['rating']); ?></div>
                        </div>
                        <span style="color: #9ca3af;"><?php echo formatDate($rev['created_at']); ?></span>
                    </div>
                    <?php if (!empty($rev['title'])): ?>
                        <h4><?php echo e($rev['title']); ?></h4>
            <?php endif; ?>
                    <p><?php echo nl2br(e($rev['comment'])); ?></p>
        </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
