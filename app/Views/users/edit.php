<?php
$title = $title ?? 'Edit User';
$activeNav = 'users';
$pageSubtitle = 'Update account details and role assignments.';
$user = $user ?? [
    'id' => 2,
    'role' => 'teacher',
    'login_id' => 'teacher01',
    'full_name' => 'Juan Dela Cruz',
    'email' => 'juan@example.test',
    'phone' => '09170000021',
    'is_active' => '1',
];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Edit User</h2>
            <div style="color:#64748b;">Modify the selected account information.</div>
        </div>
    </div>

    <?php
    $action = url('users/' . (string) ($user['id'] ?? 0));
    $method = 'PUT';
    require __DIR__ . DIRECTORY_SEPARATOR . 'form.php';
    ?>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
