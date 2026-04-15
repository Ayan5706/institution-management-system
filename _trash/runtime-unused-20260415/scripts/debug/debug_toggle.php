<?php
// Debug script to test the toggle functionality
require __DIR__ . '/config/database.php';

try {
    $db = new PDO('sqlite:' . DATABASE_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check users table structure
    echo "===== USERS TABLE STRUCTURE =====\n";
    $result = $db->query("PRAGMA table_info(users)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['name'] . " (" . $col['type'] . ")\n";
    }
    
    // Check if updated_at column exists
    echo "\n===== CHECKING UPDATED_AT COLUMN =====\n";
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'updated_at') {
            $hasColumn = true;
            echo "✓ updated_at column EXISTS\n";
            break;
        }
    }
    if (!$hasColumn) {
        echo "✗ updated_at column MISSING!\n";
    }
    
    // Get admin accounts
    echo "\n===== ADMIN ACCOUNTS =====\n";
    $stmt = $db->query("SELECT id, login_id, role, is_active FROM users WHERE role IN ('VP', 'MANAGER', 'ACCOUNTANT') LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
    
    // Test the update query on a test account
    if (!empty($results)) {
        echo "\n===== TESTING UPDATE QUERY =====\n";
        $testId = $results[0]['id'];
        $newStatus = $results[0]['is_active'] ? 0 : 1;
        
        // Try the same update as the controller does
        $updateStmt = $db->prepare('UPDATE `users` SET `is_active` = :is_active, `updated_at` = :updated_at WHERE `id` = :_id');
        $success = $updateStmt->execute([
            ':is_active' => $newStatus,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':_id' => $testId,
        ]);
        
        echo "Test update on user ID $testId: " . ($success ? "SUCCESS" : "FAILED") . "\n";
        echo "Rows affected: " . $updateStmt->rowCount() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
