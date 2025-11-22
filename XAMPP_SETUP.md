# XAMPP Setup Guide

This guide will help you set up the Online Grocery Store project on XAMPP for local development.

## Prerequisites

- XAMPP installed on your system
- PHP 7.4 or higher
- MySQL/MariaDB (included with XAMPP)
- Apache web server (included with XAMPP)

## Installation Steps

### 1. Start XAMPP Services

1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service

### 2. Copy Project to XAMPP

Copy the entire `online-grocery-store` folder to your XAMPP `htdocs` directory:

**Windows:**
```
C:\xampp\htdocs\online-grocery-store\
```

**macOS:**
```
/Applications/XAMPP/htdocs/online-grocery-store/
```

**Linux:**
```
/opt/lampp/htdocs/online-grocery-store/
```

### 3. Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click on "New" to create a new database
3. Database name: `grocery_store`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 4. Import Database Schema

1. In phpMyAdmin, select the `grocery_store` database
2. Click on the "Import" tab
3. Click "Choose File" and select: `sql/schema.sql`
4. Click "Go" to import

Alternatively, you can run the SQL file directly:
- Click on "SQL" tab in phpMyAdmin
- Copy and paste the contents of `sql/schema.sql`
- Click "Go"

### 5. Configure Database Connection

The project is already configured to work with XAMPP's default MySQL settings:
- **Host:** `localhost`
- **Username:** `root`
- **Password:** (empty by default)
- **Database:** `grocery_store`

If your XAMPP MySQL has a different password, you can create a `.env` file in the project root (optional):

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password_here
DB_NAME=grocery_store
```

Or modify `config/db.php` directly if needed.

### 6. Set File Permissions (Linux/macOS)

Make sure the uploads directory is writable:

```bash
chmod -R 755 public/uploads
```

### 7. Access the Application

Open your web browser and navigate to:

```
http://localhost/online-grocery-store/
```

## Create Admin User

After setting up the database, create an admin user by running:

```
http://localhost/online-grocery-store/create-admin.php
```

Or use the SQL script:
1. Open phpMyAdmin
2. Select `grocery_store` database
3. Go to SQL tab
4. Run the contents of `sql/create-admin.sql`

## Default Admin Credentials

After running `create-admin.php`, you can use:
- **Email:** admin@groceryking.com
- **Password:** Admin@123

**Important:** Change the admin password after first login!

## Troubleshooting

### Issue: "Database connection failed"

**Solution:**
- Verify MySQL is running in XAMPP Control Panel
- Check database name is `grocery_store`
- Verify username is `root` and password is empty (or update config if different)
- Make sure the database was created and schema imported

### Issue: "404 Not Found" or pages not loading

**Solution:**
- Verify `.htaccess` file exists in project root
- Check Apache `mod_rewrite` is enabled:
  - Open `httpd.conf` in XAMPP
  - Find: `#LoadModule rewrite_module modules/mod_rewrite.so`
  - Remove the `#` to uncomment it
  - Restart Apache

### Issue: Images not displaying

**Solution:**
- Check `public/uploads/` directory exists and is writable
- Verify file permissions (755 for directories, 644 for files)
- Check the path in browser developer tools

### Issue: "Permission denied" errors

**Solution:**
- On Linux/macOS, set proper permissions:
  ```bash
  chmod -R 755 public/uploads
  chmod 644 .htaccess
  ```

### Issue: URL rewriting not working

**Solution:**
1. Edit `httpd.conf` (usually in `C:\xampp\apache\conf\` or `/Applications/XAMPP/etc/httpd.conf`)
2. Find the section for your htdocs directory:
   ```apache
   <Directory "C:/xampp/htdocs">
       Options Indexes FollowSymLinks
       AllowOverride None
       Require all granted
   </Directory>
   ```
3. Change `AllowOverride None` to `AllowOverride All`
4. Restart Apache

## Project Structure

```
online-grocery-store/
├── admin/              # Admin panel pages
├── auth/               # Authentication pages
├── cart/               # Shopping cart
├── classes/            # PHP classes
├── config/             # Configuration files
├── includes/           # Header and footer
├── pages/              # Public pages
├── public/             # Public assets (CSS, images, uploads)
├── sql/                # Database SQL files
└── index.php           # Homepage
```

## Development Tips

1. **Enable Error Display (Development Only):**
   Add to `index.php` or create `config/dev.php`:
   ```php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   ```

2. **Check PHP Version:**
   Create `info.php` in project root:
   ```php
   <?php phpinfo(); ?>
   ```
   Visit: `http://localhost/online-grocery-store/info.php`
   (Delete this file after checking!)

3. **Database Management:**
   Use phpMyAdmin at `http://localhost/phpmyadmin` for easy database management

## Production Deployment

Before deploying to production:
- Remove or secure `create-admin.php`
- Set proper file permissions
- Use environment variables for sensitive data
- Enable HTTPS
- Update database credentials
- Disable error display

## Support

If you encounter any issues:
1. Check Apache and MySQL are running in XAMPP
2. Verify all files are in the correct location
3. Check error logs in XAMPP (`logs/` directory)
4. Verify database connection settings

---

**Happy Coding! 🛒**

