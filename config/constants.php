<?php
// Prevent direct access
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Site Information
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'GroceryKing.com');
}

if (!defined('SITE_DESCRIPTION')) {
    define('SITE_DESCRIPTION', 'Fresh Groceries Delivered to Your Door');
}

// Auto-detect base URL
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // XAMPP/localhost - detect base path using multiple methods
    // Primary folder: grocery-king
    $basePath = null;
    
    // Method 1: Use SCRIPT_FILENAME (most reliable - always available)
    if (isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
        $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
        
        // Calculate relative path from document root
        if (strpos($scriptFile, $docRoot) === 0) {
            $relativePath = substr($scriptFile, strlen($docRoot));
            $relativePath = str_replace('\\', '/', $relativePath);
            $relativePath = trim($relativePath, '/');
            
            // Extract the base folder (first part of path)
            $pathParts = explode('/', $relativePath);
            if (!empty($pathParts[0])) {
                // Check if it's grocery-king or online-grocery-store
                if ($pathParts[0] === 'grocery-king' || $pathParts[0] === 'online-grocery-store') {
                    $basePath = '/' . $pathParts[0] . '/';
                } elseif ($pathParts[0] !== 'index.php' && $pathParts[0] !== '') {
                    // Use first folder as base
                    $basePath = '/' . $pathParts[0] . '/';
                }
            }
        }
    }
    
    // Method 2: Use SCRIPT_NAME as fallback
    if (empty($basePath) && isset($_SERVER['SCRIPT_NAME'])) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        if (strpos($scriptName, '/grocery-king/') !== false) {
            $basePath = '/grocery-king/';
        } elseif (strpos($scriptName, '/online-grocery-store/') !== false) {
            $basePath = '/online-grocery-store/';
        } else {
            // Extract folder from SCRIPT_NAME
            $pathParts = explode('/', trim($scriptName, '/'));
            if (!empty($pathParts[0]) && $pathParts[0] !== 'index.php') {
                $basePath = '/' . $pathParts[0] . '/';
            }
        }
    }
    
    // Method 3: Use REQUEST_URI as final fallback
    if (empty($basePath) && isset($_SERVER['REQUEST_URI'])) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = strtok($requestUri, '?');
        $pathParts = explode('/', trim($requestUri, '/'));
        
        if (!empty($pathParts[0])) {
            $firstPart = $pathParts[0];
            if ($firstPart === 'grocery-king' || $firstPart === 'online-grocery-store') {
                $basePath = '/' . $firstPart . '/';
            }
        }
    }
    
    // Default fallback to grocery-king
    if (empty($basePath)) {
        $basePath = '/grocery-king/';
    }
    
    // Ensure basePath ends with /
    $basePath = rtrim($basePath, '/') . '/';
    
    $siteUrl = $protocol . '://' . $host . $basePath;
    
    define('SITE_URL', $siteUrl);
}

// File Paths
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . 'public' . DIRECTORY_SEPARATOR);
}

if (!defined('UPLOADS_PATH')) {
    $uploadsPath = BASE_PATH . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    
    // Ensure uploads directory exists and is writable
    if (!is_dir($uploadsPath)) {
        @mkdir($uploadsPath, 0777, true);
    }
    
    // Ensure directory is writable
    if (is_dir($uploadsPath) && !is_writable($uploadsPath)) {
        @chmod($uploadsPath, 0777);
    }
    
    define('UPLOADS_PATH', $uploadsPath);
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', BASE_PATH . 'config' . DIRECTORY_SEPARATOR);
}

// Pagination
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 12);
}

if (!defined('REVIEWS_PER_PAGE')) {
    define('REVIEWS_PER_PAGE', 10);
}


// Admin
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', SITE_URL . 'admin/');
}

// Session
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600 * 24); // 24 hours
}

// Currency
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'LKR');
}

if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'LKR');
}

// File Upload
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}

if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
}

// Security
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8);
}

// Timezone
date_default_timezone_set('America/New_York');
