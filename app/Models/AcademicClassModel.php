<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class AcademicClassModel extends BaseModel
{
    protected string $table = 'classes';

    /** @var array<int, string> */
    protected array $fillable = ['name', 'level', 'status'];
}
