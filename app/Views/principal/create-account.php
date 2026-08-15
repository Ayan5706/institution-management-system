<?php
/** @var string $title */
/** @var string $pageSubtitle */
$activeNav = 'accounts';
$title = $title ?? 'Create Admin Account';
$pageSubtitle = $pageSubtitle ?? 'Add new VP, Manager, or Accountant';
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;"><?php echo e($title); ?></h2>
            <div style="color:#64748b;"><?php echo e($pageSubtitle); ?></div>
        </div>
        <a class="btn-back" href="<?php echo e(url('principal/accounts')); ?>">← Back to Accounts</a>
    </div>

    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 20px;
        }

        .btn-back {
            padding: 10px 16px;
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-back:hover {
            background: #e2e8f0;
        }

        .form-container {
            max-width: 600px;
            background: #f8fbff;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input::placeholder {
            color: #cbd5e1;
        }

        .form-required {
            color: #dc2626;
        }

        .error-message {
            color: #991b1b;
            font-size: 0.85rem;
            margin-top: 4px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .error-input {
            border-color: #dc2626;
        }

        .success-message {
            padding: 12px;
            background: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 8px;
            color: #065f46;
            font-weight: 600;
            margin-bottom: 20px;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .notice-banner {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
        }

        .notice-banner.error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-submit {
            flex: 1;
            padding: 12px 20px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .btn-submit:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px 20px;
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .temp-password-message {
            padding: 16px;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            color: #92400e;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-top: 20px;
            display: none;
        }

        .temp-password-message.show {
            display: block;
        }

        .temp-password {
            font-weight: 700;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin: 10px 0;
            padding: 10px;
            background: #fff;
            border-radius: 4px;
            color: #0f172a;
        }
    </style>

    <div class="success-message" id="successMessage"></div>
    <div class="notice-banner error" id="createAccountMessage"></div>

    <div class="form-container">
        <form id="createAccountForm" onsubmit="handleSubmit(event)">
            <div class="form-group">
                <label class="form-label" for="roleSelect">
                    Role <span class="form-required">*</span>
                </label>
                <select class="form-select" id="roleSelect" name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="VP">Vice Principal (VP)</option>
                    <option value="MANAGER">Academic Manager</option>
                    <option value="ACCOUNTANT">Accountant</option>
                </select>
                <div class="error-message" id="roleError"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="loginId">
                    Login ID <span class="form-required">*</span>
                </label>
                <input type="text" class="form-input" id="loginId" name="login_id" placeholder="Select a role" readonly required>
                <div class="error-message" id="loginIdError"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="fullName">
                    Full Name <span class="form-required">*</span>
                </label>
                <input type="text" class="form-input" id="fullName" name="full_name" placeholder="e.g., John Doe" required>
                <div class="error-message" id="fullNameError"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">
                    Email <span class="form-required">*</span>
                </label>
                <input type="email" class="form-input" id="email" name="email" placeholder="e.g., john@example.com" required>
                <div class="error-message" id="emailError"></div>
            </div>

            <div class="form-actions">
                <a href="<?php echo e(url('principal/accounts')); ?>" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit" id="submitBtn">Create Account</button>
            </div>
        </form>
    </div>

    <div class="temp-password-message" id="tempPasswordMessage">
        <strong>Account Created Successfully!</strong>
        <p>Share this temporary password with the new user:</p>
        <div class="temp-password" id="tempPassword"></div>
        <p style="margin-bottom: 0;">The user will be required to change this password on their first login.</p>
    </div>
</div>

<script>
    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => {
            el.classList.remove('show');
            el.textContent = '';
        });
        document.querySelectorAll('.form-input, .form-select').forEach(el => {
            el.classList.remove('error-input');
        });
    }

    function validateFullName(value) {
        if (value === '') {
            return { valid: false, message: 'Full Name is required.' };
        }
        if (!/^[a-zA-Z\s]+$/.test(value)) {
            return { valid: false, message: 'Please enter your complete name (e.g., John Doe).' };
        }
        if (value.trim().length < 2) {
            return { valid: false, message: 'Full Name must be at least 2 characters.' };
        }
        return { valid: true, message: '' };
    }

    function validateEmail(value) {
        if (value === '') {
            return { valid: false, message: 'Email is required.' };
        }
        if (!/^[a-zA-Z0-9]+@gmail\.com$/i.test(value)) {
            return { valid: false, message: 'Please enter a valid email.' };
        }
        return { valid: true, message: '' };
    }

    function showFieldError(fieldName, message) {
        const errorEl = document.getElementById(fieldName + 'Error');
        const inputEl = document.getElementById(fieldName);
        
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }
        if (inputEl) {
            inputEl.classList.add('error-input');
        }
    }

    function clearFieldError(fieldName) {
        const errorEl = document.getElementById(fieldName + 'Error');
        const inputEl = document.getElementById(fieldName);
        
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('show');
        }
        if (inputEl) {
            inputEl.classList.remove('error-input');
        }
    }

    function validateField(fieldName) {
        const inputEl = document.getElementById(fieldName);
        if (!inputEl) return true;

        let validation;
        switch (fieldName) {
            case 'fullName':
                validation = validateFullName(inputEl.value);
                break;
            case 'email':
                validation = validateEmail(inputEl.value);
                break;
            default:
                return true;
        }

        if (validation.valid) {
            clearFieldError(fieldName);
        } else {
            showFieldError(fieldName, validation.message);
        }

        return validation.valid;
    }

    // Attach blur listeners for real-time validation
    document.addEventListener('DOMContentLoaded', function() {
        ['fullName', 'email'].forEach(fieldName => {
            const inputEl = document.getElementById(fieldName);
            if (inputEl) {
                inputEl.addEventListener('blur', function() {
                    validateField(fieldName);
                });
                inputEl.addEventListener('input', function() {
                    // Clear error on input if it was previously shown
                    if (this.classList.contains('error-input')) {
                        clearFieldError(fieldName);
                    }
                });
            }
        });
    });

    function handleSubmit(event) {
        event.preventDefault();
        clearErrors();
        clearCreateMessage();

        // Validate all fields before submitting
        let isFormValid = true;
        ['fullName', 'email'].forEach(fieldName => {
            if (!validateField(fieldName)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            return;
        }

        const form = document.getElementById('createAccountForm');
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';

        const formData = new FormData(form);

        fetch('<?php echo e(url('principal/accounts')); ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                full_name: formData.get('full_name'),
                email: formData.get('email'),
                role: formData.get('role')
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            if (data.success) {
                // Display success message
                document.getElementById('tempPassword').textContent = data.data.temp_password;
                document.getElementById('tempPasswordMessage').classList.add('show');
                clearCreateMessage();
                
                // Reset form
                form.reset();
                clearErrors();

                // Optionally redirect after delay
                setTimeout(() => {
                    window.location.href = '<?php echo e(url('principal/accounts')); ?>';
                }, 3000);
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorEl = document.getElementById(field + 'Error');
                        const inputEl = document.getElementById(field === 'loginId' ? 'loginId' : field === 'fullName' ? 'fullName' : field);
                        
                        if (errorEl) {
                            errorEl.textContent = data.errors[field];
                            errorEl.classList.add('show');
                        }
                        
                        if (inputEl) {
                            inputEl.classList.add('error-input');
                        }
                    });
                } else {
                    showCreateMessage(`Error: ${data.message || 'Failed to create account'}`);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCreateMessage(`Error creating account: ${error.message}`);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }

    function showCreateMessage(message) {
        const banner = document.getElementById('createAccountMessage');
        if (!banner) return;

        banner.textContent = message;
        banner.style.display = 'block';
    }

    function clearCreateMessage() {
        const banner = document.getElementById('createAccountMessage');
        if (!banner) return;

        banner.textContent = '';
        banner.style.display = 'none';
    }

    function refreshLoginId() {
        const roleSelect = document.getElementById('roleSelect');
        const loginIdInput = document.getElementById('loginId');
        if (!roleSelect || !loginIdInput) return;

        const role = roleSelect.value;
        if (!role) {
            loginIdInput.value = '';
            loginIdInput.placeholder = 'Select a role';
            return;
        }

        loginIdInput.value = 'Loading...';

        fetch(`<?php echo e(url('principal/accounts/next-login-id')); ?>?role=${encodeURIComponent(role)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.login_id) {
                loginIdInput.value = data.data.login_id;
            } else {
                loginIdInput.value = '';
                showCreateMessage(data.message || 'Unable to generate login ID.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loginIdInput.value = '';
            showCreateMessage(`Error generating login ID: ${error.message}`);
        });
    }

    document.getElementById('roleSelect')?.addEventListener('change', refreshLoginId);
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
