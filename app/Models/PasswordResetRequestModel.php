<?php

declare(strict_types=1);

namespace App\Models;

final class PasswordResetRequestModel extends BaseModel
{
    protected string $table = 'password_reset_requests';

    protected array $fillable = [
        'requested_by',
        'status',
        'created_at',
        'resolved_at',
        'resolved_by',
    ];

    /** @return array<int, array<string, mixed>> */
    public function pending(): array
    {
        return $this->where('status', 'PENDING');
    }
}
