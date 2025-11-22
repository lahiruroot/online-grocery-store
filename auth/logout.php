<?php
/**
 * Logout Page
 * Destroy session and redirect
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to home
redirect('index.php');
