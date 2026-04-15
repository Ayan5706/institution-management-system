<?php

/**
 * IMS Installation Script
 *
 * First-time setup wizard for the Institution Management System.
 * Creates directories, sets permissions, and prompts for configuration.
 *
 * Usage: php scripts/install.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

class InstallationWizard
{
    private array $config = [];
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function run(): void
    {
        $this->displayHeader();
        $this->checkRequirements();
        $this->createDirectories();
        $this->setPermissions();
        $this->promptDatabaseConfig();
        $this->testDatabaseConnection();
        $this->runMigrations();
        $this->displayCompletion();
    }

    private function displayHeader(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║     Institution Management System - Installation          ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function checkRequirements(): void
    {
        echo "Checking requirements...\n";
        echo str_repeat("─", 60) . "\n";

        $requirements = [
            'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'GD Library' => extension_loaded('gd'),
            'JSON' => extension_loaded('json'),
        ];

        $allMet = true;
        foreach ($requirements as $name => $met) {
            $status = $met ? '✓' : '✗';
            echo "[{$status}] {$name}\n";
            if (!$met) {
                $allMet = false;
            }
        }

        if (!$allMet) {
            echo "\n❌ Some requirements are not met. Please install missing extensions.\n";
            exit(1);
        }

        echo "\n✓ All requirements met!\n\n";
    }

    private function createDirectories(): void
    {
        echo "Creating necessary directories...\n";
        echo str_repeat("─", 60) . "\n";

        $directories = [
            'storage',
            'storage/logs',
            'storage/cache',
            'public/uploads',
            'public/uploads/avatars',
            'public/uploads/documents',
            'public/uploads/products',
            'public/uploads/products/images',
            'public/uploads/products/documents',
            'public/uploads/products/thumbnails',
            'public/uploads/temp',
        ];

        foreach ($directories as $dir) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                echo "✓ Created: {$dir}\n";
            } else {
                echo "  Exists: {$dir}\n";
            }
        }

        echo "\n";
    }

    private function setPermissions(): void
    {
        echo "Setting file permissions...\n";
        echo str_repeat("─", 60) . "\n";

        $dirs = [
            'storage' => 0755,
            'storage/logs' => 0755,
            'public/uploads' => 0755,
        ];

        foreach ($dirs as $dir => $mode) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($path)) {
                chmod($path, $mode);
                echo "✓ Permissions: {$dir} ({$mode})\n";
            }
        }

        echo "\n";
    }

    private function promptDatabaseConfig(): void
    {
        echo "Database Configuration\n";
        echo str_repeat("─", 60) . "\n";

        $defaults = [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'ims_final',
            'username' => 'root',
            'password' => '',
        ];

        foreach ($defaults as $key => $default) {
            echo "Enter database {$key}";
            if ($default) {
                echo " [{$default}]";
            }
            echo ": ";

            $input = trim(fgets(STDIN));
            $this->config[$key] = $input !== '' ? $input : $default;
        }

        echo "\n";
    }

    private function testDatabaseConnection(): void
    {
        echo "Testing database connection...\n";
        echo str_repeat("─", 60) . "\n";

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=utf8mb4',
                $this->config['host'],
                $this->config['port']
            );

            $pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $this->createDatabase($pdo);

            echo "✓ Database connection successful!\n";
            echo "✓ Database '{$this->config['database']}' ready!\n";
            echo "\n";
        } catch (\PDOException $e) {
            echo "✗ Connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function createDatabase(\PDO $pdo): void
    {
        $db = $this->config['database'];
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    private function runMigrations(): void
    {
        echo "Running database migrations...\n";
        echo str_repeat("─", 60) . "\n";

        $migrationScript = $this->basePath . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'migrate.php';
        if (is_file($migrationScript)) {
            echo "Please run: php scripts/migrate.php\n";
        }

        echo "\n";
    }

    private function displayCompletion(): void
    {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║              Installation Complete! ✓                    ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\nNext steps:\n";
        echo "  1. php scripts/migrate.php    (Run migrations)\n";
        echo "  2. php scripts/seed.php       (Seed initial data)\n";
        echo "  3. Start your web server\n";
        echo "  4. Navigate to http://localhost\n";
        echo "\n";
    }
}

$wizard = new InstallationWizard(BASE_PATH);
$wizard->run();
