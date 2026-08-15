<?php
$title = $title ?? 'Reset Password';
$token = $token ?? '';
$message = $message ?? '';
$error = $error ?? '';
?>
<?php ob_start(); ?>
<form method="post" action="<?php echo e(url('reset-password')); ?>">
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

        .back {
            display: inline-block;
            margin-top: 18px;
            color: var(--accent);
            text-decoration: none;
        }
    </style>

    <input id="token" name="token" type="hidden" value="<?php echo e($token); ?>">

    <div class="field">
        <label for="password">New Password</label>
        <div class="password-wrapper">
            <input id="password" name="password" type="password" placeholder="Enter new password" required>
            <button type="button" id="togglePassword" class="password-toggle" aria-label="Toggle password visibility" title="Show/Hide password">
                <span class="toggle-icon">Show</span>
            </button>
        </div>
    </div>

    <div class="field">
        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm new password" required>
    </div>

    <button class="button" type="submit">Update Password</button>
    <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
</form>
<script>
(() => {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');
    const toggleIcon = document.querySelector('.toggle-icon');

    if (!toggleButton || !passwordInput) {
        return;
    }

    toggleButton.addEventListener('click', (e) => {
        e.preventDefault();

        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.textContent = isPassword ? 'Hide' : 'Show';
        toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        toggleButton.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
        passwordInput.focus();
    });

    toggleButton.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleButton.click();
        }
    });
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
