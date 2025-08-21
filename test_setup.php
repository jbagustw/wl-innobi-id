<?php
// test_setup.php - Test koneksi dan setup helper
header('Content-Type: text/html; charset=utf-8');

require_once 'config.php';
require_once 'database.php';

echo "<h1>Worship Leader - Test & Setup</h1>";
echo "<hr>";

// Test database connection
echo "<h2>1. Test Database Connection</h2>";
try {
    $db = new Database();
    echo "<p style='color:green'>✓ Database connected successfully!</p>";
    
    // Check tables
    $tables = $db->query("SHOW TABLES");
    echo "<p>Tables found: " . count($tables) . "</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>$tableName</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Generate password hashes
echo "<h2>2. Password Hashes</h2>";
$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'user123' => password_hash('user123', PASSWORD_DEFAULT),
];

echo "<p>Copy these hashes to update your users:</p>";
echo "<pre style='background:#f0f0f0; padding:10px; border-radius:5px;'>";
foreach ($passwords as $plain => $hash) {
    echo "Password: $plain\n";
    echo "Hash: $hash\n\n";
}
echo "</pre>";

// Update passwords in database
echo "<h2>3. Update Default Users</h2>";
if (isset($_GET['update']) && $_GET['update'] === 'true') {
    try {
        // Update admin password
        $db->execute(
            "UPDATE users SET password = ? WHERE username = 'admin'",
            [$passwords['admin123']]
        );
        echo "<p style='color:green'>✓ Admin password updated</p>";
        
        // Update user password
        $db->execute(
            "UPDATE users SET password = ? WHERE username = 'user'",
            [$passwords['user123']]
        );
        echo "<p style='color:green'>✓ User password updated</p>";
        
        // Check if users exist, if not create them
        $adminExists = $db->query("SELECT id FROM users WHERE username = 'admin'");
        if (empty($adminExists)) {
            $db->execute(
                "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)",
                ['admin', $passwords['admin123'], 'Administrator', 'admin@worship.com', 'admin']
            );
            echo "<p style='color:green'>✓ Admin user created</p>";
        }
        
        $userExists = $db->query("SELECT id FROM users WHERE username = 'user'");
        if (empty($userExists)) {
            $db->execute(
                "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)",
                ['user', $passwords['user123'], 'User Test', 'user@worship.com', 'user']
            );
            echo "<p style='color:green'>✓ Test user created</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error updating users: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p><a href='?update=true' style='background:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Click here to update passwords</a></p>";
}

// Check existing users
echo "<h2>4. Existing Users</h2>";
try {
    $users = $db->query("SELECT id, username, role, full_name, email, is_active FROM users");
    if (!empty($users)) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Active</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error fetching users: " . $e->getMessage() . "</p>";
}

// Test API endpoint
echo "<h2>5. Test API Endpoint</h2>";
$apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
          "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']) . "/api.php/auth/login";
echo "<p>API URL: <code>$apiUrl</code></p>";

// JavaScript test
echo <<<HTML
<button onclick="testAPI()" style="background:#FF9800; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; margin-right:10px;">Test API Connection</button>
<button onclick="testLogin()" style="background:#2196F3; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Test Login API</button>
<div id="result" style="margin-top:20px;"></div>

<script>
async function testAPI() {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '<p>Testing API connection...</p>';
    
    try {
        const response = await fetch('api.php/test', {
            method: 'GET'
        });
        
        const data = await response.json();
        
        if (response.ok) {
            resultDiv.innerHTML = '<p style="color:green">✓ API Connection Success!</p><pre style="background:#f0f0f0; padding:10px;">' + 
                                 JSON.stringify(data, null, 2) + '</pre>';
        } else {
            resultDiv.innerHTML = '<p style="color:red">✗ API Connection Failed</p><pre style="background:#ffe0e0; padding:10px;">' + 
                                 JSON.stringify(data, null, 2) + '</pre>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<p style="color:red">✗ Error: ' + error.message + '</p>';
    }
}

async function testLogin() {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '<p>Testing login...</p>';
    
    try {
        const response = await fetch('api.php/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: 'admin',
                password: 'admin123'
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            resultDiv.innerHTML = '<p style="color:green">✓ Login Test Success!</p><pre style="background:#f0f0f0; padding:10px;">' + 
                                 JSON.stringify(data, null, 2) + '</pre>';
        } else {
            resultDiv.innerHTML = '<p style="color:red">✗ Login Test Failed</p><pre style="background:#ffe0e0; padding:10px;">' + 
                                 JSON.stringify(data, null, 2) + '</pre>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<p style="color:red">✗ Error: ' + error.message + '</p>';
    }
}
</script>

<hr>
<h2>6. Quick Links</h2>
<p>
    <a href="index.html" style="margin-right:20px;">Go to App</a>
    <a href="admin.html">Go to Admin Panel</a>
</p>
HTML;
?>