<?php
/**
 * Test Credentials Verification
 * Verifies standardized test credentials are properly configured
 */

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 70) . "\n";
echo "TEST CREDENTIALS VERIFICATION\n";
echo str_repeat("=", 70) . "\n\n";

// Check seeder file
echo "1. Seeder Configuration Check\n";
echo str_repeat("-", 70) . "\n";

$seederFile = BASE_PATH . '/database/seeders/UsersTableSeeder.php';
if (!file_exists($seederFile)) {
    echo "✗ Seeder file not found\n";
} else {
    echo "✓ Seeder file exists\n";
    
    $content = file_get_contents($seederFile);
    
    // Check for each role
    $roles = [
        'PRINCIPAL' => ['login_id' => 'principal', 'password' => 'principal123'],
        'VP' => ['login_id' => 'vp', 'password' => 'vp123'],
        'MANAGER' => ['login_id' => 'manager', 'password' => 'manager123'],
        'ACCOUNTANT' => ['login_id' => 'accountant', 'password' => 'accountant123'],
        'TEACHER' => ['login_id' => 'teacher', 'password' => 'teacher123'],
        'STUDENT' => ['login_id' => 'student', 'password' => 'student123'],
    ];
    
    $allRolesFound = true;
    foreach ($roles as $role => $creds) {
        if (strpos($content, "'role' => '$role'") !== false &&
            strpos($content, "'login_id' => '" . $creds['login_id'] . "'") !== false &&
            strpos($content, "'" . $creds['password'] . "'") !== false) {
            echo "  ✓ $role configured\n";
        } else {
            echo "  ✗ $role NOT properly configured\n";
            $allRolesFound = false;
        }
    }
}

echo "\n";

// Check for old credentials
echo "2. Old Credentials Cleanup Check\n";
echo str_repeat("-", 70) . "\n";

$oldCredentials = [
    'admin@imsschool.local' => 'Admin old email',
    'principal.wilson' => 'Principal wilson old login',
    'dr.johnson' => 'Dr Johnson old teacher login',
    'mr.smith' => 'Mr Smith old teacher login',
    'ms.davis' => 'Ms Davis old teacher login',
    'janderson' => 'John Anderson old student',
    'sbrown' => 'Sarah Brown old student',
    'mharris' => 'Michael Harris old student',
    'ltaylor' => 'Lisa Taylor old student',
    'dmiller' => 'David Miller old student',
];

$found_old = false;
foreach ($oldCredentials as $oldCred => $description) {
    if (strpos($content, $oldCred) !== false) {
        echo "  ⚠ Found old credential: $oldCred ($description)\n";
        $found_old = true;
    }
}

if (!$found_old) {
    echo "  ✓ All old credentials removed\n";
}

echo "\n";

// Check seeder has exactly 6 users
echo "3. Seeder User Count Check\n";
echo str_repeat("-", 70) . "\n";

// Count role assignments
$principalCount = substr_count($content, "'role' => 'PRINCIPAL'");
$vpCount = substr_count($content, "'role' => 'VP'");
$managerCount = substr_count($content, "'role' => 'MANAGER'");
$accountantCount = substr_count($content, "'role' => 'ACCOUNTANT'");
$teacherCount = substr_count($content, "'role' => 'TEACHER'");
$studentCount = substr_count($content, "'role' => 'STUDENT'");

$totalCount = $principalCount + $vpCount + $managerCount + $accountantCount + $teacherCount + $studentCount;

echo "  Principal accounts: $principalCount\n";
echo "  VP accounts: $vpCount\n";
echo "  Manager accounts: $managerCount\n";
echo "  Accountant accounts: $accountantCount\n";
echo "  Teacher accounts: $teacherCount\n";
echo "  Student accounts: $studentCount\n";
echo "  Total: $totalCount\n";

if ($totalCount === 6 && 
    $principalCount === 1 && 
    $vpCount === 1 && 
    $managerCount === 1 && 
    $accountantCount === 1 && 
    $teacherCount === 1 && 
    $studentCount === 1) {
    echo "  ✓ Exactly one account per role\n";
} else {
    echo "  ✗ Role distribution incorrect\n";
}

echo "\n";

// Check credentials format
echo "4. Credentials Format Check\n";
echo str_repeat("-", 70) . "\n";

$credentialChecks = [
    "'login_id' => 'principal'" => 'Principal login ID',
    "'password' => password_hash('principal123'" => 'Principal password hash',
    "'login_id' => 'vp'" => 'VP login ID',
    "'password' => password_hash('vp123'" => 'VP password hash',
    "'login_id' => 'manager'" => 'Manager login ID',
    "'password' => password_hash('manager123'" => 'Manager password hash',
    "'login_id' => 'accountant'" => 'Accountant login ID',
    "'password' => password_hash('accountant123'" => 'Accountant password hash',
    "'login_id' => 'teacher'" => 'Teacher login ID',
    "'password' => password_hash('teacher123'" => 'Teacher password hash',
    "'login_id' => 'student'" => 'Student login ID',
    "'password' => password_hash('student123'" => 'Student password hash',
];

$formatOk = true;
foreach ($credentialChecks as $pattern => $description) {
    if (strpos($content, $pattern) !== false) {
        echo "  ✓ $description configured\n";
    } else {
        echo "  ✗ $description MISSING\n";
        $formatOk = false;
    }
}

echo "\n";

// Final Summary
echo str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";

$checks = [
    'Seeder file exists' => file_exists($seederFile),
    'All roles configured' => $allRolesFound,
    'Old credentials removed' => !$found_old,
    'Exactly 6 accounts (1 per role)' => $totalCount === 6 && 
                                         $principalCount === 1 && 
                                         $vpCount === 1 && 
                                         $managerCount === 1 && 
                                         $accountantCount === 1 && 
                                         $teacherCount === 1 && 
                                         $studentCount === 1,
    'Credentials format correct' => $formatOk,
];

$passed = array_sum($checks);
$total = count($checks);

echo "\n";
foreach ($checks as $check => $status) {
    $mark = $status ? '✓' : '✗';
    echo "$mark $check\n";
}

echo "\n";

if ($passed === $total) {
    echo "✅ TEST CREDENTIALS PROPERLY CONFIGURED\n\n";
    echo "Quick Reference:\n";
    echo "  Principal:     principal / principal123\n";
    echo "  VP:            vp / vp123\n";
    echo "  Manager:       manager / manager123\n";
    echo "  Accountant:    accountant / accountant123\n";
    echo "  Teacher:       teacher / teacher123\n";
    echo "  Student:       student / student123\n";
    echo "\nNext Step:\n";
    echo "  Run: php scripts/seed.php\n";
    echo "  Then test login: http://localhost/IMS_FINAL/public/login\n";
} else {
    echo "⚠️ SOME CHECKS FAILED\n\n";
    echo "Please review the errors above.\n";
}

echo "\n" . str_repeat("=", 70) . "\n\n";
