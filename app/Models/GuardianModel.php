<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class GuardianModel extends BaseModel
{
    protected string $table = 'guardians';

    /** @var array<int, string> */
    protected array $fillable = ['full_name', 'relationship', 'phone', 'email', 'address', 'occupation'];
}
