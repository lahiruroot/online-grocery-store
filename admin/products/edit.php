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
                $error = $uploadResult['error'];
                }
            }
        
        if (empty($error)) {
            $result = $product->update($productId, $data);
            if ($result['success']) {
                $success = 'Product updated successfully!';
                // Refresh product data
                $productData = $product->getById($productId);
            } else {
                $error = $result['error'];
            }
        }
    }
}

$page_title = 'Edit Product';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>Edit Product</h1>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mt-4"><?php echo e($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Product Name *</label>
            <input type="text" name="name" value="<?php echo e($productData['name'] ?? ''); ?>" required class="form-control">
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Category *</label>
                    <select name="category_id" required class="form-control">
                        <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == ($productData['category_id'] ?? 0)) ? 'selected' : ''; ?>>
                        <?php echo e($cat['name']); ?>
                            </option>
                <?php endforeach; ?>
                    </select>
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
            <textarea name="description" rows="4" class="form-control"><?php echo e($productData['description'] ?? ''); ?></textarea>
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Current Image</label>
            <?php if (!empty($productData['image'])): ?>
                <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($productData['image']); ?>" 
                                 alt="Current image" 
                     style="max-width: 200px; max-height: 200px; border: 1px solid #e5e7eb; border-radius: 0.25rem; padding: 0.5rem; margin-bottom: 0.5rem; display: block;">
            <?php else: ?>
                <p style="color: #9ca3af;">No image</p>
                    <?php endif; ?>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; margin-top: 1rem;">Upload New Image (optional)</label>
            <input type="file" name="image" accept="image/*" class="form-control">
                    <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                        Leave empty to keep current image. Allowed formats: JPG, PNG, GIF, WebP. Max size: 5MB
                    </small>
                </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Price *</label>
                    <?php 
                // Validate price - if corrupted, show 0
                $editPrice = validatePrice($productData['price'] ?? 0);
                if ($editPrice == 0 && isset($productData['price']) && (float)$productData['price'] > 100000) {
                    echo '<div class="alert alert-error" style="margin-bottom: 0.5rem; padding: 0.75rem; background-color: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; border-radius: 0.25rem;">Warning: Current price appears to be corrupted. Please enter a valid price.</div>';
                    }
                    ?>
                <input type="number" name="price" step="0.01" min="0" value="<?php echo $editPrice; ?>" required class="form-control">
                </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Discount Price</label>
                    <?php 
                $editDiscount = null;
                if (isset($productData['discount_price']) && $productData['discount_price'] !== null) {
                    $editDiscount = validatePrice($productData['discount_price']);
                    if ($editDiscount == 0) {
                        $editDiscount = null;
                        }
                    }
                    ?>
                <input type="number" name="discount_price" step="0.01" min="0" value="<?php echo $editDiscount !== null ? $editDiscount : ''; ?>" class="form-control">
                    <small style="color: #6b7280; font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                    Leave empty if no discount
                    </small>
                </div>
                </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Stock Quantity</label>
                <input type="number" name="stock_quantity" min="0" value="<?php echo e($productData['stock_quantity'] ?? 0); ?>" class="form-control">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                    <select name="status" class="form-control">
                    <option value="active" <?php echo ($productData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($productData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="featured" value="1" <?php echo ($productData['featured'] ?? 0) ? 'checked' : ''; ?>>
                <span>Featured Product</span>
            </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="<?php echo SITE_URL; ?>admin/products/manage.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
