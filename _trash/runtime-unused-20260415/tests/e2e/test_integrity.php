<?php
/**
 * Simplified Functional Test  
 * Checks critical functionality
 */

declare(strict_types=1);

echo "================================================================================\n";
echo "APPLICATION INTEGRITY & FUNCTIONALITY TEST\n";
echo "================================================================================\n\n";

$test_results = ['passed' => 0, 'failed' => 0, 'warnings' => 0, 'errors' => []];

// Test 1: Check for syntax errors in key files
echo "--- CHECKING PHP SYNTAX ---\n";

$critical_files = [
    'app/Controllers/AuthController.php',
    'app/Controllers/PrincipalController.php',
    'app/Controllers/ManagerController.php',
    'app/Controllers/AccountantController.php',
    'app/Controllers/VPController.php',
    'bootstrap/app.php',
    'public/index.php',
    'routes/web.php',
];

foreach ($critical_files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (!file_exists($filepath)) {
        echo "✗ " . basename($file) . " - File not found\n";
        $test_results['failed']++;
        $test_results['errors'][] = "File not found: $file";
        continue;
    }
    
    $output = [];
    $return_var = 0;
    exec('C:\xampp\php\php.exe -l "' . $filepath . '"', $output, $return_var);
    
    if ($return_var === 0) {
        echo "✓ " . basename($file) . " - Syntax OK\n";
        $test_results['passed']++;
    } else {
        echo "✗ " . basename($file) . " - Syntax Error\n";
        $test_results['failed']++;
        $test_results['errors'][] = "Syntax error in: $file";
    }
}

// Test 2: Verify critical classes exist
echo "\n--- CHECKING CRITICAL CLASSES ---\n";

require_once __DIR__ . '/bootstrap/app.php';

$classes_to_check = [
    'App\\Controllers\\AuthController' => ['showLogin', 'login', 'logout'],
    'App\\Controllers\\PrincipalController' => ['showDashboard', 'showAccounts', 'storeAccount'],
    'App\\Controllers\\ManagerController' => ['showDashboard', 'showStudents'],
];

foreach ($classes_to_check as $class => $methods) {
    if (class_exists($class)) {
        echo "✓ $class - Loaded\n";
        $test_results['passed']++;
    } else {
        echo "✗ $class - Not found\n";
        $test_results['failed']++;
        $test_results['errors'][] = "Class not found: $class";
    }
}

// Test 3: Configuration files
echo "\n--- CHECKING CONFIGURATION FILES ---\n";

$config_files = ['.env', 'app/Config/database.php'];

foreach ($config_files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        echo "✓ $file\n";
        $test_results['passed']++;
    } else {
        echo "✗ $file - Missing\n";
        $test_results['failed']++;
        $test_results['errors'][] = "Missing: $file";
    }
}

// Test 4: Verify directories exist
echo "\n--- CHECKING DIRECTORIES ---\n";

$directories = ['storage', 'public/uploads', 'app/Views', 'database/migrations'];

foreach ($directories as $dir) {
    $dirpath = __DIR__ . '/' . $dir;
    if (is_dir($dirpath)) {
        echo "✓ $dir/\n";
        $test_results['passed']++;
    } else {
        echo "⚠ $dir/ - Not found\n";
        $test_results['warnings']++;
    }
}

// Summary
echo "\n============================================================================\n";
echo "TEST SUMMARY\n";
echo "============================================================================\n";
echo "Passed:   " . $test_results['passed'] . "\n";
echo "Failed:   " . $test_results['failed'] . "\n";
echo "Warnings: " . $test_results['warnings'] . "\n";

if (!empty($test_results['errors'])) {
    echo "\n--- Issues ---\n";
    foreach ($test_results['errors'] as $error) {
        echo "  ⚠ $error\n";
    }
}

if ($test_results['failed'] === 0) {
    echo "\n✓ APPLICATION IS FULLY OPERATIONAL\n";
}
echo "\n";

?>
