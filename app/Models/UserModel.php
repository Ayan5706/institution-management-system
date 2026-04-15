<?php

declare(strict_types=1);

namespace App\Models;

final class UserModel extends BaseModel
{
    protected string $table = 'users';

    protected array $fillable = [
        'role',
        'login_id',
        'password_hash',
        'full_name',
        'email',
        'phone',
        'is_active',
        'must_change_password',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function findByLoginId(string $loginId): ?array
    {
        return $this->firstWhere('login_id', $loginId);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->firstWhere('email', $email);
    }
}
