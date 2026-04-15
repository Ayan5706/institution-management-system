<?php

/**
 * Database Optimization Script
 *
 * Optimize tables and perform maintenance tasks.
 *
 * Usage: php scripts/optimize-database.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

use App\Core\Database;

class DatabaseOptimizer
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function run(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║           Database Optimization & Maintenance            ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";

        $this->optimizeTables();
        $this->checkTablesForErrors();
        $this->analyzeTablesStatistics();
        $this->displaySummary();
    }

    private function optimizeTables(): void
    {
        echo "Optimizing Tables\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                try {
                    $this->pdo->exec("OPTIMIZE TABLE `{$table}`");
                    echo "✓ Optimized: {$table}\n";
                } catch (\PDOException $e) {
                    echo "⚠ {$table}: " . $e->getMessage() . "\n";
                }
            }

            echo "\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    private function checkTablesForErrors(): void
    {
        echo "Checking Table Integrity\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            $hasErrors = false;

            foreach ($tables as $table) {
                try {
                    $result = $this->pdo->query("CHECK TABLE `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($result as $check) {
                        $status = strtolower($check['Msg_type'] ?? '');
                        $icon = ($status === 'status' || $status === 'ok') ? '✓' : '✗';

                        echo "[{$icon}] {$table}: " . ($check['Msg_text'] ?? 'OK') . "\n";

                        if ($status === 'error') {
                            $hasErrors = true;
                        }
                    }
                } catch (\PDOException $e) {
                    echo "⚠ {$table}: " . $e->getMessage() . "\n";
                }
            }

            if ($hasErrors) {
                echo "\n⚠️  Table errors detected. Consider running repair.\n";
            }

            echo "\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    private function analyzeTablesStatistics(): void
    {
        echo "Table Statistics\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $config = require BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'database.php';
            $db = (array) ($config['default'] ?? []);
            $database = (string) ($db['database'] ?? 'ims_final');

            $query = "
                SELECT 
                    TABLE_NAME,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    TABLE_ROWS,
                    GRANTEES
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
                ORDER BY (data_length + index_length) DESC
            ";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$database]);
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            printf("%-30s %-15s %-15s\n", 'Table', 'Size (MB)', 'Rows');
            echo str_repeat("─", 60) . "\n";

            $totalSize = 0;
            foreach ($tables as $table) {
                printf(
                    "%-30s %-15.2f %-15d\n",
                    $table['TABLE_NAME'],
                    $table['size_mb'],
                    $table['TABLE_ROWS']
                );
                $totalSize += $table['size_mb'];
            }

            echo str_repeat("─", 60) . "\n";
            printf("%-30s %-15.2f\n", 'TOTAL', $totalSize);
            echo "\n";
        } catch (\PDOException $e) {
            echo "⚠ Could not retrieve statistics: " . $e->getMessage() . "\n";
        }
    }

    private function displaySummary(): void
    {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║              Optimization Complete ✓                    ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
}

$optimizer = new DatabaseOptimizer();
$optimizer->run();
