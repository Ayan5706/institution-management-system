<div class="profile-card">
    <div class="profile-section-title">Profile Information</div>

    <div class="profile-row">
        <div class="profile-label">Full Name</div>
        <div class="profile-value"><?php echo e($user['full_name'] ?? 'N/A'); ?></div>
    </div>

    <div class="profile-row">
        <div class="profile-label">Email</div>
        <div class="profile-value"><?php echo e($user['email'] ?? 'N/A'); ?></div>
    </div>

    <div class="profile-row">
        <div class="profile-label">Email Verification</div>
        <div class="profile-value">
            <?php echo !empty($email_verification_pending) ? 'Pending verification' : 'Verified'; ?>
        </div>
    </div>

    <div class="profile-row">
        <div class="profile-label">Phone Number</div>
        <div class="profile-value"><?php echo e($user['phone'] ?? 'N/A'); ?></div>
    </div>

    <div class="profile-row">
        <div class="profile-label">Role</div>
        <div class="profile-value"><?php echo e(ucfirst(strtolower((string) ($user['role'] ?? 'N/A')))); ?></div>
    </div>

    <div class="profile-row">
        <div class="profile-label">Account Status</div>
        <div class="profile-value"><?php echo ($user['is_active'] ?? false) ? 'Active' : 'Inactive'; ?></div>
    </div>

    <div class="profile-row">
        <div class="profile-label">Login ID</div>
        <div class="profile-value"><?php echo e($user['login_id'] ?? 'N/A'); ?></div>
    </div>
</div>
