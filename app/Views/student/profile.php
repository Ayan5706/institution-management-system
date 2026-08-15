<?php
/** @var array $user */
/** @var bool $profile_not_found */
$activeNav = 'profile';
$user = $user ?? [];
$emailRequestPending = $email_request_pending ?? false;
$emailVerificationPending = $email_verification_pending ?? false;
$emailVerificationEmail = $email_verification_email ?? '';
$emailVerificationExpiresAt = $email_verification_expires_at ?? '';
$emailOtpAction = url('student/profile/email/verify-otp');
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">My Profile</h2>
            <div style="color:#64748b;">Manage your profile details</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <style>
        .profile-container {
            max-width: 640px;
            margin: 20px 0;
        }

        .profile-card {
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .profile-section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .profile-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 16px;
            margin-bottom: 12px;
            align-items: center;
        }

        .profile-label {
            font-weight: 600;
            color: #0f172a;
        }

        .profile-value {
            color: #64748b;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #0f172a;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            background: #fff;
        }

        .form-input:focus {
            outline: 0;
            border-color: #2563eb;
            background: #f8fbff;
        }

        .form-input:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper .form-input {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            transition: color 0.2s ease, transform 0.2s ease;
            border-radius: 8px;
        }

        .password-toggle:hover {
            color: #0f172a;
            transform: scale(1.05);
        }

        .password-toggle:active {
            transform: scale(0.98);
        }

        .password-toggle:focus {
            outline: 2px solid rgba(37, 99, 235, 0.25);
            outline-offset: 2px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #0d9488);
            color: #fff;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-secondary {
            background: #fff;
            color: #0f172a;
            border: 1px solid #dbe4f0;
        }

        .btn-secondary:hover {
            background: #f8fbff;
            border-color: #2563eb;
        }

        .success-message {
            padding: 12px 16px;
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            color: #065f46;
            display: none;
        }

        .error-message {
            padding: 12px 16px;
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            color: #7f1d1d;
            display: none;
        }

        .inline-error {
            margin-top: 6px;
            font-size: 0.85rem;
            color: #dc2626;
            font-weight: 600;
        }

        .inline-hint {
            margin-top: 6px;
            font-size: 0.8rem;
            color: #64748b;
        }

        .otp-section {
            margin-top: 12px;
            padding: 12px;
            background: #eef2ff;
            border: 1px dashed #c7d2fe;
            border-radius: 10px;
        }

        @media (max-width: 640px) {
            .profile-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>

    <div class="profile-container">
        <?php if ($profile_not_found): ?>
            <div class="notice-warning">
                <strong>⚠️ Profile Setup Needed</strong><br>
                Your student profile hasn''t been set up yet. Please contact your institution to complete your enrollment.
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-section-title">Profile Information</div>

            <div class="profile-row">
                <div class="profile-label">Full Name</div>
                <div class="profile-value"><?php echo e($user['full_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Email</div>
                <div class="profile-value"><?php echo e($user['email'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Father Name</div>
                <div class="profile-value"><?php echo e($student_profile['father_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Date of Birth</div>
                <div class="profile-value"><?php echo e($student_profile['date_of_birth'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Email Verification</div>
                <div class="profile-value">
                    <?php echo !empty($emailVerificationPending) ? 'Pending verification' : 'Verified'; ?>
                </div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Phone Number</div>
                <div class="profile-value"><?php echo e($user['phone'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Role</div>
                <div class="profile-value"><?php echo e(ucfirst(strtolower((string) ($user['role'] ?? 'N/A')))); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Account Status</div>
                <div class="profile-value"><?php echo ($user['is_active'] ?? false) ? 'Active' : 'Inactive'; ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Login ID</div>
                <div class="profile-value"><?php echo e($user['login_id'] ?? 'N/A'); ?></div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-section-title">Edit Contact Details</div>

            <div id="profile-success" class="success-message">Profile updated successfully.</div>
            <div id="profile-error" class="error-message"></div>

            <form id="profile-form" method="post">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-input" value="<?php echo e($user['full_name'] ?? ''); ?>" required>
                    <div id="full-name-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="father_name">Father Name</label>
                    <input type="text" id="father_name" name="father_name" class="form-input" value="<?php echo e($student_profile['father_name'] ?? ''); ?>" required>
                    <div id="father-name-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo e($user['phone'] ?? ''); ?>">
                       <div id="phone-error" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?php echo e($student_profile['date_of_birth'] ?? ''); ?>" required>
                    <div id="dob-error" class="error-message"></div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="reset" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>

        <?php
        $otp_expiry_text = '';
        if (!empty($emailVerificationPending) && $emailVerificationExpiresAt !== '') {
            $otp_expiry_text = 'OTP expires at ' . $emailVerificationExpiresAt . ' UTC.';
        }
        $verifyDisabled = !empty($emailRequestPending) || empty($emailVerificationPending);
        ?>

        <div class="profile-card">
            <div class="profile-section-title">Change Email</div>

            <?php if (!empty($emailRequestPending)): ?>
                <div class="success-message" style="display: block;">
                    Your email change request is pending principal approval.
                </div>
            <?php endif; ?>

            <div id="email-success" class="success-message"></div>
            <div id="email-error" class="error-message"></div>

            <form id="email-form" method="post">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label class="form-label" for="current_email">Current Email</label>
                    <input type="email" id="current_email" class="form-input" value="<?php echo e($user['email'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_email">New Email</label>
                    <input type="email" id="new_email" name="new_email" class="form-input" placeholder="Enter new email" required value="<?php echo e($emailVerificationEmail); ?>" <?php echo !empty($emailRequestPending) ? 'disabled' : ''; ?> <?php echo !empty($emailVerificationPending) ? 'readonly' : ''; ?>>
                    <div class="inline-error" id="new-email-error" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_email">Confirm New Email</label>
                    <input type="email" id="confirm_email" name="confirm_email" class="form-input" placeholder="Confirm new email" required value="<?php echo e($emailVerificationEmail); ?>" <?php echo !empty($emailRequestPending) ? 'disabled' : ''; ?> <?php echo !empty($emailVerificationPending) ? 'readonly' : ''; ?>>
                    <div class="inline-error" id="confirm-email-error" style="display: none;"></div>
                </div>

                <div id="otp-section" class="otp-section" style="display: <?php echo !empty($emailVerificationPending) ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="email_otp">OTP Code</label>
                        <input type="text" id="email_otp" name="email_otp" class="form-input" placeholder="Enter 6-digit OTP" inputmode="numeric" pattern="\d{6}" maxlength="6" <?php echo !empty($emailRequestPending) ? 'disabled' : ''; ?>>
                        <div class="inline-error" id="otp-error" style="display: none;"></div>
                        <?php if ($otp_expiry_text !== ''): ?>
                            <div class="inline-hint" id="otp-expiry-text"><?php echo e($otp_expiry_text); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" id="send-otp-btn" <?php echo !empty($emailRequestPending) ? 'disabled' : ''; ?>>Send OTP</button>
                    <button type="button" class="btn btn-primary" id="verify-otp-btn" <?php echo $verifyDisabled ? 'disabled' : ''; ?>>Verify OTP</button>
                    <button type="reset" class="btn btn-secondary" <?php echo !empty($emailRequestPending) ? 'disabled' : ''; ?>>Cancel</button>
                </div>
            </form>
        </div>

        <div class="profile-card">
            <div class="profile-section-title">Change Password</div>

            <div id="password-success" class="success-message">Password changed successfully.</div>
            <div id="password-error" class="error-message"></div>

            <form id="password-form" method="post">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                        <button type="button" class="password-toggle" data-target="current_password" aria-label="Show password" title="Show password">
                            <span class="toggle-icon">Show</span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-input" required>
                        <button type="button" class="password-toggle" data-target="new_password" aria-label="Show password" title="Show password">
                            <span class="toggle-icon">Show</span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const profileForm = document.getElementById('profile-form');
        const profileSuccess = document.getElementById('profile-success');
        const profileError = document.getElementById('profile-error');
        const fullNameInput = document.getElementById('full_name');
        const fullNameError = document.getElementById('full-name-error');
        const fatherNameInput = document.getElementById('father_name');
        const fatherNameError = document.getElementById('father-name-error');
        const phoneInput = document.getElementById('phone');
        const phoneError = document.getElementById('phone-error');
        const dobInput = document.getElementById('date_of_birth');
        const dobError = document.getElementById('dob-error');
        const emailForm = document.getElementById('email-form');
        const emailSuccess = document.getElementById('email-success');
        const emailError = document.getElementById('email-error');
        const emailOtpAction = '<?php echo e($emailOtpAction ?? ''); ?>';
        const passwordForm = document.getElementById('password-form');
        const passwordSuccess = document.getElementById('password-success');
        const passwordError = document.getElementById('password-error');

        function showMessage(container, message, isSuccess) {
            container.textContent = message;
            container.style.display = 'block';

            if (isSuccess) {
                setTimeout(() => {
                    container.style.display = 'none';
                }, 3000);
            }
        }

        function clearMessages() {
            profileSuccess.style.display = 'none';
            profileError.style.display = 'none';
            if (fullNameError) {
                fullNameError.style.display = 'none';
                fullNameError.textContent = '';
            }
            if (fatherNameError) {
                fatherNameError.style.display = 'none';
                fatherNameError.textContent = '';
            }
               if (phoneError) {
                   phoneError.style.display = 'none';
                   phoneError.textContent = '';
               }
            if (dobError) {
                dobError.style.display = 'none';
                dobError.textContent = '';
            }
            if (emailSuccess) {
                emailSuccess.style.display = 'none';
            }
            if (emailError) {
                emailError.style.display = 'none';
            }
            const newEmailError = document.getElementById('new-email-error');
            const confirmEmailError = document.getElementById('confirm-email-error');
            const otpError = document.getElementById('otp-error');
            if (newEmailError) {
                newEmailError.style.display = 'none';
                newEmailError.textContent = '';
            }
            if (confirmEmailError) {
                confirmEmailError.style.display = 'none';
                confirmEmailError.textContent = '';
            }
            if (otpError) {
                otpError.style.display = 'none';
                otpError.textContent = '';
            }
            passwordSuccess.style.display = 'none';
            passwordError.style.display = 'none';
        }

        function initPasswordToggles() {
            const toggles = document.querySelectorAll('.password-toggle[data-target]');

            toggles.forEach((toggle) => {
                const targetId = toggle.getAttribute('data-target');
                const input = targetId ? document.getElementById(targetId) : null;
                const icon = toggle.querySelector('.toggle-icon');

                if (!input) {
                    return;
                }

                const updateState = (isPassword) => {
                    const label = isPassword ? 'Show password' : 'Hide password';
                    if (icon) {
                        icon.textContent = isPassword ? 'Show' : 'Hide';
                    }
                    toggle.setAttribute('aria-label', label);
                    toggle.setAttribute('title', label);
                };

                updateState(input.type === 'password');

                toggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    updateState(!isPassword);
                    input.focus();
                });

                toggle.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggle.click();
                    }
                });
            });
        }

        initPasswordToggles();

        function validatePhoneField(value) {
            if (value !== '' && !/^\d{10}$/.test(value)) {
                if (phoneError) {
                    showMessage(phoneError, 'Phone number must be exactly 10 digits', false);
                }
                return false;
            }
            if (phoneError) {
                phoneError.style.display = 'none';
                phoneError.textContent = '';
            }
            return true;
        }

        if (phoneInput) {
            phoneInput.addEventListener('input', () => {
                const value = phoneInput.value.trim();
                if (value === '') {
                    if (phoneError) {
                        phoneError.style.display = 'none';
                        phoneError.textContent = '';
                    }
                    return;
                }
                validatePhoneField(value);
            });
        }

        function validateDateField(value) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return false;
            }
            const parsed = new Date(value + 'T00:00:00');
            return !Number.isNaN(parsed.getTime());
        }

        profileForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearMessages();

            const formData = new FormData(profileForm);
            const fullName = (formData.get('full_name') || '').toString().trim();
            const fatherName = (formData.get('father_name') || '').toString().trim();
               const phone = (formData.get('phone') || '').toString().trim();
            const dateOfBirth = (formData.get('date_of_birth') || '').toString().trim();

            if (fullName === '') {
                if (fullNameError) {
                    showMessage(fullNameError, 'Full name is required.', false);
                }
                return;
            }

            if (!/^[A-Za-z\s]+$/.test(fullName)) {
                if (fullNameError) {
                    showMessage(fullNameError, 'Full name must contain letters only.', false);
                }
                return;
            }

            if (fatherName === '') {
                if (fatherNameError) {
                    showMessage(fatherNameError, 'Father name is required.', false);
                }
                return;
            }

            if (!/^[A-Za-z\s\.]+$/.test(fatherName)) {
                if (fatherNameError) {
                    showMessage(fatherNameError, 'Father name must contain letters only.', false);
                }
                return;
            }

               if (!validatePhoneField(phone)) {
                   return;
               }

            if (dateOfBirth === '') {
                if (dobError) {
                    showMessage(dobError, 'Date of birth is required.', false);
                }
                return;
            }

            if (!validateDateField(dateOfBirth)) {
                if (dobError) {
                    showMessage(dobError, 'Date must be in YYYY-MM-DD format.', false);
                }
                return;
            }

            try {
                const response = await fetch('<?php echo e(url('student/profile')); ?>', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(profileSuccess, result.message || 'Profile updated successfully.', true);
                } else {
                    showMessage(profileError, result.message || 'Unable to update profile.', false);
                }
            } catch (error) {
                showMessage(profileError, 'An error occurred. Please try again.', false);
            }
        });

        if (emailForm) {
            const newEmailInput = document.getElementById('new_email');
            const confirmEmailInput = document.getElementById('confirm_email');
            const newEmailError = document.getElementById('new-email-error');
            const confirmEmailError = document.getElementById('confirm-email-error');
            const otpSection = document.getElementById('otp-section');
            const otpInput = document.getElementById('email_otp');
            const otpError = document.getElementById('otp-error');
            const verifyOtpButton = document.getElementById('verify-otp-btn');

            const setEmailInputsReadOnly = (isReadOnly) => {
                if (newEmailInput) {
                    newEmailInput.readOnly = isReadOnly;
                }
                if (confirmEmailInput) {
                    confirmEmailInput.readOnly = isReadOnly;
                }
            };

            if (otpSection && otpSection.style.display !== 'none') {
                setEmailInputsReadOnly(true);
                if (verifyOtpButton) {
                    verifyOtpButton.disabled = false;
                }
            }

            emailForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearMessages();

                const formData = new FormData(emailForm);
                const newEmail = (formData.get('new_email') || '').toString().trim();
                const confirmEmail = (formData.get('confirm_email') || '').toString().trim();

                if (newEmail === '' || confirmEmail === '') {
                    showMessage(emailError, 'New email and confirmation are required.', false);
                    return;
                }

                // Validate email format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(newEmail)) {
                    if (newEmailError) {
                        newEmailError.textContent = 'Please enter a valid email.';
                        newEmailError.style.display = 'block';
                    }
                    showMessage(emailError, 'Please enter a valid email', false);
                    return;
                }

                if (!emailRegex.test(confirmEmail)) {
                    if (confirmEmailError) {
                        confirmEmailError.textContent = 'Please enter a valid email.';
                        confirmEmailError.style.display = 'block';
                    }
                    showMessage(emailError, 'Please enter a valid email', false);
                    return;
                }

                if (newEmail.toLowerCase() !== confirmEmail.toLowerCase()) {
                    showMessage(emailError, 'Email confirmation does not match.', false);
                    return;
                }

                if (otpSection) {
                    try {
                        const response = await fetch('<?php echo e(url('student/profile/email')); ?>', {
                            method: 'POST',
                            body: formData,
                        });

                        const result = await response.json();

                        if (result.success) {
                            if (emailSuccess) {
                                showMessage(emailSuccess, result.message || 'OTP sent to your new email.', true);
                            }
                            otpSection.style.display = 'block';
                            setEmailInputsReadOnly(true);
                            if (verifyOtpButton) {
                                verifyOtpButton.disabled = false;
                            }
                        } else {
                            showMessage(emailError, result.message || 'Unable to send OTP.', false);
                        }
                    } catch (error) {
                        showMessage(emailError, 'An error occurred. Please try again.', false);
                    }
                    return;
                }
            });

            if (verifyOtpButton && otpSection && otpInput) {
                verifyOtpButton.addEventListener('click', async () => {
                    clearMessages();

                    const otpValue = otpInput.value.trim();
                    if (!/^[0-9]{6}$/.test(otpValue)) {
                        if (otpError) {
                            otpError.textContent = 'OTP must be a 6-digit code.';
                            otpError.style.display = 'block';
                        }
                        return;
                    }

                    const formData = new FormData(emailForm);
                    formData.set('email_otp', otpValue);

                    try {
                        const response = await fetch(emailOtpAction, {
                            method: 'POST',
                            body: formData,
                        });

                        const result = await response.json();

                        if (result.success) {
                            emailForm.reset();
                            otpSection.style.display = 'none';
                            setEmailInputsReadOnly(false);
                            if (emailSuccess) {
                                showMessage(emailSuccess, result.message || 'Email request submitted for approval.', true);
                            }
                        } else {
                            showMessage(emailError, result.message || 'Unable to verify OTP.', false);
                        }
                    } catch (error) {
                        showMessage(emailError, 'An error occurred. Please try again.', false);
                    }
                });
            }

            emailForm.addEventListener('reset', () => {
                if (otpSection) {
                    otpSection.style.display = 'none';
                }
                if (otpInput) {
                    otpInput.value = '';
                }
                setEmailInputsReadOnly(false);
                if (verifyOtpButton) {
                    verifyOtpButton.disabled = true;
                }
            });
        }

        passwordForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearMessages();

            const formData = new FormData(passwordForm);

            try {
                const response = await fetch('<?php echo e(url('change-password')); ?>', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    passwordForm.reset();
                    showMessage(passwordSuccess, result.message || 'Password changed successfully.', true);
                } else {
                    showMessage(passwordError, result.message || 'Unable to change password.', false);
                }
            } catch (error) {
                showMessage(passwordError, 'An error occurred. Please try again.', false);
            }
        });
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
