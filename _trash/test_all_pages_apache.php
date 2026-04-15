<?php
/**
 * Comprehensive Website Testing Script - Apache Version
 * Tests all pages, routes, and features in the IMS application
 */

declare(strict_types=1);

$base_url = 'http://localhost/IMS_FINAL/public';
$test_results = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'errors' => [],
    'details' => []
];

function test_page($url, $description, &$results) {
    global $base_url;
    $full_url = $base_url . $url;
    
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
            'follow_location' => false
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
        } elseif (strpos($response, 'Parse error') !== false || 
                  strpos($response, 'Fatal error') !== false ||
                  strpos($response, 'Uncaught') !== false) {
            $has_error = true;
        }
        
        // 302/303/307 redirects to login are OK (expected for auth pages without session)
        if ($status >= 300 && $status < 400) {
            $results['passed']++;
            $results['details'][] = "✓ $description (HTTP $status - Redirect OK)";
            echo "✓ $description (HTTP $status - Redirect)\n";
            return true;
        } elseif ($has_error || $status >= 400) {
            $results['failed']++;
            $results['errors'][] = "✗ $description: HTTP $status";
            $results['details'][] = "✗ $description (HTTP $status)";
            echo "✗ $description (HTTP $status)\n";
            return false;
        } else {
            $results['passed']++;
            $results['details'][] = "✓ $description (HTTP $status)";
            echo "✓ $description (HTTP $status)\n";
            return true;
        }
    } catch (Exception $e) {
        $results['failed']++;
        $results['errors'][] = "✗ $description: " . $e->getMessage();
        $results['details'][] = "✗ $description: " . $e->getMessage();
        echo "✗ $description: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "================================================================================\n";
echo "IMS COMPREHENSIVE WEBSITE TESTING (Apache)\n";
echo "================================================================================\n\n";

// Test public pages (no auth required)
echo "--- PUBLIC PAGES (No Authentication) ---\n";
test_page('/', 'Landing Page', $test_results);
test_page('/login', 'Login Page', $test_results);
test_page('/forgot-password', 'Forgot Password Page', $test_results);
test_page('/reset-password', 'Reset Password Page', $test_results);

// Test authenticated pages
echo "\n--- AUTHENTICATED PAGES (May redirect to login - expected) ---\n";
test_page('/dashboard', 'General Dashboard', $test_results);
test_page('/change-password', 'Change Password', $test_results);

echo "\n--- PRINCIPAL MODULE ---\n";
test_page('/principal/dashboard', 'Principal - Dashboard', $test_results);
test_page('/principal/accounts', 'Principal - Manage Accounts', $test_results);
test_page('/principal/students', 'Principal - Students', $test_results);
test_page('/principal/teachers', 'Principal - Teachers', $test_results);
test_page('/principal/password-resets', 'Principal - Password Resets', $test_results);
test_page('/principal/audit-log', 'Principal - Audit Log', $test_results);
test_page('/principal/config', 'Principal - Configuration', $test_results);

echo "\n--- VP MODULE ---\n";
test_page('/vp/dashboard', 'VP - Dashboard', $test_results);
test_page('/vp/programs', 'VP - Programs', $test_results);
test_page('/vp/semesters', 'VP - Semesters', $test_results);
test_page('/vp/subjects', 'VP - Subjects', $test_results);
test_page('/vp/teachers', 'VP - Teachers', $test_results);
test_page('/vp/assignments', 'VP - Assignments', $test_results);
test_page('/vp/timetable', 'VP - Timetable', $test_results);
test_page('/vp/password-requests', 'VP - Password Requests', $test_results);

echo "\n--- MANAGER MODULE ---\n";
test_page('/manager/dashboard', 'Manager - Dashboard', $test_results);
test_page('/manager/students', 'Manager - Students', $test_results);
test_page('/manager/students/csv-upload', 'Manager - CSV Upload', $test_results);
test_page('/manager/password-resets', 'Manager - Password Resets', $test_results);

echo "\n--- ACCOUNTANT MODULE ---\n";
test_page('/accountant/dashboard', 'Accountant - Dashboard', $test_results);
test_page('/accountant/semester-fees', 'Accountant - Semester Fees', $test_results);
test_page('/accountant/student-fees', 'Accountant - Student Fees', $test_results);

echo "\n--- TEACHER MODULE ---\n";
test_page('/teacher/dashboard', 'Teacher - Dashboard', $test_results);
test_page('/teacher/attendance/history', 'Teacher - Attendance History', $test_results);

echo "\n--- STUDENT MODULE ---\n";
test_page('/student/dashboard', 'Student - Dashboard', $test_results);
test_page('/student/timetable', 'Student - Timetable', $test_results);
test_page('/student/attendance', 'Student - Attendance', $test_results);
test_page('/student/fees', 'Student - Fees', $test_results);
test_page('/student/profile', 'Student - Profile', $test_results);

echo "\n--- ADMIN MODULE ---\n";
test_page('/admin', 'Admin - Dashboard', $test_results);
test_page('/admin/config', 'Admin - Configuration', $test_results);
test_page('/admin/audit-logs', 'Admin - Audit Logs', $test_results);

echo "\n--- REPORTS MODULE ---\n";
test_page('/reports', 'Reports - Home', $test_results);
test_page('/reports/academic', 'Reports - Academic', $test_results);
test_page('/reports/attendance', 'Reports - Attendance', $test_results);
test_page('/reports/finance', 'Reports - Finance', $test_results);

echo "\n--- GENERAL MODULES ---\n";
test_page('/programs', 'Programs List', $test_results);
test_page('/semesters', 'Semesters List', $test_results);
test_page('/subjects', 'Subjects List', $test_results);
test_page('/students', 'Students List', $test_results);
test_page('/attendance', 'Attendance List', $test_results);
test_page('/fees', 'Student Fees List', $test_results);

echo "\n============================================================================\n";
echo "TEST SUMMARY\n";
echo "============================================================================\n";
echo "Passed:  " . $test_results['passed'] . "\n";
echo "Failed:  " . $test_results['failed'] . "\n";

if (!empty($test_results['errors'])) {
    echo "\n--- ERRORS DETECTED ---\n";
    foreach (array_unique($test_results['errors']) as $error) {
        echo $error . "\n";
    }
}

echo "\nNote: Auth pages showing 302/307 redirects are expected (user not logged in)\n";
echo "\n";

?>
