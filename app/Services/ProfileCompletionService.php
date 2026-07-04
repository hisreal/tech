<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/** Reusable student profile completion calculator and persistence helper. */
final class ProfileCompletionService
{
    /** @var array<int,string> */
    public const REQUIRED_STUDENT_FIELDS = [
        'gender',
        'date_of_birth',
        'address',
        'religion',
        'nationality',
        'state',
        'local_government',
        'phone',
    ];

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /** @param array<string,mixed> $student @return array{status:string,percentage:int,missing:array<int,string>,complete:bool} */
    public function evaluate(array $student): array
    {
        $missing = [];
        foreach (self::REQUIRED_STUDENT_FIELDS as $field) {
            if (trim((string) ($student[$field] ?? '')) === '') {
                $missing[] = $field;
            }
        }

        $total = count(self::REQUIRED_STUDENT_FIELDS);
        $filled = $total - count($missing);
        $percentage = $total > 0 ? (int) round(($filled / $total) * 100) : 100;
        $complete = $missing === [];

        return [
            'status' => $complete ? 'complete' : 'incomplete',
            'percentage' => $percentage,
            'missing' => $missing,
            'complete' => $complete,
        ];
    }

    /** @param array<string,mixed> $student */
    public function sync(array $student): array
    {
        $completion = $this->evaluate($student);
        if (!empty($student['id'])) {
            $this->db->execute(
                'UPDATE students SET profile_completion_status = :status, profile_completion_percentage = :percentage WHERE id = :id',
                [
                    'status' => $completion['status'],
                    'percentage' => $completion['percentage'],
                    'id' => (int) $student['id'],
                ]
            );
        }

        return $completion;
    }

    public function label(string $field): string
    {
        return match ($field) {
            'date_of_birth' => 'Date of Birth',
            'state' => 'State of Origin',
            'local_government' => 'Local Government Area',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }
}
