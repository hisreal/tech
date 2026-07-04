<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Handles Excel-compatible CSV student import validation and persistence.
 */
final class StudentImportService
{
    /** @var array<int, string> */
    public const HEADERS = [
        'registration_no',
        'first_name',
        'last_name',
        'class',
    ];

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Returns sample CSV content for the downloadable template.
     */
    public function templateCsv(): string
    {
        return $this->csvString([
            self::HEADERS,
            ['REG-2026-010', 'Aisha', 'Bello', 'JSS 1'],
        ]);
    }

    /**
     * Parses an uploaded CSV file and returns validation preview data.
     *
     * @param array<string, mixed> $file
     * @return array{success: bool, message: string, rows: array<int, array<string, mixed>>, valid_count: int, invalid_count: int}
     */
    public function preview(array $file): array
    {
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $this->emptyResult('Please upload a valid CSV file.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'txt'], true)) {
            return $this->emptyResult('Upload the CSV template. You can edit it with Excel and save as CSV.');
        }

        $parsed = $this->parseCsv((string) $file['tmp_name']);
        if (!$parsed['success']) {
            return $this->emptyResult($parsed['message']);
        }

        $seenRegistration = [];
        $rows = [];

        foreach ($parsed['rows'] as $index => $row) {
            $validation = $this->validateRow($row, $seenRegistration);
            $rows[] = [
                'line' => $index + 2,
                'data' => $row,
                'errors' => $validation,
                'valid' => $validation === [],
            ];
        }

        $valid = count(array_filter($rows, static fn (array $row): bool => $row['valid']));

        return [
            'success' => true,
            'message' => 'File parsed successfully.',
            'rows' => $rows,
            'valid_count' => $valid,
            'invalid_count' => count($rows) - $valid,
        ];
    }

    /**
     * Imports previously validated rows.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed>|null $actor
     * @return array{success: bool, message: string, imported: int, skipped: int, credentials?: array<int,array<string,string>>}
     */
    public function import(array $rows, ?array $actor = null): array
    {
        $validRows = array_values(array_filter($rows, static fn (array $row): bool => ($row['valid'] ?? false) === true));
        if ($validRows === []) {
            return ['success' => false, 'message' => 'There are no valid rows to import.', 'imported' => 0, 'skipped' => count($rows)];
        }

        $studentRole = $this->roleId('student');
        $session = $this->activeSession();
        if ($studentRole === null || $session === null) {
            return ['success' => false, 'message' => 'Student role or active academic session is missing.', 'imported' => 0, 'skipped' => count($rows)];
        }

        $imported = 0;
        $credentials = [];
        $this->db->beginTransaction();

        try {
            foreach ($validRows as $row) {
                $data = $row['data'];
                $class = $this->classByName((string) $data['class']);
                if (!$class) {
                    continue;
                }

                $password = $this->temporaryPassword();
                $registrationNo = (string) $data['registration_no'];

                $this->db->execute(
                    'INSERT INTO users (username, email, password_hash, user_type, status, password_must_change, temp_password_created_at) VALUES (:username, :email, :password_hash, :user_type, :status, :password_must_change, NOW())',
                    [
                        'username' => $registrationNo,
                        'email' => null,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'user_type' => 'student',
                        'status' => 'active',
                        'password_must_change' => 1,
                    ]
                );
                $userId = (int) $this->db->lastInsertId();
                $this->db->execute('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)', ['user_id' => $userId, 'role_id' => $studentRole]);

                $this->db->execute(
                    'INSERT INTO students (user_id, admission_no, registration_no, first_name, last_name, status, profile_completion_status, profile_completion_percentage) VALUES (:user_id, :admission_no, :registration_no, :first_name, :last_name, :status, :profile_completion_status, :profile_completion_percentage)',
                    [
                        'user_id' => $userId,
                        'admission_no' => null,
                        'registration_no' => $registrationNo,
                        'first_name' => (string) $data['first_name'],
                        'last_name' => (string) $data['last_name'],
                        'status' => 'active',
                        'profile_completion_status' => 'incomplete',
                        'profile_completion_percentage' => 0,
                    ]
                );
                $studentId = (int) $this->db->lastInsertId();

                $this->db->execute(
                    'INSERT INTO student_enrollments (student_id, session_id, class_id, section_id, roll_number, status, enrolled_at) VALUES (:student_id, :session_id, :class_id, :section_id, :roll_number, :status, :enrolled_at)',
                    [
                        'student_id' => $studentId,
                        'session_id' => (int) $session['id'],
                        'class_id' => (int) $class['id'],
                        'section_id' => null,
                        'roll_number' => $registrationNo,
                        'status' => 'active',
                        'enrolled_at' => date('Y-m-d'),
                    ]
                );

                $imported++;
                $credentials[] = [
                    'registration_no' => $registrationNo,
                    'username' => $registrationNo,
                    'temporary_password' => $password,
                    'full_name' => trim((string) $data['first_name'] . ' ' . (string) $data['last_name']),
                ];
            }

            $this->audit((int) ($actor['id'] ?? 0), 'bulk_student_import', ['imported' => $imported, 'minimal_template' => true]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Import failed. No rows were saved.', 'imported' => 0, 'skipped' => count($rows)];
        }

        return ['success' => true, 'message' => "Imported {$imported} student(s) successfully. Copy the temporary passwords below before leaving this page.", 'imported' => $imported, 'skipped' => count($rows) - $imported, 'credentials' => $credentials];
    }

    /** @return array{success: bool, message: string, rows?: array<int, array<string, string>>} */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return ['success' => false, 'message' => 'Unable to read uploaded file.'];
        }

