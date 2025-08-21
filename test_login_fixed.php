<?php
// test_login_fixed.php - Test login after password fix
header('Content-Type: application/json');

try {
    echo "=== TESTING LOGIN AFTER PASSWORD FIX ===\n\n";
    
    // Load configuration and classes
    require_once 'config.php';
    require_once 'database.php';
    require_once 'auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    echo "✓ Classes loaded successfully\n\n";
    
    // Test admin login
    echo "Testing admin login (admin/admin123)...\n";
    try {
        $adminResult = $auth->login('admin', 'admin123');
        echo "✓ Admin login successful!\n";
        echo "  - Token: " . substr($adminResult['token'], 0, 30) . "...\n";
        echo "  - User: " . $adminResult['user']['username'] . "\n";
        echo "  - Role: " . $adminResult['user']['role'] . "\n";
        echo "  - Full Name: " . $adminResult['user']['full_name'] . "\n\n";
    } catch (Exception $e) {
        echo "❌ Admin login failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test user login
    echo "Testing user login (user/user123)...\n";
    try {
        $userResult = $auth->login('user', 'user123');
        echo "✓ User login successful!\n";
        echo "  - Token: " . substr($userResult['token'], 0, 30) . "...\n";
        echo "  - User: " . $userResult['user']['username'] . "\n";
        echo "  - Role: " . $userResult['user']['role'] . "\n";
        echo "  - Full Name: " . $userResult['user']['full_name'] . "\n\n";
    } catch (Exception $e) {
        echo "❌ User login failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test wrong password
    echo "Testing wrong password (admin/wrongpass)...\n";
    try {
        $wrongResult = $auth->login('admin', 'wrongpass');
        echo "❌ Wrong password login should have failed!\n\n";
    } catch (Exception $e) {
        echo "✓ Wrong password correctly rejected: " . $e->getMessage() . "\n\n";
    }
    
    // Test non-existent user
    echo "Testing non-existent user (nonexistent/pass)...\n";
    try {
        $nonexistentResult = $auth->login('nonexistent', 'pass');
        echo "❌ Non-existent user login should have failed!\n\n";
    } catch (Exception $e) {
        echo "✓ Non-existent user correctly rejected: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== LOGIN TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
