<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
// api.php - Fixed API endpoint with simple routing
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration
require_once 'config.php';
require_once 'database.php';
require_once 'auth.php';

// Error handler untuk menangkap semua error
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $errstr]);
    exit();
});

try {
    // Initialize database and auth
    $db = new Database();
    $auth = new Auth($db);

    // Get request data - SIMPLIFIED routing
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Parse URL - gunakan metode yang lebih sederhana
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Hapus query string
    $uri_parts = explode('?', $request_uri);
    $path = $uri_parts[0];
    
    // Cari posisi api.php
    $api_pos = strpos($path, 'api.php');
    if ($api_pos !== false) {
        // Ambil path setelah api.php
        $path = substr($path, $api_pos + 7); // 7 = length of 'api.php'
    }
    
    // Bersihkan slash
    $path = trim($path, '/');
    
    // Jika kosong, coba dari query parameter
    if (empty($path) && isset($_GET['endpoint'])) {
        $path = $_GET['endpoint'];
    }
    
    // Split path menjadi segments
    $request = !empty($path) ? explode('/', $path) : [];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Route the request
    $endpoint = $request[0] ?? '';
    $id = $request[1] ?? null;
    
    // Debug log (hapus di production)
    // error_log("Endpoint: $endpoint, ID: $id, Path: $path");

    switch ($endpoint) {
        case 'auth':
            handleAuth($auth, $method, $id ?? '', $input);
            break;
            
        case 'users':
            handleUsers($db, $auth, $method, $id, $input);
            break;
            
        case 'songs':
            handleSongs($db, $auth, $method, $id, $input);
            break;
            
        case 'compositions':
            handleCompositions($db, $auth, $method, $id, $input);
            break;
            
        case 'stats':
            handleStats($db, $auth, $method);
            break;
            
        case 'test':
            // Endpoint untuk testing
            echo json_encode([
                'success' => true,
                'message' => 'API is working',
                'endpoint' => $endpoint,
                'path' => $path,
                'method' => $method
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint: ' . $endpoint, 404);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage(), 'code' => $code]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'details' => $e->getMessage()]);
}

// Authentication handlers
function handleAuth($auth, $method, $action, $input) {
    switch ($method) {
        case 'POST':
            if ($action === 'login') {
                if (!isset($input['username']) || !isset($input['password'])) {
                    throw new Exception('Username dan password harus diisi', 400);
                }
                $result = $auth->login($input['username'], $input['password']);
                echo json_encode($result);
            } elseif ($action === 'register') {
                $result = $auth->register($input);
                echo json_encode($result);
            } elseif ($action === 'logout') {
                $token = getBearerToken();
                $result = $auth->logout($token);
                echo json_encode($result);
            } elseif ($action === 'refresh') {
                $token = getBearerToken();
                $result = $auth->refreshToken($token);
                echo json_encode($result);
            } else {
                throw new Exception('Invalid auth action: ' . $action, 400);
            }
            break;
            
        case 'GET':
            if ($action === 'verify') {
                $token = getBearerToken();
                $result = $auth->verifyToken($token);
                echo json_encode($result);
            } else {
                echo json_encode(['endpoint' => 'auth', 'action' => $action]);
            }
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
}

// User handlers
function handleUsers($db, $auth, $method, $id, $input) {
    $user = $auth->requireAuth();
    
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific user
                if ($user['role'] !== 'admin' && $user['id'] != $id) {
                    throw new Exception('Unauthorized', 403);
                }
                $result = $db->query("SELECT id, username, full_name, email, role, created_at, is_active FROM users WHERE id = ?", [$id]);
                echo json_encode($result[0] ?? null);
            } else {
                // Get all users (admin only)
                if ($user['role'] !== 'admin') {
                    throw new Exception('Unauthorized', 403);
                }
                $result = $db->query("SELECT id, username, full_name, email, role, created_at, is_active FROM users ORDER BY created_at DESC");
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            if ($user['role'] !== 'admin' && $user['id'] != $id) {
                throw new Exception('Unauthorized', 403);
            }
            
            $updates = [];
            $params = [];
            
            if (isset($input['full_name'])) {
                $updates[] = "full_name = ?";
                $params[] = $input['full_name'];
            }
            if (isset($input['email'])) {
                $updates[] = "email = ?";
                $params[] = $input['email'];
            }
            if (isset($input['password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            if (isset($input['is_active']) && $user['role'] === 'admin') {
                $updates[] = "is_active = ?";
                $params[] = $input['is_active'] ? 1 : 0;
            }
            
            if (!empty($updates)) {
                $params[] = $id;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
                $db->execute($sql, $params);
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            if ($user['role'] !== 'admin') {
                throw new Exception('Unauthorized', 403);
            }
            $db->execute("UPDATE users SET is_active = 0 WHERE id = ?", [$id]);
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
}

// Song handlers
function handleSongs($db, $auth, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific song
                $result = $db->query("SELECT * FROM songs WHERE id = ? AND is_active = 1", [$id]);
                echo json_encode($result[0] ?? null);
            } else {
                // Get all songs with filters
                $where = ["is_active = 1"];
                $params = [];
                
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $where[] = "(title LIKE ? OR lyrics LIKE ?)";
                    $search = '%' . $_GET['search'] . '%';
                    $params[] = $search;
                    $params[] = $search;
                }
                if (isset($_GET['key']) && !empty($_GET['key'])) {
                    $where[] = "song_key = ?";
                    $params[] = $_GET['key'];
                }
                if (isset($_GET['tempo']) && !empty($_GET['tempo'])) {
                    $where[] = "tempo = ?";
                    $params[] = $_GET['tempo'];
                }
                if (isset($_GET['theme']) && !empty($_GET['theme'])) {
                    $where[] = "theme = ?";
                    $params[] = $_GET['theme'];
                }
                
                $sql = "SELECT * FROM songs WHERE " . implode(' AND ', $where) . " ORDER BY title";
                $result = $db->query($sql, $params);
                echo json_encode($result);
            }
            break;
            
        case 'POST':
            $user = $auth->requireAuth();
            
            // Validasi input
            if (!isset($input['title']) || !isset($input['song_key']) || !isset($input['tempo']) || !isset($input['theme'])) {
                throw new Exception('Data lagu tidak lengkap', 400);
            }
            
            $sql = "INSERT INTO songs (title, song_key, tempo, theme, lyrics, artist, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $input['title'],
                $input['song_key'],
                $input['tempo'],
                $input['theme'],
                $input['lyrics'] ?? '',
                $input['artist'] ?? null,
                $user['id']
            ];
            
            $songId = $db->execute($sql, $params);
            
            // Log activity
            $db->execute(
                "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
                [$user['id'], 'create', 'song', $songId, json_encode(['title' => $input['title']]), $_SERVER['REMOTE_ADDR'] ?? '']
            );
            
            echo json_encode(['id' => $songId, 'success' => true]);
            break;
            
        case 'PUT':
            $user = $auth->requireAuth();
            
            $updates = [];
            $params = [];
            
            foreach (['title', 'song_key', 'tempo', 'theme', 'lyrics', 'artist'] as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            if (!empty($updates)) {
                $params[] = $id;
                $sql = "UPDATE songs SET " . implode(', ', $updates) . " WHERE id = ?";
                $db->execute($sql, $params);
                
                // Log activity
                $db->execute(
                    "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
                    [$user['id'], 'update', 'song', $id, json_encode($input), $_SERVER['REMOTE_ADDR'] ?? '']
                );
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            $user = $auth->requireAuth();
            if ($user['role'] !== 'admin') {
                throw new Exception('Unauthorized', 403);
            }
            
            $db->execute("UPDATE songs SET is_active = 0 WHERE id = ?", [$id]);
            
            // Log activity
            $db->execute(
                "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, ?, ?, ?, ?)",
                [$user['id'], 'delete', 'song', $id, $_SERVER['REMOTE_ADDR'] ?? '']
            );
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
}

// Composition handlers
function handleCompositions($db, $auth, $method, $id, $input) {
    $user = $auth->requireAuth();
    
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific composition with songs
                $comp = $db->query(
                    "SELECT c.*, u.username FROM compositions c 
                     JOIN users u ON c.user_id = u.id 
                     WHERE c.id = ? AND (c.user_id = ? OR ?)",
                    [$id, $user['id'], $user['role'] === 'admin' ? 1 : 0]
                );
                
                if (!empty($comp)) {
                    $comp = $comp[0];
                    $comp['songs'] = $db->query(
                        "SELECT s.*, cs.order_position, cs.notes 
                         FROM composition_songs cs 
                         JOIN songs s ON cs.song_id = s.id 
                         WHERE cs.composition_id = ? 
                         ORDER BY cs.order_position",
                        [$id]
                    );
                    echo json_encode($comp);
                } else {
                    echo json_encode(null);
                }
            } else {
                // Get all compositions for user (or all for admin)
                if ($user['role'] === 'admin') {
                    $sql = "SELECT c.*, u.username, COUNT(cs.id) as song_count 
                           FROM compositions c 
                           LEFT JOIN users u ON c.user_id = u.id
                           LEFT JOIN composition_songs cs ON c.id = cs.composition_id 
                           GROUP BY c.id 
                           ORDER BY c.created_at DESC";
                    $result = $db->query($sql);
                } else {
                    $sql = "SELECT c.*, COUNT(cs.id) as song_count 
                           FROM compositions c 
                           LEFT JOIN composition_songs cs ON c.id = cs.composition_id 
                           WHERE c.user_id = ? 
                           GROUP BY c.id 
                           ORDER BY c.created_at DESC";
                    $result = $db->query($sql, [$user['id']]);
                }
                echo json_encode($result);
            }
            break;
            
        case 'POST':
            $db->beginTransaction();
            
            try {
                // Validasi input
                if (!isset($input['name']) || !isset($input['theme'])) {
                    throw new Exception('Nama dan tema komposisi harus diisi', 400);
                }
                
                // Create composition
                $sql = "INSERT INTO compositions (user_id, name, theme, notes, event_date) VALUES (?, ?, ?, ?, ?)";
                $compId = $db->execute($sql, [
                    $user['id'],
                    $input['name'],
                    $input['theme'],
                    $input['notes'] ?? null,
                    $input['event_date'] ?? null
                ]);
                
                // Add songs
                if (!empty($input['songs'])) {
                    foreach ($input['songs'] as $index => $song) {
                        $db->execute(
                            "INSERT INTO composition_songs (composition_id, song_id, order_position, notes) VALUES (?, ?, ?, ?)",
                            [$compId, $song['id'], $index + 1, $song['notes'] ?? null]
                        );
                    }
                }
                
                $db->commit();
                
                // Log activity
                $db->execute(
                    "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
                    [$user['id'], 'create', 'composition', $compId, json_encode(['name' => $input['name']]), $_SERVER['REMOTE_ADDR'] ?? '']
                );
                
                echo json_encode(['id' => $compId, 'success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'PUT':
            // Verify ownership
            $comp = $db->query("SELECT user_id FROM compositions WHERE id = ?", [$id]);
            if (empty($comp) || ($comp[0]['user_id'] != $user['id'] && $user['role'] !== 'admin')) {
                throw new Exception('Unauthorized', 403);
            }
            
            $db->beginTransaction();
            
            try {
                // Update composition
                $updates = [];
                $params = [];
                
                foreach (['name', 'theme', 'notes', 'event_date'] as $field) {
                    if (isset($input[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $input[$field];
                    }
                }
                
                if (!empty($updates)) {
                    $params[] = $id;
                    $sql = "UPDATE compositions SET " . implode(', ', $updates) . " WHERE id = ?";
                    $db->execute($sql, $params);
                }
                
                // Update songs if provided
                if (isset($input['songs'])) {
                    // Remove existing songs
                    $db->execute("DELETE FROM composition_songs WHERE composition_id = ?", [$id]);
                    
                    // Add new songs
                    foreach ($input['songs'] as $index => $song) {
                        $db->execute(
                            "INSERT INTO composition_songs (composition_id, song_id, order_position, notes) VALUES (?, ?, ?, ?)",
                            [$id, $song['id'], $index + 1, $song['notes'] ?? null]
                        );
                    }
                }
                
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Verify ownership
            $comp = $db->query("SELECT user_id FROM compositions WHERE id = ?", [$id]);
            if (empty($comp) || ($comp[0]['user_id'] != $user['id'] && $user['role'] !== 'admin')) {
                throw new Exception('Unauthorized', 403);
            }
            
            $db->execute("DELETE FROM compositions WHERE id = ?", [$id]);
            
            // Log activity
            $db->execute(
                "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, ?, ?, ?, ?)",
                [$user['id'], 'delete', 'composition', $id, $_SERVER['REMOTE_ADDR'] ?? '']
            );
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
}

// Statistics handler
function handleStats($db, $auth, $method) {
    $user = $auth->requireAuth();
    
    if ($method !== 'GET') {
        throw new Exception('Method not allowed', 405);
    }
    
    $stats = [
        'total_songs' => $db->query("SELECT COUNT(*) as count FROM songs WHERE is_active = 1")[0]['count'],
        'total_users' => $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")[0]['count'],
        'user_compositions' => $db->query("SELECT COUNT(*) as count FROM compositions WHERE user_id = ?", [$user['id']])[0]['count'],
        'recent_activities' => []
    ];
    
    if ($user['role'] === 'admin') {
        $stats['recent_activities'] = $db->query(
            "SELECT al.*, u.username FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             ORDER BY al.created_at DESC LIMIT 10"
        );
    }
    
    echo json_encode($stats);
}

// Helper function to get Bearer token
function getBearerToken() {
    $headers = getAllHeaders();
    
    // Check different header formats
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (empty($authHeader)) {
        throw new Exception('No authorization header', 401);
    }
    
    $matches = [];
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        throw new Exception('Invalid authorization header', 401);
    }
    
    return $matches[1];
}

// Get all headers (fallback for nginx)
function getAllHeaders() {
    if (function_exists('getallheaders')) {
        return getallheaders();
    }
    
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}
?>