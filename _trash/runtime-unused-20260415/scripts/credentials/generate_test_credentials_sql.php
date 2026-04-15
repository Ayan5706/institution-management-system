<?php
/**
 * Generate SQL INSERT statements with hashed passwords
 * for test credentials
 */

$credentials = [
    ['login_id' => 'principal', 'full_name' => 'Principal Test Account', 'email' => 'principal@imsschool.local', 'phone' => '555-1001', 'password' => 'principal123', 'role' => 'PRINCIPAL'],
    ['login_id' => 'vp', 'full_name' => 'Vice Principal Test Account', 'email' => 'vp@imsschool.local', 'phone' => '555-1002', 'password' => 'vp123', 'role' => 'VP'],
    ['login_id' => 'manager', 'full_name' => 'Manager Test Account', 'email' => 'manager@imsschool.local', 'phone' => '555-1003', 'password' => 'manager123', 'role' => 'MANAGER'],
    ['login_id' => 'accountant', 'full_name' => 'Accountant Test Account', 'email' => 'accountant@imsschool.local', 'phone' => '555-1004', 'password' => 'accountant123', 'role' => 'ACCOUNTANT'],
    ['login_id' => 'teacher', 'full_name' => 'Teacher Test Account', 'email' => 'teacher@imsschool.local', 'phone' => '555-1005', 'password' => 'teacher123', 'role' => 'TEACHER'],
    ['login_id' => 'student', 'full_name' => 'Student Test Account', 'email' => 'student@imsschool.local', 'phone' => '555-1006', 'password' => 'student123', 'role' => 'STUDENT'],
];

// Generate SQL
$sql = "-- IMS Test Credentials SQL\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- This file contains INSERT statements for standardized test credentials\n\n";
$sql .= "-- BEFORE RUNNING:\n";
$sql .= "-- 1. Open phpMyAdmin\n";
$sql .= "-- 2. Select the IMS database\n";
$sql .= "-- 3. Go to the 'SQL' tab\n";
$sql .= "-- 4. Paste this entire script\n";
$sql .= "-- 5. Click 'Go'\n\n";
$sql .= "-- OPTIONAL: Clear existing test data first (if needed)\n";
$sql .= "-- DELETE FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT');\n\n";

$sql .= "-- Insert standardized test credentials\n";
$sql .= "INSERT INTO users (full_name, login_id, email, phone, password, role, is_active, created_at, updated_at) VALUES\n";

foreach ($credentials as $index => $cred) {
    $hash = password_hash($cred['password'], PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');
    
    $sql .= "(
  '" . addslashes($cred['full_name']) . "',
  '" . addslashes($cred['login_id']) . "',
  '" . addslashes($cred['email']) . "',
  '" . addslashes($cred['phone']) . "',
  '" . $hash . "',
  '" . $cred['role'] . "',
  1,
  '" . $now . "',
  '" . $now . "'
)";
    
    if ($index < count($credentials) - 1) {
        $sql .= ",\n";
    } else {
        $sql .= ";\n";
    }
}

$sql .= "\n-- Verification query (run after import):\n";
$sql .= "-- SELECT login_id, role, is_active, email FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT') ORDER BY role;\n\n";
$sql .= "-- Expected result: 6 rows with test credentials\n";

// Output
echo $sql;
