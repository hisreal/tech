<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class SectionModel extends BaseModel
{
    protected string $table = 'sections';

    /** @var array<int, string> */
    protected array $fillable = ['class_id', 'name', 'capacity', 'status'];
}
