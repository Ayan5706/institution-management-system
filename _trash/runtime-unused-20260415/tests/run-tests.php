<?php

/**
 * IMS Test Runner
 * 
 * Command-line utility for running tests with various options
 * 
 * Usage:
 *   php tests/run-tests.php [options]
 * 
 * Options:
 *   --unit                 Run only unit tests
 *   --integration          Run only integration tests
 *   --filter=CLASS         Run tests matching class name
 *   --coverage             Generate coverage report
 *   --help                 Show this help message
 * 
 * Examples:
 *   php tests/run-tests.php
 *   php tests/run-tests.php --unit
 *   php tests/run-tests.php --filter=CacheTest
 *   php tests/run-tests.php --coverage
 */

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';
require_once $basePath . '/bootstrap/app.php';

use PHPUnit\TextUI\Command;

$options = getopt('', ['unit', 'integration', 'filter:', 'coverage', 'help']);

if (isset($options['help'])) {
    echo file_get_contents(__FILE__);
    exit(0);
}

$phpunitArgs = [];

// Add configuration file
$phpunitArgs[] = '--configuration=' . $basePath . '/phpunit.xml';

// Handle test suite selection
if (isset($options['unit'])) {
    $phpunitArgs[] = $basePath . '/tests/unit';
} elseif (isset($options['integration'])) {
    $phpunitArgs[] = $basePath . '/tests/integration';
} else {
    // Run all tests
    $phpunitArgs[] = $basePath . '/tests';
}

// Handle filter
if (isset($options['filter'])) {
    $phpunitArgs[] = '--filter=' . $options['filter'];
}

// Handle coverage
if (isset($options['coverage'])) {
    $phpunitArgs[] = '--coverage-html=' . $basePath . '/storage/reports/coverage';
    $phpunitArgs[] = '--coverage-text';
}

// Display information
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Institution Management System - Test Runner\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Running tests...\n";
echo "Configuration: phpunit.xml\n";
echo "Test directory: " . (isset($options['unit']) ? 'tests/unit' : (isset($options['integration']) ? 'tests/integration' : 'tests')) . "\n";

if (isset($options['coverage'])) {
    echo "Coverage report will be generated to: storage/reports/coverage\n";
}

echo "\n─────────────────────────────────────────────────────────────────\n\n";

// Run PHPUnit
try {
    $command = new Command();
    $command->run($phpunitArgs);
} catch (Exception $e) {
    echo "\nError running tests: " . $e->getMessage() . "\n";
    exit(1);
}
