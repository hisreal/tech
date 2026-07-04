<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\FileUploader;
use App\Models\SettingsModel;

final class SettingsService
{
    private SettingsModel $model;
    private SettingsValidator $validator;

    /** @var array<string,array{value:mixed,type:string,group:string,public?:bool}> */
    private array $defaults = [
        'school.name' => ['value' => 'Brighter Future Standard School, Katsina', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.motto' => ['value' => 'Sound Education for Great Future', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.logo' => ['value' => '', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.address' => ['value' => 'Along Old KTTV, Gawu Road, Near Layout Primary School, Katsina, Katsina State', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.phone' => ['value' => '08169192710, 08158592533', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.email' => ['value' => 'info@brighterfuture.edu.ng', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.website' => ['value' => 'https://brighterfuture.edu.ng', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.type' => ['value' => 'Creche, Nursery, Primary & JSS', 'type' => 'string', 'group' => 'general', 'public' => true],
        'school.principal_name' => ['value' => 'Mrs. Amina Bello', 'type' => 'string', 'group' => 'general', 'public' => true],
        'academic.current_session_id' => ['value' => '', 'type' => 'number', 'group' => 'academic'],
        'academic.current_term_id' => ['value' => '', 'type' => 'number', 'group' => 'academic'],
        'result.pass_mark' => ['value' => 50, 'type' => 'number', 'group' => 'results'],
        'result.grading_system' => ['value' => 'A-F Grading Scale', 'type' => 'string', 'group' => 'results'],
        'result.enable_position_calculation' => ['value' => true, 'type' => 'boolean', 'group' => 'results'],
        'academic.auto_promote_students' => ['value' => false, 'type' => 'boolean', 'group' => 'academic'],
        'result.auto_publish_results' => ['value' => false, 'type' => 'boolean', 'group' => 'results'],
        'timetable.opening_time' => ['value' => '08:00', 'type' => 'time', 'group' => 'timetable'],
        'timetable.closing_time' => ['value' => '15:00', 'type' => 'time', 'group' => 'timetable'],
        'attendance.start_time' => ['value' => '08:05', 'type' => 'time', 'group' => 'attendance'],
        'attendance.late_arrival_threshold' => ['value' => '08:30', 'type' => 'time', 'group' => 'attendance'],
        'attendance.grace_period_minutes' => ['value' => 10, 'type' => 'number', 'group' => 'attendance'],
        'attendance.enable_student_attendance' => ['value' => true, 'type' => 'boolean', 'group' => 'attendance'],
        'attendance.enable_teacher_attendance' => ['value' => true, 'type' => 'boolean', 'group' => 'attendance'],
        'cbt.default_duration_minutes' => ['value' => 30, 'type' => 'number', 'group' => 'cbt'],
        'cbt.default_pass_mark' => ['value' => 50, 'type' => 'number', 'group' => 'cbt'],
        'cbt.maximum_attempts' => ['value' => 1, 'type' => 'number', 'group' => 'cbt'],
        'cbt.randomize_questions' => ['value' => true, 'type' => 'boolean', 'group' => 'cbt'],
        'cbt.randomize_answers' => ['value' => true, 'type' => 'boolean', 'group' => 'cbt'],
        'cbt.auto_submit' => ['value' => true, 'type' => 'boolean', 'group' => 'cbt'],
        'cbt.show_results_immediately' => ['value' => false, 'type' => 'boolean', 'group' => 'cbt'],
        'cbt.allow_review_after_exam' => ['value' => true, 'type' => 'boolean', 'group' => 'cbt'],
    ];

    public function __construct(?SettingsModel $model = null, ?SettingsValidator $validator = null)
    {
        $this->model = $model ?? new SettingsModel();
        $this->validator = $validator ?? new SettingsValidator();
    }

    /** @return array{settings:array<string,mixed>,sessions:array<int,array<string,mixed>>,terms:array<int,array<string,mixed>>} */
    public function pageData(): array
    {
        $this->ensureDefaults();
        $settings = $this->flatten($this->model->all());
        $sessions = $this->model->academicSessions();
        $terms = $this->model->terms($this->int($settings['academic.current_session_id'] ?? 0) ?: null);

        $resolved = [];
        if ($this->int($settings['academic.current_session_id'] ?? 0) < 1) {
            foreach ($sessions as $session) {
                if (($session['status'] ?? '') === 'active') {
                    $settings['academic.current_session_id'] = (int) $session['id'];
                    $resolved['academic.current_session_id'] = ['value' => (int) $session['id'], 'type' => 'number', 'group' => 'academic'];
                    $terms = $this->model->terms((int) $session['id']);
                    break;
                }
            }
        }
        if ($this->int($settings['academic.current_term_id'] ?? 0) < 1) {
            foreach ($terms as $term) {
                if (($term['status'] ?? '') === 'active') {
                    $settings['academic.current_term_id'] = (int) $term['id'];
                    $resolved['academic.current_term_id'] = ['value' => (int) $term['id'], 'type' => 'number', 'group' => 'academic'];
                    break;
                }
            }
        }
        if ($resolved) {
            $this->model->upsertMany($resolved, null);
        }

        return ['settings' => $settings, 'sessions' => $sessions, 'terms' => $terms];
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $file @param array<string,mixed>|null $actor */
    public function save(string $section, array $data, ?array $file, ?array $actor): array
    {
        $normalized = $this->normalize($section, $data);
        $errors = $this->validator->validate($section, $normalized);
        if ($errors) {
            return ['success' => false, 'message' => 'Validation failed. Please check the highlighted fields.', 'errors' => $errors];
        }

        $before = $this->flatten($this->model->all());
        $settings = $this->mapToSettings($section, $normalized);

        try {
            if ($section === 'school_information' && $file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $oldLogo = (string) ($before['school.logo'] ?? '');
                $uploaded = FileUploader::upload($file, 'branding', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 2 * 1024 * 1024);
                $settings['school.logo'] = ['value' => $uploaded['path'], 'type' => 'string', 'group' => 'general', 'public' => true];
                if ($oldLogo !== '' && str_starts_with($oldLogo, 'app/Storage/uploads/')) {
                    FileUploader::delete($oldLogo);
                }
            }

            $this->model->upsertMany($settings, isset($actor['id']) ? (int) $actor['id'] : null);
            $after = $this->flatten($this->model->all());
            $changed = $this->changedValues(array_keys($settings), $before, $after);
            $this->model->audit($actor, $section, $this->slice($before, array_keys($settings)), $changed);
        } catch (\InvalidArgumentException $exception) {
            return ['success' => false, 'message' => $exception->getMessage(), 'errors' => ['school_logo' => $exception->getMessage()]];
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'System error. Settings could not be saved.', 'errors' => []];
        }

        return ['success' => true, 'message' => $section === 'school_information' && isset($settings['school.logo']) ? 'Settings saved successfully. Logo uploaded successfully.' : 'Settings saved successfully.', 'errors' => []];
    }

    public function logoUrl(?string $path): string
    {
        $path = trim((string) $path);
        return $path !== '' ? '../' . ltrim($path, './') : '../assets/img/logo.svg';
    }

    private function ensureDefaults(): void
    {
        $existing = $this->model->all();
        $missing = [];
        foreach ($this->defaults as $key => $default) {
            if (!array_key_exists($key, $existing)) {
                $missing[$key] = $default;
            }
        }
        if ($missing) {
            $this->model->upsertMany($missing, null);
        }
    }

    /** @param array<string,array{value:mixed,type:string,group:string}> $settings @return array<string,mixed> */
    private function flatten(array $settings): array
    {
        $flat = [];
        foreach ($this->defaults as $key => $default) {
            $flat[$key] = $settings[$key]['value'] ?? $default['value'];
        }
        foreach ($settings as $key => $setting) {
            $flat[$key] = $setting['value'];
        }
        return $flat;
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private function normalize(string $section, array $data): array
    {
        return match ($section) {
            'school_information' => [
                'school_name' => trim((string) ($data['school_name'] ?? '')),
                'school_motto' => trim((string) ($data['school_motto'] ?? '')),
                'school_address' => trim((string) ($data['school_address'] ?? '')),
                'phone' => trim((string) ($data['phone'] ?? '')),
                'email' => trim((string) ($data['email'] ?? '')),
                'website' => trim((string) ($data['website'] ?? '')),
                'school_type' => trim((string) ($data['school_type'] ?? '')),
                'principal_name' => trim((string) ($data['principal_name'] ?? '')),
            ],
            'academic_settings' => [
                'current_session_id' => $this->int($data['current_session_id'] ?? 0),
                'current_term_id' => $this->int($data['current_term_id'] ?? 0),
                'pass_mark' => $this->number($data['pass_mark'] ?? 0),
                'grading_system' => trim((string) ($data['grading_system'] ?? '')),
                'enable_position_calculation' => $this->bool($data['enable_position_calculation'] ?? false),
                'auto_promote_students' => $this->bool($data['auto_promote_students'] ?? false),
                'auto_publish_results' => $this->bool($data['auto_publish_results'] ?? false),
            ],
            'attendance_settings' => [
                'opening_time' => trim((string) ($data['opening_time'] ?? '')),
                'closing_time' => trim((string) ($data['closing_time'] ?? '')),
                'attendance_start_time' => trim((string) ($data['attendance_start_time'] ?? '')),
                'late_arrival_threshold' => trim((string) ($data['late_arrival_threshold'] ?? '')),
                'attendance_grace_period' => $this->int($data['attendance_grace_period'] ?? 0),
                'enable_student_attendance' => $this->bool($data['enable_student_attendance'] ?? false),
                'enable_teacher_attendance' => $this->bool($data['enable_teacher_attendance'] ?? false),
            ],
            'cbt_settings' => [
                'default_duration_minutes' => $this->int($data['default_duration_minutes'] ?? 0),
                'default_pass_mark' => $this->number($data['default_pass_mark'] ?? 0),
                'maximum_attempts' => $this->int($data['maximum_attempts'] ?? 0),
                'randomize_questions' => $this->bool($data['randomize_questions'] ?? false),
                'randomize_answers' => $this->bool($data['randomize_answers'] ?? false),
                'auto_submit' => $this->bool($data['auto_submit'] ?? false),
                'show_results_immediately' => $this->bool($data['show_results_immediately'] ?? false),
                'allow_review_after_exam' => $this->bool($data['allow_review_after_exam'] ?? false),
            ],
            default => [],
        };
    }

    /** @param array<string,mixed> $data @return array<string,array{value:mixed,type:string,group:string,public?:bool}> */
    private function mapToSettings(string $section, array $data): array
    {
        return match ($section) {
            'school_information' => [
                'school.name' => ['value' => $data['school_name'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.motto' => ['value' => $data['school_motto'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.address' => ['value' => $data['school_address'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.phone' => ['value' => $data['phone'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.email' => ['value' => $data['email'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.website' => ['value' => $data['website'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.type' => ['value' => $data['school_type'], 'type' => 'string', 'group' => 'general', 'public' => true],
                'school.principal_name' => ['value' => $data['principal_name'], 'type' => 'string', 'group' => 'general', 'public' => true],
            ],
            'academic_settings' => [
                'academic.current_session_id' => ['value' => $data['current_session_id'], 'type' => 'number', 'group' => 'academic'],
                'academic.current_term_id' => ['value' => $data['current_term_id'], 'type' => 'number', 'group' => 'academic'],
                'result.pass_mark' => ['value' => $data['pass_mark'], 'type' => 'number', 'group' => 'results'],
                'result.grading_system' => ['value' => $data['grading_system'], 'type' => 'string', 'group' => 'results'],
                'result.enable_position_calculation' => ['value' => $data['enable_position_calculation'], 'type' => 'boolean', 'group' => 'results'],
                'academic.auto_promote_students' => ['value' => $data['auto_promote_students'], 'type' => 'boolean', 'group' => 'academic'],
                'result.auto_publish_results' => ['value' => $data['auto_publish_results'], 'type' => 'boolean', 'group' => 'results'],
            ],
            'attendance_settings' => [
                'timetable.opening_time' => ['value' => $data['opening_time'], 'type' => 'time', 'group' => 'timetable'],
                'timetable.closing_time' => ['value' => $data['closing_time'], 'type' => 'time', 'group' => 'timetable'],
                'attendance.start_time' => ['value' => $data['attendance_start_time'], 'type' => 'time', 'group' => 'attendance'],
                'attendance.late_arrival_threshold' => ['value' => $data['late_arrival_threshold'], 'type' => 'time', 'group' => 'attendance'],
                'attendance.grace_period_minutes' => ['value' => $data['attendance_grace_period'], 'type' => 'number', 'group' => 'attendance'],
                'attendance.enable_student_attendance' => ['value' => $data['enable_student_attendance'], 'type' => 'boolean', 'group' => 'attendance'],
                'attendance.enable_teacher_attendance' => ['value' => $data['enable_teacher_attendance'], 'type' => 'boolean', 'group' => 'attendance'],
            ],
            'cbt_settings' => [
                'cbt.default_duration_minutes' => ['value' => $data['default_duration_minutes'], 'type' => 'number', 'group' => 'cbt'],
                'cbt.default_pass_mark' => ['value' => $data['default_pass_mark'], 'type' => 'number', 'group' => 'cbt'],
                'cbt.maximum_attempts' => ['value' => $data['maximum_attempts'], 'type' => 'number', 'group' => 'cbt'],
                'cbt.randomize_questions' => ['value' => $data['randomize_questions'], 'type' => 'boolean', 'group' => 'cbt'],
                'cbt.randomize_answers' => ['value' => $data['randomize_answers'], 'type' => 'boolean', 'group' => 'cbt'],
                'cbt.auto_submit' => ['value' => $data['auto_submit'], 'type' => 'boolean', 'group' => 'cbt'],
                'cbt.show_results_immediately' => ['value' => $data['show_results_immediately'], 'type' => 'boolean', 'group' => 'cbt'],
                'cbt.allow_review_after_exam' => ['value' => $data['allow_review_after_exam'], 'type' => 'boolean', 'group' => 'cbt'],
            ],
            default => [],
        };
    }

    /** @param array<int,string> $keys @param array<string,mixed> $before @param array<string,mixed> $after @return array<string,mixed> */
    private function changedValues(array $keys, array $before, array $after): array
    {
        $changed = [];
        foreach ($keys as $key) {
            if (($before[$key] ?? null) != ($after[$key] ?? null)) {
                $changed[$key] = $after[$key] ?? null;
            }
        }
        return $changed;
    }

    /** @param array<string,mixed> $data @param array<int,string> $keys @return array<string,mixed> */
    private function slice(array $data, array $keys): array
    {
        $slice = [];
        foreach ($keys as $key) {
            $slice[$key] = $data[$key] ?? null;
        }
        return $slice;
    }

    private function bool(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on', 'enabled'], true);
    }

    private function int(mixed $value): int
    {
        return (int) $value;
    }

    private function number(mixed $value): float
    {
        return (float) $value;
    }
}
