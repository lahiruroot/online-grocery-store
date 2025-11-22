<?php
/**
 * Checkout Page
 * Process order placement
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Cart.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Product.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to checkout');
    redirect('auth/login.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$cart = new Cart();
$order = new Order();
$userModel = new User();
$product = new Product();

$userId = getCurrentUserId();

// Get cart items
$cartItems = $cart->getItems($userId);

if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty');
    redirect('cart/view-cart.php');
}

$cartTotal = $cart->getTotal($userId);
$taxAmount = $cartTotal * 0.10;
$shippingAmount = $cartTotal > 100 ? 0 : 10;
$totalAmount = $cartTotal + $taxAmount + $shippingAmount;
    
// Get user data
$user = $userModel->getById($userId);

// Handle order placement
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
    $billingAddress = sanitize($_POST['billing_address'] ?? $shippingAddress);
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash_on_delivery');

    if (empty($shippingAddress)) {
        $error = 'Shipping address is required';
    } else {
        $result = $order->create($userId, $shippingAddress, $paymentMethod, $billingAddress);

        if ($result['success']) {
            $success = true;
            setFlashMessage('success', 'Order placed successfully! Order #' . $result['order_number']);
            redirect('user/order-confirmation.php?order_id=' . $result['order_id']);
        } else {
            $error = $result['error'];
        }
    }
}

$page_title = 'Checkout';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1>Checkout</h1>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
        <!-- Order Details -->
        <div>
            <h2>Shipping Information</h2>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Full Name</label>
                <input type="text" name="name" value="<?php echo e($user['name'] ?? ''); ?>" required class="form-control">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email</label>
                <input type="email" name="email" value="<?php echo e($user['email'] ?? ''); ?>" required class="form-control">
                        </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Phone</label>
                <input type="tel" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>" class="form-control">
                        </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Shipping Address *</label>
                <textarea name="shipping_address" rows="4" required class="form-control" placeholder="Enter your complete shipping address"><?php echo e($user['address'] ?? ''); ?></textarea>
                        </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Billing Address (if different)</label>
                <textarea name="billing_address" rows="4" class="form-control" placeholder="Enter your billing address (optional)"></textarea>
                        </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Payment Method</label>
                <select name="payment_method" class="form-control">
                    <option value="cash_on_delivery">Cash on Delivery</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                            </select>
            </div>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="card">
                <div class="card-body">
                    <h3>Order Summary</h3>
                    
                    <div style="margin: 1rem 0;">
                        <?php foreach ($cartItems as $item): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                                <span><?php echo e($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                <span><?php echo formatPrice($product->getPrice($item) * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin: 1rem 0; padding: 1rem 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($cartTotal); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Tax (10%):</span>
                            <span><?php echo formatPrice($taxAmount); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Shipping:</span>
                            <span><?php echo formatPrice($shippingAmount); ?></span>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: bold; margin-top: 1rem;">
                        <span>Total:</span>
                        <span><?php echo formatPrice($totalAmount); ?></span>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Place Order</button>
                    <a href="<?php echo SITE_URL; ?>cart/view-cart.php" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;">Back to Cart</a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
