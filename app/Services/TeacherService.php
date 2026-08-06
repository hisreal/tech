<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\FileUploader;
use App\Helpers\Paginator;
use App\Helpers\Security;
use App\Models\StaffModel;
use App\Traits\Auditable;

/**
 * Backing service for Teacher Management: list/create/update/delete + subject/class assignment.
 */
final class TeacherService
{
    use Auditable;

    private const GENDERS = ['male', 'female', 'other'];
    private const STATUSES = ['active', 'inactive', 'on_leave', 'suspended', 'deleted'];
    private const DEFAULT_PER_PAGE = 10;

    private StaffModel $staff;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->staff = new StaffModel($this->db);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function list(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ["s.staff_type = 'teacher'"];
        $params = [];

        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.staff_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }
        if (($deptId = $this->intFilter($filters['department_id'] ?? 0)) !== null) {
            $where[] = 's.department_id = :department_id';
            $params['department_id'] = $deptId;
        }
        if (($subjectId = $this->intFilter($filters['subject_id'] ?? 0)) !== null) {
            $where[] = 's.id IN (SELECT teacher_id FROM teacher_subjects WHERE subject_id = :subject_id)';
            $params['subject_id'] = $subjectId;
        }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) {
            $where[] = 's.id IN (SELECT teacher_id FROM teacher_classes WHERE class_id = :class_id)';
            $params['class_id'] = $classId;
        }

        $status = $this->enumFilter($filters['status'] ?? '', self::STATUSES);
        if ($status !== null) {
            $where[] = 's.employment_status = :status';
            $params['status'] = $status;
        } else {
            $where[] = "s.employment_status <> 'deleted'";
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT s.*, d.name AS department_name,
                    (SELECT GROUP_CONCAT(sub.name ORDER BY sub.name SEPARATOR ', ') FROM teacher_subjects ts INNER JOIN subjects sub ON sub.id = ts.subject_id WHERE ts.teacher_id = s.id) AS subject_names,
                    (SELECT GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') FROM teacher_classes tc INNER JOIN classes c ON c.id = tc.class_id WHERE tc.teacher_id = s.id) AS class_names
                 FROM staff s
                 LEFT JOIN departments d ON d.id = s.department_id
                 {$whereSql}
                 ORDER BY s.last_name ASC, s.first_name ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    public function teacherIdForUser(int $userId): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM staff WHERE user_id = :uid', ['uid' => $userId]);

        return $row ? (int) $row['id'] : null;
    }

    public function totalStudentsForTeacher(int $teacherId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT se.student_id) c
             FROM teacher_classes tc
             INNER JOIN student_enrollments se ON se.class_id = tc.class_id AND se.section_id = tc.section_id
             WHERE tc.teacher_id = :id AND se.status = 'active'",
            ['id' => $teacherId]
        );

        return (int) ($row['c'] ?? 0);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        $staff = $this->staff->find($id);

        if ($staff === null || $staff['staff_type'] !== 'teacher') {
            return null;
        }

        $staff['department_name'] = null;
        if (!empty($staff['department_id'])) {
            $dept = $this->db->fetchOne('SELECT name FROM departments WHERE id = :id', ['id' => $staff['department_id']]);
            $staff['department_name'] = $dept['name'] ?? null;
        }

        $staff['subjects'] = $this->db->fetchAll(
            'SELECT sub.id, sub.name FROM teacher_subjects ts INNER JOIN subjects sub ON sub.id = ts.subject_id WHERE ts.teacher_id = :id ORDER BY sub.name',
            ['id' => $id]
        );
        $staff['classes'] = $this->db->fetchAll(
            "SELECT tc.section_id AS id, tc.class_id, CONCAT(c.name, ' - ', sec.name) AS name
             FROM teacher_classes tc
             INNER JOIN classes c ON c.id = tc.class_id
             INNER JOIN sections sec ON sec.id = tc.section_id
             WHERE tc.teacher_id = :id
             ORDER BY c.name, sec.name",
            ['id' => $id]
        );
        $staff['documents'] = $this->db->fetchAll(
            'SELECT * FROM staff_documents WHERE staff_id = :id ORDER BY uploaded_at DESC',
            ['id' => $id]
        );

        return $staff;
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<string,mixed>> $files
     * @param array<string,mixed>|null $actor
     * @return array{success:bool,message:string,errors?:array<string,string>,credentials?:array<string,string>,id?:int}
     */
    public function create(array $data, array $files, ?array $actor): array
    {
        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields, null, true);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO users (username, email, password_hash, user_type, status, password_must_change, temp_password_created_at) VALUES (:username, :email, :password_hash, :user_type, :status, 1, NOW())',
                [
                    'username' => $fields['username'],
                    'email' => $fields['email'] !== '' ? $fields['email'] : null,
                    'password_hash' => password_hash($fields['password'], PASSWORD_DEFAULT),
                    'user_type' => 'teacher',
                    'status' => 'active',
                ]
            );
            $userId = (int) $this->db->lastInsertId();

            $roleRow = $this->db->fetchOne("SELECT id FROM roles WHERE slug = 'teacher' LIMIT 1");
            if ($roleRow) {
                $this->db->execute('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)', ['user_id' => $userId, 'role_id' => $roleRow['id']]);
            }

            $staffNo = $this->generateStaffNo();
            $photoFile = $this->resolvePhotoFile($files);
            $photoPath = $photoFile ? FileUploader::upload($photoFile, 'staff', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024)['path'] : null;

            $staffPayload = array_merge($this->staffPayload($fields, $photoPath), [
                'user_id' => $userId,
                'staff_no' => $staffNo,
                'staff_type' => 'teacher',
            ]);
            $staffId = (int) $this->staff->create($staffPayload);

            $this->syncTeacherSubjects($staffId, $fields['subject_ids']);
            $this->syncTeacherClasses($staffId, $fields['class_ids']);
            $this->syncDocuments($staffId, $files, $actor);

            $this->audit($actor, 'staff', 'teacher.created', 'staff', $staffId, null, $staffPayload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to create the teacher right now.'];
        }

        return [
            'success' => true,
            'message' => "Teacher created successfully. Staff ID: {$staffNo}.",
            'id' => $staffId,
            'credentials' => ['username' => $fields['username'], 'staff_no' => $staffNo],
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<string,mixed>> $files
     * @param array<string,mixed>|null $actor
     */
    public function update(int $id, array $data, array $files, ?array $actor): array
    {
        $before = $this->staff->find($id);

        if ($before === null || $before['staff_type'] !== 'teacher') {
            return ['success' => false, 'message' => 'Teacher not found.'];
        }

        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields, $id, false);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $photoFile = $this->resolvePhotoFile($files);
            $photoPath = null;
            if ($photoFile !== null) {
                $photoPath = FileUploader::upload($photoFile, 'staff', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024)['path'];
                if (!empty($before['passport_path']) && str_starts_with((string) $before['passport_path'], 'app/Storage/uploads/')) {
                    FileUploader::delete((string) $before['passport_path']);
                }
            }

            $staffPayload = $this->staffPayload($fields, $photoPath);
            $this->staff->update($id, $staffPayload);

            $this->syncTeacherSubjects($id, $fields['subject_ids']);
            $this->syncTeacherClasses($id, $fields['class_ids']);
            $this->syncDocuments($id, $files, $actor);

            if ($fields['email'] !== '' && !empty($before['user_id'])) {
                $this->db->execute('UPDATE users SET email = :email WHERE id = :id', ['email' => $fields['email'], 'id' => $before['user_id']]);
            }

            $this->audit($actor, 'staff', 'teacher.updated', 'staff', $id, $before, $staffPayload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update the teacher right now.'];
        }

        return ['success' => true, 'message' => 'Teacher updated successfully.'];
    }

    /**
     * Soft-deletes a teacher (employment_status = 'deleted', a value your own
     * schema already defines for this) and deactivates the linked login,
     * rather than removing the row - results, timetable entries, and other
     * staff-linked records should not silently vanish.
     *
     * @param array<string,mixed>|null $actor
     */
    public function delete(int $id, ?array $actor): array
    {
        $before = $this->staff->find($id);

        if ($before === null || $before['staff_type'] !== 'teacher') {
            return ['success' => false, 'message' => 'Teacher not found.'];
        }
        if ($before['employment_status'] === 'deleted') {
            return ['success' => false, 'message' => 'This teacher has already been deleted.'];
        }

        $this->db->beginTransaction();
        try {
            $this->staff->update($id, ['employment_status' => 'deleted']);
            if (!empty($before['user_id'])) {
                $this->db->execute('UPDATE users SET status = "inactive" WHERE id = :id', ['id' => $before['user_id']]);
            }
            $this->audit($actor, 'staff', 'teacher.deleted', 'staff', $id, $before, ['employment_status' => 'deleted']);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to delete this teacher right now.'];
        }

        return ['success' => true, 'message' => 'Teacher deleted successfully.'];
    }

    /** @param array<int,int> $subjectIds @param array<string,mixed>|null $actor */
    public function assignSubjects(int $teacherId, array $subjectIds, ?array $actor): array
    {
        $teacher = $this->staff->find($teacherId);
        if ($teacher === null || $teacher['staff_type'] !== 'teacher') {
            return ['success' => false, 'message' => 'Teacher not found.'];
        }

        $before = $this->db->fetchAll('SELECT subject_id FROM teacher_subjects WHERE teacher_id = :id', ['id' => $teacherId]);

        $this->db->beginTransaction();
        try {
            $this->syncTeacherSubjects($teacherId, $subjectIds);
            $this->audit(
                $actor,
                'staff',
                'teacher.subjects_assigned',
                'staff',
                $teacherId,
                ['subject_ids' => array_column($before, 'subject_id')],
                ['subject_ids' => $subjectIds]
            );
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update subject assignments right now.'];
        }

        return ['success' => true, 'message' => 'Subject assignments updated successfully.'];
    }

    /** @param array<int,int> $classIds @param array<string,mixed>|null $actor */
    public function assignClasses(int $teacherId, array $classIds, ?array $actor): array
    {
        $teacher = $this->staff->find($teacherId);
        if ($teacher === null || $teacher['staff_type'] !== 'teacher') {
            return ['success' => false, 'message' => 'Teacher not found.'];
        }

        $before = $this->db->fetchAll('SELECT class_id FROM teacher_classes WHERE teacher_id = :id', ['id' => $teacherId]);

        $this->db->beginTransaction();
        try {
            $this->syncTeacherClasses($teacherId, $classIds);
            $this->audit(
                $actor,
                'staff',
                'teacher.classes_assigned',
                'staff',
                $teacherId,
                ['class_ids' => array_column($before, 'class_id')],
                ['class_ids' => $classIds]
            );
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update class assignments right now.'];
        }

        return ['success' => true, 'message' => 'Class assignments updated successfully.'];
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> Aggregate data for the Teacher Reports page. */
    public function reportsSummary(array $filters = []): array
    {
        $deptId = $this->intFilter($filters['department_id'] ?? 0);

        $where = ["s.staff_type = 'teacher'", "s.employment_status <> 'deleted'"];
        $params = [];
        if ($deptId !== null) {
            $where[] = 's.department_id = :department_id';
            $params['department_id'] = $deptId;
        }
        $whereSql = implode(' AND ', $where);

        $byDepartment = $this->db->fetchAll(
            "SELECT COALESCE(d.name, 'Unassigned') AS department_name, COUNT(*) AS total,
                    SUM(s.employment_status = 'active') AS active_count
             FROM staff s
             LEFT JOIN departments d ON d.id = s.department_id
             WHERE {$whereSql}
             GROUP BY d.id
             ORDER BY total DESC",
            $params
        );

        $byGender = $this->db->fetchAll(
            "SELECT COALESCE(s.gender, 'unspecified') AS gender, COUNT(*) AS total
             FROM staff s WHERE {$whereSql} GROUP BY gender",
            $params
        );

        $byEmploymentStatus = $this->db->fetchAll(
            "SELECT s.employment_status AS status, COUNT(*) AS total
             FROM staff s WHERE {$whereSql} GROUP BY s.employment_status ORDER BY total DESC",
            $params
        );

        $byContractType = $this->db->fetchAll(
            "SELECT COALESCE(s.contract_type, 'unspecified') AS contract_type, COUNT(*) AS total
             FROM staff s WHERE {$whereSql} GROUP BY s.contract_type ORDER BY total DESC",
            $params
        );

        $totalTeachers = (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM staff s WHERE {$whereSql}", $params)['c'] ?? 0);
        $withSubjects = (int) ($this->db->fetchOne(
            "SELECT COUNT(DISTINCT s.id) c FROM staff s
             INNER JOIN teacher_subjects ts ON ts.teacher_id = s.id
             WHERE {$whereSql}",
            $params
        )['c'] ?? 0);
        $withClasses = (int) ($this->db->fetchOne(
            "SELECT COUNT(DISTINCT s.id) c FROM staff s
             INNER JOIN teacher_classes tc ON tc.teacher_id = s.id
             WHERE {$whereSql}",
            $params
        )['c'] ?? 0);

        return [
            'department_id' => $deptId,
            'total_teachers' => $totalTeachers,
            'with_subjects' => $withSubjects,
            'without_subjects' => max(0, $totalTeachers - $withSubjects),
            'with_classes' => $withClasses,
            'without_classes' => max(0, $totalTeachers - $withClasses),
            'by_department' => $byDepartment,
            'by_gender' => $byGender,
            'by_employment_status' => $byEmploymentStatus,
            'by_contract_type' => $byContractType,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function departmentsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM departments WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function subjectsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function classesForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM classes WHERE status = "active" ORDER BY name ASC');
    }

    /**
     * Returns class+section combinations for assignment checkboxes. A
     * teacher_classes row requires a specific section_id (it's part of the
     * table's composite primary key), so assignment happens at the
     * class+section level, not the bare class level.
     *
     * @return array<int,array<string,mixed>>
     */
    public function classSectionsForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT sec.id, sec.class_id, CONCAT(c.name, ' - ', sec.name) AS label
             FROM sections sec INNER JOIN classes c ON c.id = sec.class_id
             WHERE sec.status = 'active'
             ORDER BY c.name, sec.name"
        );
    }

    public function generateStaffNo(): string
    {
        $prefix = 'TCH' . date('Y') . '-';
        $count = (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM staff WHERE staff_no LIKE :prefix", ['prefix' => $prefix . '%'])['c'] ?? 0);

        for ($attempt = $count + 1; $attempt < $count + 100; $attempt++) {
            $candidate = $prefix . str_pad((string) $attempt, 3, '0', STR_PAD_LEFT);
            if (!$this->db->fetchOne('SELECT 1 FROM staff WHERE staff_no = :no', ['no' => $candidate])) {
                return $candidate;
            }
        }

        return $prefix . Security::randomString(6);
    }

    /** @return array<string,mixed> */
    private function normalizeFields(array $data): array
    {
        return [
            'first_name' => trim((string) ($data['first_name'] ?? '')),
            'middle_name' => trim((string) ($data['middle_name'] ?? '')),
            'last_name' => trim((string) ($data['last_name'] ?? '')),
            'gender' => strtolower(trim((string) ($data['gender'] ?? ''))),
            'date_of_birth' => trim((string) ($data['date_of_birth'] ?? '')),
            'nationality' => trim((string) ($data['nationality'] ?? '')),
            'state' => trim((string) ($data['state'] ?? '')),
            'local_government' => trim((string) ($data['local_government'] ?? '')),
            'address' => trim((string) ($data['address'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'department_id' => (int) ($data['department'] ?? $data['department_id'] ?? 0),
            'designation' => trim((string) ($data['designation'] ?? '')),
            'employment_date' => trim((string) ($data['employment_date'] ?? '')),
            'employment_status' => strtolower(str_replace(' ', '_', trim((string) ($data['employment_status'] ?? 'active')))),
            'qualification' => trim((string) ($data['qualification'] ?? '')),
            'specialization' => trim((string) ($data['specialization'] ?? '')),
            'experience' => trim((string) ($data['experience'] ?? '')),
            'salary_grade' => trim((string) ($data['salary_grade'] ?? '')),
            'contract_type' => trim((string) ($data['contract_type'] ?? '')),
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => (string) ($data['password'] ?? ''),
            'confirm_password' => (string) ($data['confirm_password'] ?? ''),
            'subject_ids' => array_values(array_filter(array_map('intval', (array) ($data['subjects'] ?? [])))),
            'class_ids' => array_values(array_filter(array_map('intval', (array) ($data['classes'] ?? [])))),
        ];
    }

    /** @return array<string,string> */
    private function validate(array $f, ?int $excludeId, bool $requireAccount): array
    {
        $errors = [];

        if ($f['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        }
        if ($f['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        }
        if ($f['gender'] !== '' && !in_array($f['gender'], self::GENDERS, true)) {
            $errors['gender'] = 'Choose a valid gender.';
        }
        if ($f['date_of_birth'] !== '' && (strtotime($f['date_of_birth']) === false || strtotime($f['date_of_birth']) > time())) {
            $errors['date_of_birth'] = 'Enter a valid date of birth.';
        }
        if ($f['phone'] === '') {
            $errors['phone'] = 'Phone number is required.';
        }
        if ($f['email'] === '' || !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        } elseif ($this->duplicateExists('staff', 'email', $f['email'], $excludeId)) {
            $errors['email'] = 'A teacher with this email already exists.';
        }
        if ($f['department_id'] < 1) {
            $errors['department_id'] = 'Choose a department.';
        }
        if ($f['designation'] === '') {
            $errors['designation'] = 'Choose a designation.';
        }
        if (!in_array($f['employment_status'], self::STATUSES, true)) {
            $errors['employment_status'] = 'Choose a valid employment status.';
        }
        if ($f['experience'] !== '' && (!is_numeric($f['experience']) || (float) $f['experience'] < 0)) {
            $errors['experience'] = 'Years of experience must be a positive number.';
        }

        if ($requireAccount) {
            if ($f['username'] === '') {
                $errors['username'] = 'Username is required.';
            } elseif ($this->duplicateExists('users', 'username', $f['username'], null)) {
                $errors['username'] = 'This username is already taken.';
            }
            if (!Security::isStrongPassword($f['password'])) {
                $errors['password'] = Security::passwordPolicyMessage();
            } elseif ($f['password'] !== $f['confirm_password']) {
                $errors['confirm_password'] = 'Password confirmation does not match.';
            }
        }

        return $errors;
    }

    /** @return array<string,mixed> */
    private function staffPayload(array $f, ?string $photoPath): array
    {
        $payload = [
            'first_name' => $f['first_name'],
            'middle_name' => $f['middle_name'] !== '' ? $f['middle_name'] : null,
            'last_name' => $f['last_name'],
            'gender' => $f['gender'] !== '' ? $f['gender'] : null,
            'date_of_birth' => $f['date_of_birth'] !== '' ? $f['date_of_birth'] : null,
            'nationality' => $f['nationality'] !== '' ? $f['nationality'] : null,
            'state' => $f['state'] !== '' ? $f['state'] : null,
            'local_government' => $f['local_government'] !== '' ? $f['local_government'] : null,
            'address' => $f['address'] !== '' ? $f['address'] : null,
            'phone' => $f['phone'] !== '' ? $f['phone'] : null,
            'email' => $f['email'] !== '' ? $f['email'] : null,
            'department_id' => $f['department_id'] ?: null,
            'designation' => $f['designation'] !== '' ? $f['designation'] : null,
            'employment_date' => $f['employment_date'] !== '' ? $f['employment_date'] : null,
            'employment_status' => $f['employment_status'],
            'qualification' => $f['qualification'] !== '' ? $f['qualification'] : null,
            'specialization' => $f['specialization'] !== '' ? $f['specialization'] : null,
            'years_experience' => $f['experience'] !== '' ? (float) $f['experience'] : 0,
            'salary_grade' => $f['salary_grade'] !== '' ? $f['salary_grade'] : null,
            'contract_type' => $f['contract_type'] !== '' ? $f['contract_type'] : null,
        ];

        if ($photoPath !== null) {
            $payload['passport_path'] = $photoPath;
        }

        return $payload;
    }

    /** Prefers the "passport" upload field, falling back to "passport_photo" (the forms use both names in different places). */
    private function resolvePhotoFile(array $files): ?array
    {
        foreach (['passport', 'profile_photo', 'passport_photo'] as $field) {
            $file = $files[$field] ?? null;
            if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                return $file;
            }
        }

        return null;
    }

    /** @param array<int,int> $subjectIds */
    private function syncTeacherSubjects(int $teacherId, array $subjectIds): void
    {
        $this->db->execute('DELETE FROM teacher_subjects WHERE teacher_id = :id', ['id' => $teacherId]);
        foreach (array_unique($subjectIds) as $subjectId) {
            if ($subjectId > 0) {
                $this->db->execute('INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (:teacher_id, :subject_id)', ['teacher_id' => $teacherId, 'subject_id' => $subjectId]);
            }
        }
    }

    /**
     * @param array<int,int> $sectionIds Section IDs, not class IDs - see classSectionsForSelect().
     */
    private function syncTeacherClasses(int $teacherId, array $sectionIds): void
    {
        $this->db->execute('DELETE FROM teacher_classes WHERE teacher_id = :id', ['id' => $teacherId]);
        foreach (array_unique($sectionIds) as $sectionId) {
            if ($sectionId <= 0) {
                continue;
            }
            $section = $this->db->fetchOne('SELECT class_id FROM sections WHERE id = :id', ['id' => $sectionId]);
            if ($section === null) {
                continue;
            }
            $this->db->execute(
                'INSERT INTO teacher_classes (teacher_id, class_id, section_id) VALUES (:teacher_id, :class_id, :section_id)',
                ['teacher_id' => $teacherId, 'class_id' => $section['class_id'], 'section_id' => $sectionId]
            );
        }
    }

    /** @param array<string,array<string,mixed>> $files @param array<string,mixed>|null $actor */
    private function syncDocuments(int $staffId, array $files, ?array $actor): void
    {
        $documentFields = [
            'cv' => 'CV / Resume',
            'certificates' => 'Certificate',
            'appointment_letter' => 'Appointment Letter',
            'id_document' => 'Identification Document',
        ];

        foreach ($documentFields as $field => $label) {
            $fileEntry = $files[$field] ?? null;

            if ($fileEntry === null) {
                continue;
            }

            // "certificates" may be a multi-file input (array of arrays); normalize to a flat list of single-file entries.
            $entries = is_array($fileEntry['name'] ?? null) ? $this->splitMultiFile($fileEntry) : [$fileEntry];

            foreach ($entries as $entry) {
                if ((int) ($entry['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                try {
                    $uploaded = FileUploader::upload($entry, 'staff/documents', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'], 5 * 1024 * 1024);
                    $this->db->execute(
                        'INSERT INTO staff_documents (staff_id, document_type, file_path, uploaded_by) VALUES (:staff_id, :type, :path, :by)',
                        ['staff_id' => $staffId, 'type' => $label, 'path' => $uploaded['path'], 'by' => isset($actor['id']) ? (int) $actor['id'] : null]
                    );
                } catch (\Throwable $throwable) {
                    Logger::exception($throwable);
                }
            }
        }
    }

    /** @param array<string,mixed> $fileEntry @return array<int,array<string,mixed>> */
    private function splitMultiFile(array $fileEntry): array
    {
        $count = count((array) $fileEntry['name']);
        $entries = [];

        for ($i = 0; $i < $count; $i++) {
            $entries[] = [
                'name' => $fileEntry['name'][$i],
                'type' => $fileEntry['type'][$i],
                'tmp_name' => $fileEntry['tmp_name'][$i],
                'error' => $fileEntry['error'][$i],
                'size' => $fileEntry['size'][$i],
            ];
        }

        return $entries;
    }

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function enumFilter(mixed $value, array $allowed): ?string
    {
        $value = strtolower(str_replace(' ', '_', trim((string) $value)));

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
