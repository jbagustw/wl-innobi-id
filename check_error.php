<?php
// check_error.php - Check what's causing error 500
// This file has minimal dependencies to avoid errors

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Output as HTML
echo "<!DOCTYPE html>\n";
echo "<html><head><title>Error Check</title></head><body>\n";
echo "<h1>PHP Error Check</h1>\n";
echo "<pre>\n";

// Basic info
echo "PHP Version: " . phpversion() . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Script: " . $_SERVER['SCRIPT_NAME'] . "\n\n";

// Check if we can see errors
echo "Error Reporting Level: " . error_reporting() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n\n";

// Check files
echo "Checking files:\n";
$files = ['config.php', 'database.php', 'auth.php'];

foreach ($files as $file) {
    echo "- $file: ";
    if (file_exists($file)) {
        echo "EXISTS";
        
        // Try to check syntax without executing
        $code = file_get_contents($file);
        if ($code === false) {
            echo " (can't read)";
        } else {
            echo " (" . strlen($code) . " bytes)";
            
            // Check for common issues
            if (substr($code, 0, 5) !== '<?php') {
                echo " WARNING: doesn't start with <?php";
            }
        }
    } else {
        echo "NOT FOUND";
    }
    echo "\n";
}

echo "\n</pre>\n";

// Now try to load each file carefully
echo "<h2>Testing Each File:</h2>\n";

// Test config.php
echo "<h3>config.php:</h3><pre>";
if (file_exists('config.php')) {
    try {
        require_once 'config.php';
        echo "✓ Loaded successfully\n";
        echo "DB_HOST: " . (defined('DB_HOST') ? 'defined' : 'not defined') . "\n";
        echo "DB_NAME: " . (defined('DB_NAME') ? 'defined' : 'not defined') . "\n";
    } catch (ParseError $e) {
        echo "✗ Parse Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    } catch (Error $e) {
        echo "✗ Fatal Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ File not found\n";
}
echo "</pre>";

// Test database.php
echo "<h3>database.php:</h3><pre>";
if (file_exists('database.php')) {
    try {
        require_once 'database.php';
        echo "✓ Loaded successfully\n";
        if (class_exists('Database')) {
            echo "✓ Database class exists\n";
        }
    } catch (ParseError $e) {
        echo "✗ Parse Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    } catch (Error $e) {
        echo "✗ Fatal Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ File not found\n";
}
echo "</pre>";

// Test auth.php
echo "<h3>auth.php:</h3><pre>";
if (file_exists('auth.php')) {
    try {
        require_once 'auth.php';
        echo "✓ Loaded successfully\n";
        if (class_exists('Auth')) {
            echo "✓ Auth class exists\n";
        }
    } catch (ParseError $e) {
        echo "✗ Parse Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    } catch (Error $e) {
        echo "✗ Fatal Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ File not found\n";
}
echo "</pre>";

// Try database connection if config is loaded
if (defined('DB_HOST')) {
    echo "<h3>Database Connection:</h3><pre>";
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        echo "✓ Connected to database\n";
    } catch (PDOException $e) {
        echo "✗ Connection failed: " . $e->getMessage() . "\n";
    }
    echo "</pre>";
}

echo "</body></html>";
?>