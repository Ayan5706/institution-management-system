<?php

/**
 * Database Connectivity Test
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "DATABASE CONNECTIVITY TEST\n";
echo str_repeat("=", 80) . "\n\n";

// Attempt to bootstrap app and check database config
try {
    require_once BASE_PATH . '/bootstrap/app.php';
    require_once BASE_PATH . '/app/Config/database.php';
    
    $dbConfig = require BASE_PATH . '/app/Config/database.php';
    $config = $dbConfig['default'];
    
    echo "Database Configuration:\n";
    echo str_repeat("-", 80) . "\n";
    echo "Driver:     " . $config['driver'] . "\n";
    echo "Host:       " . $config['host'] . "\n";
    echo "Port:       " . $config['port'] . "\n";
    echo "Database:   " . $config['database'] . "\n";
    echo "Username:   " . $config['username'] . "\n";
    echo "Charset:    " . $config['charset'] . "\n";
    
    // Try to connect
    echo "\n";
    echo str_repeat("-", 80) . "\n";
    echo "Attempting Database Connection...\n";
    echo str_repeat("-", 80) . "\n";
    
    try {
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        $pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'] ?: null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        
        echo "✓ Connection successful\n";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\nDatabase Tables Found: " . count($tables) . "\n";
        foreach ($tables as $table) {
            echo "  ✓ " . $table . "\n";
        }
        
        // Get version
        $version = $pdo->query("SELECT VERSION()")->fetch(PDO::FETCH_COLUMN);
        echo "\n✓ Database Version: " . $version . "\n";
        
        echo "\n";
        echo str_repeat("=", 80) . "\n";
        echo "STATUS: ✓ DATABASE CONNECTIVITY VERIFIED\n";
        echo str_repeat("=", 80) . "\n";
        
    } catch (PDOException $e) {
        echo "⚠ Database Connection Failed\n";
        echo "Reason: " . $e->getMessage() . "\n";
        echo "\nNote: This is expected if MariaDB/MySQL isn't running.\n";
        echo "Configure .env file and ensure database server is active.\n";
        echo "\n";
        echo str_repeat("=", 80) . "\n";
        echo "STATUS: ⚠ DATABASE NOT CONNECTED (Expected - Server may not be running)\n";
        echo str_repeat("=", 80) . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
