<?php
$title = $title ?? 'Verify Email';
?>
<?php ob_start(); ?>
<style>
    .message-container {
        padding: 20px;
        border-radius: 14px;
        margin-bottom: 18px;
        text-align: center;
    }

    .message {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #22c55e;
    }

    .error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
    }

    .back {
        display: inline-block;
        margin-top: 18px;
        color: var(--accent);
        text-decoration: none;
    }

    .back:hover {
        text-decoration: underline;
    }
</style>

<div class="message-container <?php echo isset($error) ? 'error' : 'message'; ?>">
    <?php if (isset($error)): ?>
        <strong>Error:</strong> <?php echo e($error); ?>
    <?php elseif (isset($message)): ?>
        <strong>Success:</strong> <?php echo e($message); ?>
    <?php endif; ?>
</div>

<a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
