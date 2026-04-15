<?php
// Simple database test
try {
    // Database path from the application
    $dbPath = __DIR__ . '/storage/database/system.db';
    
    if (!file_exists($dbPath)) {
        die("Database file not found at: $dbPath\n");
    }
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "===== DATABASE CONNECTION SUCCESS =====\n\n";
    
    // Check users table structure
    echo "===== USERS TABLE COLUMNS =====\n";
    $result = $db->query("PRAGMA table_info(users)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['name'] . " (" . $col['type'] . ")\n";
    }
    
    // Check if updated_at column exists
    echo "\n===== UPDATED_AT COLUMN CHECK =====\n";
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'updated_at') {
            $hasColumn = true;
            echo "✓ Column EXISTS\n";
            break;
        }
    }
    if (!$hasColumn) {
        echo "✗ Column MISSING - This is likely the cause of the 500 error!\n";
        echo "\n===== FIX: Adding updated_at column =====\n";
        try {
            $db->exec("ALTER TABLE users ADD COLUMN updated_at DATETIME DEFAULT NULL");
            echo "✓ Column added successfully\n";
        } catch (Exception $e) {
            echo "Could not add column: " . $e->getMessage() . "\n";
        }
    }
    
    // Get an admin account
    echo "\n===== ADMIN ACCOUNTS =====\n";
    $stmt = $db->query("SELECT id, login_id, role, is_active FROM users WHERE role IN ('VP', 'MANAGER', 'ACCOUNTANT') LIMIT 1");
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($account) {
        echo "Found account: " . json_encode($account) . "\n";
        
        // Test the update
        echo "\n===== TESTING UPDATE =====\n";
        $newStatus = $account['is_active'] ? 0 : 1;
        
        $updateStmt = $db->prepare('UPDATE `users` SET `is_active` = :is_active, `updated_at` = :updated_at WHERE `id` = :_id');
        $success = $updateStmt->execute([
            ':is_active' => $newStatus,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':_id' => $account['id'],
        ]);
        
        echo "Update result: " . ($success ? "SUCCESS" : "FAILED") . "\n";
        echo "Rows affected: " . $updateStmt->rowCount() . "\n";
        
        // Verify the update
        $verify = $db->prepare("SELECT is_active, updated_at FROM users WHERE id = ?");
        $verify->execute([$account['id']]);
        $updated = $verify->fetch(PDO::FETCH_ASSOC);
        echo "Verification: " . json_encode($updated) . "\n";
    } else {
        echo "No admin accounts found\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
