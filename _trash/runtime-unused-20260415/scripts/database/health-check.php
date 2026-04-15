<?php

/**
 * System Health Check Script
 *
 * Diagnose system health and identify issues.
 *
 * Usage: php scripts/health-check.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

use App\Core\Database;

class HealthCheck
{
    private \PDO $pdo;
    private string $basePath;
    private array $results = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        try {
            $this->pdo = Database::connection();
        } catch (\Exception $e) {
            $this->pdo = null;
        }
    }

    public function run(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║              System Health Check                         ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";

        $this->checkPHP();
        $this->checkExtensions();
        $this->checkDirectories();
        $this->checkPermissions();
        $this->checkDatabase();
        $this->checkDiskSpace();
        $this->displayReport();
    }

    private function checkPHP(): void
    {
        echo "PHP Environment\n";
        echo str_repeat("─", 60) . "\n";

        $version = phpversion();
        echo "PHP Version: {$version}\n";

        $required = '7.4.0';
        $ok = version_compare($version, $required, '>=');
        $status = $ok ? '✓' : '✗';
        echo "[{$status}] Required: >= {$required}\n\n";

        $this->results['php'] = $ok;
    }

    private function checkExtensions(): void
    {
        echo "Required Extensions\n";
        echo str_repeat("─", 60) . "\n";

        $required = ['pdo_mysql', 'gd', 'json', 'mbstring', 'openssl'];
        $allOk = true;

        foreach ($required as $ext) {
            $loaded = extension_loaded($ext);
            $status = $loaded ? '✓' : '✗';
            echo "[{$status}] {$ext}\n";
            if (!$loaded) {
                $allOk = false;
            }
        }

        echo "\n";
        $this->results['extensions'] = $allOk;
    }

    private function checkDirectories(): void
    {
        echo "Directory Structure\n";
        echo str_repeat("─", 60) . "\n";

        $dirs = [
            'app',
            'bootstrap',
            'public',
            'storage',
            'storage/logs',
            'storage/cache',
            'storage/backups',
            'public/uploads',
        ];

        $allOk = true;
        foreach ($dirs as $dir) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            $exists = is_dir($path);
            $status = $exists ? '✓' : '✗';
            echo "[{$status}] {$dir}\n";
            if (!$exists) {
                $allOk = false;
            }
        }

        echo "\n";
        $this->results['directories'] = $allOk;
    }

    private function checkPermissions(): void
    {
        echo "File Permissions\n";
        echo str_repeat("─", 60) . "\n";

        $dirs = [
            'storage',
            'public/uploads',
            'storage/logs',
        ];

        $allOk = true;
        foreach ($dirs as $dir) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($path)) {
                $writable = is_writable($path);
                $readable = is_readable($path);
                $ok = $writable && $readable;

                $status = $ok ? '✓' : '✗';
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                echo "[{$status}] {$dir} ({$perms})\n";

                if (!$ok) {
                    $allOk = false;
                }
            }
        }

        echo "\n";
        $this->results['permissions'] = $allOk;
    }

    private function checkDatabase(): void
    {
        echo "Database Connection\n";
        echo str_repeat("─", 60) . "\n";

        if (!$this->pdo) {
            echo "[✗] Connection FAILED\n\n";
            $this->results['database'] = false;
            return;
        }

        try {
            $version = $this->pdo->query('SELECT VERSION()')->fetchColumn();
            echo "[✓] Connected to MySQL {$version}\n";

            $tables = $this->pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            $count = count($tables);
            echo "[✓] {$count} tables found\n";

            // Check migrations
            $migrations = $this->pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn() ?? 0;
            echo "[✓] {$migrations} migrations applied\n\n";

            $this->results['database'] = true;
        } catch (\Exception $e) {
            echo "[✗] Error: " . $e->getMessage() . "\n\n";
            $this->results['database'] = false;
        }
    }

    private function checkDiskSpace(): void
    {
        echo "Disk Space\n";
        echo str_repeat("─", 60) . "\n";

        $uploadsDir = $this->basePath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $total = disk_total_space($uploadsDir);
        $free = disk_free_space($uploadsDir);
        $used = $total - $free;
        $percent = (($used / $total) * 100);

        echo "Total: " . $this->formatBytes($total) . "\n";
        echo "Used:  " . $this->formatBytes($used) . " ({$percent}%)\n";
        echo "Free:  " . $this->formatBytes($free) . "\n";

        $ok = $free > (100 * 1024 * 1024); // At least 100MB free
        $status = $ok ? '✓' : '⚠';
        echo "[{$status}] Disk space OK\n\n";

        $this->results['disk_space'] = $ok;
    }

    private function displayReport(): void
    {
        echo "Summary\n";
        echo str_repeat("─", 60) . "\n";

        $allOk = array_reduce($this->results, fn ($carry, $item) => $carry && $item, true);

        foreach ($this->results as $check => $ok) {
            $status = $ok ? '✓' : '✗';
            $name = ucfirst(str_replace('_', ' ', $check));
            echo "[{$status}] {$name}\n";
        }

        echo "\n";

        if ($allOk) {
            echo "╔══════════════════════════════════════════════════════════╗\n";
            echo "║              All Systems Operational ✓                   ║\n";
            echo "╚══════════════════════════════════════════════════════════╝\n";
        } else {
            echo "⚠️  Some issues detected. Please review above and fix.\n";
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
}

$check = new HealthCheck(BASE_PATH);
$check->run();
