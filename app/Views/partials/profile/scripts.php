<script>
    const profileForm = document.getElementById('profile-form');
    const profileSuccess = document.getElementById('profile-success');
    const profileError = document.getElementById('profile-error');
    const fullNameInput = document.getElementById('full_name');
    const fullNameError = document.getElementById('full-name-error');
    const phoneInput = document.getElementById('phone');
    const phoneError = document.getElementById('phone-error');
    const emailForm = document.getElementById('email-form');
    const emailSuccess = document.getElementById('email-success');
    const emailError = document.getElementById('email-error');
    const emailOtpAction = '<?php echo e($email_otp_action ?? ''); ?>';
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
        if (phoneError) {
            phoneError.style.display = 'none';
            phoneError.textContent = '';
        }
        if (emailError) {
            emailError.style.display = 'none';
        }
        if (emailSuccess) {
            emailSuccess.style.display = 'none';
        }
        const otpError = document.getElementById('otp-error');
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

    profileForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessages();

        const formData = new FormData(profileForm);
        const fullName = (formData.get('full_name') || '').toString().trim();
        const phone = (formData.get('phone') || '').toString().trim();

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

        if (!validatePhoneField(phone)) {
            return;
        }

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

        const validateEmailField = (emailInput, errorElement) => {
            const gmailRegex = /^[A-Za-z0-9]+@gmail\.com$/;
            const value = emailInput.value.trim();

            if (value === '') {
                errorElement.textContent = 'Email is required.';
                errorElement.style.display = 'block';
                return false;
            }

            if (!gmailRegex.test(value)) {
                errorElement.textContent = 'Please enter a valid Gmail address (example: name@gmail.com).';
                errorElement.style.display = 'block';
                return false;
            }

            errorElement.textContent = '';
            errorElement.style.display = 'none';
            return true;
        };

        if (newEmailInput && confirmEmailInput) {
            newEmailInput.addEventListener('input', () => {
                if (newEmailError && newEmailError.textContent) {
                    validateEmailField(newEmailInput, newEmailError);
                }
            });

            newEmailInput.addEventListener('blur', () => {
                if (newEmailInput.value.trim() !== '') {
                    validateEmailField(newEmailInput, newEmailError);
                }
            });

            confirmEmailInput.addEventListener('input', () => {
                if (confirmEmailError && confirmEmailError.textContent) {
                    validateEmailField(confirmEmailInput, confirmEmailError);
                }
            });

            confirmEmailInput.addEventListener('blur', () => {
                if (confirmEmailInput.value.trim() !== '') {
                    validateEmailField(confirmEmailInput, confirmEmailError);
                }
            });
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

            // Validate email format - Gmail only with letters/numbers before @
            const gmailRegex = /^[A-Za-z0-9]+@gmail\.com$/;
            if (!gmailRegex.test(newEmail)) {
                if (newEmailError) {
                    newEmailError.textContent = 'Please enter a valid Gmail address (example: name@gmail.com).';
                    newEmailError.style.display = 'block';
                }
                showMessage(emailError, 'Please enter a valid Gmail address (example: name@gmail.com).', false);
                return;
            }

            if (!gmailRegex.test(confirmEmail)) {
                if (confirmEmailError) {
                    confirmEmailError.textContent = 'Please enter a valid Gmail address (example: name@gmail.com).';
                    confirmEmailError.style.display = 'block';
                }
                showMessage(emailError, 'Please enter a valid Gmail address (example: name@gmail.com).', false);
                return;
            }

            if (newEmail.toLowerCase() !== confirmEmail.toLowerCase()) {
                showMessage(emailError, 'Email confirmation does not match.', false);
                return;
            }

            if (otpSection) {
                try {
                    const response = await fetch('<?php echo e($email_action ?? ''); ?>', {
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
