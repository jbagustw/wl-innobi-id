<?php
// test_register.php - Test registration endpoint
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
    require_once 'Database.php';
    require_once 'Auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    // Test registration with sample data
    $testData = [
        'username' => 'testuser_' . time(),
        'password' => 'testpass123',
        'full_name' => 'Test User',
        'email' => 'test@example.com'
    ];
    
    $result = $auth->register($testData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration test successful',
        'result' => $result,
        'test_data' => $testData
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
