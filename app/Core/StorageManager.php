<?php

declare(strict_types=1);

namespace App\Core;

class StorageManager
{
    private string $basePath;
    private Logger $logger;
    private Cache $cache;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage';
        $this->logger = new Logger($this->basePath . DIRECTORY_SEPARATOR . 'logs');
        $this->cache = new Cache($this->basePath . DIRECTORY_SEPARATOR . 'cache');
    }

    /**
     * Get storage directory path
     */
    public function getPath(string $type = ''): string
    {
        if ($type === '') {
            return $this->basePath;
        }

        $paths = [
            'logs' => 'logs',
            'cache' => 'cache',
            'sessions' => 'sessions',
            'temp' => 'temp',
            'backups' => 'backups',
            'exports' => 'exports',
        ];

        if (!isset($paths[$type])) {
            throw new \InvalidArgumentException("Unknown storage type: {$type}");
        }

        return $this->basePath . DIRECTORY_SEPARATOR . $paths[$type];
    }

    /**
     * Get logger instance
     */
    public function logger(): Logger
    {
        return $this->logger;
    }

    /**
     * Get cache instance
     */
    public function cache(): Cache
    {
        return $this->cache;
    }

    /**
     * Check if storage directory is writable
     */
    public function isWritable(string $type = ''): bool
    {
        $path = $type ? $this->getPath($type) : $this->basePath;
        return is_writable($path);
    }

    /**
     * Get total storage size in bytes
     */
    public function getTotalSize(string $type = ''): int
    {
        $path = $type ? $this->getPath($type) : $this->basePath;
        return $this->calculateDirSize($path);
    }

    /**
     * Get storage size formatted as human-readable
     */
    public function getFormattedSize(string $type = ''): string
    {
        $bytes = $this->getTotalSize($type);
        return $this->formatBytes($bytes);
    }

    /**
     * Get file count in storage type
     */
    public function getFileCount(string $type = ''): int
    {
        $path = $type ? $this->getPath($type) : $this->basePath;
        return $this->countFiles($path);
    }

    /**
     * Get storage statistics
     */
    public function getStats(): array
    {
        $types = ['logs', 'cache', 'sessions', 'temp', 'backups', 'exports'];
        $stats = [];

        foreach ($types as $type) {
            try {
                $path = $this->getPath($type);
                $stats[$type] = [
                    'path' => $path,
                    'exists' => is_dir($path),
                    'writable' => is_writable($path),
                    'size_bytes' => $this->getTotalSize($type),
                    'size_mb' => round($this->getTotalSize($type) / 1024 / 1024, 2),
                    'files' => $this->getFileCount($type),
                ];
            } catch (\Exception $e) {
                $stats[$type] = ['error' => $e->getMessage()];
            }
        }

        return $stats;
    }

    /**
     * Clean old files in a storage type
     */
    public function cleanOldFiles(string $type, int $daysOld = 30): int
    {
        $path = $this->getPath($type);
        $threshold = time() - ($daysOld * 86400);
        $deleted = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $files = glob($path . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $threshold) {
                if (unlink($file)) {
                    $deleted++;
                    $this->logger->info("Deleted old file: {$file}");
                }
            }
        }

        return $deleted;
    }

    /**
     * Ensure storage directory exists
     */
    public function ensureDirectories(): void
    {
        $types = ['logs', 'cache', 'sessions', 'temp', 'backups', 'exports'];

        foreach ($types as $type) {
            $path = $this->getPath($type);
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                $this->logger->info("Created storage directory: {$type}");
            }
        }
    }

    /**
     * Fix storage permissions
     */
    public function fixPermissions(): array
    {
        $types = ['logs', 'cache', 'sessions', 'temp', 'backups', 'exports'];
        $results = [];

        foreach ($types as $type) {
            try {
                $path = $this->getPath($type);
                if (is_dir($path)) {
                    chmod($path, 0755);
                    $results[$type] = [
                        'success' => true,
                        'message' => "Permissions fixed for {$type}",
                    ];
                } else {
                    $results[$type] = [
                        'success' => false,
                        'message' => "Directory not found: {$type}",
                    ];
                }
            } catch (\Exception $e) {
                $results[$type] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Cleanup all storage
     */
    public function cleanup(): array
    {
        $results = [
            'cache_cleared' => $this->cache->flush(),
            'logs_cleaned' => $this->cleanOldFiles('logs', 30),
            'temp_cleaned' => $this->cleanOldFiles('temp', 1),
            'backups_cleaned' => $this->cleanOldFiles('backups', 30),
        ];

        $this->logger->info('Storage cleanup completed', $results);

        return $results;
    }

    /**
     * Calculate directory size recursively
     */
    private function calculateDirSize(string $path): int
    {
        $size = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    $size += $this->calculateDirSize($filePath);
                } else {
                    $size += filesize($filePath);
                }
            }
        }

        return $size;
    }

    /**
     * Count files recursively
     */
    private function countFiles(string $path): int
    {
        $count = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    $count += $this->countFiles($filePath);
                } else {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
