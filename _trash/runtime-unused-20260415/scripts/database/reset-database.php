<?php

/**
 * Database Reset Script
 *
 * WARNING: This script will drop and recreate the database!
 * Use only in development environments.
 *
 * Usage: php scripts/reset-database.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

use App\Core\Database;

class DatabaseReset
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function reset(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════╗\n";
        echo "║     DATABASE RESET - DEVELOPMENT     ║\n";
        echo "╚══════════════════════════════════════╝\n";
        echo "\n";
        echo "⚠️  WARNING: This will DELETE all data in the database!\n";
        echo "This action CANNOT be undone.\n\n";

        echo "Type 'yes' to confirm: ";
        $input = trim(fgets(STDIN));

        if (strtolower($input) !== 'yes') {
            echo "Reset cancelled.\n\n";
            exit(0);
        }

        try {
            $config = require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'database.php';
            $db = (array) ($config['default'] ?? []);
            $database = (string) ($db['database'] ?? 'ims_final');

            echo "\nDropping database '{$database}'...\n";
            $this->pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
            echo "✓ Database dropped.\n";

            echo "Creating fresh database '{$database}'...\n";
            $this->pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✓ Database created.\n";

            echo "\n✓ Reset complete!\n";
            echo "\nNext steps:\n";
            echo "  1. php scripts/migrate.php (Run migrations)\n";
            echo "  2. php scripts/seed.php    (Seed initial data)\n\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

$reset = new DatabaseReset();
$reset->reset();
