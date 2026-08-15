<?php

/**
 * Database Seeder Runner
 *
 * This script runs all configured seeders to populate the database with
 * comprehensive test data for BCA, MSC, and other programs.
 *
 * Usage from project root: 
 *   php scripts/seed.php
 *   php scripts/seed.php --force
 */

use App\Seeders\DatabaseSeeder;

define('BASE_PATH', dirname(__DIR__));

// Register autoloader
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

// Load environment and config
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

$usage = "
Database Seeder Runner
Usage: php scripts/seed.php [options]

Options:
  --force    Skip confirmation prompt
  --help     Show this message
";

$force = in_array('--force', $argv);
$showHelp = in_array('--help', $argv);

if ($showHelp) {
    echo $usage;
    exit(0);
}

echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";
echo "║  Database Seeder - IMS Test Data Populate         ║\n";
echo "╚════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    // Get database connection configuration
    $config = require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'database.php';
    
    $db = (array) ($config['default'] ?? []);
    
    $host = (string) ($db['host'] ?? '127.0.0.1');
    $port = (int) ($db['port'] ?? 3306);
    $database = (string) ($db['database'] ?? 'ims_final');
    $charset = (string) ($db['charset'] ?? 'utf8mb4');
    $username = (string) ($db['username'] ?? 'root');
    $password = (string) ($db['password'] ?? '');
    
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Database Connection: {$database} @ {$host}:{$port}\n";
    echo "\nData to be seeded:\n";
    echo "  ✓ Programs (BCA and MSC)\n";
    echo "  ✓ Semesters (4 total)\n";
    echo "  ✓ Subjects (10 core subjects)\n";
    echo "  ✓ Teachers (3 faculty members)\n";
    echo "  ✓ Students (8 total, enrolled in programs)\n";
    echo "  ✓ Teacher Assignments (10 subject assignments)\n";
    echo "  ✓ Timetables (12 class slots)\n";
    echo "\n";

    if (!$force) {
        echo "This will seed your database with small test data.\n";
        echo "Are you sure you want to continue? (yes/no): ";
        $input = trim(fgets(STDIN));

        if (strtolower($input) !== 'yes' && $input !== 'y') {
            echo "Seeding cancelled.\n";
            exit(0);
        }
    }

    echo "\nSeeding database...\n";
    echo str_repeat("─", 50) . "\n";

    $seeder = new DatabaseSeeder($pdo);
    $seeder->run();

    echo str_repeat("─", 50) . "\n";
    echo "\n✓ Database seeding completed successfully!\n";
    echo "\n✅ Test data ready. Login credentials:\n";
    echo "   Principal: principal.wilson\n";
    echo "   Teacher:   dr.johnson\n";
    echo "   Student:   janderson (BCA)\n";
    echo "   Password:  password123 (for all accounts)\n";
    echo "\n📄 For detailed information, see:\n";
    echo "   - SEED_DATA.md (complete curriculum details)\n";
    echo "   - TEST_DATA_SUMMARY.md (quick reference)\n";
    echo "\n";

} catch (\Exception $e) {
    echo "\n✗ Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
    
    if (isset($pdo)) {
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (\Exception $rollbackError) {
            echo "Rollback error: {$rollbackError->getMessage()}\n";
        }
    }
    
    exit(1);
}
