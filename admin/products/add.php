<?php
/**
 * Add Product
 * Admin add new product
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Category.php';

if (!isAdmin()) {
    redirect('index.php');
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

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4" style="max-width: 800px;">
    <h1>Add New Product</h1>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mt-4"><?php echo e($success); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Product Name *</label>
            <input type="text" name="name" value="<?php echo e($formData['name'] ?? ''); ?>" required class="form-control">
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Category *</label>
                    <select name="category_id" required class="form-control">
                        <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($formData['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['name']); ?>
                            </option>
                <?php endforeach; ?>
                    </select>
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
            <textarea name="description" rows="4" class="form-control"><?php echo e($formData['description'] ?? ''); ?></textarea>
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Product Image *</label>
            <input type="file" name="image" accept="image/*" required class="form-control">
                </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Price *</label>
                <input type="number" name="price" step="0.01" min="0" value="<?php echo e($formData['price'] ?? ''); ?>" required class="form-control">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Discount Price</label>
                <input type="number" name="discount_price" step="0.01" min="0" value="<?php echo e($formData['discount_price'] ?? ''); ?>" class="form-control">
            </div>
                </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Stock Quantity</label>
                <input type="number" name="stock_quantity" min="0" value="<?php echo e($formData['stock_quantity'] ?? 0); ?>" class="form-control">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo ($formData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($formData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
                </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="featured" value="1" <?php echo ($formData['featured'] ?? 0) ? 'checked' : ''; ?>>
                <span>Featured Product</span>
            </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Create Product</button>
                    <a href="<?php echo SITE_URL; ?>admin/products/manage.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
