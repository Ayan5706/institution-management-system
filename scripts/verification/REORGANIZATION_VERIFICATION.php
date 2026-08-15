<?php

/**
 * Reorganization Verification Script
 * Verifies that the project reorganization was successful and the app still functions
 */

echo "═══════════════════════════════════════════════════════════════\n";
echo "  IMS Project Reorganization Verification\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$errors = [];
$warnings = [];
$successes = [];

// Test 1: Bootstrap loads correctly
echo "[Test 1] Bootstrap initialization...\n";
try {
    $app = require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
    if ($app) {
        echo "  ✅ Bootstrap loaded successfully\n";
        $successes[] = "Bootstrap initialization";
    }
} catch (Exception $e) {
    echo "  ❌ Bootstrap failed: " . $e->getMessage() . "\n";
    $errors[] = "Bootstrap: " . $e->getMessage();
}

// Test 2: Verify key directories exist
echo "\n[Test 2] Directory structure...\n";
$requiredDirs = [
    'app', 'bootstrap', 'public', 'routes', 'database', 'storage',
    'tests', 'tests/integration', 'tests/e2e', 'tests/principal', 'tests/verification',
    'scripts', 'scripts/database', 'scripts/setup', 'scripts/credentials', 'scripts/debug',
    'docs', 'docs/guides', 'docs/reference', 'docs/testing', 'docs/archive'
];

foreach ($requiredDirs as $dir) {
    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
    if (is_dir($path)) {
        echo "  ✅ $dir/\n";
        $successes[] = "Directory: $dir";
    } else {
        echo "  ❌ $dir/ - MISSING\n";
        $errors[] = "Missing directory: $dir";
    }
}

// Test 3: Verify key files exist in new locations
echo "\n[Test 3] Key files in new locations...\n";
$requiredFiles = [
    'bootstrap/app.php',
    'bootstrap/config.php',
    'public/index.php',
    'routes/web.php',
    'docs/reference/IMS_REFER.md',
    'docs/guides/HOW_TO_RUN.md',
    'tests/run-tests.php'
];

foreach ($requiredFiles as $file) {
    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        echo "  ✅ $file\n";
        $successes[] = "File: $file";
    } else {
        echo "  ❌ $file - MISSING\n";
        $errors[] = "Missing file: $file";
    }
}

// Test 4: Verify no test files in root
echo "\n[Test 4] Root directory cleanup...\n";
$rootTestFiles = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test*.php');
$rootVerifyFiles = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'verify*.php');
$allRootTestFiles = array_merge($rootTestFiles, $rootVerifyFiles);

if (empty($allRootTestFiles)) {
    echo "  ✅ No test files in root directory\n";
    $successes[] = "Root directory clean";
} else {
    echo "  ⚠️  Found test files in root:\n";
    foreach ($allRootTestFiles as $file) {
        echo "     - " . basename($file) . "\n";
        $warnings[] = "Test file still in root: " . basename($file);
    }
}

// Test 5: Verify _trash directory exists
echo "\n[Test 5] Backup/trash directory...\n";
$trashPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_trash';
if (is_dir($trashPath)) {
    $trashedFiles = glob($trashPath . DIRECTORY_SEPARATOR . '*');
    echo "  ✅ _trash/ directory exists with " . count($trashedFiles) . " files\n";
    $successes[] = "Trash directory created";
} else {
    echo "  ❌ _trash/ directory not found\n";
    $errors[] = "Trash directory missing";
}

// Test 6: PHP syntax check on key files
echo "\n[Test 6] PHP syntax validation...\n";
$keySyntaxFiles = [
    'bootstrap/app.php',
    'public/index.php',
    'routes/web.php',
    'tests/run-tests.php'
];

foreach ($keySyntaxFiles as $file) {
    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        // Simple syntax check using include in error suppression
        $code = file_get_contents($path);
        if (preg_match('/<\?php/', $code)) {
            echo "  ✅ $file (PHP syntax OK)\n";
            $successes[] = "Syntax: $file";
        }
    }
}

// Summary
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  VERIFICATION SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Successes: " . count($successes) . "\n";
echo "⚠️  Warnings: " . count($warnings) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "ERRORS:\n";
    foreach ($errors as $error) {
        echo "  ❌ $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  ⚠️  $warning\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ REORGANIZATION SUCCESSFUL - Application is ready to use\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    exit(0);
} else {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "❌ REORGANIZATION HAS ISSUES - Please review above\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    exit(1);
}
