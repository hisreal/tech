<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;
use App\Traits\Auditable;

/**
 * Backing service for Attendance Management: student/teacher roster loading,
 * bulk daily marking (upsert on the schema's per-day unique keys), single
 * record edit/delete, paginated listing, report generation, and analytics.
 */
final class AttendanceService
{
    use Auditable;

    private const STATUSES = ['present', 'absent', 'late', 'excused', 'leave'];
    private const DEFAULT_PER_PAGE = 15;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Select helpers
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function sessionsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name, status FROM academic_sessions ORDER BY start_date DESC');
    }

    /** @return array<int,array<string,mixed>> */
    public function termsForSelect(?int $sessionId = null): array
    {
        if ($sessionId) {
            return $this->db->fetchAll('SELECT id, name, session_id, start_date, end_date FROM terms WHERE session_id = :sid ORDER BY start_date ASC', ['sid' => $sessionId]);
        }

        return $this->db->fetchAll('SELECT id, name, session_id, start_date, end_date FROM terms ORDER BY start_date DESC');
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
            return $this->db->fetchAll('SELECT id, name, class_id FROM sections WHERE class_id = :cid AND status = "active" ORDER BY name ASC', ['cid' => $classId]);
        }

        return $this->db->fetchAll('SELECT id, name, class_id FROM sections WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function departmentsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM departments WHERE status = "active" ORDER BY name ASC');
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

    public function studentIdForUser(int $userId): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM students WHERE user_id = :uid', ['uid' => $userId]);

        return $row ? (int) $row['id'] : null;
    }

    public function currentTermId(): ?int
    {
        $row = $this->db->fetchOne("SELECT setting_value FROM school_settings WHERE setting_key = 'academic.current_term_id'");
        $id = $row ? (int) $row['setting_value'] : 0;

        if ($id > 0) {
            return $id;
        }

        $row = $this->db->fetchOne("SELECT id FROM terms WHERE status = 'active' ORDER BY id DESC LIMIT 1");

        return $row ? (int) $row['id'] : null;
    }

    // ------------------------------------------------------------------
    // Roster loading (for marking)
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function studentRoster(int $sessionId, int $classId, ?int $sectionId): array
    {
        $where = ['se.session_id = :session_id', 'se.class_id = :class_id', "se.status = 'active'", "s.status = 'active'"];
        $params = ['session_id' => $sessionId, 'class_id' => $classId];

        if ($sectionId) {
            $where[] = 'se.section_id = :section_id';
            $params['section_id'] = $sectionId;
        }

        $whereSql = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT se.student_id AS id, s.registration_no, s.admission_no, s.first_name, s.last_name
             FROM student_enrollments se
             INNER JOIN students s ON s.id = se.student_id
             WHERE {$whereSql}
             ORDER BY s.last_name ASC, s.first_name ASC",
            $params
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function teacherRoster(): array
    {
        return $this->db->fetchAll(
            "SELECT id, staff_no, first_name, last_name, department_id
             FROM staff
             WHERE staff_type = 'teacher' AND employment_status = 'active'
             ORDER BY last_name ASC, first_name ASC"
        );
    }

    /** @return array<int,array<string,mixed>> Keyed by student_id. */
    public function existingStudentMarksForDate(int $classId, ?int $sectionId, string $date): array
    {
        $where = ['class_id = :class_id', 'attendance_date = :date'];
        $params = ['class_id' => $classId, 'date' => $date];

        if ($sectionId) {
            $where[] = 'section_id = :section_id';
            $params['section_id'] = $sectionId;
        }

        $rows = $this->db->fetchAll('SELECT * FROM student_attendance WHERE ' . implode(' AND ', $where), $params);

        $keyed = [];
        foreach ($rows as $row) {
            $keyed[(int) $row['student_id']] = $row;
        }

        return $keyed;
    }

    /** @return array<int,array<string,mixed>> Keyed by staff_id. */
    public function existingTeacherMarksForDate(string $date): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM teacher_attendance WHERE attendance_date = :date', ['date' => $date]);

        $keyed = [];
        foreach ($rows as $row) {
            $keyed[(int) $row['staff_id']] = $row;
        }

        return $keyed;
    }

    // ------------------------------------------------------------------
    // Marking (bulk upsert)
    // ------------------------------------------------------------------

    /**
     * @param array<int,string> $statuses student_id => status
     * @param array<int,string> $notes student_id => note
     * @param array<string,mixed>|null $actor
     * @return array{success:bool,message:string,errors?:array<string,string>}
     */
    public function markStudentAttendance(int $sessionId, int $termId, int $classId, ?int $sectionId, string $date, array $statuses, array $notes, ?array $actor): array
    {
        $errors = [];
        if ($sessionId < 1) { $errors['session_id'] = 'Choose an academic session.'; }
        if ($termId < 1) { $errors['term_id'] = 'Choose a term.'; }
        if ($classId < 1) { $errors['class_id'] = 'Choose a class.'; }
        if ($date === '' || strtotime($date) === false) { $errors['attendance_date'] = 'Choose a valid date.'; }
        elseif (strtotime($date) > strtotime(date('Y-m-d'))) { $errors['attendance_date'] = 'Attendance date cannot be in the future.'; }
        if (!$statuses) { $errors['statuses'] = 'No students were submitted for marking.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $markedBy = isset($actor['id']) ? (int) $actor['id'] : null;
        $marked = 0;

        $this->db->beginTransaction();
        try {
            foreach ($statuses as $studentId => $status) {
                $studentId = (int) $studentId;
                $status = in_array($status, self::STATUSES, true) ? $status : 'present';

                if ($studentId < 1) {
                    continue;
                }

                $this->db->execute(
                    'INSERT INTO student_attendance (student_id, session_id, term_id, class_id, section_id, attendance_date, status, marked_by, notes)
                     VALUES (:student_id, :session_id, :term_id, :class_id, :section_id, :date, :status, :marked_by, :notes)
                     ON DUPLICATE KEY UPDATE session_id = VALUES(session_id), term_id = VALUES(term_id), class_id = VALUES(class_id),
                        section_id = VALUES(section_id), status = VALUES(status), marked_by = VALUES(marked_by), notes = VALUES(notes)',
                    [
                        'student_id' => $studentId,
                        'session_id' => $sessionId,
                        'term_id' => $termId,
                        'class_id' => $classId,
                        'section_id' => $sectionId ?: null,
                        'date' => $date,
                        'status' => $status,
                        'marked_by' => $markedBy,
                        'notes' => ($notes[$studentId] ?? '') !== '' ? $notes[$studentId] : null,
                    ]
                );
                $marked++;
            }

            $this->audit($actor, 'attendance', 'attendance.student.marked', 'student_attendance', $classId, null, [
                'class_id' => $classId, 'section_id' => $sectionId, 'date' => $date, 'students_marked' => $marked,
            ]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save attendance right now.'];
        }

        return ['success' => true, 'message' => "Attendance saved for {$marked} student(s) on {$date}."];
    }

    /**
     * @param array<int,array<string,string>> $entries staff_id => ['status'=>, 'check_in'=>, 'check_out'=>, 'notes'=>]
     * @param array<string,mixed>|null $actor
     */
    public function markTeacherAttendance(string $date, array $entries, ?array $actor): array
    {
        $errors = [];
        if ($date === '' || strtotime($date) === false) { $errors['attendance_date'] = 'Choose a valid date.'; }
        elseif (strtotime($date) > strtotime(date('Y-m-d'))) { $errors['attendance_date'] = 'Attendance date cannot be in the future.'; }
        if (!$entries) { $errors['entries'] = 'No teachers were submitted for marking.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $markedBy = isset($actor['id']) ? (int) $actor['id'] : null;
        $marked = 0;

        $this->db->beginTransaction();
        try {
            foreach ($entries as $staffId => $entry) {
                $staffId = (int) $staffId;
                if ($staffId < 1) {
                    continue;
                }

                $status = in_array($entry['status'] ?? '', self::STATUSES, true) ? $entry['status'] : 'present';
                $checkIn = trim((string) ($entry['check_in'] ?? ''));
                $checkOut = trim((string) ($entry['check_out'] ?? ''));
                $notes = trim((string) ($entry['notes'] ?? ''));

                $this->db->execute(
                    'INSERT INTO teacher_attendance (staff_id, attendance_date, check_in, check_out, status, marked_by, notes)
                     VALUES (:staff_id, :date, :check_in, :check_out, :status, :marked_by, :notes)
                     ON DUPLICATE KEY UPDATE check_in = VALUES(check_in), check_out = VALUES(check_out), status = VALUES(status),
                        marked_by = VALUES(marked_by), notes = VALUES(notes)',
                    [
                        'staff_id' => $staffId,
                        'date' => $date,
                        'check_in' => $checkIn !== '' ? $checkIn : null,
                        'check_out' => $checkOut !== '' ? $checkOut : null,
                        'status' => $status,
                        'marked_by' => $markedBy,
                        'notes' => $notes !== '' ? $notes : null,
                    ]
                );
                $marked++;
            }

            $this->audit($actor, 'attendance', 'attendance.teacher.marked', 'teacher_attendance', null, null, [
                'date' => $date, 'teachers_marked' => $marked,
            ]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to save teacher attendance right now.'];
        }

        return ['success' => true, 'message' => "Attendance saved for {$marked} teacher(s) on {$date}."];
    }

    // ------------------------------------------------------------------
    // Single record update / delete
    // ------------------------------------------------------------------

    /** @return array<string,mixed>|null */
    public function findStudentRecord(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM student_attendance WHERE id = :id', ['id' => $id]);
    }

    public function updateStudentRecord(int $id, string $status, ?string $notes, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM student_attendance WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Attendance record not found.'];
        }
        if (!in_array($status, self::STATUSES, true)) {
            return ['success' => false, 'message' => 'Choose a valid status.'];
        }

        $this->db->execute('UPDATE student_attendance SET status = :status, notes = :notes WHERE id = :id', [
            'status' => $status, 'notes' => $notes !== null && $notes !== '' ? $notes : null, 'id' => $id,
        ]);
        $this->audit($actor, 'attendance', 'attendance.student.updated', 'student_attendance', $id, $before, ['status' => $status, 'notes' => $notes]);

        return ['success' => true, 'message' => 'Student attendance record updated successfully.'];
    }

    public function updateTeacherRecord(int $id, string $status, ?string $notes, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM teacher_attendance WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Attendance record not found.'];
        }
        if (!in_array($status, self::STATUSES, true)) {
            return ['success' => false, 'message' => 'Choose a valid status.'];
        }

        $this->db->execute('UPDATE teacher_attendance SET status = :status, notes = :notes WHERE id = :id', [
            'status' => $status, 'notes' => $notes !== null && $notes !== '' ? $notes : null, 'id' => $id,
        ]);
        $this->audit($actor, 'attendance', 'attendance.teacher.updated', 'teacher_attendance', $id, $before, ['status' => $status, 'notes' => $notes]);

        return ['success' => true, 'message' => 'Teacher attendance record updated successfully.'];
    }

    public function deleteStudentRecord(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM student_attendance WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Attendance record not found.'];
        }

        $this->db->execute('DELETE FROM student_attendance WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'attendance', 'attendance.student.deleted', 'student_attendance', $id, $before, null);

        return ['success' => true, 'message' => 'Student attendance record deleted successfully.'];
    }

    public function deleteTeacherRecord(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM teacher_attendance WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Attendance record not found.'];
        }

        $this->db->execute('DELETE FROM teacher_attendance WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'attendance', 'attendance.teacher.deleted', 'teacher_attendance', $id, $before, null);

        return ['success' => true, 'message' => 'Teacher attendance record deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Listing (Attendance Records page)
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listStudentAttendance(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.registration_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }
        if (($studentId = $this->intFilter($filters['student_id'] ?? 0)) !== null) { $where[] = 'sa.student_id = :student_id'; $params['student_id'] = $studentId; }
        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'sa.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'sa.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'sa.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($sectionId = $this->intFilter($filters['section_id'] ?? 0)) !== null) { $where[] = 'sa.section_id = :section_id'; $params['section_id'] = $sectionId; }
        $classIds = array_values(array_filter(array_map('intval', (array) ($filters['class_ids'] ?? []))));
        if ($classIds) {
            $placeholders = [];
            foreach ($classIds as $i => $cid) {
                $key = 'cid' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = $cid;
            }
            $where[] = 'sa.class_id IN (' . implode(',', $placeholders) . ')';
        }
        if (($date = trim((string) ($filters['date'] ?? ''))) !== '') { $where[] = 'sa.attendance_date = :date'; $params['date'] = $date; }
        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'sa.attendance_date >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'sa.attendance_date <= :date_to'; $params['date_to'] = $dateTo; }
        if (($status = $this->enumFilter($filters['status'] ?? '')) !== null) { $where[] = 'sa.status = :status'; $params['status'] = $status; }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT sa.*, s.registration_no, s.first_name, s.last_name, c.name AS class_name, sec.name AS section_name,
                    COALESCE(NULLIF(TRIM(CONCAT(st.first_name, ' ', st.last_name)), ''), u.username) AS marked_by_name
                 FROM student_attendance sa
                 INNER JOIN students s ON s.id = sa.student_id
                 INNER JOIN classes c ON c.id = sa.class_id
                 LEFT JOIN sections sec ON sec.id = sa.section_id
                 LEFT JOIN users u ON u.id = sa.marked_by
                 LEFT JOIN staff st ON st.user_id = u.id
                 {$whereSql}
                 ORDER BY sa.attendance_date DESC, s.last_name ASC, s.first_name ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listTeacherAttendance(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(st.first_name LIKE :search1 OR st.last_name LIKE :search2 OR st.staff_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }
        if (($departmentId = $this->intFilter($filters['department_id'] ?? 0)) !== null) { $where[] = 'st.department_id = :department_id'; $params['department_id'] = $departmentId; }
        if (($date = trim((string) ($filters['date'] ?? ''))) !== '') { $where[] = 'ta.attendance_date = :date'; $params['date'] = $date; }
        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'ta.attendance_date >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'ta.attendance_date <= :date_to'; $params['date_to'] = $dateTo; }
        if (($status = $this->enumFilter($filters['status'] ?? '')) !== null) { $where[] = 'ta.status = :status'; $params['status'] = $status; }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT ta.*, st.staff_no, st.first_name, st.last_name, d.name AS department_name
                 FROM teacher_attendance ta
                 INNER JOIN staff st ON st.id = ta.staff_id
                 LEFT JOIN departments d ON d.id = st.department_id
                 {$whereSql}
                 ORDER BY ta.attendance_date DESC, st.last_name ASC, st.first_name ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    // ------------------------------------------------------------------
    // Reports
    // ------------------------------------------------------------------

    /**
     * @param array<string,mixed> $filters report_type, scope(student|teacher|all), session_id, term_id,
     *     class_id, section_id, department_id, date_from, date_to, search
     * @return array{rows:array<int,array<string,mixed>>,totals:array<string,mixed>}
     */
    public function generateReport(array $filters): array
    {
        $reportType = (string) ($filters['report_type'] ?? 'Daily Attendance Report');
        $scope = (string) ($filters['scope'] ?? 'all');

        [$groupExpr, $labelExpr] = match ($reportType) {
            'Weekly Attendance Report' => ["YEARWEEK(%s, 3)", "CONCAT('Week ', WEEK(%s, 3), ' - ', YEAR(%s))"],
            'Monthly Attendance Report' => ["DATE_FORMAT(%s, '%%Y-%%m')", "DATE_FORMAT(%s, '%%M %%Y')"],
            'Class Attendance Report' => ['sa.class_id', 'c.name'],
            'Department Attendance Report' => ['st.department_id', "COALESCE(d.name, 'Unassigned')"],
            'Student Attendance Report' => ['sa.student_id', "CONCAT(s.first_name, ' ', s.last_name, ' (', s.registration_no, ')')"],
            'Teacher Attendance Report' => ['ta.staff_id', "CONCAT(st.first_name, ' ', st.last_name, ' (', st.staff_no, ')')"],
            default => ['%s', '%s'],
        };

        $rows = [];
        $wantsStudent = $scope !== 'teacher' && !in_array($reportType, ['Teacher Attendance Report', 'Department Attendance Report'], true);
        $wantsTeacher = $scope !== 'student' && !in_array($reportType, ['Student Attendance Report', 'Class Attendance Report'], true);

        if ($wantsStudent) {
            $rows = array_merge($rows, $this->studentAggregate(
                sprintf($groupExpr, 'sa.attendance_date', 'sa.attendance_date'),
                sprintf($labelExpr, 'sa.attendance_date', 'sa.attendance_date', 'sa.attendance_date'),
                $filters
            ));
        }
        if ($wantsTeacher) {
            $rows = array_merge($rows, $this->teacherAggregate(
                sprintf($groupExpr, 'ta.attendance_date', 'ta.attendance_date'),
                sprintf($labelExpr, 'ta.attendance_date', 'ta.attendance_date', 'ta.attendance_date'),
                $filters
            ));
        }

        usort($rows, static fn (array $a, array $b): int => strcmp((string) $a['label'], (string) $b['label']));

        $totalPresent = array_sum(array_column($rows, 'present'));
        $totalAbsent = array_sum(array_column($rows, 'absent'));
        $totalOther = array_sum(array_column($rows, 'other'));
        $totalRecords = array_sum(array_column($rows, 'total'));

        return [
            'rows' => $rows,
            'totals' => [
                'present' => $totalPresent,
                'absent' => $totalAbsent,
                'other' => $totalOther,
                'records' => $totalRecords,
                'rate' => $this->rate($totalPresent, $totalRecords),
            ],
        ];
    }

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    private function studentAggregate(string $groupExpr, string $labelExpr, array $filters): array
    {
        $where = ['1=1'];
        $params = [];

        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'sa.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'sa.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'sa.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($sectionId = $this->intFilter($filters['section_id'] ?? 0)) !== null) { $where[] = 'sa.section_id = :section_id'; $params['section_id'] = $sectionId; }
        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'sa.attendance_date >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'sa.attendance_date <= :date_to'; $params['date_to'] = $dateTo; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.registration_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT {$labelExpr} AS label,
                    SUM(sa.status = 'present') AS present, SUM(sa.status = 'absent') AS absent,
                    SUM(sa.status IN ('late','excused','leave')) AS other, COUNT(*) AS total
                 FROM student_attendance sa
                 INNER JOIN students s ON s.id = sa.student_id
                 INNER JOIN classes c ON c.id = sa.class_id
                 WHERE {$whereSql}
                 GROUP BY {$groupExpr}
                 ORDER BY label ASC";

        $rows = $this->db->fetchAll($sql, $params);

        foreach ($rows as &$row) {
            $row['category'] = 'Student Attendance';
            $row['rate'] = $this->rate((int) $row['present'], (int) $row['total']);
        }

        return $rows;
    }

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    private function teacherAggregate(string $groupExpr, string $labelExpr, array $filters): array
    {
        $where = ['1=1'];
        $params = [];

        if (($departmentId = $this->intFilter($filters['department_id'] ?? 0)) !== null) { $where[] = 'st.department_id = :department_id'; $params['department_id'] = $departmentId; }
        if (($dateFrom = trim((string) ($filters['date_from'] ?? ''))) !== '') { $where[] = 'ta.attendance_date >= :date_from'; $params['date_from'] = $dateFrom; }
        if (($dateTo = trim((string) ($filters['date_to'] ?? ''))) !== '') { $where[] = 'ta.attendance_date <= :date_to'; $params['date_to'] = $dateTo; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(st.first_name LIKE :search1 OR st.last_name LIKE :search2 OR st.staff_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT {$labelExpr} AS label,
                    SUM(ta.status = 'present') AS present, SUM(ta.status = 'absent') AS absent,
                    SUM(ta.status IN ('late','excused','leave')) AS other, COUNT(*) AS total
                 FROM teacher_attendance ta
                 INNER JOIN staff st ON st.id = ta.staff_id
                 LEFT JOIN departments d ON d.id = st.department_id
                 WHERE {$whereSql}
                 GROUP BY {$groupExpr}
                 ORDER BY label ASC";

        $rows = $this->db->fetchAll($sql, $params);

        foreach ($rows as &$row) {
            $row['category'] = 'Teacher Attendance';
            $row['rate'] = $this->rate((int) $row['present'], (int) $row['total']);
        }

        return $rows;
    }

    // ------------------------------------------------------------------
    // Analytics
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function analyticsOverview(?int $sessionId, ?int $termId): array
    {
        $sessionWhere = '';
        $sessionParams = [];
        if ($sessionId) {
            $sessionWhere = ' AND sa.session_id = :session_id';
            $sessionParams['session_id'] = $sessionId;
        }

        $session = $sessionId ? $this->db->fetchOne('SELECT start_date, end_date FROM academic_sessions WHERE id = :id', ['id' => $sessionId]) : null;
        $dateFrom = $session['start_date'] ?? null;
        $dateTo = $session['end_date'] ?? null;

        $studentTotals = $this->db->fetchOne(
            "SELECT SUM(status='present') present, COUNT(*) total FROM student_attendance sa WHERE 1=1{$sessionWhere}",
            $sessionParams
        );
        $studentPresent = (int) ($studentTotals['present'] ?? 0);
        $studentTotal = (int) ($studentTotals['total'] ?? 0);

        $teacherDateWhere = '';
        $teacherParams = [];
        if ($dateFrom && $dateTo) {
            $teacherDateWhere = ' WHERE ta.attendance_date BETWEEN :date_from AND :date_to';
            $teacherParams = ['date_from' => $dateFrom, 'date_to' => $dateTo];
        }
        $teacherTotals = $this->db->fetchOne(
            "SELECT SUM(status='present') present, COUNT(*) total FROM teacher_attendance ta{$teacherDateWhere}",
            $teacherParams
        );
        $teacherPresent = (int) ($teacherTotals['present'] ?? 0);
        $teacherTotal = (int) ($teacherTotals['total'] ?? 0);

        $overallPresent = $studentPresent + $teacherPresent;
        $overallTotal = $studentTotal + $teacherTotal;

        // Monthly trend (Jan-Dec) within the session's date range, falling back to the current calendar year.
        $trendFrom = $dateFrom ?? (date('Y') . '-01-01');
        $trendTo = $dateTo ?? (date('Y') . '-12-31');

        $studentMonthly = $this->db->fetchAll(
            "SELECT MONTH(attendance_date) m, SUM(status='present') present, COUNT(*) total
             FROM student_attendance WHERE attendance_date BETWEEN :from AND :to GROUP BY MONTH(attendance_date)",
            ['from' => $trendFrom, 'to' => $trendTo]
        );
        $teacherMonthly = $this->db->fetchAll(
            "SELECT MONTH(attendance_date) m, SUM(status='present') present, COUNT(*) total
             FROM teacher_attendance WHERE attendance_date BETWEEN :from AND :to GROUP BY MONTH(attendance_date)",
            ['from' => $trendFrom, 'to' => $trendTo]
        );

        $studentTrend = array_fill(1, 12, 0);
        foreach ($studentMonthly as $row) { $studentTrend[(int) $row['m']] = $this->rateFloat((int) $row['present'], (int) $row['total']); }
        $teacherTrend = array_fill(1, 12, 0);
        foreach ($teacherMonthly as $row) { $teacherTrend[(int) $row['m']] = $this->rateFloat((int) $row['present'], (int) $row['total']); }

        // Per-class rates within the session.
        $classRows = $this->db->fetchAll(
            "SELECT c.name, SUM(sa.status='present') present, COUNT(*) total
             FROM student_attendance sa INNER JOIN classes c ON c.id = sa.class_id
             WHERE 1=1{$sessionWhere} GROUP BY sa.class_id, c.name ORDER BY c.name ASC",
            $sessionParams
        );
        $classRates = [];
        foreach ($classRows as $row) {
            $classRates[$row['name']] = $this->rateFloat((int) $row['present'], (int) $row['total']);
        }

        // Insights: perfect attendance / frequently absent (threshold 3+).
        $studentPerfect = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM (SELECT student_id FROM student_attendance sa WHERE 1=1{$sessionWhere}
                GROUP BY student_id HAVING SUM(status<>'present') = 0 AND COUNT(*) > 0) t",
            $sessionParams
        )['c'] ?? 0);
        $studentFrequentAbsent = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM (SELECT student_id FROM student_attendance sa WHERE 1=1{$sessionWhere}
                GROUP BY student_id HAVING SUM(status='absent') >= 3) t",
            $sessionParams
        )['c'] ?? 0);
        $teacherPerfect = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM (SELECT staff_id FROM teacher_attendance ta{$teacherDateWhere}
                GROUP BY staff_id HAVING SUM(status<>'present') = 0 AND COUNT(*) > 0) t",
            $teacherParams
        )['c'] ?? 0);
        $teacherFrequentAbsent = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM (SELECT staff_id FROM teacher_attendance ta{$teacherDateWhere}
                GROUP BY staff_id HAVING SUM(status='absent') >= 3) t",
            $teacherParams
        )['c'] ?? 0);

        $bestClass = $classRates ? array_search(max($classRates), $classRates, true) : null;
        $worstClass = $classRates ? array_search(min($classRates), $classRates, true) : null;

        return [
            'overallRate' => $this->rate($overallPresent, $overallTotal),
            'studentRate' => $this->rate($studentPresent, $studentTotal),
            'teacherRate' => $this->rate($teacherPresent, $teacherTotal),
            'studentTrend' => array_values($studentTrend),
            'teacherTrend' => array_values($teacherTrend),
            'classRates' => $classRates,
            'bestClass' => $bestClass,
            'worstClass' => $worstClass,
            'studentPerfectAttendance' => $studentPerfect,
            'studentFrequentlyAbsent' => $studentFrequentAbsent,
            'teacherPerfectAttendance' => $teacherPerfect,
            'teacherFrequentlyAbsent' => $teacherFrequentAbsent,
            'distribution' => [
                'present' => $this->rateFloat($overallPresent, $overallTotal),
                'other' => $this->rateFloat($studentTotal + $teacherTotal - $overallPresent - $this->overallAbsent($sessionWhere, $sessionParams, $teacherDateWhere, $teacherParams), $overallTotal),
                'absent' => $this->rateFloat($this->overallAbsent($sessionWhere, $sessionParams, $teacherDateWhere, $teacherParams), $overallTotal),
            ],
        ];
    }

    private function overallAbsent(string $sessionWhere, array $sessionParams, string $teacherDateWhere, array $teacherParams): int
    {
        $studentAbsent = (int) ($this->db->fetchOne("SELECT SUM(status='absent') c FROM student_attendance sa WHERE 1=1{$sessionWhere}", $sessionParams)['c'] ?? 0);
        $teacherAbsent = (int) ($this->db->fetchOne("SELECT SUM(status='absent') c FROM teacher_attendance ta{$teacherDateWhere}", $teacherParams)['c'] ?? 0);

        return $studentAbsent + $teacherAbsent;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function enumFilter(mixed $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, self::STATUSES, true) ? $value : null;
    }

    private function rate(int $present, int $total): string
    {
        if ($total <= 0) {
            return '0%';
        }

        return rtrim(rtrim(number_format(($present / $total) * 100, 1), '0'), '.') . '%';
    }

    private function rateFloat(int $present, int $total): float
    {
        return $total > 0 ? round(($present / $total) * 100, 1) : 0.0;
    }
}
