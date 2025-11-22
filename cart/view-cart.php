<?php
/**
 * View Cart Page
 * Display cart items and allow quantity updates
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/Product.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your cart');
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$cart = new Cart();
$product = new Product();

$userId = getCurrentUserId();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $productId = (int)$_POST['product_id'];
        $quantity = max(1, (int)$_POST['quantity']);
        $cart->updateQuantity($userId, $productId, $quantity);
    } elseif (isset($_POST['remove_item'])) {
        $productId = (int)$_POST['product_id'];
        $cart->removeItem($userId, $productId);
    }
    redirect('cart/view-cart.php');
}

// Get cart items
$cartItems = $cart->getItems($userId);
$cartTotal = $cart->getTotal($userId);

$page_title = 'Shopping Cart';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Shopping Cart</h1>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info mt-4">
            <p>Your cart is empty. <a href="<?php echo SITE_URL; ?>pages/products.php">Continue Shopping</a></p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Cart Items -->
            <div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="text-align: left; padding: 1rem;">Product</th>
                            <th style="text-align: center; padding: 1rem;">Price</th>
                            <th style="text-align: center; padding: 1rem;">Quantity</th>
                            <th style="text-align: right; padding: 1rem;">Subtotal</th>
                            <th style="text-align: center; padding: 1rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($cartItems as $item): ?>
                    <?php
                            $itemPrice = $product->getPrice($item);
                            $subtotal = $itemPrice * $item['quantity'];
                    ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 1rem;">
                                    <div style="display: flex; gap: 1rem; align-items: center;">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($item['image']); ?>" 
                                                 alt="<?php echo e($item['name']); ?>" 
                                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.25rem;">
                                        <?php endif; ?>
                                    <div>
                                            <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $item['product_id']; ?>">
                                                <strong><?php echo e($item['name']); ?></strong>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center; padding: 1rem;">
                                    <?php echo formatPrice($itemPrice); ?>
                                </td>
                                <td style="text-align: center; padding: 1rem;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                               style="width: 60px; padding: 0.25rem; text-align: center;">
                                        <button type="submit" name="update_quantity" class="btn btn-small btn-outline" style="margin-left: 0.5rem;">Update</button>
                                    </form>
                            </td>
                                <td style="text-align: right; padding: 1rem;">
                                    <strong><?php echo formatPrice($subtotal); ?></strong>
                            </td>
                                <td style="text-align: center; padding: 1rem;">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Remove this item from cart?');">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-small" style="background: #ef4444; color: white;">Remove</button>
                                    </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <!-- Cart Summary -->
            <div>
                <div class="card">
            <div class="card-body">
                        <h3>Order Summary</h3>
                        <div style="margin: 1rem 0; padding: 1rem 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Subtotal:</span>
                                <span><?php echo formatPrice($cartTotal); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Tax (10%):</span>
                                <span><?php echo formatPrice($cartTotal * 0.10); ?></span>
                </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Shipping:</span>
                                <span><?php echo $cartTotal > 100 ? formatPrice(0) : formatPrice(10); ?></span>
                            </div>
                </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: bold; margin-top: 1rem;">
                    <span>Total:</span>
                            <span><?php echo formatPrice($cartTotal + ($cartTotal * 0.10) + ($cartTotal > 100 ? 0 : 10)); ?></span>
                        </div>
                        <a href="<?php echo SITE_URL; ?>pages/checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Proceed to Checkout</a>
                        <a href="<?php echo SITE_URL; ?>pages/products.php" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
