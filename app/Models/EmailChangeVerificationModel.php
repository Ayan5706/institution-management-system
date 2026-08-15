<?php

declare(strict_types=1);

namespace App\Models;

final class EmailChangeVerificationModel extends BaseModel
{
    protected string $table = 'email_change_verifications';

    protected array $fillable = [
        'user_id',
        'new_email',
        'otp_hash',
        'expires_at',
        'verified_at',
        'created_at',
    ];

    public function getActiveForUser(int $userId): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM email_change_verifications WHERE user_id = :user_id AND verified_at IS NULL AND expires_at >= UTC_TIMESTAMP() ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function hasActiveForUser(int $userId): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT id FROM email_change_verifications WHERE user_id = :user_id AND verified_at IS NULL AND expires_at >= UTC_TIMESTAMP() LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function clearActiveForUser(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'DELETE FROM email_change_verifications WHERE user_id = :user_id AND verified_at IS NULL'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount();
    }
}
