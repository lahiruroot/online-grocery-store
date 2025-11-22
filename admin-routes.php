<?php
/**
 * Admin Routes Helper
 * This page shows all available admin routes
 * Access: http://localhost:8080/admin-routes.php
 */

require_once 'config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo SITE_URL; ?>">
    <title>Admin Routes - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .routes-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .route-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .route-card h3 {
            color: #10b981;
            margin-bottom: 10px;
        }
        .route-list {
            list-style: none;
            padding: 0;
        }
        .route-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .route-list li:last-child {
            border-bottom: none;
        }
        .route-list a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .route-list a:hover {
            text-decoration: underline;
        }
        .route-url {
            font-family: monospace;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="routes-container">
        <h1>Admin Routes Guide</h1>
        <p>Here are all available admin routes in the application:</p>

        <div class="route-card">
            <h3>📦 Products</h3>
            <ul class="route-list">
                <li><a href="admin/products/manage.php"><span class="route-url">admin/products/manage.php</span> - Manage Products</a></li>
                <li><a href="admin/products/add.php"><span class="route-url">admin/products/add.php</span> - Add New Product</a></li>
                <li><a href="admin/products/edit.php?id=1"><span class="route-url">admin/products/edit.php?id=1</span> - Edit Product</a></li>
            </ul>
        </div>

        <div class="route-card">
            <h3>📁 Categories</h3>
            <ul class="route-list">
                <li><a href="admin/categories/manage.php"><span class="route-url">admin/categories/manage.php</span> - Manage Categories</a></li>
                <li><a href="admin/categories/add.php"><span class="route-url">admin/categories/add.php</span> - Add New Category</a></li>
                <li><a href="admin/categories/edit.php?id=1"><span class="route-url">admin/categories/edit.php?id=1</span> - Edit Category</a></li>
            </ul>
        </div>

        <div class="route-card">
            <h3>📝 Blogs</h3>
            <ul class="route-list">
                <li><a href="admin/blogs/manage.php"><span class="route-url">admin/blogs/manage.php</span> - Manage Blogs</a></li>
                <li><a href="admin/blogs/add.php"><span class="route-url">admin/blogs/add.php</span> - Add New Blog</a></li>
                <li><a href="admin/blogs/edit.php?id=1"><span class="route-url">admin/blogs/edit.php?id=1</span> - Edit Blog</a></li>
            </ul>
        </div>

        <div class="route-card">
            <h3>👥 Users & Orders</h3>
            <ul class="route-list">
                <li><a href="admin/users/manage.php"><span class="route-url">admin/users/manage.php</span> - Manage Users</a></li>
                <li><a href="admin/orders/manage.php"><span class="route-url">admin/orders/manage.php</span> - Manage Orders</a></li>
                <li><a href="admin/reviews/manage.php"><span class="route-url">admin/reviews/manage.php</span> - Approve Reviews</a></li>
            </ul>
        </div>

        <div class="route-card">
            <h3>🏠 Main Pages</h3>
            <ul class="route-list">
                <li><a href="admin/index.php"><span class="route-url">admin/index.php</span> - Admin Dashboard</a></li>
                <li><a href="user/dashboard.php"><span class="route-url">user/dashboard.php</span> - User Dashboard</a></li>
                <li><a href="index.php"><span class="route-url">index.php</span> - Home Page</a></li>
            </ul>
        </div>

        <div class="route-card" style="background: #fef3c7; border-left: 4px solid #f59e0b;">
            <h3>⚠️ Note</h3>
            <p>There is no <code>add.php</code> file at the root level. Use the specific admin routes above:</p>
            <ul>
                <li>To add a product: <strong>admin/products/add.php</strong></li>
                <li>To add a category: <strong>admin/categories/add.php</strong></li>
                <li>To add a blog: <strong>admin/blogs/add.php</strong></li>
            </ul>
        </div>
    </div>
</body>
</html>

