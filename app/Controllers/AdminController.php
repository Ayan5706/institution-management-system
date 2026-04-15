<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SystemConfigModel;

class AdminController extends BaseController
{
    private SystemConfigModel $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = new SystemConfigModel();
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): void
    {
        $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
        ]);
    }

    /**
     * Show system configuration page
     */
    public function showConfig(): void
    {
        $configs = $this->config->all();

        $this->view('admin.config', [
            'title' => 'System Configuration',
            'configs' => $configs ?? [],
        ]);
    }

    /**
     * Update system configuration
     */
    public function updateConfig(): void
    {
        $configKey = (string) $this->input('config_key', '');
        $configValue = (string) $this->input('config_value', '');

        if ($configKey === '' || $configValue === '') {
            $this->json([
                'success' => false,
                'message' => 'Configuration key and value are required.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'System configuration updated successfully.',
        ], 200);
    }

    /**
     * System health check
     */
    public function systemHealth(): void
    {
        $health = [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->checkStorageSpace(),
            'uploads' => $this->checkUploadDirectory(),
            'permissions' => $this->checkPermissions(),
        ];

        $this->view('admin.system_health', [
            'title' => 'System Health',
            'health' => $health,
        ]);
    }

    /**
     * Cleanup uploaded files
     */
    public function cleanupFiles(): void
    {
        try {
            $tempDir = __DIR__ . '/../../public/uploads/temp';
            $files = array_diff(scandir($tempDir), ['.', '..']);
            $cleaned = 0;

            foreach ($files as $file) {
                $filePath = $tempDir . '/' . $file;
                if (is_file($filePath) && time() - filemtime($filePath) > 86400) { // 24 hours
                    unlink($filePath);
                    $cleaned++;
                }
            }

            $this->json([
                'success' => true,
                'message' => 'Cleanup completed.',
                'data' => ['files_cleaned' => $cleaned],
            ], 200);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): array
    {
        try {
            $conn = $this->db->getConnection();
            return [
                'status' => 'ok',
                'message' => 'Database connection is healthy.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage space
     */
    private function checkStorageSpace(): array
    {
        $uploadsDir = __DIR__ . '/../../public/uploads';
        $freeSpace = disk_free_space($uploadsDir);
        $totalSpace = disk_total_space($uploadsDir);

        return [
            'status' => $freeSpace > (100 * 1024 * 1024) ? 'ok' : 'warning', // 100MB threshold
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace),
            'usage_percentage' => round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Check upload directory
     */
    private function checkUploadDirectory(): array
    {
        $uploadsDir = __DIR__ . '/../../public/uploads';
        $writable = is_writable($uploadsDir);

        return [
            'status' => $writable ? 'ok' : 'error',
            'message' => $writable ? 'Upload directory is writable.' : 'Upload directory is not writable.',
            'path' => $uploadsDir,
        ];
    }

    /**
     * Check file permissions
     */
    private function checkPermissions(): array
    {
        $dirs = [
            __DIR__ . '/../../public/uploads/avatars',
            __DIR__ . '/../../public/uploads/documents',
            __DIR__ . '/../../public/uploads/products/images',
        ];

        $allOk = true;
        foreach ($dirs as $dir) {
            if (!is_writable($dir)) {
                $allOk = false;
                break;
            }
        }

        return [
            'status' => $allOk ? 'ok' : 'error',
            'message' => $allOk ? 'All upload directories have proper permissions.' : 'Some directories have permission issues.',
        ];
    }

    /**
     * Format bytes to human-readable format
     */
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
