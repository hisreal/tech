<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;
use App\Models\AcademicClassModel;
use App\Models\AcademicSessionModel;
use App\Models\DepartmentModel;
use App\Models\SchoolCalendarModel;
use App\Models\SectionModel;
use App\Models\SubjectModel;
use App\Models\TermModel;
use PDOException;

/**
 * Backing service for Academic Setup: sessions, terms, classes, sections, departments, subjects, calendar.
 */
final class AcademicService
{
    private const SESSION_STATUSES = ['active', 'inactive', 'completed', 'upcoming'];
    private const TERM_STATUSES = ['active', 'inactive', 'completed'];
    private const TERM_NAMES = ['First Term', 'Second Term', 'Third Term'];
    private const CLASS_LEVELS = ['creche', 'nursery', 'primary', 'junior', 'senior'];
    private const ACTIVE_INACTIVE = ['active', 'inactive'];
    private const SUBJECT_TYPES = ['core', 'elective'];
    private const CALENDAR_EVENT_TYPES = ['examination', 'holiday', 'pta_meeting', 'staff_meeting', 'sports', 'graduation', 'orientation', 'other'];
    private const CALENDAR_STATUSES = ['scheduled', 'cancelled', 'completed'];
    private const DEFAULT_PER_PAGE = 10;

    private AcademicSessionModel $sessions;
    private TermModel $terms;
    private AcademicClassModel $classes;
    private SectionModel $sections;
    private DepartmentModel $departments;
    private SubjectModel $subjects;
    private SchoolCalendarModel $calendarEvents;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->sessions = new AcademicSessionModel($this->db);
        $this->terms = new TermModel($this->db);
        $this->classes = new AcademicClassModel($this->db);
        $this->sections = new SectionModel($this->db);
        $this->departments = new DepartmentModel($this->db);
        $this->subjects = new SubjectModel($this->db);
        $this->calendarEvents = new SchoolCalendarModel($this->db);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listSessions(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['name LIKE :search', 'search', $this->likeValue($filters['search'] ?? '')],
            ['status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::SESSION_STATUSES)],
        ]);

        return Paginator::paginateQuery($this->db, "SELECT * FROM academic_sessions{$where} ORDER BY start_date DESC, id DESC", $params, $page, $perPage);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listTerms(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['t.name LIKE :search', 'search', $this->likeValue($filters['search'] ?? '')],
            ['t.session_id = :session_id', 'session_id', $this->intFilter($filters['session_id'] ?? 0)],
            ['t.status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::TERM_STATUSES)],
        ]);

        return Paginator::paginateQuery(
            $this->db,
            "SELECT t.*, s.name AS session_name FROM terms t INNER JOIN academic_sessions s ON s.id = t.session_id{$where} ORDER BY s.start_date DESC, t.start_date ASC",
            $params,
            $page,
            $perPage
        );
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listClasses(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['name LIKE :search', 'search', $this->likeValue($filters['search'] ?? '')],
            ['level = :level', 'level', $this->enumFilter($filters['level'] ?? '', self::CLASS_LEVELS)],
            ['status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::ACTIVE_INACTIVE)],
        ]);

        return Paginator::paginateQuery($this->db, "SELECT * FROM classes{$where} ORDER BY name ASC", $params, $page, $perPage);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listSections(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['sec.name LIKE :search', 'search', $this->likeValue($filters['search'] ?? '')],
            ['sec.class_id = :class_id', 'class_id', $this->intFilter($filters['class_id'] ?? 0)],
            ['sec.status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::ACTIVE_INACTIVE)],
        ]);

        return Paginator::paginateQuery(
            $this->db,
            "SELECT sec.*, c.name AS class_name FROM sections sec INNER JOIN classes c ON c.id = sec.class_id{$where} ORDER BY c.name ASC, sec.name ASC",
            $params,
            $page,
            $perPage
        );
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listDepartments(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['name LIKE :search', 'search', $this->likeValue($filters['search'] ?? '')],
            ['status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::ACTIVE_INACTIVE)],
        ]);

