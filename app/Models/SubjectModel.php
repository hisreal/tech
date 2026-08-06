<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class SubjectModel extends BaseModel
{
    protected string $table = 'subjects';

    /** @var array<int, string> */
    protected array $fillable = ['code', 'name', 'department_id', 'subject_type', 'status'];
}
