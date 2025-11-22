# Complete List of All Links - Grocery King

**Base URL:** `http://localhost/grocery-king/`

All links are based on: `http://localhost/grocery-king/`

---

## 🏠 Public Pages (No Login Required)

### Main Pages
- **Home Page**
  - `http://localhost/grocery-king/`
  - `http://localhost/grocery-king/index.php`

- **Products**
  - `http://localhost/grocery-king/pages/products.php`

- **Categories**
  - `http://localhost/grocery-king/pages/categories.php`
  - `http://localhost/grocery-king/pages/category-products.php?id=1` (with category ID)

- **Product Details**
  - `http://localhost/grocery-king/pages/product-detail.php?id=1` (with product ID)

- **About**
  - `http://localhost/grocery-king/pages/about.php`

- **Contact**
  - `http://localhost/grocery-king/pages/contact.php`

- **Reviews**
  - `http://localhost/grocery-king/pages/reviews.php`

---

## 🔐 Authentication Pages

- **Login**
  - `http://localhost/grocery-king/auth/login.php`

- **Register**
  - `http://localhost/grocery-king/auth/register.php`

- **Logout**
  - `http://localhost/grocery-king/auth/logout.php` (redirects after logout)

- **Create Admin** (First-time setup)
  - `http://localhost/grocery-king/create-admin.php`

---

## 👤 User Pages (Login Required)

- **User Dashboard**
  - `http://localhost/grocery-king/user/dashboard.php`

- **My Orders**
  - `http://localhost/grocery-king/user/orders.php`
  - `http://localhost/grocery-king/user/orders.php?page=2` (pagination)

- **Order Details**
  - `http://localhost/grocery-king/user/order-detail.php?id=1` (with order ID)

- **Order Confirmation**
  - `http://localhost/grocery-king/user/order-confirmation.php?order_id=1` (with order ID)

- **My Profile**
  - `http://localhost/grocery-king/user/profile.php`

- **Wishlist**
  - `http://localhost/grocery-king/pages/wishlist.php` (requires login)

---

## 🛒 Shopping Cart

- **View Cart**
  - `http://localhost/grocery-king/cart/view-cart.php`

- **Checkout**
  - `http://localhost/grocery-king/pages/checkout.php` (requires login and items in cart)

---

## 👨‍💼 Admin Pages (Admin Login Required)

### Admin Dashboard
- **Admin Home**
  - `http://localhost/grocery-king/admin/index.php`

### Product Management
- **Manage Products**
  - `http://localhost/grocery-king/admin/products/manage.php`
  - `http://localhost/grocery-king/admin/products/manage.php?page=2` (pagination)

- **Add New Product**
  - `http://localhost/grocery-king/admin/products/add.php`

- **Edit Product**
  - `http://localhost/grocery-king/admin/products/edit.php?id=1` (with product ID)

- **Delete Product**
  - `http://localhost/grocery-king/admin/products/manage.php?delete=1` (with product ID)

### Category Management
- **Manage Categories**
  - `http://localhost/grocery-king/admin/categories/manage.php`

- **Add New Category**
  - `http://localhost/grocery-king/admin/categories/add.php`

- **Edit Category**
  - `http://localhost/grocery-king/admin/categories/edit.php?id=1` (with category ID)

- **Delete Category**
  - `http://localhost/grocery-king/admin/categories/manage.php?delete=1` (with category ID)

### Blog Management
- **Manage Blogs**
  - `http://localhost/grocery-king/admin/blogs/manage.php`
  - `http://localhost/grocery-king/admin/blogs/manage.php?page=2` (pagination)

- **Add New Blog**
  - `http://localhost/grocery-king/admin/blogs/add.php`

- **Edit Blog**
  - `http://localhost/grocery-king/admin/blogs/edit.php?id=1` (with blog ID)

- **Delete Blog**
  - `http://localhost/grocery-king/admin/blogs/manage.php?delete=1` (with blog ID)

