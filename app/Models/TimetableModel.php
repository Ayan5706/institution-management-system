<?php

declare(strict_types=1);

namespace App\Models;

final class TimetableModel extends BaseModel
{
    protected string $table = 'timetables';

    protected array $fillable = [
        'teacher_assignment_id',
        'day',
        'start_time',
        'end_time',
    ];
}
