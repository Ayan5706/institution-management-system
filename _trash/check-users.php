<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=ims_final;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SELECT id, email, login_id, role, is_active, password_hash FROM users ORDER BY id');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total Users: " . count($users) . "\n";
echo str_repeat('-', 80) . "\n";
echo sprintf("%-3s | %-25s | %-15s | %-10s | Active | Password Sample\n", 'ID', 'Email', 'Login ID', 'Role');
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    $active = $user['is_active'] ? 'Yes' : 'No';
    $pwdSample = substr($user['password_hash'] ?? '', 0, 20);
    echo sprintf("%-3d | %-25s | %-15s | %-10s | %-6s | %s\n", 
        $user['id'], 
        $user['email'] ?? 'NULL', 
        $user['login_id'] ?? 'NULL', 
        $user['role'] ?? 'NULL', 
        $active,
        $pwdSample
    );
}
