<?php if (!empty($show_warning)): ?>
    <div class="notice-warning">
        <strong>⚠️ Profile Setup Needed</strong><br>
        <?php echo e((string) ($warning_text ?? 'Your profile has not been set up yet.')); ?>
    </div>
<?php endif; ?>
