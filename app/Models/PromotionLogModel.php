<?php

declare(strict_types=1);

namespace App\Models;

final class PromotionLogModel extends BaseModel
{
    protected string $table = 'promotions_log';

    protected array $fillable = [
        'semester_id',
        'student_id',
        'status',
        'performed_by',
        'performed_at',
        'notes',
    ];
}
