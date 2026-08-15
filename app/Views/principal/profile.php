<?php
/** @var array $user */
$activeNav = 'principal/profile';
$user = $user ?? [];
$profile_action = url('principal/profile');
$email_action = url('principal/profile/email');
$email_otp_action = url('principal/profile/email/verify-otp');
$emailVerificationPending = $email_verification_pending ?? false;
$emailVerificationEmail = $email_verification_email ?? '';
$emailVerificationExpiresAt = $email_verification_expires_at ?? '';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">My Profile</h2>
            <div style="color:#64748b;">Manage your profile details</div>
        </div>
        <a href="<?php echo e(url('principal/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <?php partial('profile/styles'); ?>

    <div class="profile-container">
        <?php partial('profile/info', ['user' => $user, 'email_verification_pending' => $emailVerificationPending]); ?>
        <?php partial('profile/edit', ['user' => $user]); ?>
        <?php partial('profile/email', [
            'user' => $user,
            'email_request_pending' => false,
            'otp_enabled' => true,
            'otp_pending' => $emailVerificationPending,
            'otp_email' => $emailVerificationEmail,
            'otp_expires_at' => $emailVerificationExpiresAt,
        ]); ?>
        <?php partial('profile/password'); ?>
    </div>

    <?php partial('profile/scripts', [
        'profile_action' => $profile_action,
        'email_action' => $email_action,
        'email_otp_action' => $email_otp_action,
    ]); ?>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
