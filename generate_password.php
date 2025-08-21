<?php
// generate_password.php - Generate password hashes for testing

echo "Password hashes for testing:\n\n";

$passwords = [
    'admin123' => 'admin123',
    'user123' => 'user123'
];

foreach ($passwords as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n";
    echo "---\n";
}

echo "\nSQL commands to update passwords:\n\n";
echo "-- Update admin password to admin123\n";
echo "UPDATE users SET password = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE username = 'admin';\n\n";
echo "-- Update user password to user123\n";
echo "UPDATE users SET password = '" . password_hash('user123', PASSWORD_DEFAULT) . "' WHERE username = 'user';\n";
?>
