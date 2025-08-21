<?php
// fix_passwords_now.php - Fix password hashes directly in database
header('Content-Type: application/json');

try {
    echo "=== FIXING PASSWORDS ===\n\n";
    
    // Load configuration and database
    require_once 'config.php';
    require_once 'database.php';
    
    $db = new Database();
    echo "✓ Database connection established\n\n";
    
    // Generate correct password hashes
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $user_hash = password_hash('user123', PASSWORD_DEFAULT);
    
    echo "Generated password hashes:\n";
    echo "Admin (admin123): " . substr($admin_hash, 0, 30) . "...\n";
    echo "User (user123): " . substr($user_hash, 0, 30) . "...\n\n";
    
    // Update passwords in database
    echo "Updating passwords in database...\n";
    
    // Update admin password
    $db->execute(
        "UPDATE users SET password = ? WHERE username = ?",
        [$admin_hash, 'admin']
    );
    echo "✓ Admin password updated\n";
    
    // Update user password
    $db->execute(
        "UPDATE users SET password = ? WHERE username = ?",
        [$user_hash, 'user']
    );
    echo "✓ User password updated\n";
    
    // Make sure users are active
    $db->execute(
        "UPDATE users SET is_active = 1 WHERE username IN (?, ?)",
        ['admin', 'user']
    );
    echo "✓ Users activated\n\n";
    
    // Verify the updates
    echo "Verifying updates...\n";
    $users = $db->query(
        "SELECT username, role, is_active, LEFT(password, 20) as password_preview FROM users WHERE username IN (?, ?)",
        ['admin', 'user']
    );
    
    foreach ($users as $user) {
        echo "✓ Username: " . $user['username'] . "\n";
        echo "  Role: " . $user['role'] . "\n";
        echo "  Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
        echo "  Password: " . $user['password_preview'] . "...\n\n";
    }
    
    // Test password verification
    echo "Testing password verification...\n";
    if (password_verify('admin123', $admin_hash)) {
        echo "✓ Admin password verification successful\n";
    } else {
        echo "❌ Admin password verification failed\n";
    }
    
    if (password_verify('user123', $user_hash)) {
        echo "✓ User password verification successful\n";
    } else {
        echo "❌ User password verification failed\n";
    }
    
    echo "\n=== PASSWORDS FIXED SUCCESSFULLY ===\n";
    echo "You can now login with:\n";
    echo "- Username: admin, Password: admin123\n";
    echo "- Username: user, Password: user123\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
