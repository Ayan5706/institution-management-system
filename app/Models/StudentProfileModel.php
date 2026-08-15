<?php

declare(strict_types=1);

namespace App\Models;

final class StudentProfileModel extends BaseModel
{
    protected string $table = 'student_profiles';

    protected array $fillable = [
        'user_id',
        'father_name',
        'registration_number',
        'date_of_birth',
        'program_id',
        'enrollment_semester_id',
    ];

    public function findByUserId(int $userId): ?array
    {
        return $this->firstWhere('user_id', $userId);
    }
}
