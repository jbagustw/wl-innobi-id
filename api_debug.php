<?php
// api_debug.php - Debug tool untuk melihat raw output dari API
header('Content-Type: text/html; charset=utf-8');

echo "<h1>API Debug Tool</h1>";
echo "<hr>";

// Test 1: Direct PHP test
echo "<h2>1. Direct PHP Test</h2>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";

// Capture output
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'config.php';
    require_once 'database.php';
    require_once 'auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    // Test login directly
    $result = $auth->login('admin', 'admin123');
    echo "Login result:\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}

$output = ob_get_clean();
echo htmlspecialchars($output);
echo "</pre>";

// Test 2: Raw API call
echo "<h2>2. Raw API Response (using CURL)</h2>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";

$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
       "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']) . "/api.php/test";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "Raw Response:\n";
echo htmlspecialchars($response);
echo "\n\nResponse Length: " . strlen($response) . " bytes";

// Check if valid JSON
echo "\n\nJSON Valid: ";
$decoded = json_decode($response);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "YES\n";
    echo "Decoded:\n";
    print_r($decoded);
} else {
    echo "NO - " . json_last_error_msg() . "\n";
    
    // Show first 500 chars to see what's wrong
    echo "\nFirst 500 characters:\n";
    echo htmlspecialchars(substr($response, 0, 500));
}
echo "</pre>";

// Test 3: Login API call
echo "<h2>3. Login API Call (using CURL)</h2>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";

$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
       "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']) . "/api.php/auth/login";

$data = json_encode([
    'username' => 'admin',
    'password' => 'admin123'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";
echo "POST Data: $data\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "Raw Response:\n";
echo htmlspecialchars($response);

// Check if valid JSON
echo "\n\nJSON Valid: ";
$decoded = json_decode($response);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "YES\n";
    echo "Decoded:\n";
    print_r($decoded);
} else {
    echo "NO - " . json_last_error_msg() . "\n";
}
echo "</pre>";

// Test 4: Check PHP errors
echo "<h2>4. PHP Configuration</h2>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";
echo "PHP Version: " . phpversion() . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "JSON Extension: " . (extension_loaded('json') ? 'Loaded' : 'Not Loaded') . "\n";
echo "PDO Extension: " . (extension_loaded('pdo') ? 'Loaded' : 'Not Loaded') . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not Loaded') . "\n";
echo "</pre>";

// Test 5: Check for BOM or whitespace
echo "<h2>5. File Check</h2>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";

$files = ['api.php', 'config.php', 'database.php', 'auth.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $firstChars = substr($content, 0, 5);
        
        echo "$file:\n";
        echo "  First 5 bytes (hex): " . bin2hex($firstChars) . "\n";
        
        // Check for BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            echo "  ⚠️ UTF-8 BOM detected!\n";
        }
        
        // Check for whitespace before <?php
        if ($firstChars !== '<?php') {
            echo "  ⚠️ File doesn't start with <?php\n";
            echo "  Starts with: " . htmlspecialchars($firstChars) . "\n";
        }
        
        // Check for closing ?>
        if (strpos($content, '?>') !== false && strpos($content, '?>') < strlen($content) - 10) {
            echo "  ⚠️ Has closing ?> tag (not at end)\n";
        }
    } else {
        echo "$file: NOT FOUND\n";
    }
}
echo "</pre>";

echo "<hr>";
echo "<a href='test_setup.php'>Back to Setup</a> | ";
echo "<a href='index.html'>Go to App</a>";
?>