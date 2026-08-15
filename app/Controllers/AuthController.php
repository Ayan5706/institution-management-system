<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;
use App\Models\UserModel;
use App\Models\PasswordResetRequestModel;
use App\Models\PasswordResetTokenModel;
use App\Models\PasswordResetVerificationModel;
use App\Models\SystemConfigModel;
use App\Services\AuthService;
use App\Services\MailService;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (empty($_SESSION['visited_home'])) {
            $this->redirect('/');
            return;
        }

        $resetStatus = (string) $this->input('reset', '');
        $message = $resetStatus === 'success'
            ? 'Your password has been updated. You can now log in.'
            : '';

        $this->view('auth.login', [
            'message' => $message,
        ]);
    }

    public function login(): void
    {
        if (empty($_SESSION['visited_home'])) {
            $this->json([
                'success' => false,
                'message' => 'Please visit the homepage before logging in.',
            ], 403);
            return;
        }

        $credential = (string) $this->input('email', '');
        $password = (string) $this->input('password', '');

        if ($credential === '' || $password === '') {
            $this->json([
                'success' => false,
                'message' => 'Login ID and password are required.',
            ], 422);
            return;
        }

        // Validate that credential is a login ID, not an email
        if (strpos($credential, '@') !== false) {
            $this->json([
                'success' => false,
                'message' => 'Please enter your Login ID.',
            ], 422);
            return;
        }

        // Use AuthService for login with JWT token generation per spec Part 5.1
        $authService = new AuthService();
        $result = $authService->login($credential, $password);

        if (!$result['success']) {
            $this->json([
                'success' => false,
                'message' => $result['error'] ?? 'Login failed'
            ], 401);
            return;
        }

        // Set session for backward compatibility
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_email'] = $result['user']['email'];
        $_SESSION['user_role'] = $result['user']['role'];
        $_SESSION['user_name'] = $result['user']['full_name'];

        $configModel = new SystemConfigModel();
        $configValues = $configModel->getValues([
            'WORKING_DAYS',
            'DAY_START_TIME',
            'DAY_END_TIME',
        ]);
        $_SESSION['system_config'] = $configValues;
        error_log('[CONFIG_DEBUG] Login loaded system config: ' . json_encode($configValues));

        $this->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $result['user'],
                'needs_profile_completion' => $this->needsProfileCompletion($result['user']),
                'accessToken' => $result['accessToken'],
                'refreshToken' => $result['refreshToken'],
            ],
        ]);
    }

    /** @param array<string, mixed> $user */
    private function needsProfileCompletion(array $user): bool
    {
        $role = strtoupper((string) ($user['role'] ?? ''));
        if ($role !== 'PRINCIPAL') {
            return false;
        }

        $fullName = trim((string) ($user['full_name'] ?? ''));
        return $fullName === '' || strcasecmp($fullName, 'Account Pending Activation') === 0;
    }

    public function showChangePassword(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $user = $userId > 0 ? (new UserModel())->find($userId) : null;
        $mustChange = $user ? ((int) ($user['must_change_password'] ?? 0) === 1) : false;

        $this->view('auth.change_password', [
            'must_change_password' => $mustChange,
        ]);
    }

    public function changePassword(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized.'], 401);
            return;
        }

        $currentPassword = (string) $this->input('current_password', '');
        $newPassword     = (string) $this->input('new_password', '');
        $confirmPassword = (string) $this->input('confirm_password', '');

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }

        $mustChangePassword = ((int) ($user['must_change_password'] ?? 0) === 1);

        if ($newPassword === '' || $confirmPassword === '') {
            $this->json(['success' => false, 'message' => 'New password fields are required.'], 422);
            return;
        }

        if (!$mustChangePassword && $currentPassword === '') {
            $this->json(['success' => false, 'message' => 'Current password is required.'], 422);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'New passwords do not match.'], 422);
            return;
        }

        // Minimum password requirements: >= 8 chars + at least 1 digit
        if (strlen($newPassword) < 8 || !preg_match('/\d/', $newPassword)) {
            $this->json(['success' => false, 'message' => 'Password must be at least 8 characters and contain at least one number.'], 422);
            return;
        }

        if ($mustChangePassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $userModel->updateById($userId, [
                'password_hash' => $hashedPassword,
                'must_change_password' => 0,
            ]);

            $this->json(['success' => true, 'message' => 'Password changed successfully.']);
            return;
        }

        // Use AuthService for standard password change
        $authService = new AuthService();
        $result = $authService->changePassword($userId, $currentPassword, $newPassword);

        if (!$result['success']) {
            $this->json(['success' => false, 'message' => $result['error'] ?? 'Password change failed'], 401);
            return;
        }

        $this->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    public function logout(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        // Get access token from header if available for blacklist (optional - session-based logout)
        $token = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $parts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
            if (count($parts) === 2 && $parts[0] === 'Bearer') {
                $token = $parts[1];
            }
        }

        // Use AuthService logout if token available
        if ($token && $userId > 0) {
            $authService = new AuthService();
            $authService->logout($token, $userId);
        }

        // Clear session
        unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['visited_home']);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $this->redirect('login');
    }

    public function showForgotPassword(): void
    {
        $this->view('auth.forgot_password');
    }

    public function sendPasswordReset(): void
    {
        $email = trim((string) $this->input('email', ''));

        if ($email === '') {
            $this->view('auth.forgot_password', [
                'title' => 'Forgot Password',
                'email' => $email,
                'field_error' => 'Email address is required.',
            ]);
            return;
        }

        if (!preg_match('/^[A-Za-z0-9]+@gmail\.com$/', $email)) {
            $this->view('auth.forgot_password', [
                'title' => 'Forgot Password',
                'email' => $email,
                'field_error' => 'Please enter a valid Gmail address (example: name@gmail.com).',
            ]);
            return;
        }

        $userModel = new UserModel();
        $resetModel = new PasswordResetRequestModel();
        $tokenModel = new PasswordResetTokenModel();
        $verificationModel = new PasswordResetVerificationModel();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $role = strtoupper((string) ($user['role'] ?? ''));
            $requiresEmailVerification = in_array($role, ['VP', 'MANAGER', 'ACCOUNTANT'], true);
            $allowedRoles = ['VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT'];

            if ($role === 'PRINCIPAL') {
                try {
                    $tokenModel->clearActiveForUser((int) $user['id']);
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expiresAt = gmdate('Y-m-d H:i:s', time() + 3600);

                    $tokenModel->create([
                        'user_id' => (int) $user['id'],
                        'token_hash' => $tokenHash,
                        'expires_at' => $expiresAt,
                        'used_at' => null,
                        'created_at' => gmdate('Y-m-d H:i:s'),
                    ]);

                    $mailService = new MailService();
                    $subject = 'IMS Password Reset Link';
                    $name = (string) ($user['full_name'] ?? 'User');
                    $resetLink = url('reset-password?token=' . $token);
                    $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                        . '<p>Use the link below to reset your password. This link expires in 60 minutes.</p>'
                        . '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">Reset Password</a></p>'
                        . '<p>If you did not request this, please ignore this email.</p>'
                        . '<p>— IMS Admin</p>';
                    $textBody = "Hello {$name},\n\n"
                        . "Use the link below to reset your password (valid for 60 minutes):\n"
                        . "{$resetLink}\n\n"
                        . "If you did not request this, please ignore this email.\n\n"
                        . "— IMS Admin";
                    $mailService->sendMail((string) $user['email'], $subject, $htmlBody, $textBody);

                    $this->view('auth.forgot_password', [
                        'title' => 'Forgot Password',
                        'message' => 'A password reset link has been emailed to you. Please check your inbox.',
                    ]);
                    return;
                } catch (\Throwable $mailError) {
                    \App\Helpers\logger_helper('mail_error', 'Password reset email failed: ' . $mailError->getMessage());
                    $debug = (bool) Config::get('app.debug', false);
                    $detail = $debug ? ' (' . $mailError->getMessage() . ')' : '';
                    $this->view('auth.forgot_password', [
                        'title' => 'Forgot Password',
                        'error' => 'Unable to send the reset email. Please contact support.' . $detail,
                    ]);
                    return;
                }
            }

            if (!in_array($role, $allowedRoles, true)) {
                $this->view('auth.forgot_password', [
                    'title' => 'Forgot Password',
                    'error' => 'Please contact your system administrator to reset your password.',
                ]);
                return;
            }

            // For VP, Manager, Accountant: Send email verification first
            if ($requiresEmailVerification) {
                try {
                    // Check if there's already a pending verification
                    $existingVerifications = $verificationModel->where('user_id', (int) $user['id']);
                    foreach ($existingVerifications as $verification) {
                        if ($verification['verified_at'] === null) {
                            $expiresAt = strtotime((string) $verification['expires_at']);
                            if ($expiresAt > time()) {
                                $this->view('auth.forgot_password', [
                                    'title' => 'Forgot Password',
                                    'message' => 'An email verification link has already been sent to your email. Please check your inbox and verify your email.',
                                ]);
                                return;
                            }
                        }
                    }

                    // Clear old verification tokens for this user
                    $verificationModel->clearActiveForUser((int) $user['id']);

                    // Create a new verification token
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expiresAt = gmdate('Y-m-d H:i:s', time() + 3600);

                    $verificationModel->create([
                        'user_id' => (int) $user['id'],
                        'token_hash' => $tokenHash,
                        'expires_at' => $expiresAt,
                        'verified_at' => null,
                        'created_at' => gmdate('Y-m-d H:i:s'),
                    ]);

                    // Send verification email
                    $mailService = new MailService();
                    $subject = 'Verify Your Email - IMS Password Reset';
                    $name = (string) ($user['full_name'] ?? 'User');
                    $verificationLink = url('verify-password-reset-email?token=' . $token);
                    $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                        . '<p>You have requested to reset your password. Please verify your email by clicking the link below before submitting your reset request to the Principal.</p>'
                        . '<p><a href="' . htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') . '">Verify Email</a></p>'
                        . '<p>This link expires in 60 minutes.</p>'
                        . '<p>If you did not request a password reset, please ignore this email.</p>'
                        . '<p>— IMS Admin</p>';
                    $textBody = "Hello {$name},\n\n"
                        . "You have requested to reset your password. Please verify your email by using the link below (valid for 60 minutes):\n"
                        . "{$verificationLink}\n\n"
                        . "If you did not request a password reset, please ignore this email.\n\n"
                        . "— IMS Admin";
                    $mailService->sendMail((string) $user['email'], $subject, $htmlBody, $textBody);

                    $this->view('auth.forgot_password', [
                        'title' => 'Forgot Password',
                        'message' => 'An email verification link has been sent to your email. Please verify your email before submitting your reset request.',
                    ]);
                    return;
                } catch (\Throwable $mailError) {
                    \App\Helpers\logger_helper('mail_error', 'Password reset verification email failed: ' . $mailError->getMessage());
                    $debug = (bool) Config::get('app.debug', false);
                    $detail = $debug ? ' (' . $mailError->getMessage() . ')' : '';
                    $this->view('auth.forgot_password', [
                        'title' => 'Forgot Password',
                        'error' => 'Unable to send the verification email. Please contact support.' . $detail,
                    ]);
                    return;
                }
            }

            // Check for pending requests (Teacher -> VP, Student -> Manager)
            $requests = $resetModel->where('requested_by', (int) $user['id']);
            foreach ($requests as $request) {
                if (strtoupper((string) ($request['status'] ?? '')) === 'PENDING') {
                    $this->view('auth.forgot_password', [
                        'title' => 'Forgot Password',
                        'message' => 'A password reset request is already pending approval. Please wait for confirmation.',
                    ]);
                    return;
                }
            }

            // Create reset request
            $resetModel->create([
                'requested_by' => (int) $user['id'],
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->view('auth.forgot_password', [
            'title' => 'Forgot Password',
            'message' => 'If an account with that email or login ID exists, a reset request has been sent for approval.',
        ]);
    }

    public function verifyPasswordResetEmail(): void
    {
        $token = trim((string) $this->input('token', ''));

        if ($token === '') {
            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'error' => 'Invalid or missing verification link.',
            ]);
            return;
        }

        $verificationModel = new PasswordResetVerificationModel();
        $resetModel = new PasswordResetRequestModel();
        $userModel = new UserModel();
        $tokenHash = hash('sha256', $token);

        $verification = $verificationModel->findByHash($tokenHash);

        if (!$verification) {
            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'error' => 'Invalid verification link.',
            ]);
            return;
        }

        // Check if token is expired
        $expiresAt = strtotime((string) $verification['expires_at']);
        if ($expiresAt <= time()) {
            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'error' => 'Verification link has expired. Please request a new password reset.',
            ]);
            return;
        }

        // Check if already verified
        if ($verification['verified_at'] !== null) {
            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'message' => 'Your email has already been verified. Please wait for the Principal to approve your password reset request.',
            ]);
            return;
        }

        $userId = (int) $verification['user_id'];
        $user = $userModel->find($userId);

        if (!$user) {
            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'error' => 'User account not found.',
            ]);
            return;
        }

        try {
            // Mark verification as verified
            $verificationModel->updateById((int) $verification['id'], [
                'verified_at' => gmdate('Y-m-d H:i:s'),
            ]);

            // Check for existing pending request
            $requests = $resetModel->where('requested_by', $userId);
            foreach ($requests as $request) {
                if (strtoupper((string) ($request['status'] ?? '')) === 'PENDING') {
                    $this->view('auth.verify_password_reset_email', [
                        'title' => 'Verify Email',
                        'message' => 'Your email has been verified. A password reset request is already pending approval from the Principal.',
                    ]);
                    return;
                }
            }

            // Create a new password reset request
            $resetModel->create([
                'requested_by' => $userId,
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Send confirmation email to user
            $mailService = new MailService();
            $subject = 'Password Reset Request Submitted - IMS';
            $name = (string) ($user['full_name'] ?? 'User');
            $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p>Your email has been verified. Your password reset request has been submitted to the Principal for approval.</p>'
                . '<p>You will receive an email notification once the Principal reviews and approves your request.</p>'
                . '<p>— IMS Admin</p>';
            $textBody = "Hello {$name},\n\n"
                . "Your email has been verified. Your password reset request has been submitted to the Principal for approval.\n"
                . "You will receive an email notification once the Principal reviews and approves your request.\n\n"
                . "— IMS Admin";
            $mailService->sendMail((string) $user['email'], $subject, $htmlBody, $textBody);

            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'message' => 'Your email has been verified successfully. Your password reset request has been submitted to the Principal for approval.',
            ]);
        } catch (\Throwable $error) {
            \App\Helpers\logger_helper('email_verification_error', 'Password reset email verification failed: ' . $error->getMessage());
            $debug = (bool) Config::get('app.debug', false);
            $detail = $debug ? ' (' . $error->getMessage() . ')' : '';
            $this->view('auth.verify_password_reset_email', [
                'title' => 'Verify Email',
                'error' => 'An error occurred while verifying your email. Please contact support.' . $detail,
            ]);
        }
    }

    public function showResetPassword(): void
    {
        $token = (string) $this->input('token', '');
        $this->view('auth.reset_password', [
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        $token = (string) $this->input('token', '');
        $password = (string) $this->input('password', '');
        $passwordConfirmation = (string) $this->input('password_confirmation', '');

        if ($token === '' || $password === '' || $passwordConfirmation === '') {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Token and new password are required.',
            ]);
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Passwords do not match.',
            ]);
            return;
        }

        if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Password must be at least 8 characters and contain at least one number.',
            ]);
            return;
        }

        $tokenModel = new PasswordResetTokenModel();
        $hash = hash('sha256', $token);
        $record = $tokenModel->findByHash($hash);

        if (!$record) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'error' => 'This reset link is invalid.',
            ]);
            return;
        }

        if (!empty($record['used_at'])) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'error' => 'This reset link has already been used.',
            ]);
            return;
        }

        // Compare directly with current UTC time to avoid timezone issues
        $currentUtc = gmdate('Y-m-d H:i:s');
        $expiresAt = (string) ($record['expires_at'] ?? '');
        if ($currentUtc > $expiresAt) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'error' => 'This reset link has expired.',
            ]);
            return;
        }

        $userId = (int) ($record['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'error' => 'This reset link is invalid.',
            ]);
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user) {
            $this->view('auth.reset_password', [
                'title' => 'Reset Password',
                'error' => 'Account no longer exists.',
            ]);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userModel->updateById($userId, [
            'password_hash' => $passwordHash,
            'must_change_password' => 0,
        ]);

        $tokenModel->updateById((int) $record['id'], [
            'used_at' => gmdate('Y-m-d H:i:s'),
        ]);

        $_SESSION['visited_home'] = true;
        $this->redirect('login?reset=success');
    }
}
