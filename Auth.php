<?php
// auth.php - Kelas autentikasi
require_once 'config.php';
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function register($data) {
        // Periksa apakah username sudah ada
        $existing = $this->db->query("SELECT id FROM users WHERE username = ?", [$data['username']]);
        if (!empty($existing)) {
            throw new Exception('Username sudah ada', 400);
        }
        
        // Buat user baru
        $userId = $this->db->execute(
            "INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)",
            [
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['full_name'] ?? null,
                $data['email'] ?? null
            ]
        );
        
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => 'Registrasi berhasil'
        ];
    }
    
    public function login($username, $password) {
        // Ambil data user
        $user = $this->db->query(
            "SELECT id, username, password, full_name, email, role, is_active FROM users WHERE username = ?",
            [$username]
        )[0] ?? null;
        
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Kredensial tidak valid', 401);
        }
        
        if (!$user['is_active']) {
            throw new Exception('Akun dinonaktifkan', 403);
        }
        
        // Buat token
        $token = $this->generateToken($user['id']);
        
        // Simpan sesi
        $this->db->execute(
            "INSERT INTO sessions (user_id, token, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
            [
                $user['id'],
                $token,
                date('Y-m-d H:i:s', time() + TOKEN_EXPIRY),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
        
        // Catat aktivitas
        $this->db->execute(
            "INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)",
            [$user['id'], 'login', $_SERVER['REMOTE_ADDR'] ?? null]
        );
        
        unset($user['password']);
        
        return [
            'success' => true,
            'token' => $token,
            'user' => $user,
            'expires_in' => TOKEN_EXPIRY
        ];
    }
    
    public function logout($token) {
        $this->db->execute("DELETE FROM sessions WHERE token = ?", [$token]);
        return ['success' => true, 'message' => 'Berhasil keluar'];
    }
    
    public function verifyToken($token) {
        $session = $this->db->query(
            "SELECT s.*, u.id, u.username, u.full_name, u.email, u.role, u.is_active 
             FROM sessions s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.token = ? AND s.expires_at > NOW()",
            [$token]
        )[0] ?? null;
        
        if (!$session) {
            throw new Exception('Token tidak valid atau sudah kadaluarsa', 401);
        }
        
        if (!$session['is_active']) {
            throw new Exception('Akun dinonaktifkan', 403);
        }
        
        return [
            'valid' => true,
            'user' => [
                'id' => $session['id'],
                'username' => $session['username'],
                'full_name' => $session['full_name'],
                'email' => $session['email'],
                'role' => $session['role']
            ]
        ];
    }
    
    public function refreshToken($oldToken) {
        $session = $this->db->query(
            "SELECT user_id FROM sessions WHERE token = ? AND expires_at > NOW()",
            [$oldToken]
        )[0] ?? null;
        
        if (!$session) {
            throw new Exception('Token tidak valid atau sudah kadaluarsa', 401);
        }
        
        // Buat token baru
        $newToken = $this->generateToken($session['user_id']);
        
        // Perbarui sesi
        $this->db->execute(
            "UPDATE sessions SET token = ?, expires_at = ? WHERE token = ?",
            [$newToken, date('Y-m-d H:i:s', time() + TOKEN_EXPIRY), $oldToken]
        );
        
        return [
            'success' => true,
            'token' => $newToken,
            'expires_in' => TOKEN_EXPIRY
        ];
    }
    
    public function requireAuth() {
        $token = $this->getBearerToken();
        $result = $this->verifyToken($token);
        return $result['user'];
    }
    
    private function generateToken($userId) {
        return bin2hex(random_bytes(32)) . '.' . base64_encode($userId . '.' . time());
    }
    
    private function getBearerToken() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new Exception('Tidak ada header otorisasi', 401);
        }
        
        $matches = [];
        if (!preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            throw new Exception('Header otorisasi tidak valid', 401);
        }
        
        return $matches[1];
    }
}
?>