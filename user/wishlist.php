<?php
/**
 * Wishlist Page
 * Display user wishlist
 */

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
    redirect('wishlist.php');
}

$items = $wishlist->getItems($userId);

$page_title = 'My Wishlist';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>My Wishlist</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info mt-4">
            <p>Your wishlist is empty. <a href="<?php echo SITE_URL; ?>pages/products.php">Browse Products</a></p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-4 mt-4">
            <?php foreach ($items as $item): ?>
                <div class="card">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" class="card-img">
                    <?php else: ?>
                        <div class="card-img" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                            No Image
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo e($item['name']); ?></h3>
                        <div class="price mb-2">
                            <?php 
                            $displayPrice = $product->getPrice($item);
                            if ($displayPrice > 0):
                            ?>
                                <span class="card-price"><?php echo formatPrice($displayPrice); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-between">
                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $item['product_id']; ?>" class="btn btn-primary btn-small">View</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" name="remove" class="btn btn-small" style="background: #ef4444; color: white;">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
