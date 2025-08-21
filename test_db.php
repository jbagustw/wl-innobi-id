<?php
// test_db.php - Test database connection
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Include config if it exists
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    echo json_encode(['error' => 'config.php file not found']);
    exit;
}

try {
    // Test if constants are defined
    $config_check = [
        'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'NOT DEFINED',
        'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED',
        'DB_USER' => defined('DB_USER') ? DB_USER : 'NOT DEFINED',
        'DB_PASS' => defined('DB_PASS') ? '***HIDDEN***' : 'NOT DEFINED'
    ];
    
    // Test database connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as mysql_version, NOW() as current_time");
    $db_info = $stmt->fetch();
    
    // Test if tables exist
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        $tables[] = array_values($row)[0];
    }
    
    $response = [
        'success' => true,
        'message' => 'Database connection successful',
        'config_check' => $config_check,
        'database_info' => $db_info,
        'tables_found' => $tables,
        'required_tables' => ['users', 'songs', 'compositions', 'composition_songs', 'sessions', 'activity_logs'],
        'missing_tables' => array_diff(['users', 'songs', 'compositions', 'composition_songs', 'sessions', 'activity_logs'], $tables)
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed: ' . $e->getMessage(),
        'config_check' => $config_check ?? null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'General error: ' . $e->getMessage(),
        'config_check' => $config_check ?? null
    ]);
}
?>