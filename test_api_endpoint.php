<?php
// test_api_endpoint.php - Test API endpoints directly
header('Content-Type: application/json');

echo "=== TESTING API ENDPOINTS ===\n\n";

// Test 1: Test basic API endpoint
echo "1. Testing basic API endpoint...\n";
$test_url = "https://wl.innobi.id/api.php/test";
echo "   URL: $test_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
echo "   Response: " . substr($response, 0, 200) . "...\n\n";

// Test 2: Test auth endpoint
echo "2. Testing auth endpoint...\n";
$test_url = "https://wl.innobi.id/api.php/auth/login";
echo "   URL: $test_url\n";

$post_data = json_encode([
    'username' => 'admin',
    'password' => 'admin123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($post_data)
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
echo "   Response: " . substr($response, 0, 200) . "...\n\n";

// Test 3: Test with wrong credentials
echo "3. Testing auth with wrong credentials...\n";
$post_data = json_encode([
    'username' => 'admin',
    'password' => 'wrongpassword'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($post_data)
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $http_code\n";
echo "   Response: " . substr($response, 0, 200) . "...\n\n";

echo "=== API ENDPOINT TEST COMPLETE ===\n";
echo "If you see JSON responses, the API is working correctly.\n";
echo "If you see HTML or other non-JSON content, there's a server configuration issue.\n";
?>
