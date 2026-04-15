<?php
$title = $title ?? 'Create User';
$activeNav = 'users';
$pageSubtitle = 'Register a new account for admin, teacher, or student access.';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">Create User</h2>
            <div style="color:#64748b;">Fill in account information to add a new user.</div>
        </div>
    </div>

    <?php require __DIR__ . DIRECTORY_SEPARATOR . 'form.php'; ?>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
