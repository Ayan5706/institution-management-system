<div class="profile-card">
    <div class="profile-section-title">Change Password</div>

    <div id="password-success" class="success-message">Password changed successfully.</div>
    <div id="password-error" class="error-message"></div>

    <form id="password-form" method="post">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="current_password">Current Password</label>
            <div class="password-wrapper">
                <input type="password" id="current_password" name="current_password" class="form-input" required>
                <button type="button" class="password-toggle" data-target="current_password" aria-label="Show password" title="Show password">
                    <span class="toggle-icon">Show</span>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="new_password">New Password</label>
            <div class="password-wrapper">
                <input type="password" id="new_password" name="new_password" class="form-input" required>
                <button type="button" class="password-toggle" data-target="new_password" aria-label="Show password" title="Show password">
                    <span class="toggle-icon">Show</span>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
    </form>
</div>
