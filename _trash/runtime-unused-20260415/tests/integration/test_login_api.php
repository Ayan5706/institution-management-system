<?php
// Test login endpoint
$ch = curl_init('http://localhost/IMS_FINAL/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'principal@school.edu',
    'password' => 'principal123'
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "Response:\n";
echo $response . "\n";
echo "\nResponse length: " . strlen($response) . " bytes\n";
echo "First 200 chars: " . substr($response, 0, 200) . "\n";
