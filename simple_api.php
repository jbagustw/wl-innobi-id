<?php
// simple_api.php - Simplified API for debugging
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering and clean any previous output
ob_start();
ob_clean();

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['message' => 'CORS preflight OK']);
    exit;
}

try {
    // Very simple routing - get path after simple_api.php
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // Remove script name from URI to get the path
    $path = str_replace($script_name, '', $request_uri);
    $path = trim($path, '/');
    
    // Remove query string
    if (strpos($path, '?') !== false) {
        $path = substr($path, 0, strpos($path, '?'));
    }
    
    // Split path into segments
    $segments = empty($path) ? [] : explode('/', $path);
    $endpoint = $segments[0] ?? 'test';
    $action = $segments[1] ?? '';
    
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    // Basic response structure
    $response = [
        'success' => true,
        'endpoint' => $endpoint,
        'action' => $action,
        'method' => $method,
        'path' => $path,
        'segments' => $segments,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Simple routing
    switch ($endpoint) {
        case 'test':
            $response['message'] = 'Simple API is working!';
            $response['server_info'] = [
                'php_version' => phpversion(),
                'request_uri' => $_SERVER['REQUEST_URI'],
                'script_name' => $_SERVER['SCRIPT_NAME'],
                'method' => $_SERVER['REQUEST_METHOD']
            ];
            break;
            
        case 'auth':
            if ($action === 'login' && $method === 'POST') {
                // Simple login test
                if (isset($input['username']) && isset($input['password'])) {
                    if ($input['username'] === 'admin' && $input['password'] === 'admin123') {
                        $response['message'] = 'Login successful!';
                        $response['token'] = 'simple_test_token_' . time();
                        $response['user'] = [
                            'id' => 1,
                            'username' => 'admin',
                            'role' => 'admin'
                        ];
                    } else {
                        $response['success'] = false;
                        $response['error'] = 'Invalid credentials';
                    }
                } else {
                    $response['success'] = false;
                    $response['error'] = 'Username and password required';
                }
            } else {
                $response['message'] = 'Auth endpoint - supported: POST /auth/login';
            }
            break;
            
        case 'songs':
            $response['message'] = 'Songs endpoint';
            $response['data'] = [
                [
                    'id' => 1,
                    'title' => 'Test Song 1',
                    'song_key' => 'C',
                    'tempo' => 'medium',
                    'theme' => 'worship'
                ],
                [
                    'id' => 2,
                    'title' => 'Test Song 2',
                    'song_key' => 'G',
                    'tempo' => 'fast',
                    'theme' => 'praise'
                ]
            ];
            break;
            
        case 'compositions':
            $response['message'] = 'Compositions endpoint';
            $response['data'] = [
                [
                    'id' => 1,
                    'name' => 'Test Composition',
                    'theme' => 'worship',
                    'created_at' => date('Y-m-d H:i:s'),
                    'song_count' => 2
                ]
            ];
            break;
            
        case 'stats':
            $response['message'] = 'Stats endpoint';
            $response['data'] = [
                'total_songs' => 10,
                'total_users' => 5,
                'user_compositions' => 3,
                'recent_activities' => []
            ];
            break;
            
        default:
            $response['success'] = false;
            $response['error'] = 'Unknown endpoint: ' . $endpoint;
            $response['available_endpoints'] = ['test', 'auth', 'songs', 'compositions', 'stats'];
            break;
    }
    
    // Output JSON response
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Clean output buffer and send error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Exception: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Clean output buffer and send error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// End output buffering
ob_end_flush();
?>