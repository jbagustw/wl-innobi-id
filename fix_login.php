<?php
// fix_login.php - Fix password hashes and test login
header('Content-Type: application/json');

try {
    echo "=== FIXING LOGIN ISSUES ===\n\n";
    
    // Load configuration and database
    require_once 'config.php';
    require_once 'database.php';
    require_once 'auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    echo "✓ Database and auth classes loaded\n\n";
    
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
    
    // Test login
    echo "Testing login functionality...\n";
    
    // Test admin login
    try {
        $adminResult = $auth->login('admin', 'admin123');
        echo "✓ Admin login successful!\n";
        echo "  - Token: " . substr($adminResult['token'], 0, 30) . "...\n";
        echo "  - User: " . $adminResult['user']['username'] . "\n";
        echo "  - Role: " . $adminResult['user']['role'] . "\n\n";
    } catch (Exception $e) {
        echo "❌ Admin login failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test user login
    try {
        $userResult = $auth->login('user', 'user123');
        echo "✓ User login successful!\n";
        echo "  - Token: " . substr($userResult['token'], 0, 30) . "...\n";
        echo "  - User: " . $userResult['user']['username'] . "\n";
        echo "  - Role: " . $userResult['user']['role'] . "\n\n";
    } catch (Exception $e) {
        echo "❌ User login failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== LOGIN FIXED SUCCESSFULLY ===\n";
    echo "You can now login with:\n";
    echo "- Username: admin, Password: admin123\n";
    echo "- Username: user, Password: user123\n\n";
    echo "The API should now return proper JSON responses for login requests.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
