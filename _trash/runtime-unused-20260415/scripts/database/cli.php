<?php

/**
 * IMS CLI - Command Line Interface
 *
 * Main entry point for all CLI commands.
 *
 * Usage: php scripts/cli.php <command> [args]
 *        php scripts/cli.php list  (See all available commands)
 */

define('BASE_PATH', dirname(__DIR__));

class IMSCLI
{
    private string $basePath;
    private array $commands = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->registerCommands();
    }

    private function registerCommands(): void
    {
        $this->commands = [
            'install' => [
                'description' => 'First-time installation wizard',
                'file' => 'install.php',
            ],
            'migrate' => [
                'description' => 'Run database migrations',
                'file' => 'migrate.php',
            ],
            'seed' => [
                'description' => 'Seed database with initial data',
                'file' => 'seed.php',
            ],
            'user' => [
                'description' => 'Manage users (create, list, reset-password)',
                'file' => 'user-manage.php',
            ],
            'backup' => [
                'description' => 'Backup database and files',
                'file' => 'backup.php',
            ],
            'restore' => [
                'description' => 'Restore from backup (not yet implemented)',
                'file' => 'restore.php',
            ],
            'permissions' => [
                'description' => 'Fix file/directory permissions',
                'file' => 'permissions.php',
            ],
            'cache' => [
                'description' => 'Clear cache and rotate logs',
                'file' => 'cache-cleanup.php',
            ],
            'reset' => [
                'description' => 'Reset database (DEVELOPMENT ONLY)',
                'file' => 'reset-database.php',
            ],
            'health' => [
                'description' => 'Run system health check',
                'file' => 'health-check.php',
            ],
            'export' => [
                'description' => 'Export table data to CSV',
                'file' => 'data-export.php',
            ],
            'optimize' => [
                'description' => 'Optimize database tables',
                'file' => 'optimize-database.php',
            ],
            'uploads' => [
                'description' => 'Manage uploads (cleanup, verify)',
                'file' => 'upload-maintenance.php',
            ],
        ];
    }

    public function run(array $argv): void
    {
        $command = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if ($command === 'list' || $command === 'help' || $command === '--help') {
            $this->displayHelp();
            return;
        }

        if (!isset($this->commands[$command])) {
            echo "Unknown command: {$command}\n";
            echo "Run: php scripts/cli.php list\n";
            return;
        }

        $this->executeCommand($command, $args);
    }

    private function executeCommand(string $command, array $args): void
    {
        $file = $this->commands[$command]['file'];
        $filepath = $this->basePath . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . $file;

        if (!is_file($filepath)) {
            echo "✗ Script not found: {$file}\n";
            return;
        }

        // Reconstruct argv for the called script
        $_SERVER['argv'] = ['scripts/' . $file, ...$args];
        $_SERVER['argc'] = count($_SERVER['argv']);

        require $filepath;
    }

    private function displayHelp(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║  Institution Management System - CLI                    ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "Usage: php scripts/cli.php <command> [options]\n\n";

        echo "Available Commands:\n";
        echo str_repeat("─", 60) . "\n";

        foreach ($this->commands as $name => $info) {
            printf("  %-15s %s\n", $name, $info['description']);
        }

        echo "\n";
        echo "Examples:\n";
        echo "  php scripts/cli.php install              # First-time setup\n";
        echo "  php scripts/cli.php migrate              # Run migrations\n";
        echo "  php scripts/cli.php seed --force         # Seed data\n";
        echo "  php scripts/cli.php user create          # Create user\n";
        echo "  php scripts/cli.php backup               # Backup everything\n";
        echo "  php scripts/cli.php health               # Health check\n";
        echo "\n";
    }
}

$cli = new IMSCLI(BASE_PATH);
$cli->run($argv);
