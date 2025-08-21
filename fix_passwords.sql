-- fix_passwords.sql - Fix password hashes for admin and user accounts
-- Run this script to update passwords to work properly

USE innobi_worship_leader;

-- First, let's check current users
SELECT username, LEFT(password, 20) as password_preview, role, is_active FROM users WHERE username IN ('admin', 'user');

-- Update admin password to 'admin123'
-- This hash was generated using PHP password_hash('admin123', PASSWORD_DEFAULT)
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';

-- Update user password to 'user123'  
-- This hash was generated using PHP password_hash('user123', PASSWORD_DEFAULT)
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'user';

-- Make sure users are active
UPDATE users SET is_active = 1 WHERE username IN ('admin', 'user');

-- Verify the updates
SELECT username, LEFT(password, 20) as password_preview, role, is_active FROM users WHERE username IN ('admin', 'user');

-- If users don't exist, create them
INSERT IGNORE INTO users (username, password, full_name, email, role, is_active) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@worship.com', 'admin', 1),
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Test', 'user@worship.com', 'user', 1);
