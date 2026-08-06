<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class StaffModel extends BaseModel
{
    protected string $table = 'staff';

    /** @var array<int, string> */
    protected array $fillable = [
        'user_id', 'staff_no', 'staff_type', 'first_name', 'middle_name', 'last_name', 'gender',
        'date_of_birth', 'phone', 'email', 'address', 'state', 'local_government', 'nationality',
        'department_id', 'designation', 'employment_date', 'employment_status', 'qualification',
        'specialization', 'years_experience', 'salary_grade', 'contract_type', 'passport_path',
    ];
}
