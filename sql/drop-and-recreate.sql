-- ============================================
-- Drop and Recreate Database
-- WARNING: This will delete the entire database and recreate it
-- Use with extreme caution! This is irreversible.
-- ============================================

-- Drop database if exists (this will delete everything)
DROP DATABASE IF EXISTS grocery_store;

-- Create database
CREATE DATABASE grocery_store 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE grocery_store;

-- Now run schema.sql to create all tables
-- Source: sql/schema.sql

SELECT 'Database dropped and ready for recreation. Please run schema.sql next.' as message;

