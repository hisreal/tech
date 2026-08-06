<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class TermModel extends BaseModel
{
    protected string $table = 'terms';

    /** @var array<int, string> */
    protected array $fillable = ['session_id', 'name', 'start_date', 'end_date', 'status'];
}
