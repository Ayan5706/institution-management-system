<?php

define('BASE_PATH', dirname(__DIR__));

// Skip bootstrap error handler for now to see raw errors
require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'app.php';
require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'helpers.php';

use App\Seeders\DatabaseSeeder;

try {
    // Get database connection
    $config = require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'database.php';
    
    $db = (array) ($config['default'] ?? []);
    
    $host = (string) ($db['host'] ?? '127.0.0.1');
    $port = (int) ($db['port'] ?? 3306);
    $database = (string) ($db['database'] ?? 'ims_final');
    $charset = (string) ($db['charset'] ?? 'utf8mb4');
    $username = (string) ($db['username'] ?? 'root');
    $password = (string) ($db['password'] ?? '');
    
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
    
    echo "DSN: {$dsn}\n";
    echo "Username: {$username}\n";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connection successful!\n";
    
    $seeder = new DatabaseSeeder($pdo);
    $seeder->run();
    
    echo "\n✓ Database seeding completed successfully!\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
