# Complete List of All Links

This document contains all available links/pages in the Online Grocery Store application.

**Base URL:** Replace `[FOLDER]` with your folder name (`grocery-king` or `online-grocery-store`)
- `http://localhost/[FOLDER]/`

---

## 🏠 Public Pages (No Login Required)

### Main Pages
- **Home Page**
  - `http://localhost/[FOLDER]/`
  - `http://localhost/[FOLDER]/index.php`

- **Products**
  - `http://localhost/[FOLDER]/pages/products.php`

- **Categories**
  - `http://localhost/[FOLDER]/pages/categories.php`
  - `http://localhost/[FOLDER]/pages/category-products.php?id=1` (with category ID)

- **Product Details**
  - `http://localhost/[FOLDER]/pages/product-detail.php?id=1` (with product ID)

- **About**
  - `http://localhost/[FOLDER]/pages/about.php`

- **Contact**
  - `http://localhost/[FOLDER]/pages/contact.php`

- **Reviews**
  - `http://localhost/[FOLDER]/pages/reviews.php`

---

## 🔐 Authentication Pages

- **Login**
  - `http://localhost/[FOLDER]/auth/login.php`

- **Register**
  - `http://localhost/[FOLDER]/auth/register.php`

- **Logout**
  - `http://localhost/[FOLDER]/auth/logout.php` (redirects after logout)

- **Create Admin** (First-time setup)
  - `http://localhost/[FOLDER]/create-admin.php`

---

## 👤 User Pages (Login Required)

- **User Dashboard**
  - `http://localhost/[FOLDER]/user/dashboard.php`

- **My Orders**
  - `http://localhost/[FOLDER]/user/orders.php`
  - `http://localhost/[FOLDER]/user/orders.php?page=2` (pagination)

- **Order Details**
  - `http://localhost/[FOLDER]/user/order-detail.php?id=1` (with order ID)

- **Order Confirmation**
  - `http://localhost/[FOLDER]/user/order-confirmation.php?order_id=1` (with order ID)

- **My Profile**
  - `http://localhost/[FOLDER]/user/profile.php`

- **Wishlist**
  - `http://localhost/[FOLDER]/pages/wishlist.php` (requires login)

---

## 🛒 Shopping Cart

- **View Cart**
  - `http://localhost/[FOLDER]/cart/view-cart.php`

- **Checkout**
  - `http://localhost/[FOLDER]/pages/checkout.php` (requires login and items in cart)

---

## 👨‍💼 Admin Pages (Admin Login Required)

### Admin Dashboard
- **Admin Home**
  - `http://localhost/[FOLDER]/admin/index.php`

### Product Management
- **Manage Products**
  - `http://localhost/[FOLDER]/admin/products/manage.php`
  - `http://localhost/[FOLDER]/admin/products/manage.php?page=2` (pagination)

- **Add New Product**
  - `http://localhost/[FOLDER]/admin/products/add.php`

- **Edit Product**
  - `http://localhost/[FOLDER]/admin/products/edit.php?id=1` (with product ID)

- **Delete Product**
  - `http://localhost/[FOLDER]/admin/products/manage.php?delete=1` (with product ID)

### Category Management
- **Manage Categories**
  - `http://localhost/[FOLDER]/admin/categories/manage.php`

- **Add New Category**
  - `http://localhost/[FOLDER]/admin/categories/add.php`

- **Edit Category**
  - `http://localhost/[FOLDER]/admin/categories/edit.php?id=1` (with category ID)

- **Delete Category**
  - `http://localhost/[FOLDER]/admin/categories/manage.php?delete=1` (with category ID)

### Blog Management
- **Manage Blogs**
  - `http://localhost/[FOLDER]/admin/blogs/manage.php`
  - `http://localhost/[FOLDER]/admin/blogs/manage.php?page=2` (pagination)

- **Add New Blog**
  - `http://localhost/[FOLDER]/admin/blogs/add.php`

- **Edit Blog**
  - `http://localhost/[FOLDER]/admin/blogs/edit.php?id=1` (with blog ID)

- **Delete Blog**
  - `http://localhost/[FOLDER]/admin/blogs/manage.php?delete=1` (with blog ID)

### Order Management
- **Manage Orders**
  - `http://localhost/[FOLDER]/admin/orders/manage.php`
  - `http://localhost/[FOLDER]/admin/orders/manage.php?page=2` (pagination)
  - `http://localhost/[FOLDER]/admin/orders/manage.php?order_id=1&update_status=shipped` (update status)

