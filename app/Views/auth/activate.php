<?php
$title = $title ?? 'Activate Account';
$token = $token ?? '';
$error = $error ?? null;
$success = $success ?? null;
$requiresPassword = $requires_password ?? true;
$inlineError = null;

if ($requiresPassword) {
    $inlineError = $error;
    $error = null;
}
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

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
            transition: color 0.2s ease, transform 0.2s ease;
            border-radius: 8px;
        }

        .password-toggle:hover {
            color: var(--text);
            transform: scale(1.1);
        }

        .password-toggle:active {
            transform: scale(0.95);
        }

        .password-toggle:focus {
            outline: 2px solid rgba(47, 127, 135, 0.35);
            outline-offset: 2px;
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

        .field-error {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #9c2b2b;
        }

        .back {
            display: inline-block;
            margin-top: 18px;
            color: var(--accent);
            text-decoration: none;
        }
    </style>

    <?php if ($error && !$requiresPassword): ?>
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
            <div class="password-wrapper">
                <input id="password" name="password" type="password" placeholder="Enter new password" required>
                <button type="button" id="toggleNewPassword" class="password-toggle" aria-label="Show password" title="Show/Hide password">
                    <span class="toggle-icon">Show</span>
                </button>
            </div>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm new password" required>
            <?php if ($inlineError): ?>
                <div class="field-error"><?php echo e($inlineError); ?></div>
            <?php endif; ?>
        </div>

        <button class="button" type="submit">Set Password</button>
        <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
    <?php else: ?>
        <button class="button" type="submit">Verify Email</button>
        <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
    <?php endif; ?>
</form>

<script>
(() => {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('toggleNewPassword');
    const toggleIcon = toggleButton ? toggleButton.querySelector('.toggle-icon') : null;

    if (toggleButton && passwordInput && toggleIcon) {
        toggleButton.addEventListener('click', (event) => {
            event.preventDefault();
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.textContent = isPassword ? 'Hide' : 'Show';
            toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
            passwordInput.focus();
        });

        toggleButton.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleButton.click();
            }
        });
    }
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
