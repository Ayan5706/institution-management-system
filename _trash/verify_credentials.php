<?php
/**
 * Verify Test Credentials are Working
 * 
 * Run this script anytime to verify all test credentials are correctly configured
 * Usage: php verify_credentials.php
 */

require 'bootstrap/app.php';
use App\Models\UserModel;

$userModel = new UserModel();

echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST CREDENTIALS VERIFICATION\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 80) . "\n\n";

$testCredentials = [
    'principal' => 'principal123',
    'vp' => 'vp123',
    'manager' => 'manager123',
    'accountant' => 'accountant123',
    'teacher' => 'teacher123',
    'student' => 'student123',
];

$allPass = true;
$passCount = 0;
$failCount = 0;

foreach ($testCredentials as $loginId => $password) {
    echo str_repeat("-", 80) . "\n";
    
    // Find user
    $user = $userModel->findByLoginId($loginId);
    
    if (!$user) {
        echo "❌ $loginId: USER NOT FOUND\n";
        $allPass = false;
        $failCount++;
        echo "\n";
        continue;
    }
    
    // Check active
    if (!$user['is_active']) {
        echo "❌ $loginId: USER INACTIVE\n";
        $allPass = false;
        $failCount++;
        echo "\n";
        continue;
    }
    
    // Verify password
    $hash = $user['password_hash'];
    $passwordMatch = str_starts_with($hash, '$2') 
        ? password_verify($password, $hash)
        : $password === $hash;
    
    if (!$passwordMatch) {
        echo "❌ $loginId: PASSWORD MISMATCH\n";
        $allPass = false;
        $failCount++;
        echo "\n";
        continue;
    }
    
    // All checks passed
    echo "✅ $loginId / $password\n";
    echo "   Role: " . $user['role'] . "\n";
    echo "   Email: " . $user['email'] . "\n";
    echo "   Active: Yes\n";
    echo "   Hash: " . substr($hash, 0, 40) . "...\n";
    $passCount++;
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "RESULTS\n";
echo str_repeat("=", 80) . "\n";
echo "Passed: $passCount/6\n";
echo "Failed: $failCount/6\n";

if ($allPass) {
    echo "\n✅ ALL CREDENTIALS VERIFIED SUCCESSFULLY\n";
    echo "\nYou can now use these credentials to log in:\n\n";
    
    foreach ($testCredentials as $loginId => $password) {
        echo "   - $loginId / $password\n";
    }
    
    echo "\nLogin at: http://localhost/IMS_FINAL/public/login\n";
} else {
    echo "\n❌ SOME CREDENTIALS FAILED VERIFICATION\n";
    echo "\nPlease check the failures above and contact support if needed.\n";
}

echo str_repeat("=", 80) . "\n\n";

exit($allPass ? 0 : 1);
?>
