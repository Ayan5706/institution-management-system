<?php

declare(strict_types=1);

namespace App\Models;

final class PasswordResetTokenModel extends BaseModel
{
    protected string $table = 'password_reset_tokens';

    protected array $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
        'created_at',
    ];

    public function findByHash(string $hash): ?array
    {
        return $this->firstWhere('token_hash', $hash);
    }

    public function clearActiveForUser(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM password_reset_tokens WHERE user_id = :user_id AND used_at IS NULL'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount();
    }
}
