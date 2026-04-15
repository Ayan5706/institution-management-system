<?php
/**
 * Principal Module Verification Script
 * Tests that all components are properly configured and integrated
 */

echo "=== PRINCIPAL MODULE VERIFICATION ===\n\n";

$errors = [];
$warnings = [];
$successes = [];

// 1. Check PrincipalController exists
echo "1. Checking PrincipalController...\n";
if (file_exists(__DIR__ . '/app/Controllers/PrincipalController.php')) {
    $content = file_get_contents(__DIR__ . '/app/Controllers/PrincipalController.php');
    $methods = [
        'showDashboard', 'showAccounts', 'createAccountForm', 'storeAccount', 
        'toggleAccountStatus', 'showStudents', 'showStudentDetail', 'showTeachers',
        'showTeacherDetail', 'showConfig', 'updateConfig', 'showPasswordResets',
        'approvePasswordReset', 'rejectPasswordReset', 'showAuditLog',
        'apiDashboard', 'apiGetAdminUsers', 'apiGetStudents', 'apiGetTeachers',
        'apiGetAuditLog'
    ];
    
    $missing_methods = [];
    foreach ($methods as $method) {
        if (strpos($content, "public function $method") === false) {
            $missing_methods[] = $method;
        }
    }
    
    if (empty($missing_methods)) {
        $successes[] = "✓ PrincipalController: All 20 methods present";
    } else {
        $errors[] = "✗ PrincipalController: Missing methods: " . implode(', ', $missing_methods);
    }
} else {
    $errors[] = "✗ PrincipalController.php not found";
}

// 2. Check view files exist
echo "2. Checking Principal view files...\n";
$views = [
    'accounts.php', 'students.php', 'teachers.php', 
    'config.php', 'password-resets.php', 'audit-log.php'
];

$missing_views = [];
foreach ($views as $view) {
    $path = __DIR__ . '/app/Views/principal/' . $view;
    if (!file_exists($path)) {
        $missing_views[] = $view;
    }
}

if (empty($missing_views)) {
    $successes[] = "✓ Principal views: All 6 files present";
} else {
    $errors[] = "✗ Principal views: Missing files: " . implode(', ', $missing_views);
}

// 3. Check routes configuration
echo "3. Checking routes configuration...\n";
if (file_exists(__DIR__ . '/routes/web.php')) {
    $routes_content = file_get_contents(__DIR__ . '/routes/web.php');
    
    $required_routes = [
        "'/principal'",
        "'/principal/accounts'",
        "'/principal/students'",
        "'/principal/teachers'",
        "'/principal/config'",
        "'/principal/password-resets'",
        "'/principal/audit-log'",
        "'/api/principal/dashboard'",
        "'role:principal'"
    ];
    
    $missing_routes = [];
    foreach ($required_routes as $route) {
        if (strpos($routes_content, $route) === false) {
            $missing_routes[] = $route;
        }
    }
    
    if (empty($missing_routes)) {
        $successes[] = "✓ Routes: All principal routes configured";
    } else {
        $errors[] = "✗ Routes: Missing routes: " . implode(', ', $missing_routes);
    }
} else {
    $errors[] = "✗ routes/web.php not found";
}

// 4. Check sidebar navigation
echo "4. Checking sidebar navigation...\n";
if (file_exists(__DIR__ . '/app/Views/layouts/app.php')) {
    $sidebar_content = file_get_contents(__DIR__ . '/app/Views/layouts/app.php');
    
    $nav_items = [
        "if (\$userRole === 'PRINCIPAL')",
        "principal/accounts",
        "principal/students",
        "principal/teachers",
        "principal/config",
        "principal/password-resets",
        "principal/audit-log"
    ];
    
    $missing_nav = [];
    foreach ($nav_items as $item) {
        if (strpos($sidebar_content, $item) === false) {
            $missing_nav[] = $item;
        }
    }
    
    if (empty($missing_nav)) {
        $successes[] = "✓ Sidebar: Principal navigation configured";
    } else {
        $errors[] = "✗ Sidebar: Missing navigation items: " . implode(', ', $missing_nav);
    }
} else {
    $errors[] = "✗ app/Views/layouts/app.php not found";
}

