<?php
/**
 * Test Credentials Importer
 * Imports standardized test credentials into the users table
 */

declare(strict_types=1);

use App\Core\Database;

define('BASE_PATH', __DIR__);

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "TEST CREDENTIALS IMPORTER\n";
echo str_repeat("=", 80) . "\n\n";

require_once BASE_PATH . '/bootstrap/app.php';

echo "✓ Application bootstrapped\n";

// Get database connection
$pdo = Database::connection();
echo "✓ Database connection established\n\n";

// SQL INSERT statement
$sql = <<<SQL
INSERT INTO users (full_name, login_id, email, phone, password_hash, role, is_active, created_at, updated_at) VALUES
('Principal Test Account', 'principal', 'principal@imsschool.local', '555-1001', '\$2y\$10\$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'PRINCIPAL', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Vice Principal Test Account', 'vp', 'vp@imsschool.local', '555-1002', '\$2y\$10\$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'VP', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Manager Test Account', 'manager', 'manager@imsschool.local', '555-1003', '\$2y\$10\$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'MANAGER', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Accountant Test Account', 'accountant', 'accountant@imsschool.local', '555-1004', '\$2y\$10\$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'ACCOUNTANT', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Teacher Test Account', 'teacher', 'teacher@imsschool.local', '555-1005', '\$2y\$10\$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'TEACHER', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Student Test Account', 'student', 'student@imsschool.local', '555-1006', '\$2y\$10\$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'STUDENT', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00')
SQL;

try {
    echo "Step 1: Checking for existing accounts...\n";
    echo str_repeat("-", 80) . "\n";
    
    $checkSql = "SELECT COUNT(*) as count FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT')";
    $result = $pdo->query($checkSql);
    $existing = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($existing['count'] > 0) {
        echo "Found {$existing['count']} existing test account(s)\n";
        echo "Removing old accounts first...\n";
        
        $deleteSql = "DELETE FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT')";
        $pdo->exec($deleteSql);
        
        echo "✓ Old accounts removed\n\n";
    } else {
        echo "✓ No existing test accounts found\n\n";
    }
    
    echo "Step 2: Inserting test credentials...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "✓ INSERT statement executed\n";
    echo "✓ 6 test accounts created\n\n";
    
    echo "Step 3: Verifying import...\n";
    echo str_repeat("-", 80) . "\n";
    
    $verifySql = "SELECT login_id, role, is_active, email FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT') ORDER BY role";
    $stmt = $pdo->query($verifySql);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Imported accounts:\n\n";
    echo str_pad("Login ID", 15) . str_pad("Role", 15) . str_pad("Active", 8) . "Email\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($accounts as $account) {
        $active = $account['is_active'] ? 'Yes' : 'No';
        echo str_pad($account['login_id'], 15) . 
             str_pad($account['role'], 15) . 
             str_pad($active, 8) . 
             $account['email'] . "\n";
    }
    
    echo "\n";
    
    if (count($accounts) === 6) {
        echo "✅ ALL TEST CREDENTIALS IMPORTED SUCCESSFULLY\n\n";
        echo "Test Credentials:\n";
        echo str_repeat("-", 80) . "\n";
        echo "Role              | Login ID    | Password\n";
        echo str_repeat("-", 80) . "\n";
        echo "PRINCIPAL         | principal   | principal123\n";
        echo "VP                | vp          | vp123\n";
        echo "MANAGER           | manager     | manager123\n";
        echo "ACCOUNTANT        | accountant  | accountant123\n";
        echo "TEACHER           | teacher     | teacher123\n";
        echo "STUDENT           | student     | student123\n";
        echo str_repeat("-", 80) . "\n\n";
        echo "Next Steps:\n";
        echo "1. Clear browser cache: Ctrl+Shift+Delete\n";
        echo "2. Visit: http://localhost/IMS_FINAL/public/login\n";
        echo "3. Use any credentials above to test\n";
    } else {
        echo "⚠️ VERIFICATION FAILED\n";
        echo "Expected 6 accounts, found " . count($accounts) . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ DATABASE ERROR\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 80) . "\n\n";
