<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class DepartmentModel extends BaseModel
{
    protected string $table = 'departments';

    /** @var array<int, string> */
    protected array $fillable = ['name', 'description', 'status'];
}
