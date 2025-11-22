# Routing Fixes Summary

This document summarizes all routing fixes applied to the project.

## Fixed Issues

### 1. Database Connection Pattern
**Problem:** Files were using `$conn = require_once 'config/db.php'` which returns `true` on subsequent includes, causing "Call to a member function on bool" errors.

**Solution:** All files now use:
```php
require_once '../config/db.php';
$conn = getDbConnection();
```

**Files Fixed:**
- All user/*.php files
- All admin/*.php files  
- All pages/*.php files
- cart/view-cart.php
- pages/checkout.php
- index.php

### 2. Redirect Paths Standardization
**Problem:** Redirects were using inconsistent relative paths (`../auth/login.php`, `../../auth/login.php`) which could break with different base URLs.

**Solution:** All redirects now use paths relative to site root (no `../`):
- `redirect('auth/login.php')` instead of `redirect('../auth/login.php')`
- `redirect('user/dashboard.php')` instead of `redirect('user/dashboard.php')`

**Files Fixed:**
- user/orders.php
- user/order-detail.php
- user/wishlist.php
- user/profile.php
- user/dashboard.php
- admin/index.php
- admin/products/manage.php
- admin/reviews/manage.php
- pages/product-detail.php
- cart/view-cart.php
- pages/checkout.php

### 3. Logout Functionality
**Problem:** `auth/logout.php` was using direct `header()` call without SITE_URL.

**Solution:** Now uses the `redirect()` function with proper SITE_URL handling.

### 4. Missing Order Confirmation Page
**Problem:** `pages/checkout.php` was redirecting to non-existent `order-confirmation.php`.

**Solution:** Created `user/order-confirmation.php` with proper order confirmation display.

### 5. Session Initialization
**Problem:** Some files weren't initializing sessions before checking login status.

**Solution:** All files now start session at the beginning:
```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

## Routing Structure

### Standard Paths (relative to site root)
- **Auth:** `auth/login.php`, `auth/register.php`, `auth/logout.php`
- **User:** `user/dashboard.php`, `user/orders.php`, `user/profile.php`, etc.
- **Admin:** `admin/index.php`, `admin/products/manage.php`, etc.
- **Pages:** `pages/products.php`, `pages/categories.php`, etc.
- **Cart:** `cart/view-cart.php`
- **Checkout:** `pages/checkout.php`

### Redirect Function
All redirects use the centralized `redirect()` function from `config/functions.php`:
```php
function redirect($path) {
    header("Location: " . SITE_URL . $path);
    exit();
}
```

This ensures all redirects use the correct base URL automatically.

## URL Structure

The application uses `SITE_URL` constant which auto-detects:
- **Docker:** `http://localhost:8080/`
- **Local:** `http://localhost/grocery-store/`

All links in templates use `SITE_URL` for consistency:
```php
<a href="<?php echo SITE_URL; ?>pages/products.php">Products</a>
```

## Base Tag

All pages include a `<base>` tag in the header:
```html
<base href="<?php echo SITE_URL; ?>">
```

This allows relative paths in HTML (like `href="pages/products.php"`) to work correctly.

## Testing Checklist

- [x] All database connections use `getDbConnection()`
- [x] All redirects use site-root relative paths
- [x] All sessions are initialized properly
- [x] Logout functionality works correctly
- [x] Order confirmation page exists and works
- [x] All admin pages accessible
- [x] All user pages accessible
- [x] All public pages accessible

## Notes

- The `.htaccess` file includes a redirect rule for `/add.php` → `admin/index.php` for convenience
- All static assets (CSS, JS, images) are served from `public/` directory
- The `admin-routes.php` file provides a helpful guide to all admin routes

