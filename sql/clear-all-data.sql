-- =====================================================
-- CLEAR ALL DATA FROM DATABASE (Keep Schema)
-- =====================================================
-- This script will delete all data but keep the table structure
-- Use this if you want to keep the schema but remove all records
-- =====================================================

USE grocery_store;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all data from tables (in reverse dependency order)
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE cart;
TRUNCATE TABLE wishlist;
TRUNCATE TABLE reviews;
TRUNCATE TABLE blogs;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Reset AUTO_INCREMENT counters
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE reviews AUTO_INCREMENT = 1;
ALTER TABLE blogs AUTO_INCREMENT = 1;
ALTER TABLE orders AUTO_INCREMENT = 1;
ALTER TABLE order_items AUTO_INCREMENT = 1;
ALTER TABLE wishlist AUTO_INCREMENT = 1;
ALTER TABLE cart AUTO_INCREMENT = 1;

SELECT 'All data cleared successfully!' as Status;
