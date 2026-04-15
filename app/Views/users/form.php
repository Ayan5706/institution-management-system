<?php
/** @var array<string, mixed> $user */
/** @var string $action */
/** @var string $method */
$user = $user ?? [];
$action = $action ?? url('users');
$method = strtoupper((string) ($method ?? 'POST'));
$isEdit = $method !== 'POST';
?>
<style>
    .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .field { margin-bottom: 18px; }
    .field label { display: block; margin-bottom: 8px; color: #475569; font-size: 0.94rem; font-weight: 700; }
    .field input, .field select {
        width: 100%; padding: 14px 15px; border-radius: 14px; border: 1px solid #cbd5e1;
        background: #fff; color: #0f172a; outline: none;
    }
    .span-2 { grid-column: span 2; }
    .form-actions { display: flex; gap: 12px; justify-content: flex-end; flex-wrap: wrap; }
    @media (max-width: 720px) { .form-grid { grid-template-columns: 1fr; } .span-2 { grid-column: span 1; } }
</style>

<form method="post" action="<?php echo e($action); ?>">
    <?php echo csrf_field(); ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="form-grid">
        <div class="field">
            <label for="full_name">Full Name</label>
            <input id="full_name" name="full_name" type="text" value="<?php echo e((string) ($user['full_name'] ?? old('full_name', ''))); ?>" placeholder="Enter full name" required>
        </div>

        <div class="field">
            <label for="login_id">Login ID</label>
            <input id="login_id" name="login_id" type="text" value="<?php echo e((string) ($user['login_id'] ?? old('login_id', ''))); ?>" placeholder="e.g. teacher01" required>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?php echo e((string) ($user['email'] ?? old('email', ''))); ?>" placeholder="name@example.com" required>
        </div>

        <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="text" value="<?php echo e((string) ($user['phone'] ?? old('phone', ''))); ?>" placeholder="09xxxxxxxxx">
        </div>

        <div class="field">
            <label for="role">Role</label>
            <?php $selectedRole = (string) ($user['role'] ?? old('role', 'student')); ?>
            <select id="role" name="role">
                <option value="admin" <?php echo $selectedRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="teacher" <?php echo $selectedRole === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                <option value="student" <?php echo $selectedRole === 'student' ? 'selected' : ''; ?>>Student</option>
            </select>
        </div>

        <div class="field">
            <label for="is_active">Status</label>
            <?php $selectedActive = (string) ($user['is_active'] ?? old('is_active', '1')); ?>
            <select id="is_active" name="is_active">
                <option value="1" <?php echo $selectedActive === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $selectedActive === '0' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-ghost" href="<?php echo e(url('users')); ?>">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo $isEdit ? 'Update User' : 'Create User'; ?></button>
    </div>
</form>
