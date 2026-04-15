<?php
/** @var array $user */
$activeNav = 'profile';
$user = $user ?? [];
$profile_action = url('teacher/profile');
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">My Profile</h2>
            <div style="color:#64748b;">Manage your profile details</div>
        </div>
        <a href="<?php echo e(url('teacher/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <?php partial('profile/styles'); ?>

    <div class="profile-container">
        <?php partial('profile/info', ['user' => $user]); ?>
        <?php partial('profile/edit', ['user' => $user]); ?>
        <?php partial('profile/password'); ?>
    </div>

    <?php partial('profile/scripts', ['profile_action' => $profile_action]); ?>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
