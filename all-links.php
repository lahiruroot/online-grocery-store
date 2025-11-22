<?php
require_once __DIR__ . '/config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo SITE_URL; ?>">
    <title>All Links - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .links-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .links-section {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .links-section h2 {
            color: #10b981;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .link-item {
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
            border-left: 3px solid #3b82f6;
            transition: all 0.2s;
        }
        .link-item:hover {
            background: #f3f4f6;
            border-left-color: #10b981;
        }
        .link-item a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            display: block;
        }
        .link-item a:hover {
            color: #10b981;
        }
        .link-url {
            font-family: monospace;
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 5px;
            word-break: break-all;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
        }
        .badge-public {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-user {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-admin {
            background: #d1fae5;
            color: #065f46;
        }
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .info-box strong {
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="links-container">
        <h1>🔗 All Application Links</h1>
        
        <div class="info-box">
            <strong>Current Base URL:</strong> <code><?php echo SITE_URL; ?></code><br>
            <strong>Folder Name:</strong> Auto-detected from your setup
        </div>

        <!-- Public Pages -->
        <div class="links-section">
            <h2>🏠 Public Pages <span class="badge badge-public">No Login</span></h2>
            <div class="link-grid">
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>">Home Page</a>
                    <div class="link-url">index.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/products.php">Products</a>
                    <div class="link-url">pages/products.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/categories.php">Categories</a>
                    <div class="link-url">pages/categories.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/about.php">About</a>
                    <div class="link-url">pages/about.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/contact.php">Contact</a>
                    <div class="link-url">pages/contact.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/reviews.php">Reviews</a>
                    <div class="link-url">pages/reviews.php</div>
                </div>
            </div>
        </div>

        <!-- Authentication -->
        <div class="links-section">
            <h2>🔐 Authentication</h2>
            <div class="link-grid">
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>auth/login.php">Login</a>
                    <div class="link-url">auth/login.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>auth/register.php">Register</a>
                    <div class="link-url">auth/register.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>create-admin.php">Create Admin</a>
                    <div class="link-url">create-admin.php</div>
                </div>
            </div>
        </div>

        <!-- User Pages -->
        <div class="links-section">
            <h2>👤 User Pages <span class="badge badge-user">Login Required</span></h2>
            <div class="link-grid">
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>user/dashboard.php">User Dashboard</a>
                    <div class="link-url">user/dashboard.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>user/orders.php">My Orders</a>
                    <div class="link-url">user/orders.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>user/profile.php">My Profile</a>
                    <div class="link-url">user/profile.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/wishlist.php">Wishlist</a>
                    <div class="link-url">pages/wishlist.php</div>
                </div>
            </div>
        </div>

        <!-- Shopping Cart -->
        <div class="links-section">
            <h2>🛒 Shopping Cart</h2>
            <div class="link-grid">
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>cart/view-cart.php">View Cart</a>
                    <div class="link-url">cart/view-cart.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>pages/checkout.php">Checkout</a>
                    <div class="link-url">pages/checkout.php</div>
                </div>
            </div>
        </div>

        <!-- Admin Pages -->
        <div class="links-section">
            <h2>👨‍💼 Admin Pages <span class="badge badge-admin">Admin Only</span></h2>
            <div class="link-grid">
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/index.php">Admin Dashboard</a>
                    <div class="link-url">admin/index.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/products/manage.php">Manage Products</a>
                    <div class="link-url">admin/products/manage.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/products/add.php">Add Product</a>
                    <div class="link-url">admin/products/add.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/categories/manage.php">Manage Categories</a>
                    <div class="link-url">admin/categories/manage.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/categories/add.php">Add Category</a>
                    <div class="link-url">admin/categories/add.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/orders/manage.php">Manage Orders</a>
                    <div class="link-url">admin/orders/manage.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/users/manage.php">Manage Users</a>
                    <div class="link-url">admin/users/manage.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/blogs/manage.php">Manage Blogs</a>
                    <div class="link-url">admin/blogs/manage.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin/reviews/manage.php">Manage Reviews</a>
                    <div class="link-url">admin/reviews/manage.php</div>
                </div>
            </div>
        </div>

        <!-- Utility Pages -->
        <div class="links-section">
            <h2>🛠️ Utility Pages</h2>
            <div class="link-grid">
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>admin-routes.php">Admin Routes Guide</a>
                    <div class="link-url">admin-routes.php</div>
                </div>
                <div class="link-item">
                    <a href="<?php echo SITE_URL; ?>debug-config.php">Debug Configuration</a>
                    <div class="link-url">debug-config.php</div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <p><strong>📝 Note:</strong> Some pages require login or admin access. You'll be redirected if you don't have permission.</p>
            <p style="margin-top: 10px;"><a href="<?php echo SITE_URL; ?>" style="color: #10b981; text-decoration: none; font-weight: 600;">← Back to Home</a></p>
        </div>
    </div>
</body>
</html>

