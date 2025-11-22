<?php

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

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product->delete((int)$_GET['delete']);
    redirect('manage.php');
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$result = $product->getAll([], $page, ITEMS_PER_PAGE);
$products = $result['products'];
$totalPages = $result['pages'];

$page_title = 'Manage Products';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Manage Products</h1>
            <a href="<?php echo SITE_URL; ?>admin/products/add.php" class="btn btn-primary">Add New Product</a>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            <p>No products found. <a href="<?php echo SITE_URL; ?>admin/products/add.php">Add your first product</a></p>
        </div>
    <?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb;">
                    <th style="text-align: left; padding: 1rem;">Image</th>
                    <th style="text-align: left; padding: 1rem;">Name</th>
                    <th style="text-align: left; padding: 1rem;">Category</th>
                    <th style="text-align: right; padding: 1rem;">Price</th>
                    <th style="text-align: center; padding: 1rem;">Stock</th>
                    <th style="text-align: center; padding: 1rem;">Status</th>
                    <th style="text-align: center; padding: 1rem;">Actions</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach ($products as $prod): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem;">
                            <?php if (!empty($prod['image'])): ?>
                                <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($prod['image']); ?>" 
                                     alt="<?php echo e($prod['name']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                        <?php endif; ?>
                    </td>
                        <td style="padding: 1rem;">
                            <strong><?php echo e($prod['name']); ?></strong>
                        </td>
                        <td style="padding: 1rem;">
                            <?php echo e($prod['category_name'] ?? 'N/A'); ?>
                        </td>
                        <td style="text-align: right; padding: 1rem;">
                            <?php echo formatPrice($product->getPrice($prod)); ?>
                        </td>
                        <td style="text-align: center; padding: 1rem;">
                            <?php echo $prod['stock_quantity']; ?>
                    </td>
                        <td style="text-align: center; padding: 1rem;">
                            <span style="padding: 0.25rem 0.75rem; border-radius: 0.25rem; background: <?php echo $prod['status'] === 'active' ? '#dcfce7' : '#fee2e2'; ?>;">
                                <?php echo ucfirst($prod['status']); ?>
                        </span>
                    </td>
                        <td style="text-align: center; padding: 1rem;">
                            <a href="<?php echo SITE_URL; ?>admin/products/edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-small btn-primary">Edit</a>
                            <a href="?delete=<?php echo $prod['id']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this product?');" 
                               class="btn btn-small" style="background: #ef4444; color: white;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination mt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
