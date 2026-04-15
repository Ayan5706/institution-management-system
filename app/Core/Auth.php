<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use Throwable;

/**
 * JWT Authentication Handler
 * Per Spec Part 4.2: JWT implementation with firebase/php-jwt
 */
final class Auth
{
    /**
     * Decode and validate JWT token
     * Returns: ['user_id' => int, 'role' => string, 'jti' => string, 'iat' => int, 'exp' => int]
     * Throws exception if invalid
     */
    public static function verifyToken(string $token): array
    {
        $secret = (string) Config::get('app.jwt_secret', 'default-insecure-secret');
        
        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array) $decoded;
        } catch (Throwable $e) {
            throw new \RuntimeException('Invalid JWT: ' . $e->getMessage());
        }
    }

    /**
     * Check if token JTI is blacklisted
     */
    public static function isTokenBlacklisted(string $jti): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM jwt_blacklist WHERE jti = ?');
        $stmt->execute([$jti]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get Authorization header token
     */
    public static function getBearerToken(): ?string
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $auth = $headers['Authorization'];
        if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Require authentication and optional role check
     * Per Spec Part 4.5: requireAuth(array $roles)
     * Returns: user array on success, exits with 401/403 on failure
     */
    public static function requireAuth(array $allowedRoles = []): array
    {
        $token = self::getBearerToken();
        if (!$token) {
            return self::error(401, 'Unauthorized: No token provided.');
        }

        try {
            $decoded = self::verifyToken($token);
        } catch (Throwable $e) {
            return self::error(401, 'Unauthorized: Invalid token.');
        }

        // Check blacklist
        if (self::isTokenBlacklisted($decoded['jti'] ?? '')) {
            return self::error(401, 'Unauthorized: Token has been revoked.');
        }

        // Fetch user
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$decoded['user_id'] ?? 0]);
        $user = $stmt->fetch();

        if (!$user) {
            return self::error(401, 'Unauthorized: User not found.');
        }

        // Check is_active
        if ((int)$user['is_active'] !== 1) {
            return self::error(401, 'Unauthorized: Account deactivated.');
        }

        // Check must_change_password
        if ((int)$user['must_change_password'] === 1) {
            return self::error(403, 'Forbidden: Password change required.', ['redirect' => '/change-password']);
        }

        // Check role if specified
        if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles)) {
            return self::error(403, 'Forbidden: Insufficient permissions.');
        }

        return $user;
    }

    /**
     * Generate both access and refresh tokens
     * Returns array with ['accessToken' => string, 'refreshToken' => string]
     */
    public static function generateTokens(int $userId, string $role): array
    {
        return [
            'accessToken' => self::generateAccessToken($userId, $role),
            'refreshToken' => self::generateRefreshToken($userId, $role),
        ];
    }

    /**
     * Generate JWT access token (3600s)
     */
    public static function generateAccessToken(int $userId, string $role): string
    {
        $secret = (string) Config::get('app.jwt_secret', 'default-insecure-secret');
        $jti = bin2hex(random_bytes(16));

        $payload = [
            'user_id' => $userId,
            'role' => $role,
            'jti' => $jti,
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Generate JWT refresh token (604800s = 7 days)
     */
    public static function generateRefreshToken(int $userId, string $role): string
    {
        $secret = (string) Config::get('app.jwt_secret', 'default-insecure-secret');
        $jti = bin2hex(random_bytes(16));

        $payload = [
            'user_id' => $userId,
            'role' => $role,
            'jti' => $jti,
            'iat' => time(),
            'exp' => time() + 604800, // 7 days
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Blacklist a token by adding its JTI to jwt_blacklist table
     */
    public static function blacklistToken(string $jti, int $userId, int $expiresAt): void
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO jwt_blacklist (jti, user_id, expires_at, created_at)
                VALUES (?, ?, FROM_UNIXTIME(?), NOW())
                ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
            ');
            $stmt->execute([$jti, $userId, $expiresAt]);
        } catch (Throwable $e) {
            error_log('Error blacklisting token: ' . $e->getMessage());
        }
    }

    /**
     * Helper to return error response and exit
     */
    private static function error(int $status, string $message, array $extra = []): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        $response = ['error' => $message];
        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }
        echo json_encode($response);
        exit;
    }
}
