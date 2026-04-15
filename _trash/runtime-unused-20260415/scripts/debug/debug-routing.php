<?php

/**
 * Routing Debug Script
 * Helps diagnose routing and .htaccess issues
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "ROUTING DEBUG & DIAGNOSTICS\n";
echo str_repeat("=", 80) . "\n\n";

// Check 1: .htaccess exists
echo "1. .htaccess File Check:\n";
echo str_repeat("-", 80) . "\n";
if (file_exists(BASE_PATH . '/public/.htaccess')) {
    echo "✓ .htaccess file found in public/ directory\n";
    $content = file_get_contents(BASE_PATH . '/public/.htaccess');
    echo "  Content length: " . strlen($content) . " bytes\n";
    if (strpos($content, 'mod_rewrite') !== false) {
        echo "  ✓ Contains mod_rewrite rules\n";
    }
} else {
    echo "✗ .htaccess file NOT found in public/ directory\n";
    echo "  This file is required for URL rewriting\n";
}

// Check 2: Apache mod_rewrite enabled
echo "\n2. Apache mod_rewrite Check:\n";
echo str_repeat("-", 80) . "\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✓ Apache mod_rewrite is enabled\n";
    } else {
        echo "✗ Apache mod_rewrite is NOT enabled\n";
        echo "  You may need to enable it in php.ini or Apache config\n";
    }
} else {
    echo "⚠ Cannot check Apache modules (function not available)\n";
    echo "  Make sure mod_rewrite is enabled in Apache\n";
}

// Check 3: Request path simulation
echo "\n3. Request Path Extraction Test:\n";
echo str_repeat("-", 80) . "\n";

// Simulate different URLs
$testCases = [
    ['REQUEST_URI' => '/', 'SCRIPT_NAME' => '/index.php', 'Expected' => '/'],
    ['REQUEST_URI' => '/login', 'SCRIPT_NAME' => '/index.php', 'Expected' => '/login'],
    ['REQUEST_URI' => '/IMS_FINAL/public/', 'SCRIPT_NAME' => '/IMS_FINAL/public/index.php', 'Expected' => '/'],
    ['REQUEST_URI' => '/IMS_FINAL/public/login', 'SCRIPT_NAME' => '/IMS_FINAL/public/index.php', 'Expected' => '/login'],
    ['REQUEST_URI' => '/IMS_FINAL/public/users/123', 'SCRIPT_NAME' => '/IMS_FINAL/public/index.php', 'Expected' => '/users/123'],
];

require_once BASE_PATH . '/app/Core/Request.php';

foreach ($testCases as $test) {
    $mockServer = [
        'REQUEST_URI' => $test['REQUEST_URI'],
        'SCRIPT_NAME' => $test['SCRIPT_NAME'],
        'REQUEST_METHOD' => 'GET',
    ];
    
    $request = new \App\Core\Request([], [], $mockServer, [], []);
    $extractedPath = $request->path();
    $matches = $extractedPath === $test['Expected'];
    
    $status = $matches ? '✓' : '✗';
    echo "$status REQUEST_URI: {$test['REQUEST_URI']}\n";
    echo "   Extracted: $extractedPath (Expected: {$test['Expected']})\n";
}

// Check 4: Public directory structure
echo "\n4. Public Directory Structure:\n";
echo str_repeat("-", 80) . "\n";
$requiredFiles = [
    'index.php',
    '.htaccess',
    'assets',
    'uploads',
];

foreach ($requiredFiles as $file) {
    $path = BASE_PATH . '/public/' . $file;
    if (file_exists($path)) {
        $type = is_dir($path) ? 'directory' : 'file';
        echo "✓ $file ($type) found\n";
    } else {
        echo "✗ $file NOT found\n";
    }
}

// Check 5: Route definition
echo "\n5. Bootstrap & Routing Check:\n";
echo str_repeat("-", 80) . "\n";
try {
    $app = require BASE_PATH . '/bootstrap/app.php';
    echo "✓ Application bootstraps successfully\n";
    
    $reflection = new ReflectionClass($app);
    echo "✓ Application class: " . $reflection->getName() . "\n";
    
    // Check if router has routes
    $router = $app->router();
    echo "✓ Router initialized\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Check 6: Session file exists
echo "\n6. Routes File Check:\n";
echo str_repeat("-", 80) . "\n";
if (file_exists(BASE_PATH . '/routes/web.php')) {
    echo "✓ routes/web.php found\n";
    $content = file_get_contents(BASE_PATH . '/routes/web.php');
    
    // Count routes
    $routeCount = substr_count($content, '$router->');
    echo "  Routes defined: ~$routeCount\n";
    
    // Check for root route
    if (preg_match('/\$router->get\s*\(\s*[\'"]\/[\'"]/i', $content)) {
        echo "  ✓ Root route (/) is defined\n";
    } else {
        echo "  ⚠ Root route (/) might not be explicitly defined\n";
    }
} else {
    echo "✗ routes/web.php NOT found\n";
}

// Final recommendation
echo "\n";
echo str_repeat("=", 80) . "\n";
echo "RECOMMENDATIONS:\n";
echo str_repeat("=", 80) . "\n";

if (!file_exists(BASE_PATH . '/public/.htaccess')) {
    echo "1. CREATE .htaccess file in public/ directory with URL rewrite rules\n";
} else {
    echo "1. ✓ .htaccess file exists\n";
}

if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
    echo "2. ENABLE mod_rewrite in Apache\n";
    echo "   - Edit: C:\\xampp\\apache\\conf\\httpd.conf\n";
    echo "   - Find: LoadModule rewrite_module modules/mod_rewrite.so\n";
    echo "   - Uncomment it (remove #)\n";
    echo "   - Restart Apache\n";
} else {
    echo "2. ✓ mod_rewrite appears to be enabled\n";
}

echo "3. After making changes, restart Apache\n";
echo "4. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "5. Try accessing: http://localhost/IMS_FINAL/public/\n";

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "DEBUG COMPLETE\n";
echo str_repeat("=", 80) . "\n\n";
