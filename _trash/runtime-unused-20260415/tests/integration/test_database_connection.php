<?php

declare(strict_types=1);

echo "=== Step 4: Database Connection Test ===\n\n";

// Load app
echo "[1] Loading bootstrap/app.php...\n";
try {
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "✓ Bootstrap loaded\n";
} catch (Throwable $e) {
    echo "✗ FATAL: " . $e->getMessage() . "\n";
    exit(1);
}

// Check config loading
echo "\n[2] Checking Config...\n";
$dbConfig = \App\Config\Config::get('database.default');
if ($dbConfig) {
    echo "✓ Database config loaded\n";
    echo "  - Host: " . ($dbConfig['host'] ?? 'N/A') . "\n";
    echo "  - Database: " . ($dbConfig['database'] ?? 'N/A') . "\n";
    echo "  - Driver: " . ($dbConfig['driver'] ?? 'N/A') . "\n";
} else {
    echo "✗ Database config NOT found\n";
    exit(1);
}

// Test database connection
echo "\n[3] Testing database connection...\n";
try {
    $db = \App\Core\Database::connection();
    echo "✓ Database connection successful\n";
    
    // Test a simple query
    $stmt = $db->query('SELECT 1 as test');
    $result = $stmt->fetch();
    echo "✓ Query execution works (SELECT 1: " . $result['test'] . ")\n";
} catch (Throwable $e) {
    echo "✗ Database connection FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Check users table exists
echo "\n[4] Checking users table...\n";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $table = $stmt->fetch();
    if ($table) {
        echo "✓ Users table exists\n";
        
        // Get basic table info
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "✓ Users table columns: " . count($columns) . "\n";
    } else {
        echo "✗ Users table NOT found\n";
    }
} catch (Throwable $e) {
    echo "✗ Table check failed: " . $e->getMessage() . "\n";
}

echo "\n=== Database Phase Complete ===\n";
