<?php

declare(strict_types=1);

namespace App\Models;

final class TeacherAssignmentModel extends BaseModel
{
    protected string $table = 'teacher_assignments';

    protected array $fillable = [
        'teacher_id',
        'subject_id',
    ];
}
