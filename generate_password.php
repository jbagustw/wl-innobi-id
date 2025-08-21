<?php
// Script untuk menghasilkan password hash
// Jalankan script ini untuk mendapatkan hash password yang benar

echo "Password Hash Generator\n";
echo "======================\n\n";

$passwords = [
    'admin123' => 'admin',
    'user123' => 'user'
];

foreach ($passwords as $password => $username) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n";
    echo "SQL: UPDATE users SET password = '$hash' WHERE username = '$username';\n";
    echo "---\n";
}

echo "\nCopy SQL statements di atas untuk update password di database.\n";
?>
