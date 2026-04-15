<?php

declare(strict_types=1);

echo "=== Step 1: Application Bootstrap Test ===\n\n";

// Simulate public/index.php without running app
echo "[1] Loading bootstrap/app.php...\n";
try {
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "✓ Bootstrap successful\n";
    echo "✓ App instance: " . get_class($app) . "\n";
} catch (Throwable $e) {
    echo "✗ FATAL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[2] Checking require relationships...\n";
echo "✓ vendor/autoload.php: " . (file_exists(__DIR__ . '/vendor/autoload.php') ? "EXISTS" : "MISSING") . "\n";
echo "✓ Firebase JWT: " . (class_exists('Firebase\JWT\JWT') ? "LOADED" : "NOT LOADED") . "\n";

echo "\n[3] Checking config files...\n";
echo "✓ bootstrap/config.php: " . (file_exists(__DIR__ . '/bootstrap/config.php') ? "EXISTS" : "MISSING") . "\n";
echo "✓ bootstrap/errors.php: " . (file_exists(__DIR__ . '/bootstrap/errors.php') ? "EXISTS" : "MISSING") . "\n";
echo "✓ bootstrap/helpers.php: " . (file_exists(__DIR__ . '/bootstrap/helpers.php') ? "EXISTS" : "MISSING") . "\n";

echo "\n=== Bootstrap Phase Complete ✓ ===\n";
