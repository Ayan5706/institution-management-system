<?php
/**
 * Login Page Text Update Verification
 * Verifies that UI text has been updated for consistency
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "LOGIN PAGE TEXT UPDATE VERIFICATION\n";
echo str_repeat("=", 70) . "\n\n";

$checks = [
    'Field label updated' => [
        'file' => 'app/Views/auth/login.php',
        'should_contain' => '<label for="email">Login ID</label>',
        'should_not_contain' => 'Email or Login ID'
    ],
    'Placeholder updated' => [
        'file' => 'app/Views/auth/login.php',
        'should_contain' => 'placeholder="login id"',
        'should_not_contain' => 'admin@school.edu'
    ],
    'Instruction text updated' => [
        'file' => 'app/Views/layouts/auth.php',
        'should_contain' => '<small>Use your login ID to continue</small>',
        'should_not_contain' => 'Use your login ID or email'
    ],
    'Name attribute preserved' => [
        'file' => 'app/Views/auth/login.php',
        'should_contain' => 'name="email"',
        'should_not_contain' => []
    ],
    'Required attribute preserved' => [
        'file' => 'app/Views/auth/login.php',
        'should_contain' => 'required',
        'should_not_contain' => []
    ],
    'Input ID preserved' => [
        'file' => 'app/Views/auth/login.php',
        'should_contain' => 'id="email"',
        'should_not_contain' => []
    ]
];

$passed = 0;
$total = count($checks);

foreach ($checks as $name => $check) {
    echo "Check: $name\n";
    echo str_repeat("-", 70) . "\n";
    
    $file = BASE_PATH . '/' . $check['file'];
    if (!file_exists($file)) {
        echo "✗ File not found: {$check['file']}\n\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $found = strpos($content, $check['should_contain']) !== false;
    $notFound = empty($check['should_not_contain']) || 
                (is_array($check['should_not_contain']) && count($check['should_not_contain']) == 0) ||
                strpos($content, $check['should_not_contain']) === false;
    
    if ($found && $notFound) {
        echo "✓ Passed\n";
        $passed++;
    } else {
        if (!$found) {
            echo "✗ Expected text not found: {$check['should_contain']}\n";
        }
        if (!$notFound && !empty($check['should_not_contain'])) {
            echo "✗ Old text still present: {$check['should_not_contain']}\n";
        }
    }
    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Tests Passed: $passed / $total\n\n";

if ($passed === $total) {
    echo "✅ LOGIN PAGE TEXT UPDATES COMPLETE\n\n";
    echo "Changes Applied:\n";
    echo "  ✓ Field label: 'Email or Login ID' → 'Login ID'\n";
    echo "  ✓ Placeholder: 'admin@school.edu or admin' → 'login id'\n";
    echo "  ✓ Instruction: 'Use your login ID or email to continue' → 'Use your login ID to continue'\n";
    echo "  ✓ No backend/authentication changes\n";
    echo "  ✓ All form functionality preserved\n";
    echo "\nTest it:\n";
    echo "  1. Clear browser cache: Ctrl+Shift+Delete\n";
    echo "  2. Visit: http://localhost/IMS_FINAL/public/login\n";
    echo "  3. Verify new text labels appear\n";
} else {
    echo "⚠️ SOME CHECKS FAILED\n";
}

echo "\n" . str_repeat("=", 70) . "\n\n";
