<?php

/**
 * Test Bootstrap File
 * Prepares the testing environment and loads necessary files
 * 
 * This file is loaded by PHPUnit before running any tests.
 * It initializes the application in test mode with proper error handling
 * and mocked dependencies.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load application autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load bootstrap configuration
require_once BASE_PATH . '/bootstrap/app.php';

// Override database configuration for testing
$GLOBALS['test_db'] = [
    'connection' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE_TEST'] ?? $_ENV['DB_DATABASE'] . '_test',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
];

// Test utilities
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/Helpers/TestHelper.php';
require_once __DIR__ . '/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/Helpers/MockHelper.php';

// Clean up after tests
register_shutdown_function(function () {
    // Cleanup temporary files
    $tempDir = BASE_PATH . '/storage/temp/test';
    if (is_dir($tempDir)) {
        array_map('unlink', array_filter((array) glob("$tempDir/*")));
        @rmdir($tempDir);
    }
});

echo "Test environment initialized.\n";
