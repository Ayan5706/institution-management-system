<script>
    const profileForm = document.getElementById('profile-form');
    const profileSuccess = document.getElementById('profile-success');
    const profileError = document.getElementById('profile-error');
    const emailForm = document.getElementById('email-form');
    const emailSuccess = document.getElementById('email-success');
    const emailError = document.getElementById('email-error');
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
        if (emailError) {
            emailError.style.display = 'none';
        }
        if (emailSuccess) {
            emailSuccess.style.display = 'none';
        }
        passwordSuccess.style.display = 'none';
        passwordError.style.display = 'none';
    }

    profileForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessages();

        const formData = new FormData(profileForm);

        try {
            const response = await fetch('<?php echo e($profile_action ?? ''); ?>', {
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

            if (newEmail.toLowerCase() !== confirmEmail.toLowerCase()) {
                showMessage(emailError, 'Email confirmation does not match.', false);
                return;
            }

            try {
                const response = await fetch('<?php echo e($email_action ?? ''); ?>', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    emailForm.reset();
                    if (emailSuccess) {
                        showMessage(emailSuccess, result.message || 'Email request submitted for approval.', true);
                    }
                } else {
                    showMessage(emailError, result.message || 'Unable to update email.', false);
                }
            } catch (error) {
                showMessage(emailError, 'An error occurred. Please try again.', false);
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
