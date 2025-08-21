<?php
// simple_test.php - Simple test tanpa dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output as plain text untuk debugging
header('Content-Type: text/plain');

echo "=== SIMPLE TEST ===\n\n";

// Test 1: PHP Works
echo "1. PHP Version: " . phpversion() . "\n";
echo "   Status: OK\n\n";

// Test 2: Required Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json'];
echo "2. Required Extensions:\n";
foreach ($required_extensions as $ext) {
    echo "   - $ext: " . (extension_loaded($ext) ? "OK" : "MISSING") . "\n";
}
echo "\n";

// Test 3: Check if files exist
echo "3. Required Files:\n";
$files = ['config.php', 'database.php', 'auth.php', 'api.php'];
foreach ($files as $file) {
    echo "   - $file: " . (file_exists($file) ? "EXISTS" : "NOT FOUND") . "\n";
}
echo "\n";

// Test 4: Try to include config
echo "4. Testing config.php:\n";
try {
    if (file_exists('config.php')) {
        // Capture any output
        ob_start();
        include_once 'config.php';
        $output = ob_get_clean();
        
        if (!empty($output)) {
            echo "   WARNING: config.php produces output: " . $output . "\n";
        }
        
        echo "   DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
        echo "   DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
        echo "   DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
        echo "   DB_PASS: " . (defined('DB_PASS') ? '***hidden***' : 'NOT DEFINED') . "\n";
        echo "   Status: OK\n";
    } else {
        echo "   Status: FILE NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
} catch (ParseError $e) {
    echo "   PARSE ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Try database connection
echo "5. Testing Database Connection:\n";
if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "   Status: CONNECTED\n";
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   Users in database: " . $result['count'] . "\n";
        
    } catch (PDOException $e) {
        echo "   ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "   Status: MISSING DATABASE CONFIGURATION\n";
}
echo "\n";

// Test 6: Test password hash
echo "6. Password Hash Test:\n";
$test_password = 'admin123';
$hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "   Password: $test_password\n";
echo "   Hash: $hash\n";
echo "   Verify: " . (password_verify($test_password, $hash) ? "OK" : "FAILED") . "\n";
echo "\n";

// Test 7: Check for syntax errors in main files
echo "7. Syntax Check:\n";
foreach ($files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "   - $file: OK\n";
        } else {
            echo "   - $file: SYNTAX ERROR\n";
            echo "     " . $output . "\n";
        }
    }
}
echo "\n";

echo "=== END OF TEST ===\n";
?>