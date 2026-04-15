<?php

declare(strict_types=1);

use App\Config\Config;
use App\Core\Autoloader;
use App\Core\Database;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
Autoloader::register(dirname(__DIR__));

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'config.php';

$migrationsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
$files = glob($migrationsPath . DIRECTORY_SEPARATOR . '*.sql') ?: [];
sort($files);

if ($files === []) {
    echo "No migration files found." . PHP_EOL;
    exit(0);
}

$pdo = Database::connection();

$bootstrapMigration = '2026_04_11_000000_create_migrations_table.sql';
$bootstrapFile = $migrationsPath . DIRECTORY_SEPARATOR . $bootstrapMigration;
if (is_file($bootstrapFile)) {
    $sql = file_get_contents($bootstrapFile);
    if ($sql !== false) {
        $pdo->exec($sql);
    }
}

$pdo->exec('CREATE TABLE IF NOT EXISTS `migrations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `migration` VARCHAR(255) NOT NULL, `batch` INT NOT NULL DEFAULT 1, `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uniq_migration_name` (`migration`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(\PDO::FETCH_COLUMN) ?: [];
$appliedLookup = array_fill_keys(array_map('strval', $applied), true);

$currentBatch = (int) ($pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn() ?: 0);
$nextBatch = $currentBatch + 1;
$appliedCount = 0;

foreach ($files as $file) {
    $name = basename($file);

    if (isset($appliedLookup[$name])) {
        echo "SKIP  - {$name}" . PHP_EOL;
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "ERROR - Cannot read {$name}" . PHP_EOL;
        exit(1);
    }

    try {
        $pdo->exec($sql);

        $stmt = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
        $stmt->execute([
            'migration' => $name,
            'batch' => $nextBatch,
        ]);

        $appliedCount++;
        echo "OK    - {$name}" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "FAIL  - {$name}: {$e->getMessage()}" . PHP_EOL;
        exit(1);
    }
}

echo PHP_EOL . "Migration run complete. Applied: {$appliedCount}" . PHP_EOL;

echo 'Database: ' . (string) Config::get('database.default.database', 'unknown') . PHP_EOL;
