<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => (int)$_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

/**
 * Redirect to a URL
 */
function redirect($path) {
    // Prevent any output before redirect
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    $url = strpos($path, 'http') === 0 ? $path : SITE_URL . ltrim($path, '/');
    
    // Ensure no output has been sent
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        // Fallback if headers already sent
        echo '<script>window.location.href="' . htmlspecialchars($url) . '";</script>';
        echo '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '">';
        exit();
    }
}

/**
 * Validate price (check if it's reasonable and not corrupted)
 * Returns float for calculations, but validates from string
 */
function validatePrice($price) {
    if ($price === null || $price === '') {
        return 0;
    }
    
    // Convert to string first, then clean
    $priceStr = (string)$price;
    $priceStr = trim($priceStr);
    $priceStr = preg_replace('/[^0-9.]/', '', $priceStr);
    
    // Validate format
    if (!preg_match('/^\d+\.?\d*$/', $priceStr)) {
        return 0;
    }
    
    $priceFloat = (float)$priceStr;
    
    // Maximum reasonable price: $100,000
    // If price is corrupted or invalid, return 0
    if ($priceFloat <= 0 || $priceFloat > 100000) {
        return 0;
    }
    
    return $priceFloat;
}

/**
 * Format price with currency symbol
 * Accepts string or numeric, always works with strings internally
 */
function formatPrice($price) {
    // If input is already a clean string like "50.00", use it directly
    if (is_string($price) && preg_match('/^\d+\.\d{2}$/', $price)) {
        $priceFloat = (float)$price;
        if ($priceFloat > 0 && $priceFloat <= 100000) {
            return '$' . $price; // Use $ directly, don't use CURRENCY_SYMBOL constant
        }
    }
    
    // Convert to string
    $priceStr = is_string($price) ? trim($price) : trim((string)$price);
    
    // Remove any non-numeric characters except decimal point
    $priceStr = preg_replace('/[^0-9.]/', '', $priceStr);
    
    // Validate format
    if (!preg_match('/^\d+\.?\d*$/', $priceStr) || empty($priceStr) || $priceStr === '.') {
        return '$0.00';
    }
    
    // Split by decimal point
    $parts = explode('.', $priceStr, 2);
    $wholePart = $parts[0];
    $decimalPart = isset($parts[1]) ? substr($parts[1], 0, 2) : '00';
    $decimalPart = str_pad($decimalPart, 2, '0', STR_PAD_RIGHT);
    
    // Validate length
    if (strlen($wholePart) > 6) {
        return '$0.00';
    }
    
    $wholeInt = (int)$wholePart;
    if ($wholeInt < 0 || $wholeInt > 100000) {
        return '$0.00';
    }
    
    // Return formatted - use direct string concatenation
    return '$' . $wholePart . '.' . $decimalPart;
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL-friendly slug from text
 */
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get star rating HTML
 */
function getStarRating($rating) {
    $rating = (int)$rating;
    $rating = max(1, min(5, $rating));
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<span class="star filled">★</span>';
        } else {
            $stars .= '<span class="star">☆</span>';
        }
    }
    return $stars;
}

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
}

/**
 * Calculate discount percentage
 */
function calculateDiscountPercent($price, $discountPrice) {
    if ($price <= 0 || $discountPrice >= $price) {
        return 0;
    }
    return round((($price - $discountPrice) / $price) * 100);
}

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Validate uploaded file
 */
function validateUploadedFile($file, $allowedTypes = null, $maxSize = null) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload error'];
    }
    
    if ($maxSize === null) {
        $maxSize = MAX_FILE_SIZE;
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size exceeds maximum allowed size'];
    }
    
    if ($allowedTypes === null) {
        $allowedTypes = ALLOWED_IMAGE_TYPES;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    return ['valid' => true];
}

/**
 * Upload file and return filename
 */
function uploadFile($file, $destinationDir = null) {
    if ($destinationDir === null) {
        $destinationDir = UPLOADS_PATH;
    }
    
    // Normalize directory path - ensure it ends with directory separator
    $destinationDir = rtrim($destinationDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    
    // Ensure directory exists
    if (!is_dir($destinationDir)) {
        if (!mkdir($destinationDir, 0777, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory: ' . $destinationDir];
        }
    }
    
    // Check if directory is writable
    if (!is_writable($destinationDir)) {
        // Try to make it writable
        @chmod($destinationDir, 0777);
        if (!is_writable($destinationDir)) {
            return ['success' => false, 'error' => 'Upload directory is not writable: ' . $destinationDir];
        }
    }
    
    // Validate file
    $validation = validateUploadedFile($file);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }
    
    // Check if temp file exists
    if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Temporary file not found'];
    }
    
    // Get file extension
    $extension = getFileExtension($file['name']);
    if (empty($extension)) {
        return ['success' => false, 'error' => 'Invalid file extension'];
    }
    
    // Generate unique filename
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $destination = $destinationDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Set proper permissions on uploaded file
        @chmod($destination, 0644);
        return ['success' => true, 'filename' => $filename];
    }
    
    // Provide detailed error message
    $errorMsg = 'Failed to move uploaded file';
    if (!is_writable($destinationDir)) {
        $errorMsg .= ': Directory not writable';
    } elseif (!file_exists($file['tmp_name'])) {
        $errorMsg .= ': Temporary file missing';
    } else {
        $errorMsg .= ': Check directory permissions for ' . $destinationDir;
    }
    
    return ['success' => false, 'error' => $errorMsg];
}

/**
 * Delete file
 */
function deleteFile($filename, $directory = null) {
    if ($directory === null) {
        $directory = UPLOADS_PATH;
    }
    
    $filepath = $directory . $filename;
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get pagination offset
 */
function getPaginationOffset($page, $perPage = ITEMS_PER_PAGE) {
    $page = max(1, (int)$page);
    return ($page - 1) * $perPage;
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= '<a href="' . $baseUrl . '?page=' . $prevPage . '" class="pagination-link">Previous</a>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="pagination-link active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= '<a href="' . $baseUrl . '?page=' . $nextPage . '" class="pagination-link">Next</a>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['type' => $type, 'message' => $message];
        }
    return null;
    }
    
/**
 * Check if string is valid JSON
 */
function isValidJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
