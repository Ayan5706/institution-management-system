<?php

declare(strict_types=1);

use App\Core\Database;

require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

$db = Database::connection();

$stmt = $db->prepare('
    SELECT u.id, u.login_id, sp.registration_number
    FROM users u
    INNER JOIN student_profiles sp ON sp.user_id = u.id
    WHERE u.role = "STUDENT" AND u.login_id <> sp.registration_number
');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
foreach ($rows as $row) {
    $update = $db->prepare('UPDATE users SET login_id = :login_id WHERE id = :id');
    $update->execute([
        'login_id' => $row['registration_number'],
        'id' => $row['id'],
    ]);
    $updated++;
}

echo "Normalized student login IDs: {$updated}" . PHP_EOL;
