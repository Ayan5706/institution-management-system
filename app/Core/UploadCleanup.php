<?php

/**
 * Upload Cleanup Utility
 * Clean temporary and orphaned files
 */

class UploadCleanup
{
    private $config;
    private $uploadDir;

    public function __construct()
    {
        $this->config = include(__DIR__ . '/../Config/uploads.php');
        $this->uploadDir = $this->config['upload_dir'];
    }

    /**
     * Clean temporary files
     */
    public function cleanTempDir()
    {
        $tempDir = $this->uploadDir . '/temp';
        $retention = $this->config['cleanup']['temp_dir_retention'];

        if (!is_dir($tempDir)) {
            return ['removed' => 0, 'space_freed' => 0];
        }

        $removed = 0;
        $spaceFreed = 0;
        $now = time();

        foreach (scandir($tempDir) as $file) {
            if ($file === '.' || $file === '..') continue;

            $filePath = $tempDir . '/' . $file;
            $fileAge = $now - filemtime($filePath);

            if ($fileAge > $retention) {
                $size = filesize($filePath);
                if (unlink($filePath)) {
                    $removed++;
                    $spaceFreed += $size;
                }
            }
        }

        return [
            'removed' => $removed,
            'space_freed' => $spaceFreed,
            'space_formatted' => $this->formatBytes($spaceFreed)
        ];
    }

    /**
     * Clean orphaned files (not referenced in database)
     */
    public function cleanOrphanedFiles($database = null)
    {
        $result = [
            'checked' => 0,
            'removed' => 0,
            'space_freed' => 0
        ];

        foreach ($this->config['directories'] as $key => $directory) {
            if ($key === 'temp') continue; // Skip temp dir

            $dirPath = $this->uploadDir . '/' . $directory;

            if (!is_dir($dirPath)) continue;

            foreach (scandir($dirPath) as $file) {
                if ($file === '.' || $file === '..') continue;

                $result['checked']++;

                // Check if file is referenced in database
                if ($database && !$this->isFileReferenced($database, $directory, $file)) {
                    $filePath = $dirPath . '/' . $file;

                    if (file_exists($filePath)) {
                        $size = filesize($filePath);
                        if (unlink($filePath)) {
                            $result['removed']++;
                            $result['space_freed'] += $size;
                        }
                    }

                    // Remove associated thumbnail if exists
                    if ($directory === 'products/images') {
                        $thumbPath = $this->uploadDir . '/products/thumbnails/' . $file;
                        if (file_exists($thumbPath)) {
                            unlink($thumbPath);
                        }
                    }
                }
            }
        }

        $result['space_formatted'] = $this->formatBytes($result['space_freed']);
        return $result;
    }

    /**
     * Check if file is referenced in database
     */
    private function isFileReferenced($database, $directory, $filename)
    {
        // This is a placeholder - implement based on your database structure
        // Example: Check if file path exists in products, documents, users tables
        
        $filePath = $directory . '/' . $filename;

        // Check in products table
        $product = $database->query("SELECT id FROM products WHERE image_path = ?", [$filePath])->first();
        if ($product) return true;

        // Check in users table (avatar)
        $user = $database->query("SELECT id FROM users WHERE avatar_path = ?", [$filePath])->first();
        if ($user) return true;

        // Check in documents table
        $document = $database->query("SELECT id FROM documents WHERE path = ?", [$filePath])->first();
        if ($document) return true;

        return false;
    }

    /**
     * Get disk usage statistics
     */
    public function getDiskUsage()
    {
        $totalSize = 0;
        $fileCount = 0;
        $byDirectory = [];

        foreach ($this->config['directories'] as $key => $directory) {
            $dirPath = $this->uploadDir . '/' . $directory;
            $dirSize = 0;
            $dirFileCount = 0;

            if (!is_dir($dirPath)) continue;

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath)) as $file) {
                if ($file->isFile()) {
                    $dirSize += $file->getSize();
                    $dirFileCount++;
                    $fileCount++;
                }
            }

            $totalSize += $dirSize;
            $byDirectory[$directory] = [
                'size' => $dirSize,
                'size_formatted' => $this->formatBytes($dirSize),
                'file_count' => $dirFileCount
            ];
        }

        $diskQuota = $this->config['storage']['disk_quota'];
        $quotaUsed = ($totalSize / $diskQuota) * 100;

        return [
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'file_count' => $fileCount,
            'quota' => $diskQuota,
            'quota_formatted' => $this->formatBytes($diskQuota),
            'quota_used_percent' => round($quotaUsed, 2),
            'by_directory' => $byDirectory
        ];
    }

    /**
     * Format bytes to readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Verify upload directory structure
     */
    public function verifyStructure()
    {
        $results = [];

        foreach ($this->config['directories'] as $key => $directory) {
            $dirPath = $this->uploadDir . '/' . $directory;

            if (!is_dir($dirPath)) {
                if (mkdir($dirPath, 0755, true)) {
                    $results[$directory] = 'created';
                } else {
                    $results[$directory] = 'error_creating';
                }
            } else {
                $results[$directory] = 'exists';
            }

            // Check permissions
            if (is_dir($dirPath)) {
                if (!is_writable($dirPath)) {
                    chmod($dirPath, 0755);
                }
            }
        }

        return $results;
    }

    /**
     * Generate cleanup report
     */
    public function generateReport()
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'disk_usage' => $this->getDiskUsage(),
            'temp_dir_cleanup' => $this->cleanTempDir(),
            'directory_structure' => $this->verifyStructure()
        ];
    }
}
