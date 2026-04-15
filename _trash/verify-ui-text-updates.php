<?php
/**
 * UI Text Updates Verification
 * Verifies that "IMS Final" → "IMS" and "Sign In" → "Login" updates are applied
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "UI TEXT UPDATES VERIFICATION\n";
echo str_repeat("=", 70) . "\n\n";

$checks = [
    'Landing Page Header' => [
        'file' => 'app/Views/home/landing.php',
        'search' => '<span>IMS</span>',
        'notFound' => 'IMS Final'
    ],
    'Landing Page CTA' => [
        'file' => 'app/Views/home/landing.php',
        'search' => 'Login Now',
        'notFound' => 'Sign In Now'
    ],
    'Landing Page Login Button' => [
        'file' => 'app/Views/home/landing.php',
        'search' => 'class="login-btn"',
        'contains' => 'Login'
    ],
    'Login Page Title' => [
        'file' => 'app/Views/auth/login.php',
        'search' => "'Login'",
        'notFound' => "'Sign In'"
    ],
    'App Config' => [
        'file' => 'app/Config/app.php',
        'search' => "'IMS'",
        'notFound' => 'IMS Final'
    ],
    'ENV Example' => [
        'file' => '.env.example',
        'search' => 'APP_NAME="IMS"',
        'notFound' => 'IMS Final'
    ]
];

$passed = 0;
$total = count($checks);

foreach ($checks as $name => $check) {
    echo "Checking: $name\n";
    echo str_repeat("-", 70) . "\n";
    
    $file = BASE_PATH . '/' . $check['file'];
    if (!file_exists($file)) {
        echo "✗ File not found: {$check['file']}\n\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $found = strpos($content, $check['search']) !== false;
    $notFound = !isset($check['notFound']) || strpos($content, $check['notFound']) === false;
    $contains = !isset($check['contains']) || strpos($content, $check['contains']) !== false;
    
    if ($found && $notFound && $contains) {
        echo "✓ Text updated correctly\n";
        $passed++;
    } else {
        if (!$found) {
            echo "✗ Expected text not found: {$check['search']}\n";
        }
        if (!$notFound && isset($check['notFound'])) {
            echo "✗ Old text still present: {$check['notFound']}\n";
        }
        if (!$contains && isset($check['contains'])) {
            echo "✗ Expected content missing: {$check['contains']}\n";
        }
    }
    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Tests Passed: $passed / $total\n\n";

if ($passed === $total) {
    echo "✅ ALL UI TEXT UPDATES VERIFIED\n\n";
    echo "Changes Applied:\n";
    echo "  • 'IMS Final' → 'IMS' (headers, config, branding)\n";
    echo "  • 'Sign In' → 'Login' (buttons, titles, CTA text)\n";
    echo "\nNo functionality modified - UI text only.\n";
} else {
    echo "⚠️ SOME CHECKS FAILED\n";
    echo "Please review the errors above.\n";
}

echo "\n" . str_repeat("=", 70) . "\n\n";
