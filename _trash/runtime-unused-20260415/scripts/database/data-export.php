<?php

/**
 * Data Export Script
 *
 * Export database tables to CSV format.
 *
 * Usage: php scripts/data-export.php [table_name]
 *        php scripts/data-export.php --all
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

use App\Core\Database;

class DataExporter
{
    private \PDO $pdo;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->pdo = Database::connection();
    }

    public function exportTable(string $tableName): void
    {
        echo "\nExporting table: {$tableName}\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $stmt = $this->pdo->query("SELECT * FROM `{$tableName}`");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                echo "✗ Table is empty or not found.\n";
                return;
            }

            $timestamp = date('Y-m-d_H-i-s');
            $exportDir = $this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'exports';

            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $filename = "{$exportDir}/{$tableName}_{$timestamp}.csv";
            $file = fopen($filename, 'w');

            // Write headers
            $headers = array_keys($rows[0]);
            fputcsv($file, $headers);

            // Write data
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

            $size = filesize($filename);
            echo "✓ Exported " . count($rows) . " rows\n";
            echo "  File: " . basename($filename) . "\n";
            echo "  Size: " . $this->formatBytes($size) . "\n\n";
        } catch (\PDOException $e) {
            echo "✗ Export failed: " . $e->getMessage() . "\n";
        }
    }

    public function exportAllTables(): void
    {
        echo "\nExporting all tables...\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($tables)) {
                echo "✗ No tables found.\n";
                return;
            }

            foreach ($tables as $table) {
                $this->exportTable($table);
            }

            echo "✓ All tables exported successfully!\n\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    public function listTables(): void
    {
        echo "\nAvailable Tables\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($tables)) {
                echo "No tables found.\n";
                return;
            }

            printf("%-30s %-15s\n", 'Table', 'Records');
            echo str_repeat("─", 60) . "\n";

            foreach ($tables as $table) {
                $count = $this->pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                printf("%-30s %-15d\n", $table, $count);
            }

            echo "\n";
        } catch (\PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    public function showHelp(): void
    {
        echo "\nData Exporter\n";
        echo str_repeat("─", 60) . "\n";
        echo "Usage: php scripts/data-export.php <command>\n\n";
        echo "Commands:\n";
        echo "  --list              List all tables and their record count\n";
        echo "  --all               Export all tables to CSV\n";
        echo "  <table_name>        Export specific table to CSV\n";
        echo "  --help              Show this message\n\n";
        echo "Exports are saved in: storage/exports/\n\n";
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

$exporter = new DataExporter(BASE_PATH);

$command = $argv[1] ?? '--help';

match ($command) {
    '--list' => $exporter->listTables(),
    '--all' => $exporter->exportAllTables(),
    '--help', '-h' => $exporter->showHelp(),
    default => (
        (str_starts_with($command, '--')) 
            ? $exporter->showHelp()
            : $exporter->exportTable($command)
    ),
};
