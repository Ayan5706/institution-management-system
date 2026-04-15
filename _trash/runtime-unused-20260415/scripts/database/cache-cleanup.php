<?php

/**
 * Cache & Logs Cleanup Script
 *
 * Clear application cache and cleanup log files.
 *
 * Usage: php scripts/cache-cleanup.php [--cache|--logs|--all]
 */

define('BASE_PATH', dirname(__DIR__));

class CacheCleanup
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function clearCache(): void
    {
        echo "\nClearing cache...\n";
        echo str_repeat("─", 60) . "\n";

        $cacheDir = $this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';

        if (!is_dir($cacheDir)) {
            echo "Cache directory not found.\n\n";
            return;
        }

        $files = glob($cacheDir . DIRECTORY_SEPARATOR . '*');
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }

        echo "✓ Cleared {$count} cache file(s).\n\n";
    }

    public function cleanLogs(int $daysOld = 30): void
    {
        echo "\nCleaning logs (older than {$daysOld} days)...\n";
        echo str_repeat("─", 60) . "\n";

        $logDir = $this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';

        if (!is_dir($logDir)) {
            echo "Log directory not found.\n\n";
            return;
        }

        $files = glob($logDir . DIRECTORY_SEPARATOR . '*.log');
        $threshold = time() - ($daysOld * 86400);
        $count = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                echo "  Deleted: " . basename($file) . "\n";
                $count++;
            }
        }

        echo "✓ Deleted {$count} old log file(s).\n\n";
    }

    public function rotateLog(string $logFile): void
    {
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (is_file($logFile) && filesize($logFile) > $maxSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $extension = pathinfo($logFile, PATHINFO_EXTENSION);
            $basename = pathinfo($logFile, PATHINFO_FILENAME);
            $dir = dirname($logFile);

            $rotatedFile = "{$dir}/{$basename}_{$timestamp}.{$extension}";
            rename($logFile, $rotatedFile);

            file_put_contents($logFile, '');
        }
    }

    public function showHelp(): void
    {
        echo "\nCache & Logs Cleanup\n";
        echo str_repeat("─", 60) . "\n";
        echo "Usage: php scripts/cache-cleanup.php <command>\n\n";
        echo "Commands:\n";
        echo "  --cache     Clear application cache\n";
        echo "  --logs      Clean old log files (>30 days)\n";
        echo "  --all       Clear cache and clean logs\n";
        echo "  --help      Show this message\n\n";
    }
}

$cleanup = new CacheCleanup(BASE_PATH);

$arg = $argv[1] ?? '--help';

if ($arg === '--cache') {
    $cleanup->clearCache();
} elseif ($arg === '--logs') {
    $cleanup->cleanLogs();
} elseif ($arg === '--all') {
    $cleanup->clearCache();
    $cleanup->cleanLogs();
} elseif ($arg === '--help' || $arg === '-h') {
    $cleanup->showHelp();
} else {
    $cleanup->showHelp();
}
