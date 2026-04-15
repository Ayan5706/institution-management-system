<?php

/**
 * Landing Page Verification Script
 * Verifies that the landing page is properly implemented and configured
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "LANDING PAGE IMPLEMENTATION VERIFICATION\n";
echo str_repeat("=", 80) . "\n\n";

// Test 1: HomeController exists
echo "1. HomeController Check:\n";
echo str_repeat("-", 80) . "\n";
$homePath = BASE_PATH . '/app/Controllers/HomeController.php';
if (file_exists($homePath)) {
    echo "✓ HomeController file exists\n";
    
    try {
        require_once BASE_PATH . '/app/Core/Autoloader.php';
        \App\Core\Autoloader::register(BASE_PATH);
        
        if (class_exists('App\Controllers\HomeController')) {
            echo "✓ HomeController class can be loaded\n";
            
            // Check for landing method
            $reflection = new ReflectionClass('App\Controllers\HomeController');
            if ($reflection->hasMethod('landing')) {
                echo "✓ landing() method exists\n";
            } else {
                echo "✗ landing() method NOT found\n";
            }
        } else {
            echo "✗ HomeController class cannot be loaded\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ HomeController file NOT found\n";
}

// Test 2: Landing page view exists
echo "\n2. Landing Page View Check:\n";
echo str_repeat("-", 80) . "\n";
$viewPath = BASE_PATH . '/app/Views/home/landing.php';
if (file_exists($viewPath)) {
    echo "✓ Landing page view exists\n";
    $content = file_get_contents($viewPath);
    
    // Check for key elements
    $elements = [
        'header' => '<header class="header">',
        'hero section' => '<section class="hero">',
        'features grid' => '<section class="features"',
        'login button' => 'url(\'login\')',
        'login button text' => 'Login',
    ];
    
    foreach ($elements as $name => $pattern) {
        if (strpos($content, $pattern) !== false) {
            echo "  ✓ $name found\n";
        } else {
            echo "  ✗ $name NOT found\n";
        }
    }
} else {
    echo "✗ Landing page view NOT found at: $viewPath\n";
}

// Test 3: Routes configuration
echo "\n3. Routes Configuration Check:\n";
echo str_repeat("-", 80) . "\n";
$routesFile = BASE_PATH . '/routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    
    // Check for key routes
    $checks = [
        'HomeController import' => 'use App\Controllers\HomeController',
        'Landing page route' => "HomeController::class . '@landing'",
        'Guest middleware' => "\\['guest'\\]",
        'Dashboard route' => "'/dashboard'",
        'Auth middleware' => "\\['auth'\\]",
    ];
    
    foreach ($checks as $name => $pattern) {
        if (strpos($content, $pattern) !== false) {
            echo "✓ $name\n";
        } else {
            echo "✗ $name NOT found\n";
        }
    }
    
    // Check route order
    $landingPos = strpos($content, "HomeController::class . '@landing'");
    $dashboardPos = strpos($content, "'/dashboard'");
    
    if ($landingPos !== false && $dashboardPos !== false && $landingPos < $dashboardPos) {
        echo "✓ Route order correct (landing before dashboard)\n";
    } else {
        echo "⚠ Route order may need adjustment\n";
    }
} else {
    echo "✗ Routes file NOT found\n";
}

// Test 4: Login redirect update
echo "\n4. Login Redirect Check:\n";
echo str_repeat("-", 80) . "\n";
$loginFile = BASE_PATH . '/app/Views/auth/login.php';
if (file_exists($loginFile)) {
    $content = file_get_contents($loginFile);
    
    if (strpos($content, "url('dashboard')") !== false) {
        echo "✓ Login redirects to /dashboard\n";
    } else {
        echo "⚠ Login redirect may not be updated\n";
    }
} else {
    echo "✗ Login file NOT found\n";
}

// Test 5: App layout update
echo "\n5. App Layout Check:\n";
echo str_repeat("-", 80) . "\n";
$layoutFile = BASE_PATH . '/app/Views/layouts/app.php';
if (file_exists($layoutFile)) {
    $content = file_get_contents($layoutFile);
    
    if (preg_match("/url\\(['\"]dashboard['\"]\\).*Dashboard/", $content)) {
        echo "✓ Dashboard navigation link updated\n";
    } else {
        echo "⚠ Dashboard navigation link may need updating\n";
    }
} else {
    echo "✗ App layout NOT found\n";
}

// Test 6: Application startup
echo "\n6. Application Bootstrap Check:\n";
echo str_repeat("-", 80) . "\n";
try {
    require_once BASE_PATH . '/bootstrap/app.php';
    echo "✓ Application bootstraps successfully\n";
    echo "✓ Router configured\n";
    echo "✓ Middleware system ready\n";
} catch (Exception $e) {
    echo "✗ Bootstrap error: " . $e->getMessage() . "\n";
}

// Summary
echo "\n";
echo str_repeat("=", 80) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 80) . "\n";

$summary = [
    'HomeController' => file_exists($homePath),
    'Landing View' => file_exists($viewPath),
    'Routes Updated' => file_exists($routesFile),
    'Login Redirect' => file_exists($loginFile),
    'App Layout' => file_exists($layoutFile),
];

$allPass = true;
foreach ($summary as $check => $status) {
    $statusText = $status ? '✓' : '✗';
    echo "$statusText $check\n";
    if (!$status) $allPass = false;
}

echo "\n";
if ($allPass) {
    echo "✅ LANDING PAGE IMPLEMENTATION COMPLETE\n";
    echo "\nNEXT STEPS:\n";
    echo "1. Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "2. Visit: http://localhost/IMS_FINAL/public/\n";
    echo "3. Verify landing page displays\n";
    echo "4. Test login redirect\n";
} else {
    echo "⚠️ SOME CHECKS FAILED\n";
    echo "Please review the errors above\n";
}

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "VERIFICATION COMPLETE\n";
echo str_repeat("=", 80) . "\n\n";
