<?php

define('BASE_PATH', 'C:\xampp\htdocs\IMS_FINAL');

// Register autoloader
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoloader.php';
\App\Core\Autoloader::register(BASE_PATH);

use App\Core\Database;

$pdo = Database::connection();

$tables = ['users', 'programs', 'semesters', 'subjects', 'teacher_assignments', 'student_profiles'];

foreach ($tables as $table) {
    echo "\n=== Table: {$table} ===\n";
    $stmt = $pdo->query("DESCRIBE {$table}");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "  {$col['Field']} ({$col['Type']}) - {$col['Null']}\n";
    }
}
