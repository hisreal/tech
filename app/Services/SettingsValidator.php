<?php

declare(strict_types=1);

namespace App\Services;

final class SettingsValidator
{
    /** @param array<string,mixed> $data @return array<string,string> */
    public function validate(string $section, array $data): array
    {
        return match ($section) {
            'school_information' => $this->schoolInformation($data),
            'academic_settings' => $this->academicSettings($data),
            'attendance_settings' => $this->attendanceSettings($data),
            'cbt_settings' => $this->cbtSettings($data),
            default => ['section' => 'Unknown settings section.'],
        };
    }

    private function schoolInformation(array $data): array
    {
        $errors = [];
        if (trim((string) ($data['school_name'] ?? '')) === '') { $errors['school_name'] = 'School name is required.'; }
        $email = trim((string) ($data['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Enter a valid email address.'; }
        $website = trim((string) ($data['website'] ?? ''));
        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) { $errors['website'] = 'Enter a valid website URL.'; }
        return $errors;
    }

    private function academicSettings(array $data): array
    {
        $errors = [];
        if ((int) ($data['current_session_id'] ?? 0) < 1) { $errors['current_session_id'] = 'Select current academic session.'; }
        if ((int) ($data['current_term_id'] ?? 0) < 1) { $errors['current_term_id'] = 'Select current term.'; }
        $this->numberRange($errors, $data, 'pass_mark', 0, 100, 'Default pass mark must be between 0 and 100.');
        return $errors;
    }

    private function attendanceSettings(array $data): array
    {
        $errors = [];
        foreach (['opening_time', 'closing_time', 'attendance_start_time', 'late_arrival_threshold'] as $field) {
            if (!$this->time((string) ($data[$field] ?? ''))) { $errors[$field] = 'Enter a valid time.'; }
        }
        $this->numberRange($errors, $data, 'attendance_grace_period', 0, 120, 'Grace period must be between 0 and 120 minutes.');
        return $errors;
    }

    private function cbtSettings(array $data): array
    {
        $errors = [];
        $this->numberRange($errors, $data, 'default_duration_minutes', 1, 600, 'Exam duration must be between 1 and 600 minutes.');
        $this->numberRange($errors, $data, 'default_pass_mark', 0, 100, 'CBT pass mark must be between 0 and 100.');
        $this->numberRange($errors, $data, 'maximum_attempts', 1, 20, 'Maximum attempts must be between 1 and 20.');
        return $errors;
    }

    /** @param array<string,string> $errors */
    private function numberRange(array &$errors, array $data, string $field, int $min, int $max, string $message): void
    {
        $value = $data[$field] ?? null;
        if (!is_numeric($value) || (float) $value < $min || (float) $value > $max) {
            $errors[$field] = $message;
        }
    }

    private function time(string $value): bool
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
    }
}
