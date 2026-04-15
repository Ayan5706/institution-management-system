<?php

declare(strict_types=1);

namespace App\Models;

final class StudentFeeModel extends BaseModel
{
    protected string $table = 'student_fees';

    protected array $fillable = [
        'student_id',
        'semester_id',
        'amount_paid',
    ];

    /** @return array<int, array<string, mixed>> */
    public function forStudent(int $studentId): array
    {
        return $this->where('student_id', $studentId);
    }
}
