<?php
// Prevent direct access
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Site Information
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'GroceryKing - Online Grocery Store');
}

if (!defined('SITE_DESCRIPTION')) {
    define('SITE_DESCRIPTION', 'Fresh Groceries Delivered to Your Door');
}

// Auto-detect base URL
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check if running in Docker
    $isDocker = getenv('DB_HOST') === 'db' || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '8080') !== false);
    
    if ($isDocker) {
        $siteUrl = $protocol . '://' . $host . '/';
    } else {
        $siteUrl = $protocol . '://' . $host . '/online-grocery-store/';
    }
    
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
    define('UPLOADS_PATH', BASE_PATH . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
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

if (!defined('BLOGS_PER_PAGE')) {
    define('BLOGS_PER_PAGE', 9);
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
