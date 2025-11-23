<?php
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
// Ensure cartTotal is a float
$cartTotal = (float)$cartTotal;

if ($cartTotal <= 0) {
    setFlashMessage('error', 'Your cart total is invalid. Please add items to your cart.');
    redirect('cart/view-cart.php');
}

$taxAmount = $cartTotal * 0.10;
$shippingAmount = $cartTotal > 100 ? 0 : 10;
$totalAmount = $cartTotal + $taxAmount + $shippingAmount;
    
// Get user data
$user = $userModel->getById($userId);

// Handle order placement
$error = '';
$success = false;

// Debug: Log all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== POST REQUEST DETECTED ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("place_order isset: " . (isset($_POST['place_order']) ? 'YES' : 'NO'));
}

// Handle order placement - MUST be before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Start output buffering to catch any accidental output
    ob_start();
    
    $shippingAddress = trim(sanitize($_POST['shipping_address'] ?? ''));
    $billingAddress = trim(sanitize($_POST['billing_address'] ?? ''));
    if (empty($billingAddress)) {
        $billingAddress = $shippingAddress;
    }
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash_on_delivery');

    // Log for debugging
    error_log("=== CHECKOUT POST RECEIVED ===");
    error_log("User ID: " . $userId);
    error_log("Shipping Address: " . substr($shippingAddress, 0, 100));
    error_log("Payment Method: " . $paymentMethod);
    error_log("POST data: " . print_r($_POST, true));

    if (empty($shippingAddress)) {
        ob_end_clean();
        $error = 'Shipping address is required';
    } else {
        try {
            error_log("Calling order->create()...");
            $result = $order->create($userId, $shippingAddress, $paymentMethod, $billingAddress);
            error_log("Order create result: " . print_r($result, true));

            if ($result['success']) {
                // Clean output buffer before redirect
                ob_end_clean();
                setFlashMessage('success', 'Order placed successfully! Order #' . $result['order_number']);
                error_log("Redirecting to order confirmation page...");
                redirect('user/order-confirmation.php?order_id=' . $result['order_id']);
                exit(); // Ensure script stops
            } else {
                ob_end_clean();
                $error = $result['error'] ?? 'Failed to place order. Please try again.';
                error_log("Order creation failed: " . ($result['error'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            ob_end_clean();
            $error = 'An error occurred while processing your order. Please try again.';
            error_log("Order creation exception: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
        } catch (Error $e) {
            ob_end_clean();
            $error = 'An error occurred while processing your order. Please try again.';
            error_log("Order creation fatal error: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
        }
    }
}

$page_title = 'Checkout';
$extra_css = 'checkout.css';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="checkout-container">
    <div class="checkout-header">
        <h1 class="checkout-header__title">Checkout</h1>
    </div>

    <?php if ($error): ?>
        <div class="checkout-alert checkout-alert--error">
            <span>⚠️</span>
            <span><?php echo e($error); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo SITE_URL; ?>pages/checkout.php" id="checkoutForm" class="checkout-form" novalidate>
        <!-- Shipping Information -->
        <div class="checkout-shipping">
            <h2 class="checkout-shipping__title">Shipping Information</h2>
            
            <div class="checkout-form__section">
                <div class="checkout-form__row">
                    <div class="checkout-form__group">
                        <label for="name" class="checkout-form__label checkout-form__label--required">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?php echo e($user['name'] ?? ''); ?>" 
                            class="checkout-form__input" 
                            required
                            autocomplete="name"
                        >
                        <span class="checkout-form__error" id="name-error"></span>
                    </div>

                    <div class="checkout-form__group">
                        <label for="email" class="checkout-form__label checkout-form__label--required">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo e($user['email'] ?? ''); ?>" 
                            class="checkout-form__input" 
                            required
                            autocomplete="email"
                        >
                        <span class="checkout-form__error" id="email-error"></span>
                    </div>
                </div>

                <div class="checkout-form__group">
                    <label for="phone" class="checkout-form__label">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?php echo e($user['phone'] ?? ''); ?>" 
                        class="checkout-form__input" 
                        autocomplete="tel"
                        pattern="[0-9+\-\s()]+"
                    >
                    <span class="checkout-form__error" id="phone-error"></span>
                </div>

                <div class="checkout-form__group">
                    <label for="shipping_address" class="checkout-form__label checkout-form__label--required">Shipping Address</label>
                    <textarea 
                        id="shipping_address" 
                        name="shipping_address" 
                        rows="4" 
                        class="checkout-form__textarea" 
                        placeholder="Enter your complete shipping address (Street, City, State, ZIP Code)"
                        required
                        autocomplete="street-address"
                    ><?php echo e($user['address'] ?? ''); ?></textarea>
                    <span class="checkout-form__error" id="shipping_address-error"></span>
                </div>

                <div class="checkout-form__group">
                    <label for="billing_address" class="checkout-form__label">Billing Address (if different)</label>
                    <textarea 
                        id="billing_address" 
                        name="billing_address" 
                        rows="4" 
                        class="checkout-form__textarea" 
                        placeholder="Enter your billing address (optional - leave blank to use shipping address)"
                        autocomplete="billing street-address"
                    ></textarea>
                    <span class="checkout-form__error" id="billing_address-error"></span>
                </div>

                <div class="checkout-form__group">
                    <label for="payment_method" class="checkout-form__label checkout-form__label--required">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="checkout-form__select" required>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Bank Transfer</option>
                    </select>
                    <span class="checkout-form__error" id="payment_method-error"></span>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="checkout-summary">
            <h3 class="checkout-summary__title">Order Summary</h3>
            
            <div class="checkout-summary__items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="checkout-summary__item">
                        <span class="checkout-summary__item-name"><?php echo e($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                        <span class="checkout-summary__item-price"><?php 
                            $itemPrice = (float)$product->getPrice($item);
                            $itemQuantity = (int)$item['quantity'];
                            echo formatPrice($itemPrice * $itemQuantity); 
                        ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="checkout-summary__breakdown">
                <div class="checkout-summary__row">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>
                <div class="checkout-summary__row">
                    <span>Tax (10%):</span>
                    <span><?php echo formatPrice($taxAmount); ?></span>
                </div>
                <div class="checkout-summary__row">
                    <span>Shipping:</span>
                    <span><?php echo formatPrice($shippingAmount); ?></span>
                </div>
            </div>
            
            <div class="checkout-summary__total">
                <span>Total:</span>
                <span><?php echo formatPrice($totalAmount); ?></span>
            </div>

            <div class="checkout-summary__actions">
                <button type="submit" name="place_order" value="1" id="placeOrderBtn" class="checkout-btn checkout-btn--primary">
                    Place Order
                </button>
                <a href="<?php echo SITE_URL; ?>cart/view-cart.php" class="checkout-btn checkout-btn--secondary">
                    Back to Cart
                </a>
            </div>
        </div>
    </form>
</div>

<script>
// Form Validation
(function() {
    const form = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    
    if (!form) {
        console.error('Checkout form not found');
        return;
    }
    
    if (!placeOrderBtn) {
        console.error('Place order button not found');
        return;
    }
    
    // Validation rules
    const validators = {
        name: {
            validate: (value) => {
                if (!value || !value.trim()) return 'Full name is required';
                const trimmed = value.trim();
                if (trimmed.length < 2) return 'Name must be at least 2 characters';
                return null;
            }
        },
        email: {
            validate: (value) => {
                if (!value || !value.trim()) return 'Email is required';
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value.trim())) return 'Please enter a valid email address';
                return null;
            }
        },
        phone: {
            validate: (value) => {
                if (value && value.trim()) {
                    const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
                    if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                        return 'Please enter a valid phone number';
                    }
                }
                return null;
            }
        },
        shipping_address: {
            validate: (value) => {
                if (!value || !value.trim()) return 'Shipping address is required';
                const trimmed = value.trim();
                if (trimmed.length < 5) return 'Please provide a complete address (at least 5 characters)';
                return null;
            }
        },
        billing_address: {
            validate: (value) => {
                if (value && value.trim() && value.trim().length < 5) {
                    return 'Please provide a complete billing address (at least 5 characters)';
                }
                return null;
            }
        },
        payment_method: {
            validate: (value) => {
                if (!value || !value.trim()) return 'Payment method is required';
                return null;
            }
        }
    };

    // Show error
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(fieldId + '-error');
        
        if (field) {
            field.classList.remove('success');
            field.classList.add('error');
        }
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
    }

    // Show success
    function showSuccess(fieldId) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(fieldId + '-error');
        
        if (field) {
            field.classList.remove('error');
            field.classList.add('success');
        }
        
        if (errorElement) {
            errorElement.classList.remove('show');
        }
    }

    // Validate field
    function validateField(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field || !validators[fieldId]) return true;
        
        const value = field.value;
        const error = validators[fieldId].validate(value);
        
        if (error) {
            showError(fieldId, error);
            return false;
        } else {
            showSuccess(fieldId);
            return true;
        }
    }

    // Validate all fields
    function validateForm() {
        let isValid = true;
        
        Object.keys(validators).forEach(fieldId => {
            if (!validateField(fieldId)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    // Real-time validation on blur
    Object.keys(validators).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', () => validateField(fieldId));
            field.addEventListener('input', function() {
                if (this.classList.contains('error') || this.classList.contains('success')) {
                    validateField(fieldId);
                }
            });
        }
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
        
        // Validate form
        const isValid = validateForm();
        console.log('Validation result:', isValid);
        
        if (!isValid) {
            console.log('VALIDATION FAILED - Blocking submission');
            e.preventDefault();
            e.stopPropagation();
            // Scroll to first error
            const firstError = form.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return false;
        }

        console.log('VALIDATION PASSED - Submitting form');
        
        // IMPORTANT: Don't disable button immediately - it can prevent form submission
        // Instead, use setTimeout to disable it after allowing the form to submit
        setTimeout(function() {
            if (placeOrderBtn) {
                placeOrderBtn.disabled = true;
                placeOrderBtn.classList.add('checkout-btn--loading');
                placeOrderBtn.textContent = 'Processing...';
            }
        }, 10);
        
        // Allow form to submit naturally - don't call preventDefault()
        // The form will POST to the server now
    });

    // Clear errors on input
    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('error')) {
            const fieldId = e.target.id;
            validateField(fieldId);
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
