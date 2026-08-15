<?php
$title = $title ?? 'Change Password';
$must_change_password = $must_change_password ?? false;
?>
<?php ob_start(); ?>
<form method="post" action="<?php echo e(url('change-password')); ?>">
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

        .toggle-icon {
            display: inline-block;
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

        .hint {
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.9rem;
        }
    </style>

    <?php if (!$must_change_password): ?>
        <div class="field">
            <label for="current_password">Current Password</label>
            <input id="current_password" name="current_password" type="password" placeholder="Enter current password" required>
        </div>
    <?php endif; ?>

    <div class="field">
        <label for="new_password">New Password</label>
        <div class="password-wrapper">
            <input id="new_password" name="new_password" type="password" placeholder="Enter new password" required>
            <button type="button" id="togglePassword" class="password-toggle" aria-label="Toggle password visibility" title="Show/Hide password">
                <span class="toggle-icon">Show</span>
            </button>
        </div>
    </div>

    <div class="field">
        <label for="confirm_password">Confirm New Password</label>
        <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm new password" required>
    </div>

    <button class="button" type="submit">Update Password</button>
    <p class="hint">Password must be at least 8 characters and contain at least one number.</p>
</form>

<script>
    (() => {
        const form = document.querySelector('form');
        const newPasswordInput = document.getElementById('new_password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.querySelector('.toggle-icon');

        // Password visibility toggle
        if (toggleButton && newPasswordInput) {
            toggleButton.addEventListener('click', (e) => {
                e.preventDefault();
                
                const isPassword = newPasswordInput.type === 'password';
                
                // Toggle input type
                newPasswordInput.type = isPassword ? 'text' : 'password';
                
                // Update toggle text
                toggleIcon.textContent = isPassword ? 'Hide' : 'Show';
                
                // Update aria-label
                toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                toggleButton.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
                
                // Focus back to input
                newPasswordInput.focus();
            });

            // Allow Enter key to toggle off when focused on button
            toggleButton.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleButton.click();
                }
            });
        }

        document.querySelector('form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    const form = document.querySelector('form');
                    form.innerHTML = `
                        <div style="
                            background: #d4edda;
                            border: 1px solid #c3e6cb;
                            color: #155724;
                            padding: 16px;
                            border-radius: 14px;
                            text-align: center;
                            font-weight: 500;
                        ">
                            ✓ ${data.message}
                        </div>
                        <p style="text-align: center; margin-top: 20px; color: var(--muted);">
                            Redirecting to dashboard...
                        </p>
                    `;
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '<?php echo e(url('dashboard')); ?>';
                    }, 2000);
                } else {
                    // Show error message
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    })();
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
