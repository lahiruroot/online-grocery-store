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

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    redirect('admin/products/manage.php');
}

// Get product
$productData = $product->getById($productId);

if (!$productData) {
    redirect('admin/products/manage.php');
}

// Get categories
$categories = $category->getAll();

$error = '';
$success = '';

// Handle product update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
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
    if (empty($data['name']) || $data['category_id'] === 0) {
        $error = 'Name and category are required';
    } elseif ($data['price'] <= 0) {
        $error = 'Price must be greater than 0';
    } elseif ($data['price'] > 100000) {
        $error = 'Price cannot exceed $100,000. Please enter a valid price.';
    } elseif ($data['discount_price'] !== null && $data['discount_price'] > 0) {
        if ($data['discount_price'] > $data['price']) {
            $error = 'Discount price cannot be greater than regular price.';
        } elseif ($data['discount_price'] > 100000) {
            $error = 'Discount price cannot exceed $100,000.';
        } elseif ($data['discount_price'] <= 0) {
            $error = 'Discount price must be greater than 0 if provided.';
        }
    }
    
    if (empty($error)) {
        // Handle image upload if new image provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if it exists
            if (!empty($productData['image'])) {
                deleteFile($productData['image']);
            }
            
            $uploadResult = uploadFile($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['filename'];
            } else {
                $error = 'Image upload failed: ' . $uploadResult['error'];
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle upload errors
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $error = 'Upload error: ' . ($uploadErrors[$_FILES['image']['error']] ?? 'Unknown error');
        }
        
        if (empty($error)) {
            $result = $product->update($productId, $data);
            if ($result['success']) {
                setFlashMessage('success', 'Product updated successfully!');
                redirect('admin/products/manage.php');
            } else {
                $error = $result['error'] ?? 'Failed to update product';
            }
        }
    }
}

$page_title = 'Edit Product';
$extra_css = 'admin-add-product.css';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-product-form">
    <div class="admin-product-form__header">
        <h1 class="admin-product-form__title">Edit Product</h1>
        <p class="admin-product-form__subtitle">Update the product details below</p>
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
                        value="<?php echo e($productData['name'] ?? ''); ?>" 
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
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == ($productData['category_id'] ?? 0)) ? 'selected' : ''; ?>>
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
                    ><?php echo e($productData['description'] ?? ''); ?></textarea>
                </div>

                <div class="admin-form__group admin-form__image-upload">
                    <label class="admin-form__label">Product Image</label>
                    <?php if (!empty($productData['image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Current Image:</p>
                            <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($productData['image']); ?>" 
                                 alt="Current image" 
                                 id="currentImage"
                                 style="max-width: 300px; max-height: 200px; border: 2px solid #e5e7eb; border-radius: 12px; padding: 0.5rem; background: #f9fafb; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    <div class="admin-form__image-preview" id="imagePreview">
                        <div class="admin-form__image-placeholder">
                            <div class="admin-form__image-placeholder-icon">📷</div>
                            <div class="admin-form__image-placeholder-text"><?php echo !empty($productData['image']) ? 'Upload new image to replace current' : 'No image selected'; ?></div>
                        </div>
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                    <div class="admin-form__file-input">
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/*" 
                            class="admin-form__input"
                        >
                        <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                            Leave empty to keep current image. Allowed formats: JPG, PNG, GIF, WebP. Max size: 5MB
                        </small>
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="price" class="admin-form__label admin-form__label--required">Price</label>
                        <?php 
                        // Validate price - if corrupted, show 0
                        $editPrice = validatePrice($productData['price'] ?? 0);
                        if ($editPrice == 0 && isset($productData['price']) && (float)$productData['price'] > 100000) {
                            echo '<div class="admin-alert admin-alert--error" style="margin-bottom: 0.5rem;">Warning: Current price appears to be corrupted. Please enter a valid price.</div>';
                        }
                        ?>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            step="0.01" 
                            min="0" 
                            value="<?php echo $editPrice; ?>" 
                            required 
                            class="admin-form__input"
                            placeholder="0.00"
                        >
                    </div>
                    <div class="admin-form__group">
                        <label for="discount_price" class="admin-form__label">Discount Price</label>
                        <?php 
                        $editDiscount = null;
                        if (isset($productData['discount_price']) && $productData['discount_price'] !== null) {
                            $editDiscount = validatePrice($productData['discount_price']);
                            if ($editDiscount == 0) {
                                $editDiscount = null;
                            }
                        }
                        ?>
                        <input 
                            type="number" 
                            id="discount_price" 
                            name="discount_price" 
                            step="0.01" 
                            min="0" 
                            value="<?php echo $editDiscount !== null ? $editDiscount : ''; ?>" 
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
                            value="<?php echo e($productData['stock_quantity'] ?? 0); ?>" 
                            class="admin-form__input"
                            placeholder="0"
                        >
                    </div>
                    <div class="admin-form__group">
                        <label for="status" class="admin-form__label">Status</label>
                        <select id="status" name="status" class="admin-form__select">
                            <option value="active" <?php echo ($productData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($productData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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
                            <?php echo ($productData['featured'] ?? 0) ? 'checked' : ''; ?>
                        >
                        <span class="admin-form__checkbox-label">Mark as Featured Product</span>
                    </label>
                </div>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="admin-btn admin-btn--primary" id="submitBtn">
                    <span>Update Product</span>
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
    const currentImage = document.getElementById('currentImage');
    const form = document.getElementById('productForm');
    const submitBtn = document.getElementById('submitBtn');
    const priceInput = document.getElementById('price');
    const discountPriceInput = document.getElementById('discount_price');

    // Show current image in preview if exists
    if (currentImage) {
        previewImg.src = currentImage.src;
        imagePreview.classList.add('has-image');
    }

    // Image preview functionality
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.add('has-image');
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (!currentImage) {
                    previewImg.src = '';
                    imagePreview.classList.remove('has-image');
                }
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
