<?php
// Check what users exist in the database
require_once dirname(__DIR__) . '/bootstrap/app.php';

$db = \App\Core\Database::connection();
$stmt = $db->prepare('SELECT id, email, login_id, role, full_name FROM users LIMIT 20');
$stmt->execute();
$users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

echo "Users in database:\n";
echo json_encode($users, JSON_PRETTY_PRINT) . "\n";
