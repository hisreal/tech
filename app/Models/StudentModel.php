<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class StudentModel extends BaseModel
{
    protected string $table = 'students';

    /** @var array<int, string> */
    protected array $fillable = [
        'user_id', 'admission_no', 'registration_no', 'first_name', 'middle_name', 'last_name',
        'gender', 'date_of_birth', 'blood_group', 'genotype', 'religion', 'nationality', 'state',
        'local_government', 'phone', 'email', 'address', 'passport_path', 'medical_conditions',
        'allergies', 'emergency_contact', 'status', 'profile_completion_status', 'profile_completion_percentage',
    ];
}
