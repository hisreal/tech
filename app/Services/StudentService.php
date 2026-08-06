<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\FileUploader;
use App\Helpers\Paginator;
use App\Helpers\Security;
use App\Models\GuardianModel;
use App\Models\StudentModel;
use App\Traits\Auditable;

/**
 * Backing service for Student Management: list/create/update/delete/promote.
 */
final class StudentService
{
    use Auditable;

    private const GENDERS = ['male', 'female', 'other'];
    private const STATUSES = ['active', 'graduated', 'withdrawn', 'suspended', 'deleted'];
    private const DEFAULT_PER_PAGE = 10;

    private StudentModel $students;
    private GuardianModel $guardians;
    private ProfileCompletionService $completionService;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->students = new StudentModel($this->db);
        $this->guardians = new GuardianModel($this->db);
        $this->completionService = new ProfileCompletionService($this->db);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function list(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $sessionId = $this->intFilter($filters['session_id'] ?? 0) ?? $this->currentSessionId() ?? 0;

        $where = [];
        $params = ['session_id' => $sessionId];

        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.admission_no LIKE :search3 OR s.registration_no LIKE :search4)';
            $like = '%' . $search . '%';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
            $params['search4'] = $like;
        }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) {
            $where[] = 'se.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        if (($sectionId = $this->intFilter($filters['section_id'] ?? 0)) !== null) {
            $where[] = 'se.section_id = :section_id';
            $params['section_id'] = $sectionId;
        }
        if (($gender = $this->enumFilter($filters['gender'] ?? '', self::GENDERS)) !== null) {
            $where[] = 's.gender = :gender';
            $params['gender'] = $gender;
        }

        $status = $this->enumFilter($filters['status'] ?? '', self::STATUSES);
        if ($status !== null) {
            $where[] = 's.status = :status';
            $params['status'] = $status;
        } else {
            $where[] = "s.status <> 'deleted'";
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT s.*, se.class_id, se.section_id, se.roll_number, c.name AS class_name, sec.name AS section_name,
                    (SELECT g.phone FROM student_guardians sg INNER JOIN guardians g ON g.id = sg.guardian_id WHERE sg.student_id = s.id AND sg.is_primary = 1 LIMIT 1) AS guardian_phone
                FROM students s
                LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.session_id = :session_id
                LEFT JOIN classes c ON c.id = se.class_id
                LEFT JOIN sections sec ON sec.id = se.section_id
                {$whereSql}
                ORDER BY s.last_name ASC, s.first_name ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        $student = $this->students->find($id);

        if ($student === null) {
            return null;
        }

        $student['enrollment'] = $this->db->fetchOne(
            'SELECT se.*, c.name AS class_name, sec.name AS section_name, s.name AS session_name
             FROM student_enrollments se
             INNER JOIN classes c ON c.id = se.class_id
             LEFT JOIN sections sec ON sec.id = se.section_id
             INNER JOIN academic_sessions s ON s.id = se.session_id
             WHERE se.student_id = :id
             ORDER BY se.session_id DESC LIMIT 1',
            ['id' => $id]
        );

        $student['guardian'] = $this->db->fetchOne(
            'SELECT g.* FROM student_guardians sg INNER JOIN guardians g ON g.id = sg.guardian_id WHERE sg.student_id = :id AND sg.is_primary = 1 LIMIT 1',
            ['id' => $id]
        );

        $student['documents'] = $this->db->fetchAll(
            'SELECT * FROM student_documents WHERE student_id = :id ORDER BY uploaded_at DESC',
            ['id' => $id]
        );

        return $student;
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<string,mixed>> $files Keyed by form field name ($_FILES shape).
     * @param array<string,mixed>|null $actor
     * @return array{success:bool,message:string,errors?:array<string,string>,credentials?:array<string,string>,id?:int}
     */
    public function create(array $data, array $files, ?array $actor): array
    {
        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields, null);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $username = $fields['registration_no'];
            $password = Security::temporaryPassword();

            $this->db->execute(
                'INSERT INTO users (username, email, password_hash, user_type, status, password_must_change, temp_password_created_at) VALUES (:username, :email, :password_hash, :user_type, :status, 1, NOW())',
                [
                    'username' => $username,
                    'email' => $fields['email'] !== '' ? $fields['email'] : null,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'user_type' => 'student',
                    'status' => 'active',
                ]
            );
            $userId = (int) $this->db->lastInsertId();

            $roleRow = $this->db->fetchOne("SELECT id FROM roles WHERE slug = 'student' LIMIT 1");
            if ($roleRow) {
                $this->db->execute('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)', ['user_id' => $userId, 'role_id' => $roleRow['id']]);
            }

            $photoFile = $this->resolvePhotoFile($files);
            $photoPath = $photoFile ? FileUploader::upload($photoFile, 'students', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024)['path'] : null;

            $studentPayload = array_merge($this->studentPayload($fields, $photoPath), [
                'user_id' => $userId,
                'profile_completion_status' => 'incomplete',
                'profile_completion_percentage' => 0,
            ]);
            $studentId = (int) $this->students->create($studentPayload);

            $this->db->execute(
                'INSERT INTO student_enrollments (student_id, session_id, class_id, section_id, status, enrolled_at) VALUES (:student_id, :session_id, :class_id, :section_id, "active", :enrolled_at)',
                [
                    'student_id' => $studentId,
                    'session_id' => $fields['session_id'],
                    'class_id' => $fields['class_id'],
                    'section_id' => $fields['section_id'] ?: null,
                    'enrolled_at' => $fields['admission_date'] !== '' ? $fields['admission_date'] : date('Y-m-d'),
                ]
            );

            $this->syncGuardian($studentId, $fields);
            $this->syncDocuments($studentId, $files, $actor);

            $studentRow = $this->students->find($studentId);
            if ($studentRow !== null) {
                $this->completionService->sync($studentRow);
            }

            $this->audit($actor, 'students', 'student.created', 'students', $studentId, null, $studentPayload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to create the student right now.'];
        }

        return [
            'success' => true,
            'message' => 'Student created successfully. Copy the temporary password below before leaving this page.',
            'id' => $studentId,
            'credentials' => ['username' => $username, 'temporary_password' => $password],
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<string,mixed>> $files
     * @param array<string,mixed>|null $actor
     */
    public function update(int $id, array $data, array $files, ?array $actor): array
    {
        $before = $this->students->find($id);

        if ($before === null) {
            return ['success' => false, 'message' => 'Student not found.'];
        }

        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields, $id);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $photoFile = $this->resolvePhotoFile($files);
            $photoPath = null;
            if ($photoFile !== null) {
                $photoPath = FileUploader::upload($photoFile, 'students', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024)['path'];
                if (!empty($before['passport_path']) && str_starts_with((string) $before['passport_path'], 'app/Storage/uploads/')) {
                    FileUploader::delete((string) $before['passport_path']);
                }
            }

            $studentPayload = $this->studentPayload($fields, $photoPath);
            $this->students->update($id, $studentPayload);

            $existingEnrollment = $this->db->fetchOne(
                'SELECT * FROM student_enrollments WHERE student_id = :id AND session_id = :session_id',
                ['id' => $id, 'session_id' => $fields['session_id']]
            );
            if ($existingEnrollment !== null) {
                $this->db->execute(
                    'UPDATE student_enrollments SET class_id = :class_id, section_id = :section_id WHERE id = :id',
                    ['class_id' => $fields['class_id'], 'section_id' => $fields['section_id'] ?: null, 'id' => $existingEnrollment['id']]
                );
            } else {
                $this->db->execute(
                    'INSERT INTO student_enrollments (student_id, session_id, class_id, section_id, status, enrolled_at) VALUES (:student_id, :session_id, :class_id, :section_id, "active", :enrolled_at)',
                    ['student_id' => $id, 'session_id' => $fields['session_id'], 'class_id' => $fields['class_id'], 'section_id' => $fields['section_id'] ?: null, 'enrolled_at' => date('Y-m-d')]
                );
            }

            $this->syncGuardian($id, $fields);
            $this->syncDocuments($id, $files, $actor);

            if ($fields['email'] !== '' && !empty($before['user_id'])) {
                $this->db->execute('UPDATE users SET email = :email WHERE id = :id', ['email' => $fields['email'], 'id' => $before['user_id']]);
            }

            $studentRow = $this->students->find($id);
            if ($studentRow !== null) {
                $this->completionService->sync($studentRow);
            }

            $this->audit($actor, 'students', 'student.updated', 'students', $id, $before, $studentPayload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update the student right now.'];
        }

        return ['success' => true, 'message' => 'Student updated successfully.'];
    }

    /**
     * Soft-deletes a student (status = 'deleted') and deactivates the linked
     * account, rather than removing the row: nearly every related table
     * (enrollments, results, attendance, payments) cascades on students.id,
     * so a real delete would silently destroy academic/financial history.
     *
     * @param array<string,mixed>|null $actor
     */
    public function delete(int $id, ?array $actor): array
    {
        $before = $this->students->find($id);

        if ($before === null) {
            return ['success' => false, 'message' => 'Student not found.'];
        }
        if ($before['status'] === 'deleted') {
            return ['success' => false, 'message' => 'This student has already been deleted.'];
        }

        $this->db->beginTransaction();
        try {
            $this->students->update($id, ['status' => 'deleted']);
            if (!empty($before['user_id'])) {
                $this->db->execute('UPDATE users SET status = "inactive" WHERE id = :id', ['id' => $before['user_id']]);
            }
            $this->audit($actor, 'students', 'student.deleted', 'students', $id, $before, ['status' => 'deleted']);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to delete this student right now.'];
        }

        return ['success' => true, 'message' => 'Student deleted successfully.'];
    }

    /**
     * @param array<int,int> $studentIds
     * @param array<string,mixed>|null $actor
     * @return array{success:bool,message:string,promoted:int,skipped:int}
     */
    public function promote(array $studentIds, int $fromSessionId, int $toSessionId, int $toClassId, ?int $toSectionId, ?array $actor): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn (int $id): bool => $id > 0)));

        if ($studentIds === [] || $fromSessionId < 1 || $toSessionId < 1 || $toClassId < 1) {
            return ['success' => false, 'message' => 'Select at least one student and a destination session and class.', 'promoted' => 0, 'skipped' => 0];
        }

        $promoted = 0;
        $skipped = 0;

        $this->db->beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                $fromEnrollment = $this->db->fetchOne(
                    'SELECT * FROM student_enrollments WHERE student_id = :student_id AND session_id = :session_id',
                    ['student_id' => $studentId, 'session_id' => $fromSessionId]
                );
                $alreadyPromoted = $this->db->fetchOne(
                    'SELECT id FROM student_enrollments WHERE student_id = :student_id AND session_id = :session_id',
                    ['student_id' => $studentId, 'session_id' => $toSessionId]
                );

                if ($fromEnrollment === null || $alreadyPromoted !== null) {
                    $skipped++;
                    continue;
                }

                $this->db->execute(
                    'INSERT INTO student_enrollments (student_id, session_id, class_id, section_id, status, enrolled_at) VALUES (:student_id, :session_id, :class_id, :section_id, "active", :enrolled_at)',
                    ['student_id' => $studentId, 'session_id' => $toSessionId, 'class_id' => $toClassId, 'section_id' => $toSectionId, 'enrolled_at' => date('Y-m-d')]
                );
                $this->db->execute('UPDATE student_enrollments SET status = "promoted" WHERE id = :id', ['id' => $fromEnrollment['id']]);
                $this->db->execute(
                    'INSERT INTO promotion_history (student_id, from_session_id, to_session_id, from_class_id, to_class_id, from_section_id, to_section_id, promoted_by) VALUES (:student_id, :from_session, :to_session, :from_class, :to_class, :from_section, :to_section, :by)',
                    [
                        'student_id' => $studentId,
                        'from_session' => $fromSessionId,
                        'to_session' => $toSessionId,
                        'from_class' => $fromEnrollment['class_id'],
                        'to_class' => $toClassId,
                        'from_section' => $fromEnrollment['section_id'],
                        'to_section' => $toSectionId,
                        'by' => isset($actor['id']) ? (int) $actor['id'] : null,
                    ]
                );
                $this->audit(
                    $actor,
                    'students',
                    'student.promoted',
                    'students',
                    $studentId,
                    ['session_id' => $fromSessionId, 'class_id' => $fromEnrollment['class_id']],
                    ['session_id' => $toSessionId, 'class_id' => $toClassId, 'section_id' => $toSectionId]
                );
                $promoted++;
            }
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to complete promotion right now.', 'promoted' => 0, 'skipped' => 0];
        }

        return [
            'success' => true,
            'message' => "Promoted {$promoted} student(s)." . ($skipped > 0 ? " Skipped {$skipped} (no matching current enrollment or already promoted)." : ''),
            'promoted' => $promoted,
            'skipped' => $skipped,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function promotionHistory(int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT ph.*, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                fc.name AS from_class_name, tc.name AS to_class_name,
                fs.name AS from_session_name, ts.name AS to_session_name,
                u.username AS promoted_by_name
             FROM promotion_history ph
             INNER JOIN students s ON s.id = ph.student_id
             INNER JOIN classes fc ON fc.id = ph.from_class_id
             INNER JOIN classes tc ON tc.id = ph.to_class_id
             INNER JOIN academic_sessions fs ON fs.id = ph.from_session_id
             INNER JOIN academic_sessions ts ON ts.id = ph.to_session_id
             LEFT JOIN users u ON u.id = ph.promoted_by
             ORDER BY ph.promoted_at DESC
             LIMIT " . max(1, $limit)
        );
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> Aggregate data for the Student Reports page. */
    public function reportsSummary(array $filters = []): array
    {
        $sessionId = (int) ($filters['session_id'] ?? 0) ?: $this->currentSessionId();
        $classId = (int) ($filters['class_id'] ?? 0) ?: null;

        $where = ['se.status = "active"', 's.status <> "deleted"'];
        $params = [];
        if ($sessionId) { $where[] = 'se.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if ($classId) { $where[] = 'se.class_id = :class_id'; $params['class_id'] = $classId; }
        $whereSql = implode(' AND ', $where);

        $byClass = $this->db->fetchAll(
            "SELECT c.name AS class_name, sec.name AS section_name, COUNT(*) AS total,
                SUM(s.gender = 'male') AS male_count, SUM(s.gender = 'female') AS female_count
             FROM student_enrollments se
             INNER JOIN students s ON s.id = se.student_id
             INNER JOIN classes c ON c.id = se.class_id
             LEFT JOIN sections sec ON sec.id = se.section_id
             WHERE {$whereSql}
             GROUP BY c.id, sec.id
             ORDER BY c.name ASC, sec.name ASC",
            $params
        );

        $byGender = $this->db->fetchAll(
            "SELECT COALESCE(s.gender, 'unspecified') AS gender, COUNT(*) AS total
             FROM student_enrollments se INNER JOIN students s ON s.id = se.student_id
             WHERE {$whereSql} GROUP BY gender",
            $params
        );

        $byStatus = $this->db->fetchAll(
            'SELECT status, COUNT(*) AS total FROM students GROUP BY status ORDER BY total DESC'
        );

        $totalEnrolled = (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM student_enrollments se INNER JOIN students s ON s.id = se.student_id WHERE {$whereSql}", $params)['c'] ?? 0);
        $withGuardian = (int) ($this->db->fetchOne(
            "SELECT COUNT(DISTINCT se.student_id) c FROM student_enrollments se
             INNER JOIN students s ON s.id = se.student_id
             INNER JOIN student_guardians sg ON sg.student_id = se.student_id
             WHERE {$whereSql}",
            $params
        )['c'] ?? 0);

        return [
            'session_id' => $sessionId,
            'class_id' => $classId,
            'total_enrolled' => $totalEnrolled,
            'with_guardian' => $withGuardian,
            'without_guardian' => max(0, $totalEnrolled - $withGuardian),
            'by_class' => $byClass,
            'by_gender' => $byGender,
            'by_status' => $byStatus,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function classesForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM classes WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function sectionsForSelect(?int $classId = null): array
    {
        if ($classId) {
            return $this->db->fetchAll('SELECT id, name, class_id FROM sections WHERE class_id = :class_id AND status = "active" ORDER BY name ASC', ['class_id' => $classId]);
        }

        return $this->db->fetchAll('SELECT id, name, class_id FROM sections WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function sessionsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name, status FROM academic_sessions ORDER BY start_date DESC');
    }

    public function currentSessionId(): ?int
    {
        $row = $this->db->fetchOne("SELECT setting_value FROM school_settings WHERE setting_key = 'academic.current_session_id'");
        $id = $row ? (int) $row['setting_value'] : 0;

        if ($id > 0) {
            return $id;
        }

        $row = $this->db->fetchOne("SELECT id FROM academic_sessions WHERE status = 'active' ORDER BY id DESC LIMIT 1");

        return $row ? (int) $row['id'] : null;
    }

    /** @return array<string,mixed> */
    private function normalizeFields(array $data): array
    {
        return [
            'admission_no' => trim((string) ($data['admission_no'] ?? '')),
            'registration_no' => trim((string) ($data['registration_no'] ?? '')),
            'first_name' => trim((string) ($data['first_name'] ?? '')),
            'middle_name' => trim((string) ($data['middle_name'] ?? '')),
            'last_name' => trim((string) ($data['last_name'] ?? '')),
            'gender' => strtolower(trim((string) ($data['gender'] ?? ''))),
            'date_of_birth' => trim((string) ($data['date_of_birth'] ?? '')),
            'blood_group' => trim((string) ($data['blood_group'] ?? '')),
            'genotype' => trim((string) ($data['genotype'] ?? '')),
            'religion' => trim((string) ($data['religion'] ?? '')),
            'nationality' => trim((string) ($data['nationality'] ?? '')),
            'state' => trim((string) ($data['state'] ?? '')),
            'local_government' => trim((string) ($data['local_government'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'address' => trim((string) ($data['address'] ?? '')),
            'medical_conditions' => trim((string) ($data['medical_conditions'] ?? '')),
            'allergies' => trim((string) ($data['allergies'] ?? '')),
            'emergency_contact' => trim((string) ($data['emergency_contact'] ?? '')),
            'status' => strtolower(trim((string) ($data['student_status'] ?? $data['status'] ?? 'active'))),
            'session_id' => (int) ($data['academic_session'] ?? $data['session_id'] ?? 0),
            'class_id' => (int) ($data['class'] ?? $data['class_id'] ?? 0),
            'section_id' => (int) ($data['section'] ?? $data['section_id'] ?? 0),
            'admission_date' => trim((string) ($data['admission_date'] ?? '')),
            'guardian_name' => trim((string) ($data['guardian_name'] ?? '')),
            'relationship' => trim((string) ($data['relationship'] ?? '')),
            'parent_phone' => trim((string) ($data['parent_phone'] ?? '')),
            'parent_email' => trim((string) ($data['parent_email'] ?? '')),
            'parent_address' => trim((string) ($data['parent_address'] ?? '')),
            'occupation' => trim((string) ($data['occupation'] ?? '')),
        ];
    }

    /** @return array<string,string> */
    private function validate(array $f, ?int $excludeId): array
    {
        $errors = [];

        if ($f['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        }
        if ($f['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        }
        if ($f['registration_no'] === '') {
            $errors['registration_no'] = 'Registration number is required.';
        } elseif ($this->duplicateExists('students', 'registration_no', $f['registration_no'], $excludeId)) {
            $errors['registration_no'] = 'This registration number is already in use.';
        }
        if ($f['admission_no'] !== '' && $this->duplicateExists('students', 'admission_no', $f['admission_no'], $excludeId)) {
            $errors['admission_no'] = 'This admission number is already in use.';
        }
        if ($f['gender'] !== '' && !in_array($f['gender'], self::GENDERS, true)) {
            $errors['gender'] = 'Choose a valid gender.';
        }
        if ($f['date_of_birth'] !== '' && (strtotime($f['date_of_birth']) === false || strtotime($f['date_of_birth']) > time())) {
            $errors['date_of_birth'] = 'Enter a valid date of birth.';
        }
        if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (!in_array($f['status'], self::STATUSES, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if ($f['session_id'] < 1) {
            $errors['session_id'] = 'Choose an academic session.';
        }
        if ($f['class_id'] < 1) {
            $errors['class_id'] = 'Choose a class.';
        }
        if ($f['guardian_name'] === '') {
            $errors['guardian_name'] = 'Guardian name is required.';
        }
        if ($f['parent_phone'] === '') {
            $errors['parent_phone'] = 'Parent phone is required.';
        }
        if ($f['parent_email'] !== '' && !filter_var($f['parent_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['parent_email'] = 'Enter a valid parent email address.';
        }

        return $errors;
    }

    /** @return array<string,mixed> */
    private function studentPayload(array $f, ?string $photoPath): array
    {
        $payload = [
            'admission_no' => $f['admission_no'] !== '' ? $f['admission_no'] : null,
            'registration_no' => $f['registration_no'],
            'first_name' => $f['first_name'],
            'middle_name' => $f['middle_name'] !== '' ? $f['middle_name'] : null,
            'last_name' => $f['last_name'],
            'gender' => $f['gender'] !== '' ? $f['gender'] : null,
            'date_of_birth' => $f['date_of_birth'] !== '' ? $f['date_of_birth'] : null,
            'blood_group' => $f['blood_group'] !== '' ? $f['blood_group'] : null,
            'genotype' => $f['genotype'] !== '' ? $f['genotype'] : null,
            'religion' => $f['religion'] !== '' ? $f['religion'] : null,
            'nationality' => $f['nationality'] !== '' ? $f['nationality'] : null,
            'state' => $f['state'] !== '' ? $f['state'] : null,
            'local_government' => $f['local_government'] !== '' ? $f['local_government'] : null,
            'phone' => $f['phone'] !== '' ? $f['phone'] : null,
            'email' => $f['email'] !== '' ? $f['email'] : null,
            'address' => $f['address'] !== '' ? $f['address'] : null,
            'medical_conditions' => $f['medical_conditions'] !== '' ? $f['medical_conditions'] : null,
            'allergies' => $f['allergies'] !== '' ? $f['allergies'] : null,
            'emergency_contact' => $f['emergency_contact'] !== '' ? $f['emergency_contact'] : null,
            'status' => $f['status'],
        ];

        if ($photoPath !== null) {
            $payload['passport_path'] = $photoPath;
        }

        return $payload;
    }

    /** Prefers the "passport" upload field, falling back to "passport_photograph" (the form has both). */
    private function resolvePhotoFile(array $files): ?array
    {
        foreach (['passport', 'passport_photograph'] as $field) {
            $file = $files[$field] ?? null;
            if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                return $file;
            }
        }

        return null;
    }

    private function syncGuardian(int $studentId, array $f): void
    {
        if ($f['guardian_name'] === '' || $f['parent_phone'] === '') {
            return;
        }

        $existing = $this->db->fetchOne(
            'SELECT g.id FROM student_guardians sg INNER JOIN guardians g ON g.id = sg.guardian_id WHERE sg.student_id = :id AND sg.is_primary = 1 LIMIT 1',
            ['id' => $studentId]
        );

        $payload = [
            'full_name' => $f['guardian_name'],
            'relationship' => $f['relationship'] !== '' ? $f['relationship'] : null,
            'phone' => $f['parent_phone'],
            'email' => $f['parent_email'] !== '' ? $f['parent_email'] : null,
            'address' => $f['parent_address'] !== '' ? $f['parent_address'] : null,
            'occupation' => $f['occupation'] !== '' ? $f['occupation'] : null,
        ];

        if ($existing !== null) {
            $this->guardians->update((int) $existing['id'], $payload);
        } else {
            $guardianId = (int) $this->guardians->create($payload);
            $this->db->execute('INSERT INTO student_guardians (student_id, guardian_id, is_primary) VALUES (:student_id, :guardian_id, 1)', ['student_id' => $studentId, 'guardian_id' => $guardianId]);
        }
    }

    /** @param array<string,array<string,mixed>> $files @param array<string,mixed>|null $actor */
    private function syncDocuments(int $studentId, array $files, ?array $actor): void
    {
        $documentFields = ['birth_certificate' => 'Birth Certificate', 'previous_result' => 'Previous School Result', 'transfer_letter' => 'Transfer Letter'];

        foreach ($documentFields as $field => $label) {
            $file = $files[$field] ?? null;

            if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            try {
                $uploaded = FileUploader::upload($file, 'students/documents', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'], 5 * 1024 * 1024);
                $this->db->execute(
                    'INSERT INTO student_documents (student_id, document_type, file_path, uploaded_by) VALUES (:student_id, :type, :path, :by)',
                    ['student_id' => $studentId, 'type' => $label, 'path' => $uploaded['path'], 'by' => isset($actor['id']) ? (int) $actor['id'] : null]
                );
            } catch (\Throwable $throwable) {
                Logger::exception($throwable);
            }
        }
    }

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function enumFilter(mixed $value, array $allowed): ?string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, $allowed, true) ? $value : null;
    }

    private function duplicateExists(string $table, string $column, string $value, ?int $excludeId): bool
    {
        $sql = "SELECT 1 FROM `{$table}` WHERE LOWER(`{$column}`) = LOWER(:value)";
        $params = ['value' => $value];

        if ($excludeId) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        return $this->db->fetchOne($sql . ' LIMIT 1', $params) !== null;
    }
}