        return Paginator::paginateQuery($this->db, "SELECT * FROM departments{$where} ORDER BY name ASC", $params, $page, $perPage);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listSubjects(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['(sub.name LIKE :search OR sub.code LIKE :search_code)', 'search', $this->likeValue($filters['search'] ?? '')],
            ['sub.department_id = :department_id', 'department_id', $this->intFilter($filters['department_id'] ?? 0)],
            ['sub.subject_type = :subject_type', 'subject_type', $this->enumFilter($filters['subject_type'] ?? '', self::SUBJECT_TYPES)],
            ['sub.status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::ACTIVE_INACTIVE)],
        ]);
        if (isset($params['search'])) {
            $params['search_code'] = $params['search'];
        }

        return Paginator::paginateQuery(
            $this->db,
            "SELECT sub.*, d.name AS department_name,
                (SELECT GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ')
                 FROM subject_classes sc INNER JOIN classes c ON c.id = sc.class_id
                 WHERE sc.subject_id = sub.id) AS class_names,
                (SELECT GROUP_CONCAT(sc.class_id ORDER BY sc.class_id SEPARATOR ',')
                 FROM subject_classes sc WHERE sc.subject_id = sub.id) AS class_ids
             FROM subjects sub
             LEFT JOIN departments d ON d.id = sub.department_id{$where}
             ORDER BY sub.name ASC",
            $params,
            $page,
            $perPage
        );
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listCalendarEvents(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$where, $params] = $this->whereClause([
            ['ce.title LIKE :search', 'search', $this->likeValue($filters['search'] ?? '')],
            ['ce.event_type = :event_type', 'event_type', $this->enumFilter($filters['event_type'] ?? '', self::CALENDAR_EVENT_TYPES)],
            ['ce.status = :status', 'status', $this->enumFilter($filters['status'] ?? '', self::CALENDAR_STATUSES)],
            ['ce.session_id = :session_id', 'session_id', $this->intFilter($filters['session_id'] ?? 0)],
        ]);

        return Paginator::paginateQuery(
            $this->db,
            "SELECT ce.*, s.name AS session_name, t.name AS term_name
             FROM school_calendar ce
             LEFT JOIN academic_sessions s ON s.id = ce.session_id
             LEFT JOIN terms t ON t.id = ce.term_id{$where}
             ORDER BY ce.start_date DESC, ce.id DESC",
            $params,
            $page,
            $perPage
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function classesForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM classes WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function departmentsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM departments WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function sessionsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM academic_sessions ORDER BY start_date DESC');
    }

