<?php
/**
 * Advanced Testing - Code Quality & Functionality
 * Checks for PHP errors, warnings, and missing files
 */

declare(strict_types=1);

echo "================================================================================\n";
echo "CODE QUALITY & FUNCTIONALITY AUDIT\n";
echo "================================================================================\n\n";

// Test 1: Check all controllers can be included
echo "--- CHECKING CONTROLLERS ---\n";
$controller_dir = __DIR__ . '/app/Controllers';
$controllers = array_diff(scandir($controller_dir), ['.', '..']);
$controller_errors = 0;

foreach ($controllers as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
    
    $controller_path = $controller_dir . '/' . $file;
    $class = 'App\\Controllers\\' . pathinfo($file, PATHINFO_FILENAME);
    
    if (!class_exists($class)) {
        echo "✗ " . $file . " - Class not found: $class\n";
        $controller_errors++;
    } else {
        $reflection = new ReflectionClass($class);
        $methods = array_filter($reflection->getMethods(), function($m) { 
            return $m->isPublic() && !$m->isStatic(); 
        });
        echo "✓ " . $file . " - " . count($methods) . " public methods\n";
    }
}

// Test 2: Check all models can be included
echo "\n--- CHECKING MODELS ---\n";
$model_dir = __DIR__ . '/app/Models';
$models = array_diff(scandir($model_dir), ['.', '..']);
$model_errors = 0;

foreach ($models as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
    
    $model_path = $model_dir . '/' . $file;
    $class = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
    
    if (!class_exists($class)) {
        echo "✗ " . $file . " - Class not found: $class\n";
        $model_errors++;
    } else {
        echo "✓ " . $file . "\n";
    }
}

// Test 3: Check all views exist
echo "\n--- CHECKING VIEWS ---\n";
$view_dir = __DIR__ . '/app/Views';

function count_views($dir) {
    $count = 0;
    if (!is_dir($dir)) return $count;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        if (is_dir($dir . '/' . $file)) {
            $count += count_views($dir . '/' . $file);
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $count++;
        }
    }
    return $count;
}

$total_views = count_views($view_dir);
echo "✓ Total view files: $total_views\n";

// Test 4: Check middleware
echo "\n--- CHECKING MIDDLEWARE ---\n";
$middleware_dir = __DIR__ . '/app/Middleware';
$middleware_files = array_diff(scandir($middleware_dir), ['.', '..']);
$middleware_errors = 0;

foreach ($middleware_files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
    
    $class = 'App\\Middleware\\' . pathinfo($file, PATHINFO_FILENAME);
    
    if (!class_exists($class)) {
        echo "✗ " . $file . " - Class not found: $class\n";
        $middleware_errors++;
    } else {
        $reflection = new ReflectionClass($class);
        if ($reflection->implementsInterface('App\\Middleware\\MiddlewareInterface')) {
            echo "✓ " . $file . " - Implements MiddlewareInterface\n";
        } else {
            echo "⚠ " . $file . " - Does not implement MiddlewareInterface\n";
        }
    }
}

// Test 5: Check database configuration
echo "\n--- CHECKING DATABASE ---\n";
require_once __DIR__ . '/bootstrap/config.php';

try {
    $db_host = \App\Config\Config::get('database.host', '127.0.0.1');
    $db_name = \App\Config\Config::get('database.database', 'ims_final');
    $db_user = \App\Config\Config::get('database.username', 'root');
    
    echo "✓ Database host: $db_host\n";
    echo "✓ Database name: $db_name\n";
    echo "✓ Database user: $db_user\n";
    
    // Try connection
    try {
        $pdo = new PDO(
            'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8mb4',
            $db_user,
            \App\Config\Config::get('database.password', '')
        );
        echo "✓ Database connection successful\n";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_NUM);
        echo "✓ Database tables: " . count($tables) . "\n";
        
    } catch (PDOException $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error reading database config: " . $e->getMessage() . "\n";
}

// Test 6: Check for common issues
echo "\n--- CHECKING FOR COMMON ISSUES ---\n";

$possible_issues = [];

// Check .env file
if (!file_exists(__DIR__ . '/.env')) {
    $possible_issues[] = ".env file not found";
} else {
    echo "✓ .env file exists\n";
}

// Check storage directory
if (!is_dir(__DIR__ . '/storage')) {
    $possible_issues[] = "storage/ directory not found or not writable";
} else {
    echo "✓ storage/ directory exists\n";
}

// Check public/uploads
if (!is_dir(__DIR__ . '/public/uploads')) {
    $possible_issues[] = "public/uploads/ directory not found";
} elseif (!is_writable(__DIR__ . '/public/uploads')) {
    echo "⚠ public/uploads/ directory exists but may not be writable\n";
} else {
    echo "✓ public/uploads/ directory exists and is writable\n";
}

// Test 7: Summary
echo "\n============================================================================\n";
echo "AUDIT SUMMARY\n";
echo "============================================================================\n";

$total_errors = $controller_errors + $model_errors + $middleware_errors + count($possible_issues);

if ($total_errors === 0) {
    echo "✓ All checks passed - Application is ready\n";
} else {
    echo "⚠ Found " . $total_errors . " issue(s) that may need attention\n";
    
    if (!empty($possible_issues)) {
        echo "\nIssues:\n";
        foreach ($possible_issues as $issue) {
            echo "  - " . $issue . "\n";
        }
    }
}

echo "\n";

?>
