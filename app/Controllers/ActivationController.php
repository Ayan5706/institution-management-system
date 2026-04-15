<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\ActivationTokenModel;
use App\Models\UserModel;

final class ActivationController extends BaseController
{
    private ActivationTokenModel $activationTokens;
    private UserModel $users;

    public function __construct()
    {
        $this->activationTokens = new ActivationTokenModel();
        $this->users = new UserModel();
    }

    public function show(string $token): void
    {
        $state = $this->resolveTokenState($token);

        $this->view('auth.activate', [
            'title' => 'Activate Account',
            'token' => $token,
            'error' => $state['error'],
            'requires_password' => $state['requires_password'],
        ]);
    }

    public function activate(string $token): void
    {
        $state = $this->resolveTokenState($token);
        $error = $state['error'];
        $record = $state['record'];
        $requiresPassword = $state['requires_password'];

        $password = (string) $this->input('password', '');
        $passwordConfirmation = (string) $this->input('password_confirmation', '');

        if ($error !== null) {
            $this->view('auth.activate', [
                'title' => 'Activate Account',
                'token' => $token,
                'error' => $error,
                'requires_password' => $requiresPassword,
            ]);
            return;
        }

        if (!$requiresPassword) {
            $this->activationTokens->updateById((int) $record['id'], [
                'used_at' => gmdate('Y-m-d H:i:s'),
            ]);

            $userId = (int) ($record['user_id'] ?? 0);
            if ($userId > 0) {
                $db = Database::connection();
                $stmt = $db->prepare(
                    'UPDATE activation_tokens SET used_at = :used_at WHERE user_id = :user_id AND created_by = :created_by AND used_at IS NULL'
                );
                $stmt->execute([
                    'used_at' => gmdate('Y-m-d H:i:s'),
                    'user_id' => $userId,
                    'created_by' => $userId,
                ]);
            }

            unset(
                $_SESSION['user_id'],
                $_SESSION['user_email'],
                $_SESSION['user_role'],
                $_SESSION['user_name']
            );
            $this->redirect('login');
            return;

            $this->view('auth.activate', [
                'title' => 'Email Verified',
                'token' => $token,
                'success' => 'Email verified successfully. You can continue using your account.',
                'requires_password' => false,
            ]);
            return;
        }

        if ($password === '' || $passwordConfirmation === '') {
            $this->view('auth.activate', [
                'title' => 'Activate Account',
                'token' => $token,
                'error' => 'Please enter and confirm your new password.',
                'requires_password' => $requiresPassword,
            ]);
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->view('auth.activate', [
                'title' => 'Activate Account',
                'token' => $token,
                'error' => 'Passwords do not match.',
                'requires_password' => $requiresPassword,
            ]);
            return;
        }

        if (strlen($password) < 8) {
            $this->view('auth.activate', [
                'title' => 'Activate Account',
                'token' => $token,
                'error' => 'Password must be at least 8 characters long.',
                'requires_password' => $requiresPassword,
            ]);
            return;
        }

        $userId = (int) ($record['user_id'] ?? 0);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $this->users->updateById($userId, [
            'password_hash' => $passwordHash,
            'must_change_password' => 0,
        ]);

        $this->activationTokens->updateById((int) $record['id'], [
            'used_at' => gmdate('Y-m-d H:i:s'),
        ]);

        $this->redirect('login');
    }

    /** @return array{record: array<string, mixed>|null, error: string|null, requires_password: bool} */
    private function resolveTokenState(string $token): array
    {
        $hash = hash('sha256', $token);
        $record = $this->activationTokens->findByHash($hash);

        if (!$record) {
            return ['record' => null, 'error' => 'This activation link is invalid.', 'requires_password' => false];
        }

        if (!empty($record['used_at'])) {
            return ['record' => $record, 'error' => 'This activation link has already been used.', 'requires_password' => false];
        }

        $expiresAt = strtotime((string) ($record['expires_at'] ?? ''));
        if ($expiresAt !== false && time() > $expiresAt) {
            return ['record' => $record, 'error' => 'This activation link has expired.', 'requires_password' => false];
        }

        $userId = (int) ($record['user_id'] ?? 0);
        $user = $this->users->find($userId);
        if (!$user) {
            return ['record' => $record, 'error' => 'Account no longer exists.', 'requires_password' => false];
        }

        return [
            'record' => $record,
            'error' => null,
            'requires_password' => $this->requiresPassword($record, $user),
        ];
    }

    /** @param array<string, mixed> $record @param array<string, mixed> $user */
    private function requiresPassword(array $record, array $user): bool
    {
        $createdBy = (int) ($record['created_by'] ?? 0);
        $userId = (int) ($record['user_id'] ?? 0);
        $isSelfIssued = $createdBy > 0 && $createdBy === $userId;
        if ($isSelfIssued) {
            return false;
        }

        return true;
    }

}