    /** @return array<int,array<string,mixed>> */
    public function termsForSelect(?int $sessionId = null): array
    {
        if ($sessionId) {
            return $this->db->fetchAll('SELECT id, name, session_id FROM terms WHERE session_id = :session_id ORDER BY start_date ASC', ['session_id' => $sessionId]);
        }

        return $this->db->fetchAll('SELECT id, name, session_id FROM terms ORDER BY session_id DESC, start_date ASC');
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor @return array{success:bool,message:string,errors?:array<string,string>} */
    public function saveSession(array $data, ?int $id, ?array $actor): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $start = trim((string) ($data['start_date'] ?? ''));
        $end = trim((string) ($data['end_date'] ?? ''));
        $status = trim((string) ($data['status'] ?? 'inactive'));
        $status = strtolower($status);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Session name is required.';
        }
        if ($start === '' || strtotime($start) === false) {
            $errors['start_date'] = 'A valid start date is required.';
        }
        if ($end === '' || strtotime($end) === false) {
            $errors['end_date'] = 'A valid end date is required.';
        }
        if (!$errors && strtotime($end) <= strtotime($start)) {
            $errors['end_date'] = 'End date must be after the start date.';
        }
        if (!in_array($status, self::SESSION_STATUSES, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if ($name !== '' && $this->duplicateExists('academic_sessions', 'name', $name, $id)) {
            $errors['name'] = 'A session with this name already exists.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['name' => $name, 'start_date' => $start, 'end_date' => $end, 'status' => $status];

        $this->db->beginTransaction();
        try {
            if ($id) {
                $before = $this->sessions->find($id);
                $this->sessions->update($id, $payload);
            } else {
                $before = null;
                $id = (int) $this->sessions->create($payload);
            }

            if ($status === 'active') {
                $this->db->execute('UPDATE academic_sessions SET status = "inactive" WHERE id <> :id AND status = "active"', ['id' => $id]);
            }

            $this->audit($actor, $before ? 'session.updated' : 'session.created', 'academic_sessions', $id, $before, $payload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the academic session right now.'];
        }

        return ['success' => true, 'message' => 'Academic session saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteSession(int $id, ?array $actor): array
    {
        $blocked = $this->blockIfReferenced('terms', 'session_id', $id, 'This session has terms configured under it. Delete or reassign those terms first, or deactivate the session instead.');
        if ($blocked !== null) {
            return $blocked;
        }

        return $this->delete($this->sessions, 'academic_sessions', $id, $actor, 'This session has enrollments or other records attached to it. Deactivate it instead of deleting.', 'session');
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveTerm(array $data, ?int $id, ?array $actor): array
    {
        $sessionId = (int) ($data['session_id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $start = trim((string) ($data['start_date'] ?? ''));
        $end = trim((string) ($data['end_date'] ?? ''));
        $status = strtolower(trim((string) ($data['status'] ?? 'inactive')));

        $errors = [];
        if ($sessionId < 1 || !$this->sessions->find($sessionId)) {
            $errors['session_id'] = 'Choose a valid academic session.';
        }
        if (!in_array($name, self::TERM_NAMES, true)) {
            $errors['name'] = 'Choose a valid term name.';
        }
        if ($start === '' || strtotime($start) === false) {
            $errors['start_date'] = 'A valid start date is required.';
        }
        if ($end === '' || strtotime($end) === false) {
            $errors['end_date'] = 'A valid end date is required.';
        }
        if (!$errors && strtotime($end) <= strtotime($start)) {
            $errors['end_date'] = 'End date must be after the start date.';
        }
        if (!in_array($status, self::TERM_STATUSES, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if (!$errors && $this->duplicateExists('terms', 'name', $name, $id, ['session_id' => $sessionId])) {
            $errors['name'] = 'This term already exists for the selected session.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['session_id' => $sessionId, 'name' => $name, 'start_date' => $start, 'end_date' => $end, 'status' => $status];

        $this->db->beginTransaction();
        try {
            if ($id) {
                $before = $this->terms->find($id);
                $this->terms->update($id, $payload);
            } else {
                $before = null;
                $id = (int) $this->terms->create($payload);
            }

            if ($status === 'active') {
                $this->db->execute('UPDATE terms SET status = "inactive" WHERE id <> :id AND session_id = :session_id AND status = "active"', ['id' => $id, 'session_id' => $sessionId]);
            }

            $this->audit($actor, $before ? 'term.updated' : 'term.created', 'terms', $id, $before, $payload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the term right now.'];
        }

        return ['success' => true, 'message' => 'Term saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteTerm(int $id, ?array $actor): array
    {
        return $this->delete($this->terms, 'terms', $id, $actor, 'This term has results, timetable entries, or other records attached to it.', 'term');
    }

    /** @param array<string,mixed>|null $actor */
    public function activateSession(int $id, ?array $actor): array
    {
        $before = $this->sessions->find($id);
        if ($before === null) {
            return ['success' => false, 'message' => 'Session not found.'];
        }

        $this->sessions->update($id, ['status' => 'active']);
        $this->db->execute('UPDATE academic_sessions SET status = "inactive" WHERE id <> :id AND status = "active"', ['id' => $id]);
        $this->audit($actor, 'session.activated', 'academic_sessions', $id, $before, ['status' => 'active']);

        return ['success' => true, 'message' => 'Academic session activated.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function activateTerm(int $id, ?array $actor): array
    {
        $before = $this->terms->find($id);
        if ($before === null) {
            return ['success' => false, 'message' => 'Term not found.'];
        }

        $this->terms->update($id, ['status' => 'active']);
        $this->db->execute('UPDATE terms SET status = "inactive" WHERE id <> :id AND session_id = :session_id AND status = "active"', ['id' => $id, 'session_id' => $before['session_id']]);
        $this->audit($actor, 'term.activated', 'terms', $id, $before, ['status' => 'active']);

        return ['success' => true, 'message' => 'Term activated.'];
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveClass(array $data, ?int $id, ?array $actor): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $level = strtolower(trim((string) ($data['level'] ?? '')));
        $status = strtolower(trim((string) ($data['status'] ?? 'active')));

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Class name is required.';
        }
        if (!in_array($level, self::CLASS_LEVELS, true)) {
            $errors['level'] = 'Choose a valid class level.';
        }
        if (!in_array($status, self::ACTIVE_INACTIVE, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if ($name !== '' && $this->duplicateExists('classes', 'name', $name, $id)) {
            $errors['name'] = 'A class with this name already exists.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['name' => $name, 'level' => $level, 'status' => $status];

        try {
            $before = $id ? $this->classes->find($id) : null;
            if ($id) {
                $this->classes->update($id, $payload);
            } else {
                $id = (int) $this->classes->create($payload);
            }
            $this->audit($actor, $before ? 'class.updated' : 'class.created', 'classes', $id, $before, $payload);
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the class right now.'];
        }

        return ['success' => true, 'message' => 'Class saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteClass(int $id, ?array $actor): array
    {
        $blocked = $this->blockIfReferenced('sections', 'class_id', $id, 'This class has sections configured under it. Delete those sections first.')
            ?? $this->blockIfReferenced('subject_classes', 'class_id', $id, 'This class has subjects assigned to it. Remove those subject assignments first.')
            ?? $this->blockIfReferenced('teacher_classes', 'class_id', $id, 'This class has teachers assigned to it. Remove those assignments first.');
        if ($blocked !== null) {
            return $blocked;
        }

        return $this->delete($this->classes, 'classes', $id, $actor, 'This class has students or other records attached to it.', 'class');
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveSection(array $data, ?int $id, ?array $actor): array
    {
        $classId = (int) ($data['class_id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $capacityRaw = trim((string) ($data['capacity'] ?? ''));
        $status = strtolower(trim((string) ($data['status'] ?? 'active')));

        $errors = [];
        if ($classId < 1 || !$this->classes->find($classId)) {
            $errors['class_id'] = 'Choose a valid class.';
        }
        if ($name === '') {
            $errors['name'] = 'Section name is required.';
        }
        $capacity = null;
        if ($capacityRaw !== '') {
            if (!ctype_digit($capacityRaw)) {
                $errors['capacity'] = 'Capacity must be a whole number.';
            } else {
                $capacity = (int) $capacityRaw;
            }
        }
        if (!in_array($status, self::ACTIVE_INACTIVE, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if (!$errors && $this->duplicateExists('sections', 'name', $name, $id, ['class_id' => $classId])) {
            $errors['name'] = 'This section already exists for the selected class.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['class_id' => $classId, 'name' => $name, 'capacity' => $capacity, 'status' => $status];

        try {
            $before = $id ? $this->sections->find($id) : null;
            if ($id) {
                $this->sections->update($id, $payload);
            } else {
                $id = (int) $this->sections->create($payload);
            }
            $this->audit($actor, $before ? 'section.updated' : 'section.created', 'sections', $id, $before, $payload);
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the section right now.'];
        }

        return ['success' => true, 'message' => 'Section saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteSection(int $id, ?array $actor): array
    {
        return $this->delete($this->sections, 'sections', $id, $actor, 'This section has students or other records attached to it.', 'section');
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveDepartment(array $data, ?int $id, ?array $actor): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $status = strtolower(trim((string) ($data['status'] ?? 'active')));

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Department name is required.';
        }
        if (!in_array($status, self::ACTIVE_INACTIVE, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if ($name !== '' && $this->duplicateExists('departments', 'name', $name, $id)) {
            $errors['name'] = 'A department with this name already exists.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['name' => $name, 'description' => $description !== '' ? $description : null, 'status' => $status];

        try {
            $before = $id ? $this->departments->find($id) : null;
            if ($id) {
                $this->departments->update($id, $payload);
            } else {
                $id = (int) $this->departments->create($payload);
            }
            $this->audit($actor, $before ? 'department.updated' : 'department.created', 'departments', $id, $before, $payload);
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the department right now.'];
        }

        return ['success' => true, 'message' => 'Department saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteDepartment(int $id, ?array $actor): array
    {
        return $this->delete($this->departments, 'departments', $id, $actor, 'This department still has subjects or staff attached to it.', 'department');
    }

    /** @param array<string,mixed> $data @param array<int,int> $classIds @param array<string,mixed>|null $actor */
    public function saveSubject(array $data, ?int $id, array $classIds, ?array $actor): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $departmentId = (int) ($data['department_id'] ?? 0);
        $type = strtolower(trim((string) ($data['subject_type'] ?? 'core')));
        $status = strtolower(trim((string) ($data['status'] ?? 'active')));

        $errors = [];
        if ($code === '') {
            $errors['code'] = 'Subject code is required.';
        }
        if ($name === '') {
            $errors['name'] = 'Subject name is required.';
        }
        if ($departmentId > 0 && !$this->departments->find($departmentId)) {
            $errors['department_id'] = 'Choose a valid department.';
        }
        if (!in_array($type, self::SUBJECT_TYPES, true)) {
            $errors['subject_type'] = 'Choose a valid subject type.';
        }
        if (!in_array($status, self::ACTIVE_INACTIVE, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if ($code !== '' && $this->duplicateExists('subjects', 'code', $code, $id)) {
            $errors['code'] = 'A subject with this code already exists.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['code' => $code, 'name' => $name, 'department_id' => $departmentId ?: null, 'subject_type' => $type, 'status' => $status];

        $this->db->beginTransaction();
        try {
            $before = $id ? $this->subjects->find($id) : null;
            if ($id) {
                $this->subjects->update($id, $payload);
            } else {
                $id = (int) $this->subjects->create($payload);
            }

            $this->db->execute('DELETE FROM subject_classes WHERE subject_id = :id', ['id' => $id]);
            foreach (array_unique($classIds) as $classId) {
                if ($classId > 0) {
                    $this->db->execute('INSERT INTO subject_classes (subject_id, class_id) VALUES (:subject_id, :class_id)', ['subject_id' => $id, 'class_id' => $classId]);
                }
            }

            $this->audit($actor, $before ? 'subject.updated' : 'subject.created', 'subjects', $id, $before, $payload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the subject right now.'];
        }

        return ['success' => true, 'message' => 'Subject saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteSubject(int $id, ?array $actor): array
    {
        $blocked = $this->blockIfReferenced('teacher_subjects', 'subject_id', $id, 'This subject has teachers assigned to it. Remove those assignments first.');
        if ($blocked !== null) {
            return $blocked;
        }

        return $this->delete($this->subjects, 'subjects', $id, $actor, 'This subject has results or other records attached to it.', 'subject');
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveCalendarEvent(array $data, ?int $id, ?array $actor): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $eventType = strtolower(trim((string) ($data['event_type'] ?? 'other')));
        $start = trim((string) ($data['start_date'] ?? ''));
        $end = trim((string) ($data['end_date'] ?? ''));
        $location = trim((string) ($data['location'] ?? ''));
        $status = strtolower(trim((string) ($data['status'] ?? 'scheduled')));
        $sessionId = (int) ($data['session_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);

        $errors = [];
        if ($title === '') {
            $errors['title'] = 'Event title is required.';
        }
        if (!in_array($eventType, self::CALENDAR_EVENT_TYPES, true)) {
            $errors['event_type'] = 'Choose a valid event type.';
        }
        if ($start === '' || strtotime($start) === false) {
            $errors['start_date'] = 'A valid start date is required.';
        }
        if ($end !== '' && strtotime($end) === false) {
            $errors['end_date'] = 'Enter a valid end date.';
        }
        if (!$errors && $end !== '' && strtotime($end) < strtotime($start)) {
            $errors['end_date'] = 'End date cannot be before the start date.';
        }
        if (!in_array($status, self::CALENDAR_STATUSES, true)) {
            $errors['status'] = 'Choose a valid status.';
        }
        if ($sessionId > 0 && !$this->sessions->find($sessionId)) {
            $errors['session_id'] = 'Choose a valid academic session.';
        }
        if ($termId > 0 && !$this->terms->find($termId)) {
            $errors['term_id'] = 'Choose a valid term.';
        }
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = [
            'session_id' => $sessionId ?: null,
            'term_id' => $termId ?: null,
            'title' => $title,
            'event_type' => $eventType,
            'start_date' => $start,
            'end_date' => $end !== '' ? $end : null,
            'location' => $location !== '' ? $location : null,
            'status' => $status,
        ];

        try {
            $before = $id ? $this->calendarEvents->find($id) : null;
            if ($id) {
                $this->calendarEvents->update($id, $payload);
            } else {
                $payload['created_by'] = isset($actor['id']) ? (int) $actor['id'] : null;
                $id = (int) $this->calendarEvents->create($payload);
            }
            $this->audit($actor, $before ? 'calendar_event.updated' : 'calendar_event.created', 'school_calendar', $id, $before, $payload);
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save the calendar event right now.'];
        }

        return ['success' => true, 'message' => 'Calendar event saved successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deleteCalendarEvent(int $id, ?array $actor): array
    {
        return $this->delete($this->calendarEvents, 'school_calendar', $id, $actor, 'This calendar event could not be deleted.', 'calendar_event');
    }

    /**
     * Deletes a row, converting foreign-key restrictions into a friendly message.
     *
     * @param array<string,mixed>|null $actor
     */
    private function delete(object $model, string $table, int $id, ?array $actor, string $inUseMessage, string $label): array
    {
        $before = $model->find($id);
        if ($before === null) {
            return ['success' => false, 'message' => 'Record not found.'];
        }

        try {
            $model->delete($id);
            $this->audit($actor, $label . '.deleted', $table, $id, $before, []);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return ['success' => false, 'message' => $inUseMessage];
            }
            Logger::exception($exception);
            return ['success' => false, 'message' => 'Unable to delete this record right now.'];
        }

        return ['success' => true, 'message' => 'Record deleted successfully.'];
    }

    /**
     * Blocks deletion when a related table still references this row via an
     * ON DELETE CASCADE foreign key (MySQL would silently wipe those rows
     * instead of raising a 23000 error, so this guard runs first).
     *
     * @return array{success:bool,message:string}|null
     */
    private function blockIfReferenced(string $table, string $column, int $id, string $message): ?array
    {
        $row = $this->db->fetchOne("SELECT 1 FROM `{$table}` WHERE `{$column}` = :id LIMIT 1", ['id' => $id]);

        return $row !== null ? ['success' => false, 'message' => $message] : null;
    }

    /**
     * Builds a WHERE clause from [sqlFragment, paramName, value] triples,
     * skipping any condition whose value is null (not filtered on).
     *
     * @param array<int,array{0:string,1:string,2:mixed}> $conditions
     * @return array{0:string,1:array<string,mixed>}
     */
    private function whereClause(array $conditions): array
    {
        $fragments = [];
        $params = [];

        foreach ($conditions as [$sql, $param, $value]) {
            if ($value === null) {
                continue;
            }
            $fragments[] = $sql;
            $params[$param] = $value;
        }

        return [$fragments === [] ? '' : ' WHERE ' . implode(' AND ', $fragments), $params];
    }

    /** Returns a LIKE-ready wildcard value, or null when the filter is empty. */
    private function likeValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : '%' . $value . '%';
    }

    /** Returns the lowercased value when it's one of $allowed, else null. */
    private function enumFilter(mixed $value, array $allowed): ?string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, $allowed, true) ? $value : null;
    }

    /** Returns a positive int filter value, or null when absent/zero. */
    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    /** @param array<string,mixed> $scope */
    private function duplicateExists(string $table, string $column, string $value, ?int $excludeId, array $scope = []): bool
    {
        $sql = "SELECT 1 FROM `{$table}` WHERE LOWER(`{$column}`) = LOWER(:value)";
        $params = ['value' => $value];

        foreach ($scope as $scopeColumn => $scopeValue) {
            $sql .= " AND `{$scopeColumn}` = :{$scopeColumn}";
            $params[$scopeColumn] = $scopeValue;
        }

        if ($excludeId) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        return $this->db->fetchOne($sql . ' LIMIT 1', $params) !== null;
    }

    /** @param array<string,mixed>|null $actor @param array<string,mixed>|null $before @param array<string,mixed> $after */
    private function audit(?array $actor, string $action, string $table, int $entityId, ?array $before, array $after): void
    {
        $this->db->execute(
            'INSERT INTO audit_logs (actor_user_id, module, action, entity_table, entity_id, old_values, new_values, ip_address, user_agent) VALUES (:actor, :module, :action, :entity_table, :entity_id, :old_values, :new_values, :ip, :agent)',
            [
                'actor' => isset($actor['id']) ? (int) $actor['id'] : null,
                'module' => 'academic',
                'action' => $action,
                'entity_table' => $table,
                'entity_id' => $entityId,
                'old_values' => $before ? json_encode($before) : null,
                'new_values' => $after ? json_encode($after) : null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
    }
}