        $headers = fgetcsv($handle);
        if (!is_array($headers)) {
            fclose($handle);
            return ['success' => false, 'message' => 'The CSV file is empty.'];
        }

        $headers = array_map([$this, 'normalizeHeader'], $headers);
        $missing = array_diff(self::HEADERS, $headers);
        if ($missing !== []) {
            fclose($handle);
            return ['success' => false, 'message' => 'Template columns are missing: ' . implode(', ', $missing)];
        }

        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null] || trim(implode('', $line)) === '') {
                continue;
            }
            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[$header] = trim((string) ($line[$index] ?? ''));
            }
            $rows[] = $assoc;
        }
        fclose($handle);

        return ['success' => true, 'message' => 'Parsed.', 'rows' => $rows];
    }


    private function normalizeHeader(string $header): string
    {
        $header = str_replace("\xEF\xBB\xBF", '', $header);
        $header = preg_replace('/[^A-Za-z0-9_]/', '', $header) ?? $header;
        return strtolower(trim($header));
    }
    /** @param array<string,string> $row @param array<string,bool> $seenRegistration @return array<int,string> */
    private function validateRow(array $row, array &$seenRegistration): array
    {
        $errors = [];
        foreach (self::HEADERS as $field) {
            if (($row[$field] ?? '') === '') {
                $errors[] = "{$field} is required.";
            }
        }

        $this->registrationDuplicateCheck($row['registration_no'] ?? '', $seenRegistration, $errors);

        if (($row['class'] ?? '') !== '' && !$this->classByName((string) $row['class'])) {
            $errors[] = 'class does not exist.';
        }

        return $errors;
    }

    /** @param array<string,bool> $seen @param array<int,string> $errors */
    private function registrationDuplicateCheck(string $value, array &$seen, array &$errors): void
    {
        if ($value === '') {
            return;
        }

        $key = strtolower($value);
        if (isset($seen[$key])) {
            $errors[] = 'Duplicate registration_no inside uploaded file.';
        }
        $seen[$key] = true;

        if ($this->db->fetchOne('SELECT 1 FROM students WHERE registration_no = :value LIMIT 1', ['value' => $value])) {
            $errors[] = 'registration_no already exists in the database.';
        }
        if ($this->db->fetchOne('SELECT 1 FROM users WHERE username = :value LIMIT 1', ['value' => $value])) {
            $errors[] = 'registration_no is already used as a username.';
        }
    }

    /** @return array<string,mixed>|null */
    private function classByName(string $name): ?array
    {
        return $this->db->fetchOne('SELECT * FROM classes WHERE LOWER(name) = LOWER(:name) LIMIT 1', ['name' => trim($name)]);
    }

    /** @return array<string,mixed>|null */
    private function activeSession(): ?array
    {
        return $this->db->fetchOne("SELECT * FROM academic_sessions WHERE status = 'active' ORDER BY id DESC LIMIT 1");
    }

    private function roleId(string $slug): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM roles WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        return $row ? (int) $row['id'] : null;
    }

    private function temporaryPassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@$%';
        $password = '';
        $max = strlen($alphabet) - 1;
        for ($i = 0; $i < 12; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }
        return $password;
    }

    /** @param array<int, array<int, string>> $rows */
    private function csvString(array $rows): string
    {
        $memory = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($memory, $row);
        }
        rewind($memory);
        return (string) stream_get_contents($memory);
    }

    /** @return array{success: bool, message: string, rows: array<int,array<string,mixed>>, valid_count: int, invalid_count: int} */
    private function emptyResult(string $message): array
    {
        return ['success' => false, 'message' => $message, 'rows' => [], 'valid_count' => 0, 'invalid_count' => 0];
    }

    /** @param array<string,mixed> $context */
    private function audit(int $actorId, string $action, array $context): void
    {
        $this->db->execute(
            'INSERT INTO audit_logs (actor_user_id, module, action, entity_table, new_values, ip_address, user_agent) VALUES (:actor, :module, :action, :entity_table, :new_values, :ip, :agent)',
            [
                'actor' => $actorId ?: null,
                'module' => 'students',
                'action' => $action,
                'entity_table' => 'students',
                'new_values' => json_encode($context),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
    }
}
