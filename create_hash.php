<?php
// create_hash.php - Generate password hashes and save to file

$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$user_hash = password_hash('user123', PASSWORD_DEFAULT);

$sql_content = "-- fix_passwords.sql - Fix password hashes for admin and user accounts
-- Run this script to update passwords to work properly

USE innobi_worship_leader;

-- Update admin password to 'admin123'
UPDATE users SET password = '$admin_hash' WHERE username = 'admin';

-- Update user password to 'user123'  
UPDATE users SET password = '$user_hash' WHERE username = 'user';

-- Verify the updates
SELECT username, LEFT(password, 10) as password_preview FROM users WHERE username IN ('admin', 'user');
";

file_put_contents('fix_passwords.sql', $sql_content);

echo "Password hashes generated successfully!\n";
echo "Admin (admin123): " . substr($admin_hash, 0, 20) . "...\n";
echo "User (user123): " . substr($user_hash, 0, 20) . "...\n";
echo "SQL file 'fix_passwords.sql' has been created.\n";
echo "Run this SQL file in your database to fix the passwords.\n";
?>
