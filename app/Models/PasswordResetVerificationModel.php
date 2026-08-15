<?php

declare(strict_types=1);

namespace App\Models;

final class PasswordResetVerificationModel extends BaseModel
{
    protected string $table = 'password_reset_verifications';

    protected array $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'verified_at',
        'created_at',
    ];

    public function findByHash(string $hash): ?array
    {
        return $this->firstWhere('token_hash', $hash);
    }

    public function clearActiveForUser(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM password_reset_verifications WHERE user_id = :user_id AND verified_at IS NULL'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount();
    }

    /** @return array<int, array<string, mixed>> */
    public function getUnverifiedRequests(): array
    {
        return $this->where('verified_at', null);
    }
}
