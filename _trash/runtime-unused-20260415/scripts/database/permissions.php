<?php

/**
 * File Permissions Manager
 *
 * Fix and manage file/directory permissions.
 *
 * Usage: php scripts/permissions.php [--fix|--check]
 */

define('BASE_PATH', dirname(__DIR__));

class PermissionsManager
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function fixPermissions(): void
    {
        echo "\nFixing file permissions...\n";
        echo str_repeat("─", 60) . "\n";

        $dirs = [
            'storage' => 0755,
            'storage/logs' => 0755,
            'storage/cache' => 0755,
            'storage/backups' => 0755,
            'public/uploads' => 0755,
            'public/uploads/avatars' => 0755,
            'public/uploads/documents' => 0755,
            'public/uploads/products' => 0755,
            'public/uploads/products/images' => 0755,
            'public/uploads/products/documents' => 0755,
            'public/uploads/products/thumbnails' => 0755,
            'public/uploads/temp' => 0755,
            'bootstrap' => 0755,
        ];

        foreach ($dirs as $dir => $mode) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($path)) {
                chmod($path, $mode);
                echo "✓ {$dir} -> " . decoct($mode) . "\n";
            }
        }

        echo "\n✓ Permissions fixed!\n\n";
    }

    public function checkPermissions(): void
    {
        echo "\nChecking file permissions...\n";
        echo str_repeat("─", 60) . "\n";

        $dirs = [
            'storage',
            'storage/logs',
            'public/uploads',
            'public/uploads/avatars',
        ];

        $allOk = true;

        foreach ($dirs as $dir) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                echo "✗ Missing: {$dir}\n";
                $allOk = false;
                continue;
            }

            $writable = is_writable($path);
            $readable = is_readable($path);
            $perms = substr(sprintf('%o', fileperms($path)), -4);

            $status = ($writable && $readable) ? '✓' : '✗';
            echo "[{$status}] {$dir} ({$perms})\n";

            if (!$writable || !$readable) {
                $allOk = false;
            }
        }

        echo "\n";
        if ($allOk) {
            echo "✓ All permissions OK!\n\n";
        } else {
            echo "✗ Some directories have permission issues.\n";
            echo "Run: php scripts/permissions.php --fix\n\n";
        }
    }

    public function showHelp(): void
    {
        echo "\nPermissions Manager\n";
        echo str_repeat("─", 60) . "\n";
        echo "Usage: php scripts/permissions.php <command>\n\n";
        echo "Commands:\n";
        echo "  --fix       Fix all directory permissions\n";
        echo "  --check     Check directory permissions\n";
        echo "  --help      Show this message\n\n";
    }
}

$manager = new PermissionsManager(BASE_PATH);

$arg = $argv[1] ?? '--check';

match ($arg) {
    '--fix' => $manager->fixPermissions(),
    '--check' => $manager->checkPermissions(),
    '--help', '-h' => $manager->showHelp(),
    default => $manager->checkPermissions(),
};
