<?php

declare(strict_types=1);

namespace App\Models;

final class SemesterModel extends BaseModel
{
    protected string $table = 'semesters';

    protected array $fillable = [
        'program_id',
        'semester_number',
        'academic_year',
        'is_current',
        'fee_amount',
    ];

    /** @return array<int, array<string, mixed>> */
    public function getCurrentSemesters(): array
    {
        return $this->where('is_current', 1);
    }
}
