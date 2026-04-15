<?php
/** @var array<string, mixed> $user */
$title = $title ?? 'User Details';
$activeNav = 'users';
$pageSubtitle = 'Review user account profile and access settings.';
$user = $user ?? [
    'id' => 1,
    'role' => 'admin',
    'login_id' => 'admin',
    'full_name' => 'System Admin',
    'email' => 'admin@example.test',
    'phone' => '09170000001',
    'is_active' => 1,
    'created_at' => '2026-04-11 10:00:00',
];
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">User Details</h2>
            <div style="color:#64748b;">Inspect one account in the system.</div>
        </div>
        <div>
            <a class="btn btn-ghost" href="<?php echo e(url('users/' . (string) ($user['id'] ?? 0) . '/edit')); ?>">Edit User</a>
        </div>
    </div>

    <style>
        .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .detail { padding: 16px; border-radius: 16px; background: #f8fbff; border: 1px solid #e2e8f0; }
        .detail label { display: block; color: #64748b; font-size: 0.88rem; margin-bottom: 6px; }
        .detail strong { color: #0f172a; font-size: 1.02rem; }
        @media (max-width: 720px) { .detail-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="detail-grid">
        <div class="detail"><label>ID</label><strong><?php echo e((string) ($user['id'] ?? '')); ?></strong></div>
        <div class="detail"><label>Role</label><strong><?php echo e((string) ($user['role'] ?? '')); ?></strong></div>
        <div class="detail"><label>Login ID</label><strong><?php echo e((string) ($user['login_id'] ?? '')); ?></strong></div>
        <div class="detail"><label>Full Name</label><strong><?php echo e((string) ($user['full_name'] ?? '')); ?></strong></div>
        <div class="detail"><label>Email</label><strong><?php echo e((string) ($user['email'] ?? '')); ?></strong></div>
        <div class="detail"><label>Phone</label><strong><?php echo e((string) ($user['phone'] ?? '-')); ?></strong></div>
        <div class="detail"><label>Status</label><strong><?php echo ((int) ($user['is_active'] ?? 0)) === 1 ? 'Active' : 'Inactive'; ?></strong></div>
        <div class="detail"><label>Created At</label><strong><?php echo e((string) ($user['created_at'] ?? '')); ?></strong></div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
