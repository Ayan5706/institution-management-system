<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetRequestModel;
use App\Models\SystemConfigModel;
use App\Services\AuthService;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (empty($_SESSION['visited_home'])) {
            $this->redirect('/');
            return;
        }

        $this->view('auth.login');
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
                'message' => 'Email/Login ID and password are required.',
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
            'GRACE_MINUTES',
        ]);
        $_SESSION['system_config'] = $configValues;
        error_log('[CONFIG_DEBUG] Login loaded system config: ' . json_encode($configValues));

        $this->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $result['user'],
                'accessToken' => $result['accessToken'],
                'refreshToken' => $result['refreshToken'],
            ],
        ]);
    }

    public function showChangePassword(): void
    {
        $this->view('auth.change_password');
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

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $this->json(['success' => false, 'message' => 'All fields are required.'], 422);
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

        // Use AuthService for password change per spec Part 4.3
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
                'error' => 'Email is required.',
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth.forgot_password', [
                'title' => 'Forgot Password',
                'error' => 'Please enter a valid email address.',
            ]);
            return;
        }

        $userModel = new UserModel();
        $resetModel = new PasswordResetRequestModel();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $role = strtoupper((string) ($user['role'] ?? ''));
            $allowedRoles = ['STUDENT', 'TEACHER', 'VP', 'MANAGER', 'ACCOUNTANT'];

            if (!in_array($role, $allowedRoles, true)) {
                $this->view('auth.forgot_password', [
                    'title' => 'Forgot Password',
                    'error' => 'Please contact your system administrator to reset your password.',
                ]);
                return;
            }

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

            $resetModel->create([
                'requested_by' => (int) $user['id'],
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->view('auth.forgot_password', [
            'title' => 'Forgot Password',
            'message' => 'If an account with that email exists, a reset request has been sent for approval.',
        ]);
    }

    public function showResetPassword(): void
    {
        $this->view('auth.reset_password');
    }

    public function resetPassword(): void
    {
        $token = (string) $this->input('token', '');
        $password = (string) $this->input('password', '');
        $passwordConfirmation = (string) $this->input('password_confirmation', '');

        if ($token === '' || $password === '' || $passwordConfirmation === '') {
            $this->json([
                'success' => false,
                'message' => 'Token and new password are required.',
            ], 422);
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->json([
                'success' => false,
                'message' => 'Passwords do not match.',
            ], 422);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Reset password endpoint is ready for database integration.',
        ]);
    }
}
