-- =====================================================
-- INITIALIZE DATABASE WITH SAMPLE DATA (OPTIONAL)
-- =====================================================
-- This script creates sample data after reset
-- Run this after reset-database.sql if you want sample data
-- =====================================================

USE grocery_store;

-- Create default admin user
-- Password: admin123 (hashed with bcrypt)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create sample categories
INSERT INTO categories (name, description, slug) VALUES
('Fruits', 'Fresh fruits and vegetables', 'fruits'),
('Vegetables', 'Fresh vegetables', 'vegetables'),
('Dairy', 'Milk, cheese, and dairy products', 'dairy'),
('Beverages', 'Drinks and beverages', 'beverages'),
('Snacks', 'Chips, cookies, and snacks', 'snacks'),
('Bakery', 'Bread, pastries, and baked goods', 'bakery');

-- Create sample products
INSERT INTO products (name, category_id, description, price, discount_price, stock_quantity, image, slug, status) VALUES
('Fresh Apples', 1, 'Red delicious apples, 1kg', 5.99, NULL, 50, 'placeholder.jpg', 'fresh-apples', 'active'),
('Bananas', 1, 'Fresh yellow bananas, 1kg', 3.99, 2.99, 30, 'placeholder.jpg', 'bananas', 'active'),
('Carrots', 2, 'Fresh carrots, 500g', 2.49, NULL, 40, 'placeholder.jpg', 'carrots', 'active'),
('Milk', 3, 'Fresh whole milk, 1 liter', 3.49, NULL, 25, 'placeholder.jpg', 'milk', 'active'),
('Bread', 6, 'Fresh white bread, 500g', 2.99, NULL, 20, 'placeholder.jpg', 'bread', 'active');

SELECT 'Sample data inserted successfully!' as Status;

