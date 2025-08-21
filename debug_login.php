<?php
// debug_login.php - Comprehensive debug script for login issues
header('Content-Type: application/json');

try {
    echo "=== DEBUG LOGIN ISSUES ===\n\n";
    
    // 1. Test config loading
    echo "1. Testing config loading...\n";
    require_once 'config.php';
    echo "   ✓ Config loaded successfully\n";
    echo "   - DB_HOST: " . DB_HOST . "\n";
    echo "   - DB_NAME: " . DB_NAME . "\n";
    echo "   - DB_USER: " . DB_USER . "\n";
    echo "   - JWT_SECRET: " . substr(JWT_SECRET, 0, 10) . "...\n\n";
    
    // 2. Test database connection
    echo "2. Testing database connection...\n";
    require_once 'database.php';
    $db = new Database();
    echo "   ✓ Database connection successful\n\n";
    
    // 3. Test auth class loading
    echo "3. Testing auth class loading...\n";
    require_once 'auth.php';
    $auth = new Auth($db);
    echo "   ✓ Auth class loaded successfully\n\n";
    
    // 4. Check if users exist
    echo "4. Checking users in database...\n";
    $users = $db->query("SELECT username, role, is_active, LEFT(password, 20) as password_preview FROM users WHERE username IN ('admin', 'user')");
    
    if (empty($users)) {
        echo "   ❌ No users found in database!\n";
        echo "   Please run the seed_data.sql script first.\n\n";
    } else {
        echo "   ✓ Found " . count($users) . " users:\n";
        foreach ($users as $user) {
            echo "   - Username: " . $user['username'] . "\n";
            echo "     Role: " . $user['role'] . "\n";
            echo "     Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
            echo "     Password: " . $user['password_preview'] . "...\n\n";
        }
    }
    
    // 5. Test password verification
    echo "5. Testing password verification...\n";
    $test_password = 'admin123';
    $test_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    if (password_verify($test_password, $test_hash)) {
        echo "   ✓ Password verification works correctly\n";
        echo "   - Test password: $test_password\n";
        echo "   - Hash: " . substr($test_hash, 0, 20) . "...\n\n";
    } else {
        echo "   ❌ Password verification failed!\n\n";
    }
    
    // 6. Test actual login
    echo "6. Testing actual login...\n";
    try {
        $result = $auth->login('admin', 'admin123');
        echo "   ✓ Login successful!\n";
        echo "   - Token: " . substr($result['token'], 0, 20) . "...\n";
        echo "   - User: " . $result['user']['username'] . "\n";
        echo "   - Role: " . $result['user']['role'] . "\n\n";
    } catch (Exception $e) {
        echo "   ❌ Login failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== DEBUG COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
