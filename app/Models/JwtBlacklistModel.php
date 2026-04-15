<?php

declare(strict_types=1);

namespace App\Models;

final class JwtBlacklistModel extends BaseModel
{
    protected string $table = 'jwt_blacklist';

    protected array $fillable = [
        'jti',
        'user_id',
        'expires_at',
        'created_at',
    ];

    public function findByJti(string $jti): ?array
    {
        return $this->firstWhere('jti', $jti);
    }
}
