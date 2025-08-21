<?php
// test_api_json.php - Test API JSON response
header('Content-Type: application/json');

try {
    echo "=== TESTING API JSON RESPONSE ===\n\n";
    
    // Test 1: Check if API file exists and is accessible
    echo "1. Testing API file accessibility...\n";
    if (file_exists('api.php')) {
        echo "   ✓ api.php exists\n";
    } else {
        echo "   ❌ api.php not found\n";
        exit(1);
    }
    
    // Test 2: Check configuration
    echo "2. Testing configuration...\n";
    require_once 'config.php';
    echo "   ✓ config.php loaded\n";
    echo "   - DB_HOST: " . DB_HOST . "\n";
    echo "   - DB_NAME: " . DB_NAME . "\n";
    echo "   - DB_USER: " . DB_USER . "\n\n";
    
    // Test 3: Check database connection
    echo "3. Testing database connection...\n";
    require_once 'database.php';
    $db = new Database();
    echo "   ✓ Database connection successful\n\n";
    
    // Test 4: Check auth class
    echo "4. Testing auth class...\n";
    require_once 'auth.php';
    $auth = new Auth($db);
    echo "   ✓ Auth class loaded\n\n";
    
    // Test 5: Check users in database
    echo "5. Checking users in database...\n";
    $users = $db->query("SELECT username, role, is_active FROM users WHERE username IN ('admin', 'user')");
    echo "   ✓ Found " . count($users) . " users\n";
    foreach ($users as $user) {
        echo "   - " . $user['username'] . " (" . $user['role'] . ") - " . ($user['is_active'] ? 'Active' : 'Inactive') . "\n";
    }
    echo "\n";
    
    // Test 6: Test password verification
    echo "6. Testing password verification...\n";
    $admin_user = $db->query("SELECT password FROM users WHERE username = 'admin'")[0] ?? null;
    if ($admin_user) {
        if (password_verify('admin123', $admin_user['password'])) {
            echo "   ✓ Admin password verification successful\n";
        } else {
            echo "   ❌ Admin password verification failed\n";
        }
    } else {
        echo "   ❌ Admin user not found\n";
    }
    echo "\n";
    
    // Test 7: Test login function
    echo "7. Testing login function...\n";
    try {
        $result = $auth->login('admin', 'admin123');
        echo "   ✓ Login successful\n";
        echo "   - Token: " . substr($result['token'], 0, 20) . "...\n";
        echo "   - User: " . $result['user']['username'] . "\n";
    } catch (Exception $e) {
        echo "   ❌ Login failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== API TEST COMPLETE ===\n";
    echo "If all tests pass, the API should return proper JSON responses.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
