<?php

declare(strict_types=1);

namespace App\Models;

final class ActivationTokenModel extends BaseModel
{
    protected string $table = 'activation_tokens';

    protected array $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
        'created_by',
        'created_at',
    ];

    public function findByHash(string $hash): ?array
    {
        return $this->firstWhere('token_hash', $hash);
    }

    public function deleteByUserId(int $userId): int
    {
        $stmt = $this->db()->prepare('DELETE FROM activation_tokens WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount();
    }

    public function hasActiveForUser(int $userId): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT id FROM activation_tokens WHERE user_id = :user_id AND created_by = :created_by AND used_at IS NULL AND expires_at >= UTC_TIMESTAMP() LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId, 'created_by' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function hasActiveTokenForUser(int $userId): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT id FROM activation_tokens WHERE user_id = :user_id AND used_at IS NULL AND expires_at >= UTC_TIMESTAMP() LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function invalidateUnusedTokensForUser(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'UPDATE activation_tokens SET used_at = :used_at WHERE user_id = :user_id AND used_at IS NULL'
        );
        $stmt->execute([
            'user_id' => $userId,
            'used_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return $stmt->rowCount();
    }
}
