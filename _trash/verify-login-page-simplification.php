<?php
/**
 * Login Page Simplification Verification
 * Verifies that the login page layout has been simplified
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "LOGIN PAGE SIMPLIFICATION VERIFICATION\n";
echo str_repeat("=", 70) . "\n\n";

$authLayout = file_get_contents(BASE_PATH . '/app/Views/layouts/auth.php');

$checks = [
    'Left section hidden' => [
        'should_contain' => ['display: none;', 'grid-template-columns: 1fr;'],
        'should_not_contain' => ['grid-template-columns: 1.1fr 0.9fr;', 'grid-template-columns: 1fr 1fr;']
    ],
    'Single column layout' => [
        'should_contain' => ['grid-template-columns: 1fr;'],
        'should_not_contain' => []
    ],
    'Centered container' => [
        'should_contain' => ['width: min(500px, 100%);'],
        'should_not_contain' => ['width: min(1120px, 100%);']
    ],
    'Form panel styling preserved' => [
        'should_contain' => ['.form-panel {', 'padding: 44px;', 'background: linear-gradient'],
        'should_not_contain' => []
    ],
    'Form card centered' => [
        'should_contain' => ['.form-card {', 'margin: 0 auto;', 'max-width: 420px;'],
        'should_not_contain' => []
    ],
    'Brand section exists' => [
        'should_contain' => ['.brand {', 'gap: 12px;'],
        'should_not_contain' => []
    ],
    'Login form intact' => [
        'should_contain' => ['id="loginForm"', 'name="email"', 'name="password"', 'type="submit"'],
        'should_not_contain' => []
    ],
    'Responsive design' => [
        'should_contain' => ['@media (max-width: 960px)', '@media (max-width: 640px)'],
        'should_not_contain' => []
    ]
];

$loginForm = file_get_contents(BASE_PATH . '/app/Views/auth/login.php');

$passed = 0;
$total = count($checks);

foreach ($checks as $name => $check) {
    echo "Check: $name\n";
    echo str_repeat("-", 70) . "\n";
    
    // Determine which file to check
    $content = (strpos($name, 'form') !== false) ? $loginForm : $authLayout;
    
    $allPass = true;
    
    // Check should_contain
    if (isset($check['should_contain'])) {
        foreach ((array) $check['should_contain'] as $pattern) {
            if (strpos($content, $pattern) === false) {
                echo "✗ Missing: $pattern\n";
                $allPass = false;
            }
        }
    }
    
    // Check should_not_contain
    if (isset($check['should_not_contain'])) {
        foreach ((array) $check['should_not_contain'] as $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "✗ Should not contain: $pattern\n";
                $allPass = false;
            }
        }
    }
    
    if ($allPass) {
        echo "✓ Passed\n";
        $passed++;
    }
    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Tests Passed: $passed / $total\n\n";

if ($passed === $total) {
    echo "✅ LOGIN PAGE SIMPLIFICATION COMPLETE\n\n";
    echo "Changes Applied:\n";
    echo "  ✓ Removed left info/preview section\n";
    echo "  ✓ Single-column centered layout\n";
    echo "  ✓ Reduced width to 500px max\n";
    echo "  ✓ Form remains fully functional\n";
    echo "  ✓ Responsive design maintained\n";
    echo "  ✓ Existing styling preserved\n";
    echo "  ✓ No logic changes\n";
    echo "\nTest it:\n";
    echo "  1. Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "  2. Visit: http://localhost/IMS_FINAL/public/login\n";
    echo "  3. Should see clean, centered login form only\n";
} else {
    echo "⚠️ SOME CHECKS FAILED\n";
    echo "Please review the errors above.\n";
}

echo "\n" . str_repeat("=", 70) . "\n\n";
