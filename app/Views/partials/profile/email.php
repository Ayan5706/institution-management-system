<?php
$otp_enabled = $otp_enabled ?? false;
$otp_pending = $otp_pending ?? false;
$otp_email = $otp_email ?? '';
$otp_expires_at = $otp_expires_at ?? '';
$otp_expiry_text = '';
if ($otp_pending && $otp_expires_at !== '') {
    $otp_expiry_text = 'OTP expires at ' . $otp_expires_at . ' UTC.';
}
$verifyDisabled = !empty($email_request_pending) || !$otp_pending;
?>

<div class="profile-card">
    <div class="profile-section-title">Change Email</div>

    <?php if (!empty($email_request_pending)): ?>
        <div class="success-message" style="display: block;">
            Your email change request is pending principal approval.
        </div>
    <?php endif; ?>

    <div id="email-success" class="success-message"></div>
    <div id="email-error" class="error-message"></div>

    <form id="email-form" method="post">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="current_email">Current Email</label>
            <input type="email" id="current_email" class="form-input" value="<?php echo e($user['email'] ?? ''); ?>" readonly>
        </div>

        <div class="form-group">
            <label class="form-label" for="new_email">New Email</label>
            <input type="email" id="new_email" name="new_email" class="form-input" placeholder="Enter new email" pattern="[A-Za-z0-9]+@gmail\.com" required value="<?php echo e($otp_email); ?>" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?> <?php echo $otp_pending ? 'readonly' : ''; ?>>
            <div class="inline-error" id="new-email-error" style="display: none;"></div>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_email">Confirm New Email</label>
            <input type="email" id="confirm_email" name="confirm_email" class="form-input" placeholder="Confirm new email" pattern="[A-Za-z0-9]+@gmail\.com" required value="<?php echo e($otp_email); ?>" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?> <?php echo $otp_pending ? 'readonly' : ''; ?>>
            <div class="inline-error" id="confirm-email-error" style="display: none;"></div>
        </div>

        <?php if ($otp_enabled): ?>
            <div id="otp-section" class="otp-section" style="display: <?php echo $otp_pending ? 'block' : 'none'; ?>;">
                <div class="form-group">
                    <label class="form-label" for="email_otp">OTP Code</label>
                    <input type="text" id="email_otp" name="email_otp" class="form-input" placeholder="Enter 6-digit OTP" inputmode="numeric" pattern="\d{6}" maxlength="6" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>>
                    <div class="inline-error" id="otp-error" style="display: none;"></div>
                    <?php if ($otp_expiry_text !== ''): ?>
                        <div class="inline-hint" id="otp-expiry-text"><?php echo e($otp_expiry_text); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <button type="submit" class="btn btn-primary" id="send-otp-btn" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>><?php echo $otp_enabled ? 'Send OTP' : 'Update Email'; ?></button>
            <?php if ($otp_enabled): ?>
                <button type="button" class="btn btn-primary" id="verify-otp-btn" <?php echo $verifyDisabled ? 'disabled' : ''; ?>>Verify OTP</button>
            <?php endif; ?>
            <button type="reset" class="btn btn-secondary" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>>Cancel</button>
        </div>
    </form>
</div>
