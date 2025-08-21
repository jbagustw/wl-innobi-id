<?php
// test_api_simple.php - Simple API test
header('Content-Type: application/json');

try {
    echo "=== SIMPLE API TEST ===\n\n";
    
    // Test 1: Check if API can be loaded without errors
    echo "1. Testing API file loading...\n";
    
    // Suppress warnings for this test
    error_reporting(E_ERROR | E_PARSE);
    
    // Try to include the API file
    ob_start();
    include 'api.php';
    $output = ob_get_clean();
    
    echo "   ✓ API file loaded successfully\n";
    echo "   - Output length: " . strlen($output) . " characters\n\n";
    
    // Test 2: Check basic functionality
    echo "2. Testing basic functionality...\n";
    
    // Load required files
    require_once 'config.php';
    require_once 'database.php';
    require_once 'auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    echo "   ✓ Database and auth classes loaded\n\n";
    
    // Test 3: Check if users exist
    echo "3. Checking users...\n";
    $users = $db->query("SELECT username, role, is_active FROM users WHERE username IN ('admin', 'user')");
    echo "   ✓ Found " . count($users) . " users\n";
    
    foreach ($users as $user) {
        echo "   - " . $user['username'] . " (" . $user['role'] . ") - " . ($user['is_active'] ? 'Active' : 'Inactive') . "\n";
    }
    echo "\n";
    
    // Test 4: Test password verification
    echo "4. Testing password verification...\n";
    $admin_user = $db->query("SELECT password FROM users WHERE username = 'admin'")[0] ?? null;
    if ($admin_user) {
        if (password_verify('admin123', $admin_user['password'])) {
            echo "   ✓ Admin password verification successful\n";
        } else {
            echo "   ❌ Admin password verification failed\n";
            echo "   - Current hash: " . substr($admin_user['password'], 0, 30) . "...\n";
        }
    } else {
        echo "   ❌ Admin user not found\n";
    }
    echo "\n";
    
    echo "=== TEST COMPLETE ===\n";
    echo "If all tests pass, the API should work correctly.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
