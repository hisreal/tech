<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;
use App\Models\SettingsModel;
use App\Traits\Auditable;

/**
 * Backing service for Result Management: score entry, grade/position
 * calculation, the approve/publish/lock workflow, broadsheets, and report cards.
 */
final class ResultService
{
    use Auditable;

    private const BATCH_STATUSES = ['draft', 'submitted', 'approved', 'published', 'locked'];
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
    public function subjectsForSelect(?int $classId = null): array
    {
        if ($classId) {
            return $this->db->fetchAll(
                'SELECT s.id, s.name FROM subjects s INNER JOIN subject_classes sc ON sc.subject_id = s.id
                 WHERE sc.class_id = :cid AND s.status = "active" ORDER BY s.name ASC',
                ['cid' => $classId]
            );
        }

        return $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = "active" ORDER BY name ASC');
    }

    /** @return array<int,array<string,mixed>> */
    public function teachersForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, staff_no, first_name, last_name FROM staff WHERE staff_type = 'teacher' AND employment_status = 'active' ORDER BY last_name ASC, first_name ASC"
        );
    }

    public function teacherIdForUser(int $userId): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM staff WHERE user_id = :uid', ['uid' => $userId]);

        return $row ? (int) $row['id'] : null;
    }

    public function studentIdForUser(int $userId): ?int
    {
        $row = $this->db->fetchOne('SELECT id FROM students WHERE user_id = :uid', ['uid' => $userId]);

        return $row ? (int) $row['id'] : null;
    }

    /** @return array<int,array<string,mixed>> */
    public function classesForTeacher(int $teacherId): array
    {
        return $this->db->fetchAll(
            'SELECT DISTINCT c.id, c.name FROM classes c INNER JOIN teacher_classes tc ON tc.class_id = c.id WHERE tc.teacher_id = :tid ORDER BY c.name ASC',
            ['tid' => $teacherId]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function sectionsForTeacher(int $teacherId, ?int $classId = null): array
    {
        $where = ['tc.teacher_id = :tid'];
        $params = ['tid' => $teacherId];
        if ($classId) {
            $where[] = 'tc.class_id = :cid';
            $params['cid'] = $classId;
        }

        return $this->db->fetchAll(
            'SELECT DISTINCT sec.id, sec.name, tc.class_id FROM sections sec
             INNER JOIN teacher_classes tc ON tc.section_id = sec.id
             WHERE ' . implode(' AND ', $where) . ' ORDER BY sec.name ASC',
            $params
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function subjectsForTeacher(int $teacherId): array
    {
        return $this->db->fetchAll(
            'SELECT DISTINCT s.id, s.name FROM subjects s INNER JOIN teacher_subjects ts ON ts.subject_id = s.id WHERE ts.teacher_id = :tid ORDER BY s.name ASC',
            ['tid' => $teacherId]
        );
    }

    public function teacherOwnsClassSection(int $teacherId, int $classId, ?int $sectionId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT 1 FROM teacher_classes WHERE teacher_id = :tid AND class_id = :cid AND section_id <=> :sid LIMIT 1',
            ['tid' => $teacherId, 'cid' => $classId, 'sid' => $sectionId]
        );

        return $row !== null;
    }

    public function teacherOwnsSubject(int $teacherId, int $subjectId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT 1 FROM teacher_subjects WHERE teacher_id = :tid AND subject_id = :sid LIMIT 1',
            ['tid' => $teacherId, 'sid' => $subjectId]
        );

        return $row !== null;
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
    // Grade settings
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function listGrades(): array
    {
        return $this->db->fetchAll('SELECT * FROM grade_settings ORDER BY min_score DESC');
    }

    public function saveGrade(array $data, ?int $id, ?array $actor): array
    {
        $grade = strtoupper(trim((string) ($data['grade'] ?? '')));
        $min = (float) ($data['min_score'] ?? -1);
        $max = (float) ($data['max_score'] ?? -1);
        $remark = trim((string) ($data['remark'] ?? ''));
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'active';

        $errors = [];
        if ($grade === '') { $errors['grade'] = 'Grade label is required.'; }
        if ($data['min_score'] === '' || $data['min_score'] === null || $min < 0) { $errors['min_score'] = 'Enter a valid minimum score.'; }
        if ($data['max_score'] === '' || $data['max_score'] === null || $max < 0) { $errors['max_score'] = 'Enter a valid maximum score.'; }
        if ($min >= 0 && $max >= 0 && $min > $max) { $errors['min_score'] = 'Minimum score must not exceed maximum score.'; }
        if ($remark === '') { $errors['remark'] = 'Remark is required.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $overlap = $this->db->fetchOne(
            'SELECT id FROM grade_settings WHERE min_score <= :max AND max_score >= :min' . ($id ? ' AND id <> :id' : ''),
            $id ? ['max' => $max, 'min' => $min, 'id' => $id] : ['max' => $max, 'min' => $min]
        );
        if ($overlap) {
            return ['success' => false, 'message' => 'This score range overlaps with an existing grade.', 'errors' => ['min_score' => 'Overlaps with an existing grade range.']];
        }

        $payload = ['grade' => $grade, 'min_score' => $min, 'max_score' => $max, 'remark' => $remark, 'status' => $status];

        if ($id) {
            $before = $this->db->fetchOne('SELECT * FROM grade_settings WHERE id = :id', ['id' => $id]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Grade not found.'];
            }
            try {
                $this->db->execute('UPDATE grade_settings SET grade=:grade, min_score=:min_score, max_score=:max_score, remark=:remark, status=:status WHERE id=:id', array_merge($payload, ['id' => $id]));
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'A grade with this label already exists.'];
            }
            $this->audit($actor, 'result', 'result.grade.updated', 'grade_settings', $id, $before, $payload);

            return ['success' => true, 'message' => 'Grade updated successfully.'];
        }

        try {
            $this->db->execute('INSERT INTO grade_settings (grade, min_score, max_score, remark, status) VALUES (:grade, :min_score, :max_score, :remark, :status)', $payload);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'A grade with this label already exists.'];
        }
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'result', 'result.grade.created', 'grade_settings', $newId, null, $payload);

        return ['success' => true, 'message' => 'Grade created successfully.'];
    }

    public function deleteGrade(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM grade_settings WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Grade not found.'];
        }
        $this->db->execute('DELETE FROM grade_settings WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'result', 'result.grade.deleted', 'grade_settings', $id, $before, null);

        return ['success' => true, 'message' => 'Grade deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Remark settings
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function listRemarks(): array
    {
        return $this->db->fetchAll('SELECT * FROM remark_settings ORDER BY category ASC, min_average DESC');
    }

    public function saveRemark(array $data, ?int $id, ?array $actor): array
    {
        $category = in_array($data['category'] ?? '', ['teacher', 'principal', 'general'], true) ? $data['category'] : 'general';
        $min = $data['min_average'] !== '' && $data['min_average'] !== null ? (float) $data['min_average'] : null;
        $max = $data['max_average'] !== '' && $data['max_average'] !== null ? (float) $data['max_average'] : null;
        $remark = trim((string) ($data['remark'] ?? ''));
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'active';

        $errors = [];
        if ($remark === '') { $errors['remark'] = 'Remark message is required.'; }
        if ($min !== null && $max !== null && $min > $max) { $errors['min_average'] = 'Minimum average must not exceed maximum average.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = ['category' => $category, 'min_average' => $min, 'max_average' => $max, 'remark' => $remark, 'status' => $status];

        if ($id) {
            $before = $this->db->fetchOne('SELECT * FROM remark_settings WHERE id = :id', ['id' => $id]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Remark not found.'];
            }
            $this->db->execute('UPDATE remark_settings SET category=:category, min_average=:min_average, max_average=:max_average, remark=:remark, status=:status WHERE id=:id', array_merge($payload, ['id' => $id]));
            $this->audit($actor, 'result', 'result.remark.updated', 'remark_settings', $id, $before, $payload);

            return ['success' => true, 'message' => 'Remark updated successfully.'];
        }

        $this->db->execute('INSERT INTO remark_settings (category, min_average, max_average, remark, status) VALUES (:category, :min_average, :max_average, :remark, :status)', $payload);
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'result', 'result.remark.created', 'remark_settings', $newId, null, $payload);

        return ['success' => true, 'message' => 'Remark created successfully.'];
    }

    public function deleteRemark(int $id, ?array $actor): array
    {
        $before = $this->db->fetchOne('SELECT * FROM remark_settings WHERE id = :id', ['id' => $id]);
        if ($before === null) {
            return ['success' => false, 'message' => 'Remark not found.'];
        }
        $this->db->execute('DELETE FROM remark_settings WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'result', 'result.remark.deleted', 'remark_settings', $id, $before, null);

        return ['success' => true, 'message' => 'Remark deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // General settings
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function generalSettings(): array
    {
        $all = $this->settings->all();
        $get = static fn (string $key, mixed $default) => $all[$key]['value'] ?? $default;

        return [
            'pass_mark' => (int) $get('result.pass_mark', 50),
            'enable_position_calculation' => (bool) $get('result.enable_position_calculation', true),
            'show_position_on_report_card' => (bool) $get('result.show_position_on_report_card', true),
            'show_average' => (bool) $get('result.show_average', true),
            'auto_publish_results' => (bool) $get('result.auto_publish_results', false),
            'auto_lock_published_results' => (bool) $get('result.auto_lock_published_results', true),
        ];
    }

    public function saveGeneralSettings(array $data, ?array $actor): array
    {
        $before = $this->generalSettings();
        $new = [
            'result.pass_mark' => ['value' => (int) ($data['pass_mark'] ?? 50), 'type' => 'number', 'group' => 'result'],
            'result.enable_position_calculation' => ['value' => !empty($data['enable_position_calculation']), 'type' => 'boolean', 'group' => 'result'],
            'result.show_position_on_report_card' => ['value' => !empty($data['show_position_on_report_card']), 'type' => 'boolean', 'group' => 'result'],
            'result.show_average' => ['value' => !empty($data['show_average']), 'type' => 'boolean', 'group' => 'result'],
            'result.auto_publish_results' => ['value' => !empty($data['auto_publish_results']), 'type' => 'boolean', 'group' => 'result'],
            'result.auto_lock_published_results' => ['value' => !empty($data['auto_lock_published_results']), 'type' => 'boolean', 'group' => 'result'],
        ];
        $this->settings->upsertMany($new, isset($actor['id']) ? (int) $actor['id'] : null);
        $this->settings->audit($actor, 'result', $before, $this->generalSettings());

        return ['success' => true, 'message' => 'Result settings saved successfully.'];
    }

    // ------------------------------------------------------------------
    // Result batches (Score Entry)
    // ------------------------------------------------------------------

    public function findOrCreateBatch(int $sessionId, int $termId, int $classId, ?int $sectionId, int $subjectId, ?int $teacherId): ?array
    {
        $existing = $this->db->fetchOne(
            'SELECT * FROM result_batches WHERE session_id=:session_id AND term_id=:term_id AND class_id=:class_id AND section_id <=> :section_id AND subject_id=:subject_id',
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId, 'subject_id' => $subjectId]
        );
        if ($existing) {
            return $existing;
        }

        $this->db->execute(
            'INSERT INTO result_batches (session_id, term_id, class_id, section_id, subject_id, teacher_id, status) VALUES (:session_id, :term_id, :class_id, :section_id, :subject_id, :teacher_id, "draft")',
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId, 'subject_id' => $subjectId, 'teacher_id' => $teacherId]
        );
        $id = (int) $this->db->lastInsertId();

        return $this->db->fetchOne('SELECT * FROM result_batches WHERE id = :id', ['id' => $id]);
    }

    /** @return array<string,mixed>|null */
    public function findBatch(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT rb.*, c.name AS class_name, sec.name AS section_name, sub.name AS subject_name,
                s.name AS session_name, t.name AS term_name, st.first_name AS teacher_first_name, st.last_name AS teacher_last_name
             FROM result_batches rb
             INNER JOIN classes c ON c.id = rb.class_id
             LEFT JOIN sections sec ON sec.id = rb.section_id
             INNER JOIN subjects sub ON sub.id = rb.subject_id
             INNER JOIN academic_sessions s ON s.id = rb.session_id
             INNER JOIN terms t ON t.id = rb.term_id
             LEFT JOIN staff st ON st.id = rb.teacher_id
             WHERE rb.id = :id",
            ['id' => $id]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function rosterForBatch(int $classId, ?int $sectionId, int $sessionId): array
    {
        $where = ['se.session_id = :session_id', 'se.class_id = :class_id', "se.status = 'active'", "s.status = 'active'"];
        $params = ['session_id' => $sessionId, 'class_id' => $classId];

        if ($sectionId) {
            $where[] = 'se.section_id = :section_id';
            $params['section_id'] = $sectionId;
        }

        return $this->db->fetchAll(
            'SELECT se.student_id AS id, s.registration_no, s.first_name, s.last_name
             FROM student_enrollments se INNER JOIN students s ON s.id = se.student_id
             WHERE ' . implode(' AND ', $where) . ' ORDER BY s.last_name ASC, s.first_name ASC',
            $params
        );
    }

    /** @return array<int,array<string,mixed>> Keyed by student_id. */
    public function existingScores(int $batchId): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM student_results WHERE result_batch_id = :id', ['id' => $batchId]);
        $keyed = [];
        foreach ($rows as $row) {
            $keyed[(int) $row['student_id']] = $row;
        }

        return $keyed;
    }

    /**
     * @param array<int,array<string,mixed>> $scores student_id => ['ca1'=>,'ca2'=>,'ca3'=>,'exam'=>,'practical'=>]
     * @param array<string,mixed>|null $actor
     */
    public function saveScores(int $batchId, array $scores, ?array $actor): array
    {
        $batch = $this->db->fetchOne('SELECT * FROM result_batches WHERE id = :id', ['id' => $batchId]);
        if ($batch === null) {
            return ['success' => false, 'message' => 'Result batch not found.'];
        }
        if ($batch['status'] === 'locked') {
            return ['success' => false, 'message' => 'This result batch is locked and cannot be edited.'];
        }

        $errors = [];
        $clean = [];
        foreach ($scores as $studentId => $entry) {
            $studentId = (int) $studentId;
            if ($studentId < 1) {
                continue;
            }
            $ca1 = $this->clampScore($entry['ca1'] ?? 0);
            $ca2 = $this->clampScore($entry['ca2'] ?? 0);
            $ca3 = $this->clampScore($entry['ca3'] ?? 0);
            $exam = $this->clampScore($entry['exam'] ?? 0);
            $practical = $this->clampScore($entry['practical'] ?? 0);
            if ($ca1 === null || $ca2 === null || $ca3 === null || $exam === null || $practical === null) {
                $errors[$studentId] = 'Scores must be numbers between 0 and 100.';
                continue;
            }
            $clean[$studentId] = compact('ca1', 'ca2', 'ca3', 'exam', 'practical');
        }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Some scores are invalid. Scores must be between 0 and 100.', 'errors' => $errors];
        }

        $grades = $this->listGrades();

        $this->db->beginTransaction();
        try {
            foreach ($clean as $studentId => $entry) {
                $total = $entry['ca1'] + $entry['ca2'] + $entry['ca3'] + $entry['exam'] + $entry['practical'];
                $grade = $this->gradeForScore($total, $grades);

                $this->db->execute(
                    'INSERT INTO student_results (result_batch_id, student_id, ca1, ca2, ca3, exam, practical, total, grade, remark, status)
                     VALUES (:batch_id, :student_id, :ca1, :ca2, :ca3, :exam, :practical, :total, :grade, :remark, :status)
                     ON DUPLICATE KEY UPDATE ca1=VALUES(ca1), ca2=VALUES(ca2), ca3=VALUES(ca3), exam=VALUES(exam), practical=VALUES(practical),
                        total=VALUES(total), grade=VALUES(grade), remark=VALUES(remark)',
                    [
                        'batch_id' => $batchId, 'student_id' => $studentId,
                        'ca1' => $entry['ca1'], 'ca2' => $entry['ca2'], 'ca3' => $entry['ca3'], 'exam' => $entry['exam'], 'practical' => $entry['practical'],
                        'total' => $total, 'grade' => $grade['grade'] ?? null, 'remark' => $grade['remark'] ?? null, 'status' => $batch['status'],
                    ]
                );
            }

            $this->recomputeSubjectPositions($batchId);

            // Keep the cached class-wide summary (total/average/position on
            // the broadsheet) in sync whenever scores change on a batch
            // that's already published - not just at publish time. Without
            // this, editing a score post-publish leaves Total/Average stale
            // while the individual subject column (read live) updates.
            if (in_array($batch['status'], ['published', 'locked'], true)) {
                $this->recomputeResultScores((int) $batch['session_id'], (int) $batch['term_id'], (int) $batch['class_id'], $batch['section_id'] ? (int) $batch['section_id'] : null);
            }

            $this->audit($actor, 'result', 'result.scores.saved', 'result_batches', $batchId, null, ['students_scored' => count($clean)]);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to save scores right now.'];
        }

        return ['success' => true, 'message' => 'Scores saved for ' . count($clean) . ' student(s).'];
    }

    private function recomputeSubjectPositions(int $batchId): void
    {
        $rows = $this->db->fetchAll('SELECT id, total FROM student_results WHERE result_batch_id = :id ORDER BY total DESC', ['id' => $batchId]);
        $position = 0;
        $lastTotal = null;
        $rank = 0;
        foreach ($rows as $row) {
            $rank++;
            if ($lastTotal === null || (float) $row['total'] !== $lastTotal) {
                $position = $rank;
                $lastTotal = (float) $row['total'];
            }
            $this->db->execute('UPDATE student_results SET position_in_subject = :pos WHERE id = :id', ['pos' => $position, 'id' => $row['id']]);
        }
    }

    private function clampScore(mixed $value): ?float
    {
        if ($value === '' || $value === null) {
            return 0.0;
        }
        if (!is_numeric($value)) {
            return null;
        }
        $value = (float) $value;

        return ($value >= 0 && $value <= 100) ? $value : null;
    }

    /** @param array<int,array<string,mixed>> $grades @return array<string,mixed> */
    private function gradeForScore(float $score, array $grades): array
    {
        foreach ($grades as $grade) {
            if ($score >= (float) $grade['min_score'] && $score <= (float) $grade['max_score']) {
                return $grade;
            }
        }

        return [];
    }

    // ------------------------------------------------------------------
    // Listing
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listBatches(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'rb.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'rb.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'rb.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($subjectId = $this->intFilter($filters['subject_id'] ?? 0)) !== null) { $where[] = 'rb.subject_id = :subject_id'; $params['subject_id'] = $subjectId; }
        if (($teacherId = $this->intFilter($filters['teacher_id'] ?? 0)) !== null) { $where[] = 'rb.teacher_id = :teacher_id'; $params['teacher_id'] = $teacherId; }
        if (($status = trim((string) ($filters['status'] ?? ''))) !== '' && in_array($status, self::BATCH_STATUSES, true)) { $where[] = 'rb.status = :status'; $params['status'] = $status; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(c.name LIKE :search1 OR sub.name LIKE :search2 OR CONCAT(st.first_name,' ',st.last_name) LIKE :search3)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT rb.*, c.name AS class_name, sec.name AS section_name, sub.name AS subject_name,
                    s.name AS session_name, t.name AS term_name, st.first_name AS teacher_first_name, st.last_name AS teacher_last_name,
                    (SELECT COUNT(*) FROM student_results sr WHERE sr.result_batch_id = rb.id) AS student_count,
                    (SELECT ROUND(AVG(sr.total), 1) FROM student_results sr WHERE sr.result_batch_id = rb.id) AS average_score
                 FROM result_batches rb
                 INNER JOIN classes c ON c.id = rb.class_id
                 LEFT JOIN sections sec ON sec.id = rb.section_id
                 INNER JOIN subjects sub ON sub.id = rb.subject_id
                 INNER JOIN academic_sessions s ON s.id = rb.session_id
                 INNER JOIN terms t ON t.id = rb.term_id
                 LEFT JOIN staff st ON st.id = rb.teacher_id
                 WHERE {$whereSql}
                 ORDER BY rb.updated_at DESC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    // ------------------------------------------------------------------
    // Workflow: submit / approve / publish / lock / unlock
    // ------------------------------------------------------------------

    public function submitBatch(int $id, ?array $actor): array
    {
        return $this->transitionBatch($id, 'draft', 'submitted', ['submitted_at' => date('Y-m-d H:i:s')], $actor);
    }

    public function approveBatch(int $id, ?array $actor): array
    {
        $userId = isset($actor['id']) ? (int) $actor['id'] : null;

        return $this->transitionBatch($id, 'submitted', 'approved', ['approved_by' => $userId, 'approved_at' => date('Y-m-d H:i:s')], $actor);
    }

    public function publishBatch(int $id, ?array $actor): array
    {
        $userId = isset($actor['id']) ? (int) $actor['id'] : null;
        $result = $this->transitionBatch($id, 'approved', 'published', ['published_by' => $userId, 'published_at' => date('Y-m-d H:i:s')], $actor);

        if ($result['success']) {
            $batch = $this->db->fetchOne('SELECT * FROM result_batches WHERE id = :id', ['id' => $id]);
            if ($batch) {
                $this->recomputeResultScores((int) $batch['session_id'], (int) $batch['term_id'], (int) $batch['class_id'], $batch['section_id'] ? (int) $batch['section_id'] : null);
            }
        }

        return $result;
    }

    public function lockBatch(int $id, ?array $actor): array
    {
        $userId = isset($actor['id']) ? (int) $actor['id'] : null;
        $batch = $this->db->fetchOne('SELECT * FROM result_batches WHERE id = :id', ['id' => $id]);
        if ($batch === null) {
            return ['success' => false, 'message' => 'Result batch not found.'];
        }
        if (!in_array($batch['status'], ['approved', 'published'], true)) {
            return ['success' => false, 'message' => 'Only approved or published batches can be locked.'];
        }

        return $this->applyBatchUpdate($id, $batch, 'locked', ['locked_by' => $userId, 'locked_at' => date('Y-m-d H:i:s')], $actor, 'result.batch.locked');
    }

    public function unlockBatch(int $id, ?array $actor): array
    {
        $batch = $this->db->fetchOne('SELECT * FROM result_batches WHERE id = :id', ['id' => $id]);
        if ($batch === null) {
            return ['success' => false, 'message' => 'Result batch not found.'];
        }
        if ($batch['status'] !== 'locked') {
            return ['success' => false, 'message' => 'This batch is not locked.'];
        }
        $revertTo = $batch['published_at'] ? 'published' : 'approved';

        return $this->applyBatchUpdate($id, $batch, $revertTo, ['locked_by' => null, 'locked_at' => null], $actor, 'result.batch.unlocked');
    }

    private function transitionBatch(int $id, string $fromStatus, string $toStatus, array $extra, ?array $actor): array
    {
        $batch = $this->db->fetchOne('SELECT * FROM result_batches WHERE id = :id', ['id' => $id]);
        if ($batch === null) {
            return ['success' => false, 'message' => 'Result batch not found.'];
        }
        if ($batch['status'] !== $fromStatus) {
            return ['success' => false, 'message' => "This batch must be {$fromStatus} before it can be moved to {$toStatus} (currently {$batch['status']})."];
        }

        return $this->applyBatchUpdate($id, $batch, $toStatus, $extra, $actor, 'result.batch.' . $toStatus);
    }

    private function applyBatchUpdate(int $id, array $before, string $toStatus, array $extra, ?array $actor, string $action): array
    {
        $extra['status'] = $toStatus;
        $sets = implode(', ', array_map(static fn (string $key): string => "{$key} = :{$key}", array_keys($extra)));

        $this->db->execute("UPDATE result_batches SET {$sets} WHERE id = :id", array_merge($extra, ['id' => $id]));
        $this->db->execute('UPDATE student_results SET status = :status WHERE result_batch_id = :id', ['status' => $toStatus, 'id' => $id]);
        $this->audit($actor, 'result', $action, 'result_batches', $id, ['status' => $before['status']], ['status' => $toStatus]);

        return ['success' => true, 'message' => 'Result batch updated to ' . ucfirst($toStatus) . '.'];
    }

    /** @param array<int,int> $ids @return array{success:bool,message:string} */
    public function bulkBatchAction(array $ids, string $action, ?array $actor): array
    {
        $method = match ($action) {
            'submit' => 'submitBatch', 'approve' => 'approveBatch', 'publish' => 'publishBatch',
            'lock' => 'lockBatch', 'unlock' => 'unlockBatch', default => null,
        };
        if ($method === null) {
            return ['success' => false, 'message' => 'Invalid bulk action.'];
        }

        $count = 0;
        foreach (array_unique(array_filter(array_map('intval', $ids))) as $id) {
            $result = $this->$method($id, $actor);
            if ($result['success']) {
                $count++;
            }
        }

        return ['success' => true, 'message' => "{$count} batch(es) updated."];
    }

    // ------------------------------------------------------------------
    // Result score aggregation (report card summary)
    // ------------------------------------------------------------------

    public function recomputeResultScores(int $sessionId, int $termId, int $classId, ?int $sectionId): void
    {
        $students = $this->rosterForBatch($classId, $sectionId, $sessionId);
        $grades = $this->listGrades();

        $allSubjectRows = $this->db->fetchAll(
            "SELECT sr.student_id, sr.total FROM student_results sr INNER JOIN result_batches rb ON rb.id = sr.result_batch_id
             WHERE rb.session_id = :session_id AND rb.term_id = :term_id AND rb.class_id = :class_id
                AND rb.section_id <=> :section_id AND rb.status IN ('published','locked')",
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId]
        );
        $subjectRowsByStudent = [];
        foreach ($allSubjectRows as $row) {
            $subjectRowsByStudent[(int) $row['student_id']][] = $row;
        }

        $totals = [];
        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $subjectRows = $subjectRowsByStudent[$studentId] ?? [];
            $subjectCount = count($subjectRows);
            $total = array_sum(array_column($subjectRows, 'total'));
            $average = $subjectCount > 0 ? round($total / $subjectCount, 2) : 0;
            $totals[$studentId] = ['total' => $total, 'average' => $average];
        }

        $ranked = $totals;
        uasort($ranked, static fn ($a, $b) => $b['total'] <=> $a['total']);
        $position = 0;
        $lastTotal = null;
        $rank = 0;
        $positions = [];
        foreach ($ranked as $studentId => $entry) {
            $rank++;
            if ($lastTotal === null || $entry['total'] !== $lastTotal) {
                $position = $rank;
                $lastTotal = $entry['total'];
            }
            $positions[$studentId] = $position;
        }

        $studentIds = array_map(static fn (array $s): int => (int) $s['id'], $students);
        $attendanceByStudent = [];
        if ($studentIds) {
            $placeholders = [];
            $attParams = ['session_id' => $sessionId, 'term_id' => $termId];
            foreach ($studentIds as $i => $sid) {
                $key = 'sid' . $i;
                $placeholders[] = ':' . $key;
                $attParams[$key] = $sid;
            }
            $allAttendanceRows = $this->db->fetchAll(
                "SELECT student_id, SUM(status='present') present, SUM(status IN ('absent')) absent
                 FROM student_attendance WHERE session_id = :session_id AND term_id = :term_id AND student_id IN (" . implode(',', $placeholders) . ')
                 GROUP BY student_id',
                $attParams
            );
            foreach ($allAttendanceRows as $row) {
                $attendanceByStudent[(int) $row['student_id']] = $row;
            }
        }

        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $entry = $totals[$studentId];
            $grade = $this->gradeForScore($entry['average'], $grades);

            $attendance = $attendanceByStudent[$studentId] ?? ['present' => 0, 'absent' => 0];

            $this->db->execute(
                'INSERT INTO result_scores (session_id, term_id, student_id, class_id, section_id, total_score, average_score, grade, position_in_class, attendance_present, attendance_absent, status)
                 VALUES (:session_id, :term_id, :student_id, :class_id, :section_id, :total_score, :average_score, :grade, :position, :present, :absent, "published")
                 ON DUPLICATE KEY UPDATE class_id=VALUES(class_id), section_id=VALUES(section_id), total_score=VALUES(total_score), average_score=VALUES(average_score),
                    grade=VALUES(grade), position_in_class=VALUES(position_in_class), attendance_present=VALUES(attendance_present), attendance_absent=VALUES(attendance_absent),
                    status=IF(status="locked","locked","published")',
                [
                    'session_id' => $sessionId, 'term_id' => $termId, 'student_id' => $studentId, 'class_id' => $classId, 'section_id' => $sectionId,
                    'total_score' => $entry['total'], 'average_score' => $entry['average'], 'grade' => $grade['grade'] ?? null,
                    'position' => $positions[$studentId] ?? null, 'present' => (int) ($attendance['present'] ?? 0), 'absent' => (int) ($attendance['absent'] ?? 0),
                ]
            );
        }
    }

    // ------------------------------------------------------------------
    // Broadsheet
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function broadsheet(int $sessionId, int $termId, int $classId, ?int $sectionId): array
    {
        $students = $this->rosterForBatch($classId, $sectionId, $sessionId);
        $subjects = $this->db->fetchAll(
            "SELECT DISTINCT sub.id, sub.name FROM result_batches rb INNER JOIN subjects sub ON sub.id = rb.subject_id
             WHERE rb.session_id = :session_id AND rb.term_id = :term_id AND rb.class_id = :class_id AND rb.section_id <=> :section_id
             ORDER BY sub.name",
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId]
        );

        $scores = $this->db->fetchAll(
            "SELECT sr.student_id, rb.subject_id, sr.total, sr.grade FROM student_results sr
             INNER JOIN result_batches rb ON rb.id = sr.result_batch_id
             WHERE rb.session_id = :session_id AND rb.term_id = :term_id AND rb.class_id = :class_id AND rb.section_id <=> :section_id",
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId]
        );
        $scoreMap = [];
        foreach ($scores as $row) {
            $scoreMap[(int) $row['student_id']][(int) $row['subject_id']] = $row;
        }

        $resultScores = $this->db->fetchAll(
            'SELECT * FROM result_scores WHERE session_id = :session_id AND term_id = :term_id AND class_id = :class_id AND section_id <=> :section_id',
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId]
        );
        $summaryMap = [];
        foreach ($resultScores as $row) {
            $summaryMap[(int) $row['student_id']] = $row;
        }

        $rows = [];
        foreach ($students as $student) {
            $studentId = (int) $student['id'];
            $rows[] = [
                'student' => $student,
                'scores' => $scoreMap[$studentId] ?? [],
                'summary' => $summaryMap[$studentId] ?? null,
            ];
        }

        usort($rows, static function ($a, $b) {
            $posA = $a['summary']['position_in_class'] ?? PHP_INT_MAX;
            $posB = $b['summary']['position_in_class'] ?? PHP_INT_MAX;

            return $posA <=> $posB;
        });

        return ['subjects' => $subjects, 'rows' => $rows];
    }

    // ------------------------------------------------------------------
    // Report card
    // ------------------------------------------------------------------

    /** @return array<string,mixed>|null */
    public function findStudentByQuery(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        return $this->db->fetchOne(
            "SELECT s.* FROM students s WHERE (s.registration_no = :q1 OR s.admission_no = :q2 OR CONCAT(s.first_name,' ',s.last_name) LIKE :q3) AND s.status <> 'deleted' LIMIT 1",
            ['q1' => $query, 'q2' => $query, 'q3' => '%' . $query . '%']
        );
    }

    /** @return array<string,mixed>|null */
    public function reportCard(int $studentId, int $sessionId, int $termId): ?array
    {
        $student = $this->db->fetchOne('SELECT * FROM students WHERE id = :id', ['id' => $studentId]);
        if ($student === null) {
            return null;
        }

        $enrollment = $this->db->fetchOne(
            'SELECT se.*, c.name AS class_name, sec.name AS section_name FROM student_enrollments se
             INNER JOIN classes c ON c.id = se.class_id LEFT JOIN sections sec ON sec.id = se.section_id
             WHERE se.student_id = :sid AND se.session_id = :session_id ORDER BY se.id DESC LIMIT 1',
            ['sid' => $studentId, 'session_id' => $sessionId]
        );

        $subjects = $this->db->fetchAll(
            "SELECT sr.*, sub.name AS subject_name FROM student_results sr
             INNER JOIN result_batches rb ON rb.id = sr.result_batch_id
             INNER JOIN subjects sub ON sub.id = rb.subject_id
             WHERE sr.student_id = :sid AND rb.session_id = :session_id AND rb.term_id = :term_id AND rb.status IN ('published','locked')
             ORDER BY sub.name",
            ['sid' => $studentId, 'session_id' => $sessionId, 'term_id' => $termId]
        );

        $summary = $this->db->fetchOne(
            'SELECT * FROM result_scores WHERE student_id = :sid AND session_id = :session_id AND term_id = :term_id',
            ['sid' => $studentId, 'session_id' => $sessionId, 'term_id' => $termId]
        );

        $classSize = $enrollment ? (int) ($this->db->fetchOne(
            'SELECT COUNT(*) c FROM result_scores WHERE session_id = :session_id AND term_id = :term_id AND class_id = :class_id AND section_id <=> :section_id',
            ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $enrollment['class_id'], 'section_id' => $enrollment['section_id']]
        )['c'] ?? 0) : 0;

        $teacherRemark = trim((string) ($summary['teacher_remark'] ?? '')) !== ''
            ? $summary['teacher_remark']
            : $this->remarkForAverage('teacher', (float) ($summary['average_score'] ?? 0));
        $principalRemark = trim((string) ($summary['principal_remark'] ?? '')) !== ''
            ? $summary['principal_remark']
            : $this->remarkForAverage('principal', (float) ($summary['average_score'] ?? 0));

        $sessionRow = $this->db->fetchOne('SELECT name FROM academic_sessions WHERE id = :id', ['id' => $sessionId]);
        $termRow = $this->db->fetchOne('SELECT name FROM terms WHERE id = :id', ['id' => $termId]);

        return [
            'student' => $student, 'enrollment' => $enrollment, 'subjects' => $subjects, 'summary' => $summary,
            'class_size' => $classSize, 'teacher_remark' => $teacherRemark, 'principal_remark' => $principalRemark,
            'session_name' => $sessionRow['name'] ?? '', 'term_name' => $termRow['name'] ?? '',
        ];
    }

    private function remarkForAverage(string $category, float $average): ?string
    {
        $rows = $this->db->fetchAll('SELECT * FROM remark_settings WHERE category = :cat AND status = "active" ORDER BY min_average DESC', ['cat' => $category]);
        foreach ($rows as $row) {
            $min = $row['min_average'] !== null ? (float) $row['min_average'] : -INF;
            $max = $row['max_average'] !== null ? (float) $row['max_average'] : INF;
            if ($average >= $min && $average <= $max) {
                return $row['remark'];
            }
        }

        return null;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
