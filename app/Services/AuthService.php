<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Auth;
use App\Models\UserModel;
use App\Models\PasswordResetRequestModel;

class AuthService
{
    private \PDO $db;
    private UserModel $userModel;
    private PasswordResetRequestModel $passwordResetModel;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->userModel = new UserModel();
        $this->passwordResetModel = new PasswordResetRequestModel();
    }

    /**
     * Authenticate user and generate tokens
     * Per spec Part 5.1 - login endpoint
     * Only accepts login_id, not email.
     * 
     * @param string $loginId User login ID
     * @param string $password User password (plain text)
     * @return array ['success' => bool, 'user' => array, 'accessToken' => string, 'refreshToken' => string, 'error' => string]
     */
    public function login(string $loginId, string $password): array
    {
        try {
            // Trim and validate inputs
            $loginId = trim($loginId);
            $password = trim($password);

            if (empty($loginId) || empty($password)) {
                return [
                    'success' => false,
                    'error' => 'Login ID and password are required'
                ];
            }

            // Reject if email address is provided
            if (strpos($loginId, '@') !== false) {
                return [
                    'success' => false,
                    'error' => 'Please enter your Login ID.'
                ];
            }

            // Find user by login_id only
            $stmt = $this->db->prepare('
                SELECT id, email, full_name, password_hash, role, is_active, must_change_password
                FROM users
                WHERE BINARY login_id = ? AND is_active = 1
                LIMIT 1
            ');
            $stmt->execute([$loginId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            // User not found or inactive
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Invalid login ID or password'
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid login ID or password'
                ];
            }

            // Generate tokens using Auth class static method
            $tokens = Auth::generateTokens((int)$user['id'], $user['role']);

            return [
                'success' => true,
                'user' => [
                    'id' => (int)$user['id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                    'must_change_password' => (int)$user['must_change_password'] === 1
                ],
                'accessToken' => $tokens['accessToken'],
                'refreshToken' => $tokens['refreshToken']
            ];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('auth_service_error', 'AuthService::login error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Login failed. Please try again.'
            ];
        }
    }

    /**
     * Refresh access token using refresh token
     * Per spec Part 4.2 token refresh
     * 
     * @param string $refreshToken Refresh token from client
     * @return array ['success' => bool, 'accessToken' => string, 'error' => string]
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            // Decode and validate refresh token
            $decoded = Auth::verifyToken($refreshToken);
            
            // Check if token is blacklisted
            if (Auth::isTokenBlacklisted($decoded['jti'] ?? '')) {
                return [
                    'success' => false,
                    'error' => 'Token has been revoked'
                ];
            }
            
            // Generate new access token with same user_id and role
            $accessToken = Auth::generateAccessToken((int)$decoded['user_id'], $decoded['role']);
            
            return [
                'success' => true,
                'accessToken' => $accessToken
            ];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('auth_service_error', 'AuthService::refreshToken error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Token refresh failed'
            ];
        }
    }

    /**
     * Logout user by blacklisting token
     * Per spec Part 5.1 - logout endpoint
     * 
     * @param string $token Access token to blacklist
    * @param int $userId User ID
     * @return array ['success' => bool, 'error' => string]
     */
    public function logout(string $token, int $userId): array
    {
        try {
            // Decode token to get JTI and expiration
            $decoded = Auth::verifyToken($token);
            
            // Blacklist the token
            Auth::blacklistToken($decoded['jti'] ?? '', $userId, $decoded['exp'] ?? time());

            return ['success' => true];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('auth_service_error', 'AuthService::logout error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Logout failed'
            ];
        }
    }

    /**
     * Change password - requires old password verification
     * Per spec Part 4.3 and Loophole #8 resolution
     * 
     * @param int $userId User ID
     * @param string $oldPassword Current password (plain text)
     * @param string $newPassword New password (plain text)
     * @return array ['success' => bool, 'error' => string]
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        try {
            $oldPassword = trim($oldPassword);
            $newPassword = trim($newPassword);

            // Validate passwords
            if (empty($oldPassword) || empty($newPassword)) {
                return [
                    'success' => false,
                    'error' => 'Old password and new password are required'
                ];
            }

            if (strlen($newPassword) < 8) {
                return [
                    'success' => false,
                    'error' => 'New password must be at least 8 characters'
                ];
            }

            if ($oldPassword === $newPassword) {
                return [
                    'success' => false,
                    'error' => 'New password must be different from old password'
                ];
            }

            // Get user's current password hash
            $stmt = $this->db->prepare('
                SELECT password_hash FROM users WHERE id = ? AND is_active = 1
            ');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            // Verify old password
            if (!password_verify($oldPassword, $user['password_hash'])) {
                return [
                    'success' => false,
                    'error' => 'Current password is incorrect'
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            // Update password and clear must_change_password flag
            $updateStmt = $this->db->prepare('
                UPDATE users 
                SET password_hash = ?, must_change_password = 0
                WHERE id = ?
            ');
            $updateStmt->execute([$hashedPassword, $userId]);

            return ['success' => true];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('auth_service_error', 'AuthService::changePassword error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Password change failed'
            ];
        }
    }

    /**
     * Request password reset - requires Principal approval
     * Per spec Part 3 password reset flow
     * 
     * @param int $userId User ID requesting reset
     * @return array ['success' => bool, 'error' => string]
     */
    public function requestPasswordReset(int $userId): array
    {
        try {
            // Check if request already pending
            $stmt = $this->db->prepare('
                SELECT id FROM password_reset_requests
                WHERE user_id = ? AND status = "PENDING"
            ');
            $stmt->execute([$userId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                return [
                    'success' => false,
                    'error' => 'Password reset request already pending. Please wait for Principal approval.'
                ];
            }

            // Get Principal ID (user with role PRINCIPAL)
            $principalStmt = $this->db->prepare('
                SELECT id FROM users WHERE role = "PRINCIPAL" AND is_active = 1 LIMIT 1
            ');
            $principalStmt->execute();
            $principal = $principalStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$principal) {
                return [
                    'success' => false,
                    'error' => 'Principal not found in system'
                ];
            }

            // Create password reset request
            $insertStmt = $this->db->prepare('
                INSERT INTO password_reset_requests
                (user_id, principal_id, status, created_at)
                VALUES (?, ?, "PENDING", ?)
            ');
            $insertStmt->execute([
                $userId,
                $principal['id'],
                date('Y-m-d H:i:s')
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('auth_service_error', 'AuthService::requestPasswordReset error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Password reset request failed'
            ];
        }
    }

    /**
     * Verify old password without changing it
     * Used in password change forms for validation
     * 
     * @param int $userId User ID
     * @param string $password Password to verify (plain text)
     * @return bool True if password matches
     */
    public function verifyPassword(int $userId, string $password): bool
    {
        try {
            $stmt = $this->db->prepare('
                SELECT password_hash FROM users WHERE id = ? AND is_active = 1
            ');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            return password_verify($password, $user['password_hash']);
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('auth_service_error', 'AuthService::verifyPassword error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate temporary password for new user creation
     * Per spec Part 4.2
     * 
     * @return string 10-character hex temporary password
     */
    public function generateTempPassword(): string
    {
        return bin2hex(random_bytes(5));
    }
}