### Order Management
- **Manage Orders**
  - `http://localhost/grocery-king/admin/orders/manage.php`
  - `http://localhost/grocery-king/admin/orders/manage.php?page=2` (pagination)
  - `http://localhost/grocery-king/admin/orders/manage.php?order_id=1&update_status=shipped` (update status)

### User Management
- **Manage Users**
  - `http://localhost/grocery-king/admin/users/manage.php`
  - `http://localhost/grocery-king/admin/users/manage.php?page=2` (pagination)

- **Delete User**
  - `http://localhost/grocery-king/admin/users/manage.php?delete=1` (with user ID)

### Review Management
- **Manage Reviews**
  - `http://localhost/grocery-king/admin/reviews/manage.php`
  - `http://localhost/grocery-king/admin/reviews/manage.php?approve=1` (approve review)
  - `http://localhost/grocery-king/admin/reviews/manage.php?reject=1` (reject review)

---

## 🛠️ Utility Pages

- **Admin Routes Guide**
  - `http://localhost/grocery-king/admin-routes.php`

- **All Links Page** (Interactive)
  - `http://localhost/grocery-king/all-links.php`

- **Debug Configuration** (Delete after use!)
  - `http://localhost/grocery-king/debug-config.php`

---

## 📁 Static Assets

### CSS Files
- `http://localhost/grocery-king/public/css/style.css`
- `http://localhost/grocery-king/public/css/auth.css`
- `http://localhost/grocery-king/public/css/index.css`
- `http://localhost/grocery-king/public/css/products.css`
- `http://localhost/grocery-king/public/css/checkout.css`
- `http://localhost/grocery-king/public/css/profile.css`
- `http://localhost/grocery-king/public/css/categories-section.css`
- `http://localhost/grocery-king/public/css/admin-add-product.css`
- `http://localhost/grocery-king/public/css/admin-add-category.css`

### Images
- `http://localhost/grocery-king/public/images/hero_img.png`
- `http://localhost/grocery-king/public/images/himg.jpg`
- `http://localhost/grocery-king/public/uploads/[filename]` (uploaded product/category images)

---

## 🔗 Quick Reference

### For Customers
1. Home → `http://localhost/grocery-king/`
2. Browse Products → `http://localhost/grocery-king/pages/products.php`
3. View Categories → `http://localhost/grocery-king/pages/categories.php`
4. Product Details → `http://localhost/grocery-king/pages/product-detail.php?id=X`
5. Add to Cart → `http://localhost/grocery-king/cart/view-cart.php`
6. Checkout → `http://localhost/grocery-king/pages/checkout.php`
7. Login → `http://localhost/grocery-king/auth/login.php`
8. Register → `http://localhost/grocery-king/auth/register.php`

### For Logged-in Users
1. Dashboard → `http://localhost/grocery-king/user/dashboard.php`
2. My Orders → `http://localhost/grocery-king/user/orders.php`
3. Order Details → `http://localhost/grocery-king/user/order-detail.php?id=X`
4. My Profile → `http://localhost/grocery-king/user/profile.php`
5. Wishlist → `http://localhost/grocery-king/pages/wishlist.php`
6. Logout → `http://localhost/grocery-king/auth/logout.php`

### For Admins
1. Admin Dashboard → `http://localhost/grocery-king/admin/index.php`
2. Products → `http://localhost/grocery-king/admin/products/manage.php`
3. Categories → `http://localhost/grocery-king/admin/categories/manage.php`
4. Orders → `http://localhost/grocery-king/admin/orders/manage.php`
5. Users → `http://localhost/grocery-king/admin/users/manage.php`
6. Blogs → `http://localhost/grocery-king/admin/blogs/manage.php`
7. Reviews → `http://localhost/grocery-king/admin/reviews/manage.php`

---

## ✅ Configuration Status

- **Base URL:** `http://localhost/grocery-king/`
- **SITE_URL Detection:** Auto-detects `grocery-king` folder
- **.htaccess:** Configured for `grocery-king`
- **All Links:** Use SITE_URL constant (auto-detected)

---

**All links are now configured for `grocery-king` folder!** 🎉

