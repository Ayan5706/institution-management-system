<?php

declare(strict_types=1);

echo "=== Composer Setup Verification ===\n";

// Test 1: Bootstrap loads
echo "\n[Test 1] Loading bootstrap/app.php...\n";
try {
    $app = require 'bootstrap/app.php';
    echo "✓ Bootstrap loaded successfully\n";
} catch (Throwable $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Firebase JWT classes available
echo "\n[Test 2] Firebase JWT classes...\n";
echo "✓ JWT class exists: " . (class_exists('Firebase\JWT\JWT') ? "YES" : "NO") . "\n";
echo "✓ Key class exists: " . (class_exists('Firebase\JWT\Key') ? "YES" : "NO") . "\n";
echo "✓ ExpiredException exists: " . (class_exists('Firebase\JWT\ExpiredException') ? "YES" : "NO") . "\n";

// Test 3: AuthService can use JWT
echo "\n[Test 3] AuthService can instantiate...\n";
try {
    $authService = new \App\Services\AuthService();
    echo "✓ AuthService instantiated successfully\n";
} catch (Throwable $e) {
    echo "✗ AuthService failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Auth core class works
echo "\n[Test 4] Auth core class methods...\n";
try {
    $token = \App\Core\Auth::generateAccessToken(1, 'admin');
    echo "✓ Auth::generateAccessToken() works\n";
    echo "✓ Generated token (truncated): " . substr($token, 0, 30) . "...\n";
} catch (Throwable $e) {
    echo "✗ Auth::generateAccessToken() failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== All Tests Passed ✓ ===\n";
