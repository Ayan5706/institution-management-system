#!/usr/bin/env php
<?php

/**
 * Upload Maintenance Script
 * CLI tool for managing uploads
 */

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die('This script must be run from command line');
}

// Setup autoloader
require_once __DIR__ . '/../bootstrap/app.php';

use App\Core\UploadCleanup;

$cleanup = new UploadCleanup();

// Parse arguments
$command = $argv[1] ?? 'help';
$options = array_slice($argv, 2);

switch ($command) {
    case 'clean-temp':
        echo "Cleaning temporary files...\n";
        $result = $cleanup->cleanTempDir();
        echo sprintf(
            "Removed %d files, freed %s\n",
            $result['removed'],
            $result['space_formatted']
        );
        break;

    case 'verify':
        echo "Verifying upload directory structure...\n";
        $results = $cleanup->verifyStructure();
        foreach ($results as $dir => $status) {
            echo sprintf("  %s: %s\n", $dir, $status);
        }
        break;

    case 'disk-usage':
        echo "Disk usage report:\n";
        $usage = $cleanup->getDiskUsage();
        echo sprintf("Total Size: %s\n", $usage['total_size_formatted']);
        echo sprintf("File Count: %d\n", $usage['file_count']);
        echo sprintf("Quota: %s\n", $usage['quota_formatted']);
        echo sprintf("Used: %d%%\n\n", $usage['quota_used_percent']);

        echo "By Directory:\n";
        foreach ($usage['by_directory'] as $dir => $stats) {
            echo sprintf("  %s: %s (%d files)\n", $dir, $stats['size_formatted'], $stats['file_count']);
        }
        break;

    case 'report':
        echo "Generating cleanup report...\n";
        $report = $cleanup->generateReport();
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        break;

    case 'help':
    default:
        echo <<<'HELP'
Upload Maintenance Tool

Usage:
  php scripts/upload-maintenance.php <command> [options]

Commands:
  clean-temp        Clean temporary upload files
  verify            Verify upload directory structure
  disk-usage        Show disk usage statistics
  report            Generate full cleanup report
  help              Show this help message

Examples:
  php scripts/upload-maintenance.php clean-temp
  php scripts/upload-maintenance.php disk-usage
  php scripts/upload-maintenance.php report

HELP;
        break;
}

echo "\nDone!\n";
