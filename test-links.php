<?php
require_once __DIR__ . '/config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Test - Verify SITE_URL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-box h2 {
            color: #10b981;
            margin-top: 0;
        }
        .url-display {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            margin: 10px 0;
            word-break: break-all;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .link-test {
            margin: 10px 0;
            padding: 10px;
            background: #f9fafb;
            border-left: 3px solid #3b82f6;
        }
        .link-test a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .link-test a:hover {
            color: #10b981;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>🔗 Link Test & Verification</h1>
    
    <div class="test-box">
        <h2>📍 Detected SITE_URL</h2>
        <div class="url-display">
            <strong>SITE_URL:</strong> <?php echo SITE_URL; ?>
        </div>
        <?php if (strpos(SITE_URL, 'grocery-king') !== false): ?>
            <p class="success">✓ Correctly detected 'grocery-king' folder</p>
        <?php elseif (strpos(SITE_URL, 'online-grocery-store') !== false): ?>
            <p class="success">✓ Detected 'online-grocery-store' folder</p>
        <?php else: ?>
            <p class="error">✗ Folder detection may be incorrect</p>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h2>🔍 Server Information</h2>
        <div class="url-display">
            <strong>DOCUMENT_ROOT:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?><br>
            <strong>SCRIPT_FILENAME:</strong> <?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Not set'; ?><br>
            <strong>SCRIPT_NAME:</strong> <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Not set'; ?><br>
            <strong>REQUEST_URI:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'Not set'; ?>
        </div>
    </div>

    <div class="test-box">
        <h2>🧪 Test Links (Click to verify)</h2>
        
        <div class="link-test">
            <strong>Home:</strong> 
            <a href="<?php echo SITE_URL; ?>" target="_blank"><?php echo SITE_URL; ?></a>
            <br><code>href="<?php echo SITE_URL; ?>"</code>
        </div>

        <div class="link-test">
            <strong>Admin Dashboard:</strong> 
            <a href="<?php echo SITE_URL; ?>admin/index.php" target="_blank"><?php echo SITE_URL; ?>admin/index.php</a>
            <br><code>href="<?php echo SITE_URL; ?>admin/index.php"</code>
        </div>

        <div class="link-test">
            <strong>User Dashboard:</strong> 
            <a href="<?php echo SITE_URL; ?>user/dashboard.php" target="_blank"><?php echo SITE_URL; ?>user/dashboard.php</a>
            <br><code>href="<?php echo SITE_URL; ?>user/dashboard.php"</code>
        </div>

        <div class="link-test">
            <strong>Login:</strong> 
            <a href="<?php echo SITE_URL; ?>auth/login.php" target="_blank"><?php echo SITE_URL; ?>auth/login.php</a>
            <br><code>href="<?php echo SITE_URL; ?>auth/login.php"</code>
        </div>

        <div class="link-test">
            <strong>Products:</strong> 
            <a href="<?php echo SITE_URL; ?>pages/products.php" target="_blank"><?php echo SITE_URL; ?>pages/products.php</a>
            <br><code>href="<?php echo SITE_URL; ?>pages/products.php"</code>
        </div>

        <div class="link-test">
            <strong>Admin Products:</strong> 
            <a href="<?php echo SITE_URL; ?>admin/products/manage.php" target="_blank"><?php echo SITE_URL; ?>admin/products/manage.php</a>
            <br><code>href="<?php echo SITE_URL; ?>admin/products/manage.php"</code>
        </div>
    </div>

    <div class="test-box">
        <h2>✅ Expected Format</h2>
        <p>All links should follow this pattern:</p>
        <div class="url-display">
            <code>http://localhost/grocery-king/[path]</code>
        </div>
        <p>For example:</p>
        <ul>
            <li><code>http://localhost/grocery-king/</code> (home)</li>
            <li><code>http://localhost/grocery-king/admin/index.php</code> (admin)</li>
            <li><code>http://localhost/grocery-king/user/dashboard.php</code> (user)</li>
            <li><code>http://localhost/grocery-king/pages/products.php</code> (products)</li>
        </ul>
    </div>

    <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px;">
        <p><a href="<?php echo SITE_URL; ?>" style="color: #10b981; text-decoration: none; font-weight: 600;">← Back to Home</a></p>
        <p style="margin-top: 10px; color: #6b7280; font-size: 0.9rem;">Delete this file after testing</p>
    </div>
</body>
</html>

