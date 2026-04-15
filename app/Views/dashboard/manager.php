<?php
$redirectUrl = url('manager/dashboard');
?>
<?php ob_start(); ?>
<div class="card content-card" style="text-align: center;">
    <h2 style="margin-top: 0;">Manager Dashboard</h2>
    <p style="color: #64748b;">Redirecting you to the updated manager dashboard.</p>
    <script>
        window.location.href = '<?php echo e($redirectUrl); ?>';
    </script>
    <meta http-equiv="refresh" content="0; url=<?php echo e($redirectUrl); ?>">
    <a class="btn btn-primary" href="<?php echo e($redirectUrl); ?>">Open Dashboard</a>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';

