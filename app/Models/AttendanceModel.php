<?php

declare(strict_types=1);

namespace App\Models;

final class AttendanceModel extends BaseModel
{
    protected string $table = 'attendance';

    protected array $fillable = [
        'student_id',
        'timetable_slot_id',
        'date',
        'status',
        'marked_by',
        'marked_at',
    ];

    /** @return array<int, array<string, mixed>> */
    public function byDate(string $date): array
    {
        return $this->where('date', $date);
    }
}
