-- ============================================
-- Create Default Admin User
-- Password: admin123 (change after first login!)
-- ============================================

USE grocery_store;

-- Check if admin already exists
SET @admin_exists = (SELECT COUNT(*) FROM users WHERE email = 'admin@GroceryKing.com' OR role = 'admin');

-- Only insert if admin doesn't exist
INSERT INTO users (name, email, password, role) 
SELECT 
    'Admin User',
    'admin@GroceryKing.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: admin123
    'admin'
WHERE @admin_exists = 0;

SELECT 
    CASE 
        WHEN @admin_exists > 0 THEN 'Admin user already exists. Skipping creation.'
        ELSE 'Admin user created successfully! Email: admin@GroceryKing.com, Password: admin123'
    END as message;

