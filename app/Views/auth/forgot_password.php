<?php
$title = $title ?? 'Forgot Password';
$emailValue = $email ?? '';
$fieldError = $field_error ?? '';
?>
<?php ob_start(); ?>
<form method="post" action="<?php echo e(url('forgot-password')); ?>">
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

        .field .inline-error {
            margin-top: 8px;
            color: var(--danger);
            font-size: 0.85rem;
            font-weight: 600;
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

    <div class="field">
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" placeholder="Enter your Email Address" value="<?php echo e($emailValue); ?>" pattern="[A-Za-z0-9]+@gmail\.com" required>
        <div class="inline-error" id="emailError" role="alert">
            <?php echo e($fieldError); ?>
        </div>
    </div>

    <button class="button" type="submit">Send Reset Link</button>
    <a class="back" href="<?php echo e(url('login')); ?>">Back to login</a>
</form>
<script>
    (function () {
        const form = document.querySelector('form');
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('emailError');

        if (!form || !emailInput || !emailError) {
            return;
        }

        const showError = (message) => {
            emailError.textContent = message || '';
        };

        const validateEmail = () => {
            if (!emailInput.value.trim()) {
                showError('Email address is required.');
                return false;
            }

            const gmailPattern = /^[A-Za-z0-9]+@gmail\.com$/;
            if (!gmailPattern.test(emailInput.value.trim())) {
                showError('Please enter a valid Gmail address (example: name@gmail.com).');
                return false;
            }

            showError('');
            return true;
        };

        emailInput.addEventListener('input', () => {
            if (emailError.textContent) {
                validateEmail();
            }
        });

        emailInput.addEventListener('blur', validateEmail);

        form.addEventListener('submit', (event) => {
            if (!validateEmail()) {
                event.preventDefault();
            }
        });
    })();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
