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
            <input type="email" id="new_email" name="new_email" class="form-input" placeholder="Enter new email" required <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_email">Confirm New Email</label>
            <input type="email" id="confirm_email" name="confirm_email" class="form-input" placeholder="Confirm new email" required <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>>Update Email</button>
            <button type="reset" class="btn btn-secondary" <?php echo !empty($email_request_pending) ? 'disabled' : ''; ?>>Cancel</button>
        </div>
    </form>
</div>
