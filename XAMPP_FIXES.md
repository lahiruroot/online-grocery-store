# XAMPP Fixes Applied

This document summarizes all the fixes applied to make the project work with XAMPP.

## Changes Made

### 1. Enhanced SITE_URL Detection (`config/constants.php`)

**Problem:** SITE_URL was not being detected correctly for XAMPP, causing 404 errors and broken CSS links.

**Solution:** Improved the URL detection logic to use multiple methods:
- Uses `REQUEST_URI` to detect the project path
- Falls back to `SCRIPT_NAME` if needed
- Calculates from document root as final fallback
- Automatically detects `/online-grocery-store/` path

### 2. Updated .htaccess for XAMPP

**Problem:** Apache configuration wasn't properly handling static files and routing.

**Solution:**
- Updated `RewriteBase` to `/online-grocery-store/`
- Changed `Order allow,deny` to `Require all granted` (Apache 2.4+ syntax)
- Added better routing rules for static files

### 3. Fixed Admin Redirect Paths

**Problem:** Admin pages were using incorrect relative paths in redirects, causing navigation issues.

**Solution:** Fixed all redirect calls in admin pages:
- Changed `redirect('manage.php')` to `redirect('admin/products/manage.php')` (or appropriate path)
- Changed `redirect('index.php')` to `redirect('admin/index.php')` for admin-only pages
- All redirects now use full paths relative to site root

**Files Fixed:**
- `admin/products/manage.php`
- `admin/products/edit.php`
- `admin/products/add.php`
- `admin/categories/manage.php`
- `admin/categories/edit.php`
- `admin/categories/add.php`
- `admin/orders/manage.php`

### 4. Created Debug Configuration File

**Added:** `debug-config.php` - A diagnostic tool to verify XAMPP setup:
- Shows current SITE_URL
- Verifies file paths
- Tests CSS/JS asset loading
- Checks database connection
- Provides test links to all pages

**⚠️ Remember to delete this file after verification!**

## Testing Checklist

After applying these fixes, test the following:

- [ ] Home page loads: `http://localhost/online-grocery-store/`
- [ ] CSS styles are applied correctly
- [ ] Admin login: `http://localhost/online-grocery-store/auth/login.php`
- [ ] Admin dashboard: `http://localhost/online-grocery-store/admin/index.php`
- [ ] Products management: `http://localhost/online-grocery-store/admin/products/manage.php`
- [ ] Categories management: `http://localhost/online-grocery-store/admin/categories/manage.php`
- [ ] All navigation links work
- [ ] Images load correctly from `public/uploads/`
- [ ] No 404 errors when navigating between pages

## Common Issues and Solutions

### Issue: Still getting 404 errors

**Solution:**
1. Verify project is in `htdocs/online-grocery-store/`
2. Check Apache `mod_rewrite` is enabled
3. Verify `.htaccess` file exists in project root
4. Check `httpd.conf` has `AllowOverride All` for your directory

### Issue: CSS not loading

**Solution:**
1. Check browser console for 404 errors on CSS files
2. Verify SITE_URL is correct using `debug-config.php`
3. Ensure `public/css/` directory exists and files are present
4. Check file permissions (755 for directories, 644 for files)

### Issue: Database connection fails

**Solution:**
1. Verify MySQL is running in XAMPP Control Panel
2. Check database name is `grocery_store`
3. Verify username is `root` and password is empty (or update `config/db.php`)
4. Import database schema from `sql/schema.sql`

## Next Steps

1. Test all pages and functionality
2. Delete `debug-config.php` after verification
3. Create admin user using `create-admin.php`
4. Start using the application!

---

**All fixes have been applied. The project should now work correctly with XAMPP!**

