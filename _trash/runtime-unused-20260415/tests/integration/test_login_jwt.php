<?php

declare(strict_types=1);

echo "=== Step 5: Login Endpoint & JWT Generation Test ===\n\n";

// Load app
echo "[1] Loading application...\n";
$app = require __DIR__ . '/bootstrap/app.php';
echo "✓ Application loaded\n";

$db = \App\Core\Database::connection();

// Check if test user exists
echo "\n[2] Checking for test users...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "✓ Active users in database: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $db->query("SELECT id, email, role, login_id FROM users WHERE is_active = 1 LIMIT 1");
        $user = $stmt->fetch();
        echo "  - Sample user: " . $user['email'] . " (role: " . $user['role'] . ")\n";
    } else {
        echo "  WARNING: No active users found\n";
    }
} catch (Throwable $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n";
}

// Test AuthService instantiation
echo "\n[3] Testing AuthService...\n";
try {
    $authService = new \App\Services\AuthService();
    echo "✓ AuthService instantiated\n";
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test JWT token generation
echo "\n[4] Testing JWT Token Generation...\n";
try {
    $token = \App\Core\Auth::generateAccessToken(1, 'admin');
    echo "✓ Access token generated\n";
    echo "  - Token length: " . strlen($token) . " chars\n";
    echo "  - Token prefix: " . substr($token, 0, 20) . "...\n";
    
    // Try to decode it
    $decoded = \App\Core\Auth::verifyToken($token);
    echo "✓ Token verified\n";
    echo "  - User ID: " . $decoded['user_id'] . "\n";
    echo "  - Role: " . $decoded['role'] . "\n";
    echo "  - JTI: " . substr($decoded['jti'], 0, 10) . "...\n";
} catch (Throwable $e) {
    echo "✗ Token generation/verification FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test refresh token
echo "\n[5] Testing Refresh Token Generation...\n";
try {
    $refreshToken = \App\Core\Auth::generateRefreshToken(1, 'admin');
    echo "✓ Refresh token generated\n";
    
    // Try to decode it
    $decoded = \App\Core\Auth::verifyToken($refreshToken);
    echo "✓ Refresh token verified\n";
} catch (Throwable $e) {
    echo "✗ Refresh token generation/verification FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test login attempt (if we have a test user)
echo "\n[6] Testing AuthService::login()...\n";
try {
    $stmt = $db->query("SELECT email, password_hash FROM users WHERE is_active = 1 LIMIT 1");
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        echo "  - Test user email: " . $testUser['email'] . "\n";
        
        // Try login with wrong password first
        $result = $authService->login($testUser['email'], 'wrongpassword');
        if ($result['success']) {
            echo "✗ ERROR: Wrong password should have failed!\n";
        } else {
            echo "✓ Wrong password correctly rejected: " . $result['error'] . "\n";
        }
    } else {
        echo "  - No test users available (skipping login test)\n";
    }
} catch (Throwable $e) {
    echo "✗ Login test error: " . $e->getMessage() . "\n";
}

echo "\n=== Login & JWT Phase Complete ===\n";