### User Management
- **Manage Users**
  - `http://localhost/[FOLDER]/admin/users/manage.php`
  - `http://localhost/[FOLDER]/admin/users/manage.php?page=2` (pagination)

- **Delete User**
  - `http://localhost/[FOLDER]/admin/users/manage.php?delete=1` (with user ID)

### Review Management
- **Manage Reviews**
  - `http://localhost/[FOLDER]/admin/reviews/manage.php`
  - `http://localhost/[FOLDER]/admin/reviews/manage.php?approve=1` (approve review)
  - `http://localhost/[FOLDER]/admin/reviews/manage.php?reject=1` (reject review)

---

## 🛠️ Utility Pages

- **Admin Routes Guide**
  - `http://localhost/[FOLDER]/admin-routes.php`

- **Debug Configuration** (Delete after use!)
  - `http://localhost/[FOLDER]/debug-config.php`

---

## 📁 Static Assets

### CSS Files
- `http://localhost/[FOLDER]/public/css/style.css`
- `http://localhost/[FOLDER]/public/css/auth.css`
- `http://localhost/[FOLDER]/public/css/index.css`
- `http://localhost/[FOLDER]/public/css/products.css`
- `http://localhost/[FOLDER]/public/css/checkout.css`
- `http://localhost/[FOLDER]/public/css/profile.css`
- `http://localhost/[FOLDER]/public/css/categories-section.css`
- `http://localhost/[FOLDER]/public/css/admin-add-product.css`
- `http://localhost/[FOLDER]/public/css/admin-add-category.css`

### Images
- `http://localhost/[FOLDER]/public/images/hero_img.png`
- `http://localhost/[FOLDER]/public/images/himg.jpg`
- `http://localhost/[FOLDER]/public/uploads/[filename]` (uploaded product/category images)

---

## 🔗 Quick Reference by Category

### For Customers
1. Home → `index.php`
2. Browse Products → `pages/products.php`
3. View Categories → `pages/categories.php`
4. Product Details → `pages/product-detail.php?id=X`
5. Add to Cart → `cart/view-cart.php`
6. Checkout → `pages/checkout.php`
7. Login → `auth/login.php`
8. Register → `auth/register.php`

### For Logged-in Users
1. Dashboard → `user/dashboard.php`
2. My Orders → `user/orders.php`
3. Order Details → `user/order-detail.php?id=X`
4. My Profile → `user/profile.php`
5. Wishlist → `pages/wishlist.php`
6. Logout → `auth/logout.php`

### For Admins
1. Admin Dashboard → `admin/index.php`
2. Products → `admin/products/manage.php`
3. Categories → `admin/categories/manage.php`
4. Orders → `admin/orders/manage.php`
5. Users → `admin/users/manage.php`
6. Blogs → `admin/blogs/manage.php`
7. Reviews → `admin/reviews/manage.php`

---

## 📝 Notes

1. **Folder Name:** Replace `[FOLDER]` with:
   - `grocery-king` (if your folder is named `grocery-king`)
   - `online-grocery-store` (if your folder is named `online-grocery-store`)

2. **Authentication:**
   - Public pages: No login required
   - User pages: Login required (customer or admin)
   - Admin pages: Admin login required

3. **URL Parameters:**
   - `?id=X` - Used for viewing/editing specific items
   - `?page=X` - Used for pagination
   - `?delete=X` - Used for deleting items (admin only)
   - `?approve=X` / `?reject=X` - Used for review management

4. **Redirects:**
   - If not logged in, user pages redirect to `auth/login.php`
   - If not admin, admin pages redirect to `index.php`
   - After logout, redirects to `index.php`

---

## 🎯 Example URLs

If your folder is **`grocery-king`**:
- Home: `http://localhost/grocery-king/`
- Login: `http://localhost/grocery-king/auth/login.php`
- Admin: `http://localhost/grocery-king/admin/index.php`
- Products: `http://localhost/grocery-king/pages/products.php`

If your folder is **`online-grocery-store`**:
- Home: `http://localhost/online-grocery-store/`
- Login: `http://localhost/online-grocery-store/auth/login.php`
- Admin: `http://localhost/online-grocery-store/admin/index.php`
- Products: `http://localhost/online-grocery-store/pages/products.php`

---

**Last Updated:** All links verified and working with XAMPP setup.

