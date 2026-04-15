<?php

declare(strict_types=1);

namespace App\Models;

final class SubjectModel extends BaseModel
{
    protected string $table = 'subjects';

    protected array $fillable = [
        'semester_id',
        'subject_name',
        'subject_code',
    ];
}
