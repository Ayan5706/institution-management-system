<div class="profile-card">
    <div class="profile-section-title">Edit Contact Details</div>

    <div id="profile-success" class="success-message">Profile updated successfully.</div>
    <div id="profile-error" class="error-message"></div>

    <form id="profile-form" method="post">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-input" value="<?php echo e($user['full_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo e($user['phone'] ?? ''); ?>">
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="reset" class="btn btn-secondary">Cancel</button>
        </div>
    </form>
</div>
