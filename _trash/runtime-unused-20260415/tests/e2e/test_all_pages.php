<?php
/**
 * Comprehensive Website Testing Script
 * Tests all pages, routes, and features in the IMS application
 */

declare(strict_types=1);

// Start output buffering to capture errors
ob_start();

$base_url = 'http://localhost:8000';
$test_results = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'errors' => []
];

function test_page($url, $description, &$results) {
    global $base_url;
    $full_url = $base_url . $url;
    
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5
        ]
    ]);
    
    try {
        $response = @file_get_contents($full_url, false, $context);
        $headers = $http_response_header ?? [];
        $status = 200;
        
        if (!empty($headers)) {
            foreach ($headers as $header) {
                if (strpos($header, 'HTTP/') === 0) {
                    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches);
                    $status = (int) ($matches[1] ?? 200);
                }
            }
        }
        
        $has_error = false;
        if ($response === false) {
            $status = 0;
            $has_error = true;
        } elseif ($status >= 500) {
            $has_error = true;
        } elseif (stripos($response, 'fatal error') !== false || stripos($response, 'parse error') !== false) {
            $has_error = true;
        }
        
        if ($has_error || $status >= 400) {
            $results['failed']++;
            $results['errors'][] = "✗ $description: HTTP $status";
            echo "✗ $description (HTTP $status)\n";
            return false;
        } else {
            $results['passed']++;
            echo "✓ $description\n";
            return true;
        }
    } catch (Exception $e) {
        $results['failed']++;
        $results['errors'][] = "✗ $description: " . $e->getMessage();
        echo "✗ $description: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "================================================================================\n";
echo "IMS COMPREHENSIVE WEBSITE TESTING\n";
echo "================================================================================\n\n";

// Test public pages (no auth required)
echo "--- PUBLIC PAGES (No Authentication) ---\n";
test_page('/', 'Landing Page', $test_results);
test_page('/login', 'Login Page', $test_results);
test_page('/forgot-password', 'Forgot Password Page', $test_results);

// Test authenticated pages (will redirect to login if not authenticated)
echo "\n--- AUTHENTICATED PAGES (May redirect to login - expected) ---\n";
test_page('/dashboard', 'General Dashboard', $test_results);
test_page('/principal/dashboard', 'Principal Dashboard', $test_results);
test_page('/principal/accounts', 'Principal - Manage Accounts', $test_results);
test_page('/vp/dashboard', 'VP Dashboard', $test_results);
test_page('/manager/dashboard', 'Manager Dashboard', $test_results);
test_page('/accountant/dashboard', 'Accountant Dashboard', $test_results);
test_page('/teacher/dashboard', 'Teacher Dashboard', $test_results);
test_page('/student/dashboard', 'Student Dashboard', $test_results);

echo "\n--- ROW-SPECIFIC PAGES (Will likely 404 without valid ID) ---\n";
test_page('/programs', 'Programs List', $test_results);
test_page('/semesters', 'Semesters List', $test_results);
test_page('/subjects', 'Subjects List', $test_results);
test_page('/students', 'Students List', $test_results);
test_page('/attendance', 'Attendance List', $test_results);
test_page('/fees', 'Student Fees List', $test_results);

echo "\n--- ADMIN PAGES ---\n";
test_page('/admin', 'Admin Dashboard', $test_results);
test_page('/admin/config', 'Admin Configuration', $test_results);

echo "\n--- REPORTS PAGES ---\n";
test_page('/reports', 'Reports Home', $test_results);
test_page('/reports/academic', 'Academic Reports', $test_results);
test_page('/reports/attendance', 'Attendance Reports', $test_results);
test_page('/reports/finance', 'Finance Reports', $test_results);

echo "\n--- SPECIAL PAGES ---\n";
test_page('/change-password', 'Change Password', $test_results);
test_page('/reset-password', 'Reset Password', $test_results);
test_page('/principal/students', 'Principal - Students', $test_results);
test_page('/principal/teachers', 'Principal - Teachers', $test_results);
test_page('/vp/programs', 'VP - Programs', $test_results);
test_page('/vp/semesters', 'VP - Semesters', $test_results);
test_page('/vp/subjects', 'VP - Subjects', $test_results);
test_page('/vp/teachers', 'VP - Teachers', $test_results);
test_page('/manager/students', 'Manager - Students', $test_results);
test_page('/accountant/semester-fees', 'Accountant - Semester Fees', $test_results);
test_page('/accountant/student-fees', 'Accountant - Student Fees', $test_results);
test_page('/teacher/attendance/history', 'Teacher - Attendance History', $test_results);
test_page('/student/timetable', 'Student - Timetable', $test_results);
test_page('/student/attendance', 'Student - Attendance', $test_results);
test_page('/student/fees', 'Student - Fees', $test_results);
test_page('/student/profile', 'Student - Profile', $test_results);

echo "\n============================================================================\n";
echo "TEST SUMMARY\n";
echo "============================================================================\n";
echo "Passed:  " . $test_results['passed'] . "\n";
echo "Failed:  " . $test_results['failed'] . "\n";
echo "Warnings: " . $test_results['warnings'] . "\n";

if (!empty($test_results['errors'])) {
    echo "\n--- ERRORS DETECTED ---\n";
    foreach ($test_results['errors'] as $error) {
        echo $error . "\n";
    }
}

echo "\n";

// Clean up output buffer
$output = ob_get_clean();
echo $output;
?>
