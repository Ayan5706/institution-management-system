<?php

/**
 * Database & Files Backup Script
 *
 * Creates backups of the database and important files.
 *
 * Usage: php scripts/backup.php [--db-only|--files-only]
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

use App\Core\Database;

class BackupManager
{
    private \PDO $pdo;
    private string $basePath;
    private string $backupDir;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->backupDir = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        $this->ensureBackupDir();
        $this->pdo = Database::connection();
    }

    private function ensureBackupDir(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function backupDatabase(): void
    {
        echo "\nBacking up database...\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $config = require $this->basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'database.php';
            $db = (array) ($config['default'] ?? []);

            $database = (string) ($db['database'] ?? 'ims_final');
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $this->backupDir . DIRECTORY_SEPARATOR . "db_{$database}_{$timestamp}.sql";

            $tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'")
                               ?: $this->pdo->query("SHOW TABLES FROM `{$database}`");

            if (!$tables) {
                // MySQL approach
                $stmt = $this->pdo->query("SELECT GROUP_CONCAT(CONCAT('SELECT * FROM ', TABLE_NAME) SEPARATOR ' UNION ') FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$database}'");
                $tables = $this->pdo->query("SHOW TABLES FROM `{$database}`")->fetchAll(\PDO::FETCH_COLUMN);
            } else {
                $tables = $tables->fetchAll(\PDO::FETCH_COLUMN);
            }

            $sql = "-- IMS Database Backup\n";
            $sql .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: {$database}\n\n";

            foreach ($tables as $table) {
                $tableData = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                if ($tableData) {
                    $sql .= $tableData['Create Table'] ?? '';
                    $sql .= ";\n\n";

                    $rows = $this->pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            return $val === null ? 'NULL' : "'" . $this->pdo->quote($val) . "'";
                        }, $row);

                        $columns = implode(', ', array_keys($row));
                        $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            file_put_contents($backupFile, $sql);

            $size = filesize($backupFile);
            $sizeFormatted = $this->formatBytes($size);

            echo "✓ Database backed up successfully!\n";
            echo "  File: " . basename($backupFile) . "\n";
            echo "  Size: {$sizeFormatted}\n\n";
        } catch (\Exception $e) {
            echo "✗ Backup failed: " . $e->getMessage() . "\n";
        }
    }

    public function backupFiles(): void
    {
        echo "\nBacking up files...\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $dirs = [
                'public/uploads/avatars',
                'public/uploads/documents',
                'public/uploads/products',
            ];

            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $this->backupDir . DIRECTORY_SEPARATOR . "files_{$timestamp}.zip";

            $zip = new ZipArchive();
            if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Failed to create zip file');
            }

            foreach ($dirs as $dir) {
                $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $dir;
                if (is_dir($fullPath)) {
                    $this->addDirToZip($zip, $fullPath, $dir);
                }
            }

            $zip->close();

            $size = filesize($backupFile);
            $sizeFormatted = $this->formatBytes($size);

            echo "✓ Files backed up successfully!\n";
            echo "  File: " . basename($backupFile) . "\n";
            echo "  Size: {$sizeFormatted}\n\n";
        } catch (\Exception $e) {
            echo "✗ Backup failed: " . $e->getMessage() . "\n";
        }
    }

    private function addDirToZip(\ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $addPath = $zipPath . '/' . $file;

            if (is_dir($filePath)) {
                $this->addDirToZip($zip, $filePath, $addPath);
            } else {
                $zip->addFile($filePath, $addPath);
            }
        }
    }

    public function cleanOldBackups(int $daysOld = 30): void
    {
        echo "\nCleaning old backups (older than {$daysOld} days)...\n";
        echo str_repeat("─", 60) . "\n";

        $threshold = time() - ($daysOld * 86400);
        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . '*');
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $threshold) {
                unlink($file);
                echo "✓ Deleted: " . basename($file) . "\n";
                $deleted++;
            }
        }

        echo "\n✓ Deleted {$deleted} old backup(s).\n\n";
    }

    public function listBackups(): void
    {
        echo "\nBackups\n";
        echo str_repeat("─", 60) . "\n";

        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . '*');
        if (empty($files)) {
            echo "No backups found.\n";
            return;
        }

        rsort($files);

        printf("%-30s %-15s %-20s\n", 'File', 'Size', 'Created');
        echo str_repeat("─", 60) . "\n";

        foreach ($files as $file) {
            if (is_file($file)) {
                $size = $this->formatBytes(filesize($file));
                $time = date('Y-m-d H:i:s', filemtime($file));
                printf("%-30s %-15s %-20s\n", basename($file), $size, $time);
            }
        }

        echo "\n";
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

    public function showHelp(): void
    {
        echo "\nBackup Manager\n";
        echo str_repeat("─", 60) . "\n";
        echo "Usage: php scripts/backup.php <command> [options]\n\n";
        echo "Commands:\n";
        echo "  --db-only       Backup database only\n";
        echo "  --files-only    Backup files only\n";
        echo "  --list          List all backups\n";
        echo "  --clean         Clean backups older than 30 days\n";
        echo "  --help          Show this message\n\n";
        echo "Default: Backup both database and files\n\n";
    }
}

$manager = new BackupManager(BASE_PATH);

$arg = $argv[1] ?? '--help';

if ($arg === '--db-only') {
    $manager->backupDatabase();
} elseif ($arg === '--files-only') {
    $manager->backupFiles();
} elseif ($arg === '--list') {
    $manager->listBackups();
} elseif ($arg === '--clean') {
    $manager->cleanOldBackups();
} elseif ($arg === '--help' || $arg === '-h') {
    $manager->showHelp();
} else {
    $manager->backupDatabase();
    $manager->backupFiles();
}

echo "\n";