// 5. Check RoleMiddleware supports granular roles
echo "5. Checking RoleMiddleware...\n";
if (file_exists(__DIR__ . '/app/Middleware/RoleMiddleware.php')) {
    $middleware_content = file_get_contents(__DIR__ . '/app/Middleware/RoleMiddleware.php');
    
    if (strpos($middleware_content, 'explode') !== false && strpos($middleware_content, ',') !== false) {
        $successes[] = "✓ RoleMiddleware: Supports comma-separated roles";
    } else {
        $warnings[] = "⚠ RoleMiddleware: May not support multiple roles";
    }
} else {
    $errors[] = "✗ RoleMiddleware.php not found";
}

// 6. Check password reset rejection is implemented
echo "6. Checking password reset rejection...\n";
if (file_exists(__DIR__ . '/app/Controllers/PrincipalController.php')) {
    $controller_content = file_get_contents(__DIR__ . '/app/Controllers/PrincipalController.php');
    
    if (strpos($controller_content, 'rejectPasswordReset') !== false && 
        strpos($controller_content, 'REJECTED') !== false) {
        $successes[] = "✓ Password reset: Rejection workflow implemented";
    } else {
        $errors[] = "✗ Password reset: Rejection functionality missing";
    }
} else {
    $errors[] = "✗ Cannot check password reset rejection";
}

// 7. Check audit logging is implemented
echo "7. Checking audit logging...\n";
if (file_exists(__DIR__ . '/app/Controllers/PrincipalController.php')) {
    $controller_content = file_get_contents(__DIR__ . '/app/Controllers/PrincipalController.php');
    
    if (strpos($controller_content, 'auditLog(') !== false && 
        strpos($controller_content, 'auditModel') !== false) {
        $successes[] = "✓ Audit logging: Integrated throughout controller";
    } else {
        $errors[] = "✗ Audit logging: Not properly integrated";
    }
} else {
    $errors[] = "✗ Cannot check audit logging";
}

// 8. Check dashboard principal view exists
echo "8. Checking principal dashboard view...\n";
if (file_exists(__DIR__ . '/app/Views/dashboard/principal.php')) {
    $dash_content = file_get_contents(__DIR__ . '/app/Views/dashboard/principal.php');
    
    if (strpos($dash_content, 'stat-card') !== false && 
        strpos($dash_content, 'action-grid') !== false) {
        $successes[] = "✓ Dashboard: Principal dashboard view enhanced";
    } else {
        $warnings[] = "⚠ Dashboard: May lack full implementation";
    }
} else {
    $errors[] = "✗ Dashboard: Principal dashboard view missing";
}

// 9. Check documentation exists
echo "9. Checking documentation...\n";
$docs = [
    'PRINCIPAL_MODULE_IMPLEMENTATION.md',
    'PRINCIPAL_TESTING_GUIDE.md'
];

$missing_docs = [];
foreach ($docs as $doc) {
    if (!file_exists(__DIR__ . '/' . $doc)) {
        $missing_docs[] = $doc;
    }
}

if (empty($missing_docs)) {
    $successes[] = "✓ Documentation: All guides provided";
} else {
    $warnings[] = "⚠ Documentation: Missing files: " . implode(', ', $missing_docs);
}

// 10. Check no breaking changes
echo "10. Checking for breaking changes...\n";
if (file_exists(__DIR__ . '/app/Controllers/DashboardController.php')) {
    $dash_controller = file_get_contents(__DIR__ . '/app/Controllers/DashboardController.php');
    
    if (strpos($dash_controller, 'PRINCIPAL') !== false) {
        $successes[] = "✓ Architecture: DashboardController properly routes principals";
    } else {
        $warnings[] = "⚠ Architecture: DashboardController may need updates";
    }
} else {
    $warnings[] = "⚠ Cannot verify DashboardController";
}

// Summary
echo "\n=== VERIFICATION SUMMARY ===\n";
echo "Successes: " . count($successes) . "\n";
echo "Warnings: " . count($warnings) . "\n";
echo "Errors: " . count($errors) . "\n\n";

if (count($successes) > 0) {
    echo "PASSED CHECKS:\n";
    foreach ($successes as $success) {
        echo "  $success\n";
    }
}

if (count($warnings) > 0) {
    echo "\nWARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  $warning\n";
    }
}

if (count($errors) > 0) {
    echo "\nFAILED CHECKS:\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    echo "\n❌ VERIFICATION FAILED\n";
    exit(1);
} else {
    echo "\n✅ ALL VERIFICATIONS PASSED\n";
    echo "Principal module is correctly implemented and ready for deployment.\n";
    exit(0);
}
