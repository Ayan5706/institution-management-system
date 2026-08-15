<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivationTokenModel;
use App\Models\UserModel;
use DateTimeImmutable;
use DateTimeZone;

final class ActivationService
{
    private UserModel $users;
    private ActivationTokenModel $tokens;
    private MailService $mail;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->tokens = new ActivationTokenModel();
        $this->mail = new MailService();
    }

    public function sendPendingPrincipalActivations(int $limit = 5): int
    {
        $principals = $this->users->where('role', 'PRINCIPAL');
        $sent = 0;

        foreach ($principals as $principal) {
            if ($sent >= $limit) {
                break;
            }

            $result = $this->sendPrincipalActivation((int) ($principal['id'] ?? 0), false, null);
            if ($result['success']) {
                $sent++;
            }
        }

        return $sent;
    }

    /** @return array{success: bool, message: string, status: int} */
    public function sendPrincipalActivation(int $userId, bool $force = false, ?int $createdBy = null): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Invalid user ID.', 'status' => 422];
        }

        $user = $this->users->find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.', 'status' => 404];
        }

        $role = strtoupper((string) ($user['role'] ?? ''));
        if ($role !== 'PRINCIPAL') {
            return ['success' => false, 'message' => 'User is not a principal.', 'status' => 422];
        }

        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '') {
            return ['success' => false, 'message' => 'Principal email is missing.', 'status' => 422];
        }

        $mustChange = (int) ($user['must_change_password'] ?? 0) === 1;
        if (!$force && !$mustChange) {
            return ['success' => false, 'message' => 'Principal is already activated.', 'status' => 409];
        }

        if (!$force && $this->tokens->hasActiveTokenForUser($userId)) {
            return ['success' => false, 'message' => 'An activation email is already active.', 'status' => 409];
        }

        if ($force) {
            $this->tokens->invalidateUnusedTokensForUser($userId);
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('+24 hours')
            ->format('Y-m-d H:i:s');

        $this->tokens->create([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_by' => $createdBy,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        $activationLink = url('activate/' . $token);
        $fullName = trim((string) ($user['full_name'] ?? ''));
        if ($fullName === '') {
            $fullName = 'Principal';
        }
        $loginId = (string) ($user['login_id'] ?? '');
        $subject = 'Activate your IMS account';

        $loginLine = $loginId !== ''
            ? '<p>Login ID: <strong>' . e($loginId) . '</strong></p>'
            : '';

        $htmlBody = sprintf(
            '<p>Hello %s,</p><p>Your IMS account has been created. Please activate your account using the link below (valid for 24 hours):</p><p><a href="%s">Activate Account</a></p>%s',
            e($fullName),
            e($activationLink),
            $loginLine
        );

        $textBody = "Hello {$fullName},\n\nYour IMS account has been created. Activate your account using the link below (valid for 24 hours):\n{$activationLink}\n";
        if ($loginId !== '') {
            $textBody .= "\nLogin ID: {$loginId}\n";
        }

        try {
            $this->mail->sendMail($email, $subject, $htmlBody, $textBody);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to send activation email: ' . $e->getMessage(),
                'status' => 500,
            ];
        }

        return ['success' => true, 'message' => 'Activation email sent.', 'status' => 200];
    }
}
