<?php
// Direct test of AuthService login from PHP
require_once dirname(__DIR__) . '/bootstrap/app.php';

$authService = new \App\Services\AuthService();

// Test with correct credentials
$result = $authService->login('principal@imsschool.local', 'principal123');

echo "Login Test Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";

// Also test with login_id
echo "\n\nLogin with login_id:\n";
$result2 = $authService->login('principal', 'principal123');
echo json_encode($result2, JSON_PRETTY_PRINT) . "\n";
