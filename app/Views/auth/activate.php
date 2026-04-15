<?php
$title = $title ?? 'Activate Account';
$token = $token ?? '';
$error = $error ?? null;
$success = $success ?? null;
$requiresPassword = $requires_password ?? true;
?>
<?php ob_start(); ?>
<form method="post" action="<?php echo e(url('activate/' . $token)); ?>">
    <?php echo csrf_field(); ?>

    <style>
        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .field input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--field-border);
            background: var(--field);
            color: var(--text);
            outline: none;
        }

        .field input:focus {
            border-color: rgba(47, 127, 135, 0.6);
            box-shadow: 0 0 0 4px rgba(47, 127, 135, 0.12);
        }

        .button {
            width: 100%;
            padding: 14px 18px;
            border: 0;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 16px 28px rgba(47, 127, 135, 0.24);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(239, 122, 122, 0.16);
            color: #9c2b2b;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }

        .back {
            display: inline-block;
            margin-top: 18px;
            color: var(--accent);
            text-decoration: none;
        }
    </style>

    <?php if ($error): ?>
        <div class="alert"><?php echo e($error); ?></div>
        <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
    <?php elseif ($success): ?>
        <div class="alert" style="background: rgba(34, 197, 94, 0.16); color: #166534;">
            <?php echo e($success); ?>
        </div>
        <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
    <?php elseif ($requiresPassword): ?>
        <div class="field">
            <label for="password">New Password</label>
            <input id="password" name="password" type="password" placeholder="Enter new password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm new password" required>
        </div>

        <button class="button" type="submit">Set Password</button>
        <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
    <?php else: ?>
        <button class="button" type="submit">Verify Email</button>
        <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
    <?php endif; ?>
</form>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
