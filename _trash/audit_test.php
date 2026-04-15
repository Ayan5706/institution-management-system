<?php
echo "=== IMS FEASIBILITY TEST ===\n\n";

// Test 1: Check bootstrap loading
echo "1. Bootstrap Loading: ";
try {
    require_once dirname(__DIR__) . '/bootstrap/app.php';
    echo "✓ OK\n";
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check Database Connection
echo "2. Database Connection: ";
try {
    $db = \App\Core\Database::connection();
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM users');
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    echo "✓ OK ({$result['cnt']} users)\n";
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check AuthService
echo "3. AuthService::login: ";
try {
    $authService = new \App\Services\AuthService();
    $result = $authService->login('principal@imsschool.local', 'principal123');
    if ($result['success']) {
        echo "✓ OK - Token generated\n";
        echo "   Access Token: " . substr($result['accessToken'], 0, 20) . "...\n";
    } else {
        echo "✗ FAILED: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
} catch (Throwable $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Tests Complete ===\n";
