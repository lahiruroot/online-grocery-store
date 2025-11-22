<?php
/**
 * Debug Configuration File
 * Use this to verify your XAMPP setup is working correctly
 * Access at: http://localhost/online-grocery-store/debug-config.php
 * DELETE THIS FILE AFTER VERIFYING YOUR SETUP!
 */

require_once __DIR__ . '/config/constants.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Debug - XAMPP Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .debug-section h2 {
            color: #10b981;
            margin-top: 0;
        }
        .debug-item {
            margin: 10px 0;
            padding: 10px;
            background: #f9fafb;
            border-left: 3px solid #3b82f6;
        }
        .debug-item strong {
            display: inline-block;
            min-width: 200px;
            color: #1f2937;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .test-link {
            display: inline-block;
            margin: 5px 10px 5px 0;
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .test-link:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <h1>🔧 XAMPP Configuration Debug</h1>
    <p><strong>⚠️ IMPORTANT:</strong> Delete this file after verifying your setup for security!</p>

    <div class="debug-section">
        <h2>📍 URL Configuration</h2>
        <div class="debug-item">
            <strong>SITE_URL:</strong> 
            <code><?php echo SITE_URL; ?></code>
            <?php if (strpos(SITE_URL, '/online-grocery-store/') !== false): ?>
                <span class="success">✓ Correct</span>
            <?php else: ?>
                <span class="warning">⚠ Check if this matches your project path</span>
            <?php endif; ?>
        </div>
        <div class="debug-item">
            <strong>Protocol:</strong> 
            <code><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; ?></code>
        </div>
        <div class="debug-item">
            <strong>Host:</strong> 
            <code><?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?></code>
        </div>
        <div class="debug-item">
            <strong>Document Root:</strong> 
            <code><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></code>
        </div>
        <div class="debug-item">
            <strong>Script Filename:</strong> 
            <code><?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Not set'; ?></code>
        </div>
        <div class="debug-item">
            <strong>Request URI:</strong> 
            <code><?php echo $_SERVER['REQUEST_URI'] ?? 'Not set'; ?></code>
        </div>
        <div class="debug-item">
            <strong>Script Name:</strong> 
            <code><?php echo $_SERVER['SCRIPT_NAME'] ?? 'Not set'; ?></code>
        </div>
    </div>

    <div class="debug-section">
        <h2>🗂️ File Paths</h2>
        <div class="debug-item">
            <strong>BASE_PATH:</strong> 
            <code><?php echo BASE_PATH; ?></code>
            <?php if (is_dir(BASE_PATH)): ?>
                <span class="success">✓ Exists</span>
            <?php else: ?>
                <span class="error">✗ Not found</span>
            <?php endif; ?>
        </div>
        <div class="debug-item">
            <strong>PUBLIC_PATH:</strong> 
            <code><?php echo PUBLIC_PATH; ?></code>
            <?php if (is_dir(PUBLIC_PATH)): ?>
                <span class="success">✓ Exists</span>
            <?php else: ?>
                <span class="error">✗ Not found</span>
            <?php endif; ?>
        </div>
        <div class="debug-item">
            <strong>UPLOADS_PATH:</strong> 
            <code><?php echo UPLOADS_PATH; ?></code>
            <?php if (is_dir(UPLOADS_PATH)): ?>
                <span class="success">✓ Exists</span>
            <?php else: ?>
                <span class="warning">⚠ Directory not found (will be created automatically)</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="debug-section">
        <h2>🎨 CSS/JS Assets</h2>
        <div class="debug-item">
            <strong>Main CSS:</strong> 
            <a href="<?php echo SITE_URL; ?>public/css/style.css" target="_blank" class="test-link">Test Link</a>
            <code><?php echo SITE_URL; ?>public/css/style.css</code>
            <?php if (file_exists(PUBLIC_PATH . 'css/style.css')): ?>
                <span class="success">✓ File exists</span>
            <?php else: ?>
                <span class="error">✗ File not found</span>
            <?php endif; ?>
        </div>
        <div class="debug-item">
            <strong>Index CSS:</strong> 
            <a href="<?php echo SITE_URL; ?>public/css/index.css" target="_blank" class="test-link">Test Link</a>
            <code><?php echo SITE_URL; ?>public/css/index.css</code>
            <?php if (file_exists(PUBLIC_PATH . 'css/index.css')): ?>
                <span class="success">✓ File exists</span>
            <?php else: ?>
                <span class="warning">⚠ File not found (optional)</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="debug-section">
        <h2>🗄️ Database Configuration</h2>
        <?php
        try {
            require_once __DIR__ . '/config/db.php';
            $db = Database::getInstance()->getConnection();
            ?>
            <div class="debug-item">
                <strong>Database Connection:</strong> 
                <span class="success">✓ Connected</span>
            </div>
            <?php
            // Test query
            $stmt = $db->query("SELECT DATABASE() as db_name");
            $dbName = $stmt->fetch()['db_name'];
            ?>
            <div class="debug-item">
                <strong>Current Database:</strong> 
                <code><?php echo $dbName; ?></code>
            </div>
        <?php
        } catch (Exception $e) {
            ?>
            <div class="debug-item">
                <strong>Database Connection:</strong> 
                <span class="error">✗ Failed: <?php echo e($e->getMessage()); ?></span>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>🔗 Test Links</h2>
        <p>Click these links to test if pages are accessible:</p>
        <a href="<?php echo SITE_URL; ?>" class="test-link">Home Page</a>
        <a href="<?php echo SITE_URL; ?>admin/index.php" class="test-link">Admin Dashboard</a>
        <a href="<?php echo SITE_URL; ?>auth/login.php" class="test-link">Login Page</a>
        <a href="<?php echo SITE_URL; ?>pages/products.php" class="test-link">Products Page</a>
        <a href="<?php echo SITE_URL; ?>create-admin.php" class="test-link">Create Admin</a>
    </div>

    <div class="debug-section" style="background: #fef3c7; border-left: 4px solid #f59e0b;">
        <h2>⚠️ Security Note</h2>
        <p><strong>Please delete this file after verifying your setup!</strong></p>
        <p>This file exposes sensitive configuration information and should not be accessible in production.</p>
    </div>
</body>
</html>

