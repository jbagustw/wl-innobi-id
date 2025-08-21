<?php
// test_api.php - Simple test to debug API issues
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Test database connection
    require_once 'config.php';
    require_once 'database.php';
    
    $db = new Database();
    
    // Test if tables exist
    $tables = $db->query("SHOW TABLES");
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'tables' => $tables,
        'php_version' => PHP_VERSION,
        'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
