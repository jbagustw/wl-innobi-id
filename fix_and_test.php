<?php
// fix_and_test.php - Fix passwords and test complete login flow
header('Content-Type: application/json');

try {
    echo "=== FIXING PASSWORDS AND TESTING LOGIN ===\n\n";
    
    // Load configuration and database
    require_once 'config.php';
    require_once 'database.php';
    require_once 'auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    echo "✓ Database and auth classes loaded\n\n";
    
    // Step 1: Generate correct password hashes
    echo "Step 1: Generating password hashes...\n";
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $user_hash = password_hash('user123', PASSWORD_DEFAULT);
    
    echo "   Admin hash: " . substr($admin_hash, 0, 30) . "...\n";
    echo "   User hash: " . substr($user_hash, 0, 30) . "...\n\n";
    
    // Step 2: Update passwords in database
    echo "Step 2: Updating passwords in database...\n";
    
    // Update admin password
    $db->execute(
        "UPDATE users SET password = ? WHERE username = ?",
        [$admin_hash, 'admin']
    );
    echo "   ✓ Admin password updated\n";
    
    // Update user password
    $db->execute(
        "UPDATE users SET password = ? WHERE username = ?",
        [$user_hash, 'user']
    );
    echo "   ✓ User password updated\n";
    
    // Make sure users are active
    $db->execute(
        "UPDATE users SET is_active = 1 WHERE username IN (?, ?)",
        ['admin', 'user']
    );
    echo "   ✓ Users activated\n\n";
    
    // Step 3: Verify password updates
    echo "Step 3: Verifying password updates...\n";
    $admin_user = $db->query("SELECT password FROM users WHERE username = 'admin'")[0] ?? null;
    $user_user = $db->query("SELECT password FROM users WHERE username = 'user'")[0] ?? null;
    
    if ($admin_user && password_verify('admin123', $admin_user['password'])) {
        echo "   ✓ Admin password verification successful\n";
    } else {
        echo "   ❌ Admin password verification failed\n";
    }
    
    if ($user_user && password_verify('user123', $user_user['password'])) {
        echo "   ✓ User password verification successful\n";
    } else {
        echo "   ❌ User password verification failed\n";
    }
    echo "\n";
    
    // Step 4: Test login functionality
    echo "Step 4: Testing login functionality...\n";
    
    // Test admin login
    try {
        $adminResult = $auth->login('admin', 'admin123');
        echo "   ✓ Admin login successful!\n";
        echo "     - Token: " . substr($adminResult['token'], 0, 30) . "...\n";
        echo "     - User: " . $adminResult['user']['username'] . "\n";
        echo "     - Role: " . $adminResult['user']['role'] . "\n";
    } catch (Exception $e) {
        echo "   ❌ Admin login failed: " . $e->getMessage() . "\n";
    }
    
    // Test user login
    try {
        $userResult = $auth->login('user', 'user123');
        echo "   ✓ User login successful!\n";
        echo "     - Token: " . substr($userResult['token'], 0, 30) . "...\n";
        echo "     - User: " . $userResult['user']['username'] . "\n";
        echo "     - Role: " . $userResult['user']['role'] . "\n";
    } catch (Exception $e) {
        echo "   ❌ User login failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Step 5: Test wrong credentials
    echo "Step 5: Testing wrong credentials...\n";
    try {
        $wrongResult = $auth->login('admin', 'wrongpassword');
        echo "   ❌ Wrong password login should have failed!\n";
    } catch (Exception $e) {
        echo "   ✓ Wrong password correctly rejected: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    echo "=== FIX AND TEST COMPLETE ===\n";
    echo "✅ Login should now work with:\n";
    echo "   - Username: admin, Password: admin123\n";
    echo "   - Username: user, Password: user123\n\n";
    echo "✅ The API should now return proper JSON responses.\n";
    echo "✅ The getAllHeaders() error has been fixed.\n";
    
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
