<?php
// test_login.php - Test login functionality
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
    require_once 'auth.php';
    
    $db = new Database();
    $auth = new Auth($db);
    
    // Test login with admin credentials
    $adminResult = $auth->login('admin', 'admin123');
    
    echo json_encode([
        'success' => true,
        'message' => 'Login test successful',
        'admin_login' => $adminResult,
        'database_connection' => 'OK',
        'auth_class' => 'OK'
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
