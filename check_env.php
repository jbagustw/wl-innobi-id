<?php
// check_env.php - Check server environment and file permissions
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $response = [
        'success' => true,
        'current_directory' => getcwd(),
        'files_check' => [
            'config.php' => file_exists('config.php'),
            'database.php' => file_exists('database.php'),
            'auth.php' => file_exists('auth.php'),
            'api.php' => file_exists('api.php'),
            'index.html' => file_exists('index.html'),
            'admin.html' => file_exists('admin.html')
        ],
        'file_permissions' => [],
        'php_errors' => [],
        'server_vars' => [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
            'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'Unknown',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Unknown'
        ]
    ];
    
    // Check file permissions
    $files_to_check = ['config.php', 'database.php', 'auth.php', 'api.php'];
    foreach ($files_to_check as $file) {
        if (file_exists($file)) {
            $response['file_permissions'][$file] = [
                'readable' => is_readable($file),
                'writable' => is_writable($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
    }
    
    // Test if we can include required files
    $include_test = [];
    
    try {
        if (file_exists('config.php')) {
            require_once 'config.php';
            $include_test['config.php'] = 'SUCCESS';
        } else {
            $include_test['config.php'] = 'FILE NOT FOUND';
        }
    } catch (Exception $e) {
        $include_test['config.php'] = 'ERROR: ' . $e->getMessage();
    }
    
    try {
        if (file_exists('database.php')) {
            require_once 'database.php';
            $include_test['database.php'] = 'SUCCESS';
        } else {
            $include_test['database.php'] = 'FILE NOT FOUND';
        }
    } catch (Exception $e) {
        $include_test['database.php'] = 'ERROR: ' . $e->getMessage();
    }
    
    try {
        if (file_exists('auth.php')) {
            require_once 'auth.php';
            $include_test['auth.php'] = 'SUCCESS';
        } else {
            $include_test['auth.php'] = 'FILE NOT FOUND';
        }
    } catch (Exception $e) {
        $include_test['auth.php'] = 'ERROR: ' . $e->getMessage();
    }
    
    $response['include_test'] = $include_test;
    
    // Test if we can create Database and Auth objects
    if (isset($include_test['database.php']) && $include_test['database.php'] === 'SUCCESS' &&
        isset($include_test['auth.php']) && $include_test['auth.php'] === 'SUCCESS') {
        try {
            $db = new Database();
            $response['database_object'] = 'SUCCESS';
            
            $auth = new Auth($db);
            $response['auth_object'] = 'SUCCESS';
        } catch (Exception $e) {
            $response['object_creation_error'] = $e->getMessage();
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
}
?>