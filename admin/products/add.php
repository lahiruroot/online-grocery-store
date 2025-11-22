<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Category.php';

if (!isAdmin()) {
    redirect('admin/index.php');
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed.");
}

$product = new Product();
$category = new Category();

$error = '';
$success = '';
$formData = [];

// Get categories
$categories = $category->getAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => sanitize($_POST['name'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'description' => sanitize($_POST['description'] ?? ''),
        'price' => (float)($_POST['price'] ?? 0),
        'discount_price' => !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null,
        'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'featured' => isset($_POST['featured']) ? 1 : 0
    ];
    
    // Validation
    if (empty($formData['name']) || $formData['category_id'] === 0) {
        $error = 'Name and category are required';
    } elseif ($formData['price'] <= 0) {
        $error = 'Price must be greater than 0';
    } elseif ($formData['discount_price'] !== null && $formData['discount_price'] >= $formData['price']) {
        $error = 'Discount price must be less than regular price';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $formData['image'] = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
                }
            }
        
        if (empty($error)) {
            $result = $product->create($formData);
            if ($result['success']) {
                $success = 'Product created successfully!';
                $formData = [];
            } else {
                $error = $result['error'];
            }
        }
    }
}

$page_title = 'Add Product';
$extra_css = 'admin-add-product.css';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-product-form">
    <div class="admin-product-form__header">
        <h1 class="admin-product-form__title">Add New Product</h1>
        <p class="admin-product-form__subtitle">Fill in the details below to create a new product</p>
    </div>

    <div class="admin-product-form__card">
        <?php if ($error): ?>
            <div class="admin-alert admin-alert--error">
                <span class="admin-alert__icon">⚠️</span>
                <span><?php echo e($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert--success">
                <span class="admin-alert__icon">✓</span>
                <span><?php echo e($success); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-product-form__form" id="productForm">
            <div class="admin-form__section">
                <div class="admin-form__group">
                    <label for="name" class="admin-form__label admin-form__label--required">Product Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?php echo e($formData['name'] ?? ''); ?>" 
                        required 
                        class="admin-form__input"
                        placeholder="Enter product name"
                    >
                </div>

                <div class="admin-form__group">
                    <label for="category_id" class="admin-form__label admin-form__label--required">Category</label>
                    <select id="category_id" name="category_id" required class="admin-form__select">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($formData['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form__group">
                    <label for="description" class="admin-form__label">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4" 
                        class="admin-form__textarea"
                        placeholder="Enter product description (optional)"
                    ><?php echo e($formData['description'] ?? ''); ?></textarea>
                </div>

                <div class="admin-form__group admin-form__image-upload">
                    <label for="image" class="admin-form__label admin-form__label--required">Product Image</label>
                    <div class="admin-form__image-preview" id="imagePreview">
                        <div class="admin-form__image-placeholder">
                            <div class="admin-form__image-placeholder-icon">📷</div>
                            <div class="admin-form__image-placeholder-text">No image selected</div>
                        </div>
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                    <div class="admin-form__file-input">
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/*" 
                            required 
                            class="admin-form__input"
                        >
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="price" class="admin-form__label admin-form__label--required">Price</label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            step="0.01" 
                            min="0" 
                            value="<?php echo e($formData['price'] ?? ''); ?>" 
                            required 
                            class="admin-form__input"
                            placeholder="0.00"
                        >
                    </div>
                    <div class="admin-form__group">
                        <label for="discount_price" class="admin-form__label">Discount Price</label>
                        <input 
                            type="number" 
                            id="discount_price" 
                            name="discount_price" 
                            step="0.01" 
                            min="0" 
                            value="<?php echo e($formData['discount_price'] ?? ''); ?>" 
                            class="admin-form__input"
                            placeholder="0.00 (optional)"
                        >
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="stock_quantity" class="admin-form__label">Stock Quantity</label>
                        <input 
                            type="number" 
                            id="stock_quantity" 
                            name="stock_quantity" 
                            min="0" 
                            value="<?php echo e($formData['stock_quantity'] ?? 0); ?>" 
                            class="admin-form__input"
                            placeholder="0"
                        >
                    </div>
                    <div class="admin-form__group">
                        <label for="status" class="admin-form__label">Status</label>
                        <select id="status" name="status" class="admin-form__select">
                            <option value="active" <?php echo ($formData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($formData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="admin-form__group">
                    <label class="admin-form__checkbox-group">
                        <input 
                            type="checkbox" 
                            name="featured" 
                            value="1" 
                            class="admin-form__checkbox"
                            <?php echo ($formData['featured'] ?? 0) ? 'checked' : ''; ?>
                        >
                        <span class="admin-form__checkbox-label">Mark as Featured Product</span>
                    </label>
                </div>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="admin-btn admin-btn--primary" id="submitBtn">
                    <span>Create Product</span>
                </button>
                <a href="<?php echo SITE_URL; ?>admin/products/manage.php" class="admin-btn admin-btn--secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const form = document.getElementById('productForm');
    const submitBtn = document.getElementById('submitBtn');
    const priceInput = document.getElementById('price');
    const discountPriceInput = document.getElementById('discount_price');

    // Image preview functionality
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.src = '';
                imagePreview.classList.remove('has-image');
            }
        });
    }

    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            const price = parseFloat(priceInput.value);
            const discountPrice = discountPriceInput.value ? parseFloat(discountPriceInput.value) : null;

            // Validate price
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                priceInput.focus();
                return false;
            }

            // Validate discount price
            if (discountPrice !== null && discountPrice >= price) {
                e.preventDefault();
                alert('Discount price must be less than regular price');
                discountPriceInput.focus();
                return false;
            }

            // Show loading state
            submitBtn.classList.add('admin-btn--loading');
            submitBtn.disabled = true;
        });
    }

    // Real-time discount price validation
    if (discountPriceInput && priceInput) {
        discountPriceInput.addEventListener('blur', function() {
            const price = parseFloat(priceInput.value);
            const discountPrice = this.value ? parseFloat(this.value) : null;

            if (discountPrice !== null && discountPrice >= price) {
                this.setCustomValidity('Discount price must be less than regular price');
                this.style.borderColor = '#ef4444';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });

        priceInput.addEventListener('input', function() {
            discountPriceInput.dispatchEvent(new Event('blur'));
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
