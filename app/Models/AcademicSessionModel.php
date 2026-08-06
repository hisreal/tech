<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class AcademicSessionModel extends BaseModel
{
    protected string $table = 'academic_sessions';

    /** @var array<int, string> */
    protected array $fillable = ['name', 'start_date', 'end_date', 'status'];
}
