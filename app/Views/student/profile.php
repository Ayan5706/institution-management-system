<?php
/** @var array $user */
/** @var bool $profile_not_found */
$activeNav = 'profile';
$user = $user ?? [];
$profile_not_found = $profile_not_found ?? false;
$profile_action = url('student/profile');
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

    <?php partial('profile/styles'); ?>

    <div class="profile-container">
        <?php partial('profile/warning', [
            'show_warning' => $profile_not_found,
            'warning_text' => 'Your student profile has not been set up yet. Please contact your institution to complete your enrollment.',
        ]); ?>

        <?php partial('profile/info', ['user' => $user]); ?>
        <?php partial('profile/edit', ['user' => $user]); ?>
        <?php partial('profile/password'); ?>
    </div>

    <?php partial('profile/scripts', ['profile_action' => $profile_action]); ?>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
<?php
/** @var array $user */
/** @var bool $profile_not_found */
$activeNav = 'profile';
$user = $user ?? [];
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
                Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment.
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
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="reset" class="btn btn-secondary">Cancel</button>
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
                    <input type="password" id="current_password" name="current_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required>
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
            passwordSuccess.style.display = 'none';
            passwordError.style.display = 'none';
        }

        profileForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearMessages();

            const formData = new FormData(profileForm);

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
<?php
/** @var array $user */
/** @var array $student_profile */
/** @var array|null $program */
/** @var bool $profile_not_found */
$activeNav = 'profile';
$user = $user ?? [];
$student_profile = $student_profile ?? [];
$program = $program ?? null;
$profile_not_found = $profile_not_found ?? false;
?>
<?php ob_start(); ?>
<div class="card content-card">
    <div class="toolbar">
        <div>
            <h2 style="margin:0 0 6px;">My Profile</h2>
            <div style="color:#64748b;">Update your personal information</div>
        </div>
        <a href="<?php echo e(url('student/dashboard')); ?>" class="btn btn-ghost">Back to Dashboard</a>
    </div>

    <style>
        .profile-container {
            max-width: 600px;
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
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .profile-row {
            display: grid;
            grid-template-columns: 150px 1fr;
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

        .info-banner {
            padding: 12px 16px;
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            color: #0c4a6e;
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

        .notice-banner {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            display: none;
        }

        .notice-banner.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .notice-banner.error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            align-items: center;
            justify-content: center;
            z-index: 1200;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-card {
            width: min(420px, 92vw);
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
        }

        .modal-title {
            margin: 0 0 8px;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .modal-text {
            margin: 0 0 16px;
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
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
        <!-- Profile Not Found Warning -->
        <?php if ($profile_not_found): ?>
            <div class="notice-warning">
                <strong>⚠️ Profile Setup Needed</strong><br>
                Your student profile hasn't been set up yet. Please contact your institution to complete your enrollment. Academic information will appear here once your profile is created.
            </div>
        <?php endif; ?>

        <!-- Profile Information (Read-only) -->
        <div class="profile-card">
            <div class="profile-section-title">Account Information</div>
            
            <div class="profile-row">
                <div class="profile-label">Login ID:</div>
                <div class="profile-value"><?php echo e($user['login_id'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Full Name:</div>
                <div class="profile-value"><?php echo e($user['full_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Role:</div>
                <div class="profile-value" style="text-transform: capitalize;"><?php echo e($user['role'] ?? 'N/A'); ?></div>
            </div>
        </div>

        <!-- Student Information (Read-only) -->
        <div class="profile-card">
            <div class="profile-section-title">Academic Information</div>
            
            <div class="profile-row">
                <div class="profile-label">Registration #:</div>
                <div class="profile-value"><?php echo e($student_profile['registration_number'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">Program:</div>
                <div class="profile-value"><?php echo e($program['program_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="profile-row">
                <div class="profile-label">DOB:</div>
                <div class="profile-value"><?php echo e($student_profile['date_of_birth'] ?? 'N/A'); ?></div>
            </div>
        </div>

        <!-- Editable Contact Information -->
        <div class="profile-card">
            <div class="profile-section-title">Contact Information (Editable)</div>

            <div id="profile-message" class="notice-banner"></div>

            <form id="profile-form">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input" placeholder="Enter phone number" value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="reset" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Password Management -->
        <div class="profile-card">
            <div class="profile-section-title">Password Management</div>

            <div class="info-banner">
                To change your password, request a password reset. Your manager will be notified for approval.
            </div>

            <button type="button" class="btn btn-primary" onclick="requestPasswordReset()">Request Password Reset</button>
        </div>
    </div>

    <div id="confirmModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <h3 class="modal-title" id="confirmTitle">Confirm Request</h3>
            <p class="modal-text" id="confirmText">Are you sure you want to request a password reset?</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('<?php echo e(url('api/student/profile')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Profile updated successfully.', 'success');
                } else {
                    showMessage('Error: ' + (result.message || 'Unable to update profile.'), 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
                console.error(error);
            }
        });

        function requestPasswordReset() {
            openConfirmModal(
                'Request Password Reset',
                'Request password reset? Your manager will be notified to approve this request.',
                () => {
                    fetch('<?php echo e(url('api/student/password-reset-request')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            showMessage(result.message || 'Password reset request submitted.', 'success');
                        } else {
                            showMessage('Error: ' + (result.message || 'Request failed.'), 'error');
                        }
                    })
                    .catch(error => {
                        showMessage('An error occurred. Please try again.', 'error');
                        console.error(error);
                    });
                }
            );
        }

        function showMessage(message, type) {
            const banner = document.getElementById('profile-message');
            if (!banner) return;

            banner.textContent = message;
            banner.classList.remove('success', 'error');
            banner.classList.add(type === 'error' ? 'error' : 'success');
            banner.style.display = 'block';

            if (type !== 'error') {
                setTimeout(() => {
                    banner.style.display = 'none';
                }, 3000);
            }
        }

        let confirmAction = null;

        function openConfirmModal(title, text, action) {
            const modal = document.getElementById('confirmModal');
            const titleEl = document.getElementById('confirmTitle');
            const textEl = document.getElementById('confirmText');
            const confirmBtn = document.getElementById('confirmButton');

            confirmAction = action;
            titleEl.textContent = title;
            textEl.textContent = text;
            confirmBtn.onclick = () => {
                if (confirmAction) {
                    confirmAction();
                }
                closeConfirmModal();
            };
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            confirmAction = null;
        }
    </script>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
?>
