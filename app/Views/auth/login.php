<?php
$title = $title ?? 'Log in to your account';
$actionUrl = url('login');
?>
<?php ob_start(); ?>
<form id="loginForm" method="post" action="<?php echo e($actionUrl); ?>" novalidate>
    <?php echo csrf_field(); ?>

    <style>
        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--field-border);
            background: var(--field);
            color: var(--text);
            outline: none;
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
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

        .field input:focus,
        .field select:focus {
            border-color: rgba(47, 127, 135, 0.6);
            box-shadow: 0 0 0 4px rgba(47, 127, 135, 0.12);
        }

        .hint {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .password-meta {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            align-items: center;
            margin-top: 10px;
        }

        .forgot-link {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .button {
            width: 100%;
            padding: 14px 18px;
            border: 0;
            border-radius: 16px;
            cursor: pointer;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #ffffff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 16px 28px rgba(47, 127, 135, 0.24);
        }

        .button:disabled {
            opacity: 0.7;
            cursor: progress;
        }

        .subtext {
            margin-top: 16px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
            text-align: center;
        }
    </style>

    <div class="field">
        <label for="email">Login ID</label>
        <input id="email" name="email" type="text" value="<?php echo e((string) old('email', '')); ?>" placeholder="Login ID" autocomplete="username" required>
    </div>

    <div class="field">
        <label for="password">Password</label>
        <div class="password-wrapper">
            <input id="password" name="password" type="password" placeholder="Password" autocomplete="current-password" required>
            <button type="button" id="togglePassword" class="password-toggle" aria-label="Toggle password visibility" title="Show/Hide password">
                <span class="toggle-icon">Show</span>
            </button>
        </div>
        <div class="password-meta">
            <a class="forgot-link" href="<?php echo e(url('forgot-password')); ?>">Forget Password?</a>
        </div>
    </div>

    <button class="button" type="submit" id="loginButton">Log in</button>

    <div class="subtext">New to IMS? Contact Administrator</div>
</form>

<script>
(() => {
    const form = document.getElementById('loginForm');
    const flash = document.getElementById('authFlash');
    const button = document.getElementById('loginButton');
    const loginIdInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');
    const toggleIcon = document.querySelector('.toggle-icon');

    // Password visibility toggle
    if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', (e) => {
            e.preventDefault();
            
            const isPassword = passwordInput.type === 'password';
            
            // Toggle input type
            passwordInput.type = isPassword ? 'text' : 'password';
            
            // Update toggle text
            toggleIcon.textContent = isPassword ? 'Hide' : 'Show';
            
            // Update aria-label
            toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
            
            // Focus back to input
            passwordInput.focus();
        });

        // Allow Enter key to toggle off when focused on button
        toggleButton.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleButton.click();
            }
        });
    }

    if (!form) {
        return;
    }

    if (loginIdInput && passwordInput) {
        loginIdInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                passwordInput.focus();
            }
        });
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (flash) {
            flash.className = 'flash';
            flash.textContent = '';
        }

        button.disabled = true;
        button.textContent = 'Logging...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                const snippet = text.trim().slice(0, 160);
                throw new Error(snippet !== '' ? snippet : 'Unexpected server response.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Login failed');
            }

            const payload = data && data.data ? data.data : {};
            const user = payload.user ? payload.user : {};
            const mustChangePassword = Boolean(user.must_change_password ?? payload.must_change_password);

            // Redirect based on must_change_password first
            if (mustChangePassword) {
                window.location.href = '<?php echo e(url('change-password')); ?>';
                return;
            }

            // Redirect based on user role to role-specific dashboard
            const role = (user.role ?? payload.role ?? '').toString().toUpperCase();
            const roleDashboardMap = {
                'PRINCIPAL': '<?php echo e(url('principal/dashboard')); ?>',
                'VP':        '<?php echo e(url('vp/dashboard')); ?>',
                'MANAGER':   '<?php echo e(url('manager/dashboard')); ?>',
                'ACCOUNTANT':'<?php echo e(url('accountant/dashboard')); ?>',
                'TEACHER':   '<?php echo e(url('teacher/dashboard')); ?>',
                'STUDENT':   '<?php echo e(url('student/dashboard')); ?>',
            };
            window.location.href = roleDashboardMap[role] || '<?php echo e(url('dashboard')); ?>';
        } catch (error) {
            if (flash) {
                flash.className = 'flash error';
                flash.textContent = error.message || 'Unable to login';
            }
        } finally {
            button.disabled = false;
            button.textContent = 'Log in';
        }
    });
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'auth.php';
