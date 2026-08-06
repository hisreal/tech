<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;
use App\Models\SettingsModel;
use App\Traits\Auditable;

/**
 * Backing service for Timetable Management: CRUD with conflict detection,
 * class/teacher grid views, publish workflow, reports, and settings
 * (periods/working days/general rules) persisted via school_settings.
 */
final class TimetableService
{
    use Auditable;

    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    private const STATUSES = ['draft', 'published', 'unpublished'];
    private const DEFAULT_PER_PAGE = 15;

    private SettingsModel $settings;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->settings = new SettingsModel($this->db);
    }

    // ------------------------------------------------------------------
    // Select helpers
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function sessionsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM academic_sessions ORDER BY start_date DESC');
    }

    /** @return array<int,array<string,mixed>> */
    public function termsForSelect(?int $sessionId = null): array
    {
        if ($sessionId) {
            return $this->db->fetchAll('SELECT id, name, session_id FROM terms WHERE session_id = :sid ORDER BY start_date ASC', ['sid' => $sessionId]);
        }

        return $this->db->fetchAll('SELECT id, name, session_id FROM terms ORDER BY start_date DESC');
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
    public function subjectsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name, code FROM subjects WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function teachersForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, staff_no, first_name, last_name, department_id FROM staff WHERE staff_type = 'teacher' AND employment_status = 'active' ORDER BY last_name ASC, first_name ASC"
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function venuesForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name, capacity FROM venues WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function departmentsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM departments WHERE status = "active" ORDER BY name ASC');
    }

    public function teacherIdForUser(int $userId): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM staff WHERE user_id = :uid', ['uid' => $userId]);

        return $row ? (int) $row['id'] : null;
    }

    /** @return array{class_id:int,section_id:?int}|null */
    public function classForStudentUser(int $userId): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT se.class_id, se.section_id FROM students s
             INNER JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
             WHERE s.user_id = :uid ORDER BY se.id DESC LIMIT 1",
            ['uid' => $userId]
        );

        return $row ? ['class_id' => (int) $row['class_id'], 'section_id' => $row['section_id'] !== null ? (int) $row['section_id'] : null] : null;
    }

    /** @return array<int,string> */
    public function workingDays(): array
    {
        $rows = $this->db->fetchAll('SELECT day_name FROM working_days WHERE is_enabled = 1 ORDER BY FIELD(day_name, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")');

        return array_column($rows, 'day_name') ?: self::DAYS;
    }

    /** @return array<int,array<string,mixed>> */
    public function periodsForSelect(): array
    {
        return $this->db->fetchAll('SELECT * FROM school_periods WHERE status = "active" ORDER BY sort_order ASC, start_time ASC');
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
    // Listing / grids / find
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function list(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);

        $sql = "SELECT te.*, c.name AS class_name, sec.name AS section_name, sub.name AS subject_name,
                    st.first_name AS teacher_first_name, st.last_name AS teacher_last_name, st.department_id,
                    v.name AS venue_name, s.name AS session_name, t.name AS term_name
                 FROM timetable_entries te
                 INNER JOIN classes c ON c.id = te.class_id
                 LEFT JOIN sections sec ON sec.id = te.section_id
                 INNER JOIN subjects sub ON sub.id = te.subject_id
                 INNER JOIN staff st ON st.id = te.teacher_id
                 LEFT JOIN venues v ON v.id = te.venue_id
                 INNER JOIN academic_sessions s ON s.id = te.session_id
                 INNER JOIN terms t ON t.id = te.term_id
                 {$whereSql}
                 ORDER BY FIELD(te.day_name,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), te.start_time ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @param array<string,mixed> $filters @return array<int,array<string,mixed>> */
    public function grid(array $filters): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);

        $sql = "SELECT te.*, c.name AS class_name, sec.name AS section_name, sub.name AS subject_name,
                    st.first_name AS teacher_first_name, st.last_name AS teacher_last_name, v.name AS venue_name
                 FROM timetable_entries te
                 INNER JOIN classes c ON c.id = te.class_id
                 LEFT JOIN sections sec ON sec.id = te.section_id
                 INNER JOIN subjects sub ON sub.id = te.subject_id
                 INNER JOIN staff st ON st.id = te.teacher_id
                 LEFT JOIN venues v ON v.id = te.venue_id
                 {$whereSql}
                 ORDER BY te.start_time ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT te.*, c.name AS class_name, sec.name AS section_name, sub.name AS subject_name,
                st.first_name AS teacher_first_name, st.last_name AS teacher_last_name,
                v.name AS venue_name, s.name AS session_name, t.name AS term_name
             FROM timetable_entries te
             INNER JOIN classes c ON c.id = te.class_id
             LEFT JOIN sections sec ON sec.id = te.section_id
             INNER JOIN subjects sub ON sub.id = te.subject_id
             INNER JOIN staff st ON st.id = te.teacher_id
             LEFT JOIN venues v ON v.id = te.venue_id
             INNER JOIN academic_sessions s ON s.id = te.session_id
             INNER JOIN terms t ON t.id = te.term_id
             WHERE te.id = :id",
            ['id' => $id]
        );
    }

    /** @param array<string,mixed> $filters @return array{0:string,1:array<string,mixed>} */
    private function buildWhere(array $filters): array
    {
        $where = ['1=1'];
        $params = [];

        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'te.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'te.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'te.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($sectionId = $this->intFilter($filters['section_id'] ?? 0)) !== null) { $where[] = 'te.section_id = :section_id'; $params['section_id'] = $sectionId; }
        if (($teacherId = $this->intFilter($filters['teacher_id'] ?? 0)) !== null) { $where[] = 'te.teacher_id = :teacher_id'; $params['teacher_id'] = $teacherId; }
        if (($departmentId = $this->intFilter($filters['department_id'] ?? 0)) !== null) { $where[] = 'te.teacher_id IN (SELECT id FROM staff WHERE department_id = :department_id)'; $params['department_id'] = $departmentId; }
        if (($day = trim((string) ($filters['day'] ?? ''))) !== '' && in_array($day, self::DAYS, true)) { $where[] = 'te.day_name = :day'; $params['day'] = $day; }
        if (($status = trim((string) ($filters['status'] ?? ''))) !== '' && in_array($status, self::STATUSES, true)) { $where[] = 'te.status = :status'; $params['status'] = $status; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(sub.name LIKE :search1 OR CONCAT(st.first_name,' ',st.last_name) LIKE :search2 OR c.name LIKE :search3)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        return [' WHERE ' . implode(' AND ', $where), $params];
    }

    // ------------------------------------------------------------------
    // Conflict detection
    // ------------------------------------------------------------------

    /**
     * @param array<string,mixed> $fields normalized fields (see normalizeFields())
     * @return array<int,string> human-readable conflict messages, empty if none
     */
    public function detectConflicts(array $fields, ?int $excludeId = null): array
    {
        $conflicts = [];
        $excludeSql = $excludeId ? ' AND te.id <> :exclude_id' : '';
        $baseParams = [
            'session_id' => $fields['session_id'],
            'term_id' => $fields['term_id'],
            'day' => $fields['day_name'],
            'start' => $fields['start_time'],
            'end' => $fields['end_time'],
        ];
        if ($excludeId) {
            $baseParams['exclude_id'] = $excludeId;
        }

        $overlapSql = 'te.session_id = :session_id AND te.term_id = :term_id AND te.day_name = :day
            AND te.start_time < :end AND te.end_time > :start' . $excludeSql;

        $teacherRow = $this->db->fetchOne(
            "SELECT te.*, sub.name AS subject_name, c.name AS class_name FROM timetable_entries te
             INNER JOIN subjects sub ON sub.id = te.subject_id INNER JOIN classes c ON c.id = te.class_id
             WHERE te.teacher_id = :teacher_id AND {$overlapSql} LIMIT 1",
            array_merge($baseParams, ['teacher_id' => $fields['teacher_id']])
        );
        if ($teacherRow) {
            $conflicts[] = "Teacher is already scheduled to teach {$teacherRow['subject_name']} for {$teacherRow['class_name']} on {$fields['day_name']} from " . substr((string) $teacherRow['start_time'], 0, 5) . ' to ' . substr((string) $teacherRow['end_time'], 0, 5) . '.';
        }

        $classRow = $this->db->fetchOne(
            "SELECT te.*, sub.name AS subject_name FROM timetable_entries te INNER JOIN subjects sub ON sub.id = te.subject_id
             WHERE te.class_id = :class_id AND te.section_id <=> :section_id AND {$overlapSql} LIMIT 1",
            array_merge($baseParams, ['class_id' => $fields['class_id'], 'section_id' => $fields['section_id']])
        );
        if ($classRow) {
            $conflicts[] = "This class already has {$classRow['subject_name']} scheduled on {$fields['day_name']} from " . substr((string) $classRow['start_time'], 0, 5) . ' to ' . substr((string) $classRow['end_time'], 0, 5) . '.';
        }

        if (!empty($fields['venue_id'])) {
            $venueRow = $this->db->fetchOne(
                "SELECT te.*, sub.name AS subject_name, c.name AS class_name FROM timetable_entries te
                 INNER JOIN subjects sub ON sub.id = te.subject_id INNER JOIN classes c ON c.id = te.class_id
                 WHERE te.venue_id = :venue_id AND {$overlapSql} LIMIT 1",
                array_merge($baseParams, ['venue_id' => $fields['venue_id']])
            );
            if ($venueRow) {
                $conflicts[] = "This venue is already booked for {$venueRow['subject_name']} ({$venueRow['class_name']}) on {$fields['day_name']} from " . substr((string) $venueRow['start_time'], 0, 5) . ' to ' . substr((string) $venueRow['end_time'], 0, 5) . '.';
            }
        }

        return $conflicts;
    }

    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function create(array $data, ?array $actor): array
    {
        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $conflicts = $this->detectConflicts($fields);
        if ($conflicts !== []) {
            return ['success' => false, 'message' => 'This entry conflicts with an existing timetable entry.', 'errors' => ['conflict' => implode(' ', $conflicts)], 'conflicts' => $conflicts];
        }

        $payload = $this->payload($fields, $actor);

        try {
            $this->db->execute(
                'INSERT INTO timetable_entries (session_id, term_id, class_id, section_id, subject_id, teacher_id, venue_id, day_name, start_time, end_time, status, created_by)
                 VALUES (:session_id, :term_id, :class_id, :section_id, :subject_id, :teacher_id, :venue_id, :day_name, :start_time, :end_time, :status, :created_by)',
                $payload
            );
            $id = (int) $this->db->lastInsertId();
            $this->audit($actor, 'timetable', 'timetable.created', 'timetable_entries', $id, null, $payload);
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to create the timetable entry right now.'];
        }

        return ['success' => true, 'message' => 'Timetable entry created successfully.', 'id' => $id];
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function update(int $id, array $data, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM timetable_entries WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Timetable entry not found.'];
        }

        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $conflicts = $this->detectConflicts($fields, $id);
        if ($conflicts !== []) {
            return ['success' => false, 'message' => 'This entry conflicts with an existing timetable entry.', 'errors' => ['conflict' => implode(' ', $conflicts)], 'conflicts' => $conflicts];
        }

        $payload = $this->payload($fields, $actor);
        unset($payload['created_by']);

        try {
            $this->db->execute(
                'UPDATE timetable_entries SET session_id = :session_id, term_id = :term_id, class_id = :class_id, section_id = :section_id,
                    subject_id = :subject_id, teacher_id = :teacher_id, venue_id = :venue_id, day_name = :day_name,
                    start_time = :start_time, end_time = :end_time, status = :status WHERE id = :id',
                array_merge($payload, ['id' => $id])
            );
            $this->audit($actor, 'timetable', 'timetable.updated', 'timetable_entries', $id, $before, $payload);
        } catch (\Throwable $throwable) {
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update the timetable entry right now.'];
        }

        return ['success' => true, 'message' => 'Timetable entry updated successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function delete(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM timetable_entries WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Timetable entry not found.'];
        }

        $this->db->execute('DELETE FROM timetable_entries WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'timetable', 'timetable.deleted', 'timetable_entries', $id, $before, null);

        return ['success' => true, 'message' => 'Timetable entry deleted successfully.'];
    }

    /** @param array<int,int> $ids @param array<string,mixed>|null $actor */
    public function bulkSetStatus(array $ids, string $status, ?array $actor): array
    {
        if (!in_array($status, self::STATUSES, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $count = 0;
        foreach (array_unique(array_filter(array_map('intval', $ids))) as $id) {
            $before = $this->db->fetchOne('SELECT * FROM timetable_entries WHERE id = :id', ['id' => $id]);
            if ($before === null) {
                continue;
            }
            $this->db->execute('UPDATE timetable_entries SET status = :status WHERE id = :id', ['status' => $status, 'id' => $id]);
            $this->audit($actor, 'timetable', 'timetable.' . $status, 'timetable_entries', $id, ['status' => $before['status']], ['status' => $status]);
            $count++;
        }

        return ['success' => true, 'message' => "{$count} timetable entr" . ($count === 1 ? 'y' : 'ies') . ' updated to ' . $status . '.'];
    }

    /** @param array<int,int> $ids @param array<string,mixed>|null $actor */
    public function bulkDelete(array $ids, ?array $actor): array
    {
        $count = 0;
        foreach (array_unique(array_filter(array_map('intval', $ids))) as $id) {
            $result = $this->delete($id, $actor);
            if ($result['success']) {
                $count++;
            }
        }

        return ['success' => true, 'message' => "{$count} timetable entr" . ($count === 1 ? 'y' : 'ies') . ' deleted.'];
    }

    // ------------------------------------------------------------------
    // Reports
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function teacherWorkloadReport(?int $sessionId, ?int $termId): array
    {
        [$whereSql, $params] = $this->buildWhere(['session_id' => $sessionId, 'term_id' => $termId]);

        return $this->db->fetchAll(
            "SELECT st.id, CONCAT(st.first_name, ' ', st.last_name) AS teacher_name, st.staff_no,
                COUNT(*) AS periods, SUM(TIME_TO_SEC(TIMEDIFF(te.end_time, te.start_time))) / 3600 AS hours,
                COUNT(DISTINCT te.subject_id) AS subject_count, COUNT(DISTINCT te.class_id) AS class_count
             FROM timetable_entries te INNER JOIN staff st ON st.id = te.teacher_id
             {$whereSql}
             GROUP BY st.id, teacher_name, st.staff_no
             ORDER BY periods DESC",
            $params
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function classScheduleReport(?int $sessionId, ?int $termId): array
    {
        [$whereSql, $params] = $this->buildWhere(['session_id' => $sessionId, 'term_id' => $termId]);

        return $this->db->fetchAll(
            "SELECT c.id, c.name AS class_name, COUNT(*) AS periods,
                SUM(TIME_TO_SEC(TIMEDIFF(te.end_time, te.start_time))) / 3600 AS hours,
                COUNT(DISTINCT te.subject_id) AS subject_count
             FROM timetable_entries te INNER JOIN classes c ON c.id = te.class_id
             {$whereSql}
             GROUP BY c.id, class_name
             ORDER BY class_name ASC",
            $params
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function venueUtilizationReport(?int $sessionId, ?int $termId): array
    {
        [$whereSql, $params] = $this->buildWhere(['session_id' => $sessionId, 'term_id' => $termId]);
        $whereSql .= ' AND te.venue_id IS NOT NULL';

        return $this->db->fetchAll(
            "SELECT v.id, v.name AS venue_name, COUNT(*) AS bookings,
                SUM(TIME_TO_SEC(TIMEDIFF(te.end_time, te.start_time))) / 3600 AS hours
             FROM timetable_entries te INNER JOIN venues v ON v.id = te.venue_id
             {$whereSql}
             GROUP BY v.id, venue_name
             ORDER BY bookings DESC",
            $params
        );
    }

    // ------------------------------------------------------------------
    // Settings (periods, working days, general rules)
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function generalSettings(): array
    {
        $all = $this->settings->all();
        $get = static fn (string $key, mixed $default) => $all[$key]['value'] ?? $default;

        return [
            'opening_time' => $get('timetable.opening_time', '08:00'),
            'closing_time' => $get('timetable.closing_time', '15:00'),
            'default_lesson_duration' => (int) $get('timetable.default_lesson_duration', 40),
            'break_duration' => (int) $get('timetable.break_duration', 30),
            'periods_per_day' => (int) $get('timetable.periods_per_day', 8),
            'enable_conflict_detection' => (bool) $get('timetable.enable_conflict_detection', true),
            'allow_double_periods' => (bool) $get('timetable.allow_double_periods', true),
            'auto_assign_break_time' => (bool) $get('timetable.auto_assign_break_time', true),
            'default_venue_id' => (int) $get('timetable.default_venue_id', 0),
        ];
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function saveGeneralSettings(array $data, ?array $actor): array
    {
        $before = $this->generalSettings();

        $new = [
            'timetable.opening_time' => ['value' => trim((string) ($data['opening_time'] ?? '08:00')), 'type' => 'string', 'group' => 'timetable'],
            'timetable.closing_time' => ['value' => trim((string) ($data['closing_time'] ?? '15:00')), 'type' => 'string', 'group' => 'timetable'],
            'timetable.default_lesson_duration' => ['value' => (int) ($data['default_lesson_duration'] ?? 40), 'type' => 'number', 'group' => 'timetable'],
            'timetable.break_duration' => ['value' => (int) ($data['break_duration'] ?? 30), 'type' => 'number', 'group' => 'timetable'],
            'timetable.periods_per_day' => ['value' => (int) ($data['periods_per_day'] ?? 8), 'type' => 'number', 'group' => 'timetable'],
            'timetable.enable_conflict_detection' => ['value' => !empty($data['enable_conflict_detection']), 'type' => 'boolean', 'group' => 'timetable'],
            'timetable.allow_double_periods' => ['value' => !empty($data['allow_double_periods']), 'type' => 'boolean', 'group' => 'timetable'],
            'timetable.auto_assign_break_time' => ['value' => !empty($data['auto_assign_break_time']), 'type' => 'boolean', 'group' => 'timetable'],
            'timetable.default_venue_id' => ['value' => (int) ($data['default_venue_id'] ?? 0), 'type' => 'number', 'group' => 'timetable'],
        ];

        $this->settings->upsertMany($new, isset($actor['id']) ? (int) $actor['id'] : null);
        $this->settings->audit($actor, 'timetable', $before, $this->generalSettings());

        return ['success' => true, 'message' => 'Timetable settings saved successfully.'];
    }

    /** @return array<int,array<string,mixed>> */
    public function allWorkingDays(): array
    {
        $existing = $this->db->fetchAll('SELECT day_name, is_enabled FROM working_days');
        $keyed = [];
        foreach ($existing as $row) {
            $keyed[$row['day_name']] = (bool) $row['is_enabled'];
        }

        $rows = [];
        foreach (self::DAYS as $day) {
            $rows[] = ['day_name' => $day, 'is_enabled' => $keyed[$day] ?? false];
        }

        return $rows;
    }

    /** @param array<int,string> $enabledDays @param array<string,mixed>|null $actor */
    public function saveWorkingDays(array $enabledDays, ?array $actor): array
    {
        $before = $this->allWorkingDays();

        foreach (self::DAYS as $day) {
            $enabled = in_array($day, $enabledDays, true) ? 1 : 0;
            $this->db->execute(
                'INSERT INTO working_days (day_name, is_enabled) VALUES (:day, :enabled) ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)',
                ['day' => $day, 'enabled' => $enabled]
            );
        }

        $this->audit($actor, 'timetable', 'timetable.working_days.updated', 'working_days', null, $before, $this->allWorkingDays());

        return ['success' => true, 'message' => 'Working days saved successfully.'];
    }

    /** @param array<string,mixed> $data @param array<string,mixed>|null $actor */
    public function savePeriod(array $data, ?int $id, ?array $actor): array
    {
        $name = trim((string) ($data['period_name'] ?? ''));
        $start = trim((string) ($data['start_time'] ?? ''));
        $end = trim((string) ($data['end_time'] ?? ''));
        $isBreak = !empty($data['is_break']);

        $errors = [];
        if ($name === '') { $errors['period_name'] = 'Period name is required.'; }
        if ($start === '' || $end === '') { $errors['start_time'] = 'Start and end time are required.'; }
        elseif (strtotime($start) >= strtotime($end)) { $errors['start_time'] = 'Start time must be before end time.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $overlap = $this->db->fetchOne(
            'SELECT id FROM school_periods WHERE status = "active" AND start_time < :end AND end_time > :start' . ($id ? ' AND id <> :id' : ''),
            $id ? ['start' => $start, 'end' => $end, 'id' => $id] : ['start' => $start, 'end' => $end]
        );
        if ($overlap) {
            return ['success' => false, 'message' => 'This period overlaps with an existing period.', 'errors' => ['start_time' => 'Overlaps with an existing period.']];
        }

        $maxOrder = (int) ($this->db->fetchOne('SELECT COALESCE(MAX(sort_order), 0) m FROM school_periods')['m'] ?? 0);
        $payload = ['period_name' => $name, 'start_time' => $start, 'end_time' => $end, 'is_break' => $isBreak ? 1 : 0];

        if ($id) {
            $before = $this->db->fetchOne('SELECT * FROM school_periods WHERE id = :id', ['id' => $id]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Period not found.'];
            }
            $this->db->execute('UPDATE school_periods SET period_name = :period_name, start_time = :start_time, end_time = :end_time, is_break = :is_break WHERE id = :id', array_merge($payload, ['id' => $id]));
            $this->audit($actor, 'timetable', 'timetable.period.updated', 'school_periods', $id, $before, $payload);

            return ['success' => true, 'message' => 'Period updated successfully.'];
        }

        $this->db->execute(
            'INSERT INTO school_periods (period_name, start_time, end_time, is_break, sort_order) VALUES (:period_name, :start_time, :end_time, :is_break, :sort_order)',
            array_merge($payload, ['sort_order' => $maxOrder + 1])
        );
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'timetable', 'timetable.period.created', 'school_periods', $newId, null, $payload);

        return ['success' => true, 'message' => 'Period created successfully.'];
    }

    /** @param array<string,mixed>|null $actor */
    public function deletePeriod(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM school_periods WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Period not found.'];
        }

        $this->db->execute('DELETE FROM school_periods WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'timetable', 'timetable.period.deleted', 'school_periods', $id, $before, null);

        return ['success' => true, 'message' => 'Period deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function normalizeFields(array $data): array
    {
        return [
            'session_id' => (int) ($data['session_id'] ?? 0),
            'term_id' => (int) ($data['term_id'] ?? 0),
            'class_id' => (int) ($data['class_id'] ?? 0),
            'section_id' => (int) ($data['section_id'] ?? 0) ?: null,
            'subject_id' => (int) ($data['subject_id'] ?? 0),
            'teacher_id' => (int) ($data['teacher_id'] ?? 0),
            'venue_id' => (int) ($data['venue_id'] ?? 0) ?: null,
            'day_name' => trim((string) ($data['day_name'] ?? $data['day'] ?? '')),
            'start_time' => trim((string) ($data['start_time'] ?? '')),
            'end_time' => trim((string) ($data['end_time'] ?? '')),
            'status' => strtolower(trim((string) ($data['status'] ?? 'draft'))),
        ];
    }

    /** @return array<string,string> */
    private function validate(array $f): array
    {
        $errors = [];

        if ($f['session_id'] < 1) { $errors['session_id'] = 'Choose an academic session.'; }
        if ($f['term_id'] < 1) { $errors['term_id'] = 'Choose a term.'; }
        if ($f['class_id'] < 1) { $errors['class_id'] = 'Choose a class.'; }
        if ($f['subject_id'] < 1) { $errors['subject_id'] = 'Choose a subject.'; }
        if ($f['teacher_id'] < 1) { $errors['teacher_id'] = 'Choose a teacher.'; }
        if (!in_array($f['day_name'], self::DAYS, true)) { $errors['day_name'] = 'Choose a valid day.'; }
        if ($f['start_time'] === '' || $f['end_time'] === '') {
            $errors['start_time'] = 'Start and end time are required.';
        } elseif (strtotime($f['start_time']) >= strtotime($f['end_time'])) {
            $errors['start_time'] = 'Start time must be before end time.';
        }
        if (!in_array($f['status'], self::STATUSES, true)) { $errors['status'] = 'Choose a valid status.'; }

        return $errors;
    }

    /** @return array<string,mixed> */
    private function payload(array $f, ?array $actor): array
    {
        return [
            'session_id' => $f['session_id'],
            'term_id' => $f['term_id'],
            'class_id' => $f['class_id'],
            'section_id' => $f['section_id'],
            'subject_id' => $f['subject_id'],
            'teacher_id' => $f['teacher_id'],
            'venue_id' => $f['venue_id'],
            'day_name' => $f['day_name'],
            'start_time' => $f['start_time'],
            'end_time' => $f['end_time'],
            'status' => $f['status'],
            'created_by' => isset($actor['id']) ? (int) $actor['id'] : null,
        ];
    }

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
