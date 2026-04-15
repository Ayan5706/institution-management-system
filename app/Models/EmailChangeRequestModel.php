<?php

declare(strict_types=1);

namespace App\Models;

final class EmailChangeRequestModel extends BaseModel
{
    protected string $table = 'email_change_requests';

    protected array $fillable = [
        'user_id',
        'new_email',
        'status',
        'created_at',
        'resolved_at',
        'resolved_by',
    ];

    public function hasPendingForUser(int $userId): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT id FROM email_change_requests WHERE user_id = :user_id AND status = "PENDING" LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
