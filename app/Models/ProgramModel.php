<?php

declare(strict_types=1);

namespace App\Models;

final class ProgramModel extends BaseModel
{
    protected string $table = 'programs';

    protected array $fillable = [
        'program_name',
        'program_code',
        'duration_semesters',
        'is_active',
        'created_by',
        'created_at',
    ];
}
