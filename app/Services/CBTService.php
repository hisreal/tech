<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;
use App\Models\SettingsModel;
use App\Traits\Auditable;

/**
 * Backing service for CBT Management: exam/question CRUD (admin + teacher
 * scoped), the student attempt lifecycle (start/answer/submit with
 * auto-marking), attempt listings, dashboard/report analytics, and settings.
 */
final class CBTService
{
    use Auditable;

    private const EXAM_STATUSES = ['draft', 'published', 'active', 'completed', 'inactive', 'archived'];
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
    public function subjectsForSelect(?int $classId = null, ?int $teacherId = null): array
    {
        $where = ['s.status = "active"'];
        $params = [];
        $joins = '';

        if ($classId) {
            $joins .= ' INNER JOIN subject_classes sc ON sc.subject_id = s.id';
            $where[] = 'sc.class_id = :cid';
            $params['cid'] = $classId;
        }
        if ($teacherId) {
            $joins .= ' INNER JOIN teacher_subjects ts ON ts.subject_id = s.id';
            $where[] = 'ts.teacher_id = :tid';
            $params['tid'] = $teacherId;
        }

        return $this->db->fetchAll(
            "SELECT DISTINCT s.id, s.name FROM subjects s{$joins} WHERE " . implode(' AND ', $where) . ' ORDER BY s.name ASC',
            $params
        );
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
            'SELECT DISTINCT sec.id, sec.name, sec.class_id FROM sections sec INNER JOIN teacher_classes tc ON tc.section_id = sec.id WHERE ' . implode(' AND ', $where) . ' ORDER BY sec.name ASC',
            $params
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function teachersForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, staff_no, first_name, last_name FROM staff WHERE staff_type = 'teacher' AND employment_status = 'active' ORDER BY last_name ASC, first_name ASC"
        );
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

    // ------------------------------------------------------------------
    // Exam CRUD
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listExams(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE, ?int $restrictToTeacherId = null): array
    {
        $where = ['1=1'];
        $params = [];

        if ($restrictToTeacherId) {
            $where[] = 'ce.teacher_id = :teacher_scope';
            $params['teacher_scope'] = $restrictToTeacherId;
        }
        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'ce.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'ce.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'ce.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($subjectId = $this->intFilter($filters['subject_id'] ?? 0)) !== null) { $where[] = 'ce.subject_id = :subject_id'; $params['subject_id'] = $subjectId; }
        if (($status = trim((string) ($filters['status'] ?? ''))) !== '' && in_array($status, self::EXAM_STATUSES, true)) { $where[] = 'ce.status = :status'; $params['status'] = $status; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(ce.title LIKE :search1 OR st.first_name LIKE :search2 OR st.last_name LIKE :search3)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT ce.*, sub.name AS subject_name, c.name AS class_name, sec.name AS section_name,
                    s.name AS session_name, t.name AS term_name, st.first_name AS teacher_first_name, st.last_name AS teacher_last_name,
                    (SELECT COUNT(*) FROM cbt_questions q WHERE q.exam_id = ce.id) AS question_count,
                    (SELECT COUNT(*) FROM cbt_attempts a WHERE a.exam_id = ce.id AND a.status IN ('submitted','auto_submitted')) AS attempt_count,
                    (SELECT ROUND(AVG(a.percentage), 1) FROM cbt_attempts a WHERE a.exam_id = ce.id AND a.status IN ('submitted','auto_submitted')) AS average_percentage
                 FROM cbt_exams ce
                 INNER JOIN subjects sub ON sub.id = ce.subject_id
                 INNER JOIN classes c ON c.id = ce.class_id
                 LEFT JOIN sections sec ON sec.id = ce.section_id
                 INNER JOIN academic_sessions s ON s.id = ce.session_id
                 INNER JOIN terms t ON t.id = ce.term_id
                 LEFT JOIN staff st ON st.id = ce.teacher_id
                 WHERE {$whereSql}
                 ORDER BY ce.updated_at DESC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @return array<string,mixed>|null */
    public function findExam(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT ce.*, sub.name AS subject_name, c.name AS class_name, sec.name AS section_name,
                    s.name AS session_name, t.name AS term_name, st.first_name AS teacher_first_name, st.last_name AS teacher_last_name,
                    (SELECT COUNT(*) FROM cbt_questions q WHERE q.exam_id = ce.id) AS question_count
             FROM cbt_exams ce
             INNER JOIN subjects sub ON sub.id = ce.subject_id
             INNER JOIN classes c ON c.id = ce.class_id
             LEFT JOIN sections sec ON sec.id = ce.section_id
             INNER JOIN academic_sessions s ON s.id = ce.session_id
             INNER JOIN terms t ON t.id = ce.term_id
             LEFT JOIN staff st ON st.id = ce.teacher_id
             WHERE ce.id = :id",
            ['id' => $id]
        );
    }

    public function saveExam(array $data, ?int $id, ?array $actor, ?int $restrictToTeacherId = null): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $sessionId = (int) ($data['session_id'] ?? 0);
        $termId = (int) ($data['term_id'] ?? 0);
        $subjectId = (int) ($data['subject_id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? 0);
        $sectionId = (int) ($data['section_id'] ?? 0) ?: null;
        $duration = (int) ($data['duration_minutes'] ?? 0);
        $passMark = $data['pass_mark'] !== '' && $data['pass_mark'] !== null ? (float) $data['pass_mark'] : 40.0;
        $maxAttempts = (int) ($data['maximum_attempts'] ?? 1) ?: 1;
        $description = trim((string) ($data['description'] ?? ''));
        $instructions = trim((string) ($data['instructions'] ?? ''));

        $errors = [];
        if ($title === '') { $errors['title'] = 'Exam title is required.'; }
        if ($sessionId < 1) { $errors['session_id'] = 'Session is required.'; }
        if ($termId < 1) { $errors['term_id'] = 'Term is required.'; }
        if ($subjectId < 1) { $errors['subject_id'] = 'Subject is required.'; }
        if ($classId < 1) { $errors['class_id'] = 'Class is required.'; }
        if ($duration < 1) { $errors['duration_minutes'] = 'Duration must be at least 1 minute.'; }
        if ($passMark < 0 || $passMark > 100) { $errors['pass_mark'] = 'Pass mark must be between 0 and 100.'; }

        if ($restrictToTeacherId) {
            $ownsSubject = $this->db->fetchOne('SELECT 1 FROM teacher_subjects WHERE teacher_id = :tid AND subject_id = :sid', ['tid' => $restrictToTeacherId, 'sid' => $subjectId]);
            if ($subjectId > 0 && !$ownsSubject) { $errors['subject_id'] = 'You are not assigned to this subject.'; }
            $ownsClass = $this->db->fetchOne('SELECT 1 FROM teacher_classes WHERE teacher_id = :tid AND class_id = :cid', ['tid' => $restrictToTeacherId, 'cid' => $classId]);
            if ($classId > 0 && !$ownsClass) { $errors['class_id'] = 'You are not assigned to this class.'; }
        }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = [
            'session_id' => $sessionId, 'term_id' => $termId, 'subject_id' => $subjectId, 'class_id' => $classId, 'section_id' => $sectionId,
            'title' => $title, 'description' => $description ?: null, 'instructions' => $instructions ?: null,
            'duration_minutes' => $duration, 'pass_mark' => $passMark, 'maximum_attempts' => $maxAttempts,
        ];

        if ($id) {
            $before = $this->db->fetchOne('SELECT * FROM cbt_exams WHERE id = :id', ['id' => $id]);
            if ($before === null || ($restrictToTeacherId && (int) $before['teacher_id'] !== $restrictToTeacherId)) {
                return ['success' => false, 'message' => 'Exam not found.'];
            }
            if (in_array($before['status'], ['active', 'completed'], true)) {
                return ['success' => false, 'message' => 'Active or completed exams cannot be edited. Deactivate it first.'];
            }
            $sets = implode(', ', array_map(static fn (string $k): string => "{$k} = :{$k}", array_keys($payload)));
            $this->db->execute("UPDATE cbt_exams SET {$sets} WHERE id = :id", array_merge($payload, ['id' => $id]));
            $this->audit($actor, 'cbt', 'cbt.exam.updated', 'cbt_exams', $id, $before, $payload);

            return ['success' => true, 'message' => 'Exam updated successfully.'];
        }

        $payload['teacher_id'] = $restrictToTeacherId ?: ((int) ($data['teacher_id'] ?? 0) ?: null);
        $payload['created_by'] = isset($actor['id']) ? (int) $actor['id'] : null;
        $payload['status'] = 'draft';

        $columns = implode(', ', array_keys($payload));
        $placeholders = implode(', ', array_map(static fn (string $k): string => ":{$k}", array_keys($payload)));
        $this->db->execute("INSERT INTO cbt_exams ({$columns}) VALUES ({$placeholders})", $payload);
        $newId = (int) $this->db->lastInsertId();
        $this->audit($actor, 'cbt', 'cbt.exam.created', 'cbt_exams', $newId, null, $payload);

        return ['success' => true, 'message' => 'Exam created successfully.', 'id' => $newId];
    }

    public function setExamStatus(int $id, string $status, ?array $actor, ?int $restrictToTeacherId = null): array
    {
        if (!in_array($status, self::EXAM_STATUSES, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }
        $before = $this->db->fetchOne('SELECT * FROM cbt_exams WHERE id = :id', ['id' => $id]);
        if ($before === null || ($restrictToTeacherId && (int) $before['teacher_id'] !== $restrictToTeacherId)) {
            return ['success' => false, 'message' => 'Exam not found.'];
        }
        if ($status === 'published') {
            $questionCount = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM cbt_questions WHERE exam_id = :id', ['id' => $id])['c'] ?? 0);
            if ($questionCount === 0) {
                return ['success' => false, 'message' => 'Add at least one question before publishing this exam.'];
            }
        }

        $extra = ['status' => $status];
        if ($status === 'published' && $before['published_at'] === null) {
            $extra['published_at'] = date('Y-m-d H:i:s');
        }
        $sets = implode(', ', array_map(static fn (string $k): string => "{$k} = :{$k}", array_keys($extra)));
        $this->db->execute("UPDATE cbt_exams SET {$sets} WHERE id = :id", array_merge($extra, ['id' => $id]));
        $this->audit($actor, 'cbt', 'cbt.exam.' . $status, 'cbt_exams', $id, ['status' => $before['status']], ['status' => $status]);

        return ['success' => true, 'message' => 'Exam status updated to ' . ucfirst($status) . '.'];
    }

    public function deleteExam(int $id, ?array $actor, ?int $restrictToTeacherId = null): array
    {
        $before = $this->db->fetchOne('SELECT * FROM cbt_exams WHERE id = :id', ['id' => $id]);
        if ($before === null || ($restrictToTeacherId && (int) $before['teacher_id'] !== $restrictToTeacherId)) {
            return ['success' => false, 'message' => 'Exam not found.'];
        }
        $attemptCount = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM cbt_attempts WHERE exam_id = :id', ['id' => $id])['c'] ?? 0);
        if ($attemptCount > 0) {
            return ['success' => false, 'message' => 'This exam has student attempts and cannot be deleted. Archive it instead.'];
        }
        $this->db->execute('DELETE FROM cbt_exams WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'cbt', 'cbt.exam.deleted', 'cbt_exams', $id, $before, null);

        return ['success' => true, 'message' => 'Exam deleted successfully.'];
    }

    // ------------------------------------------------------------------
    // Question CRUD
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function listQuestions(int $examId): array
    {
        return $this->db->fetchAll('SELECT * FROM cbt_questions WHERE exam_id = :id ORDER BY sort_order ASC, id ASC', ['id' => $examId]);
    }

    public function saveQuestion(array $data, ?int $id, int $examId, ?array $actor, ?int $restrictToTeacherId = null): array
    {
        $exam = $this->db->fetchOne('SELECT * FROM cbt_exams WHERE id = :id', ['id' => $examId]);
        if ($exam === null || ($restrictToTeacherId && (int) $exam['teacher_id'] !== $restrictToTeacherId)) {
            return ['success' => false, 'message' => 'Exam not found.'];
        }
        if (in_array($exam['status'], ['active', 'completed'], true)) {
            return ['success' => false, 'message' => 'Questions cannot be changed while the exam is active or completed.'];
        }

        $text = trim((string) ($data['question_text'] ?? ''));
        $optionA = trim((string) ($data['option_a'] ?? ''));
        $optionB = trim((string) ($data['option_b'] ?? ''));
        $optionC = trim((string) ($data['option_c'] ?? ''));
        $optionD = trim((string) ($data['option_d'] ?? ''));
        $correct = strtoupper(trim((string) ($data['correct_option'] ?? '')));
        $mark = $data['mark'] !== '' && $data['mark'] !== null ? (float) $data['mark'] : 1.0;

        $errors = [];
        if ($text === '') { $errors['question_text'] = 'Question text is required.'; }
        if ($optionA === '' || $optionB === '' || $optionC === '' || $optionD === '') { $errors['options'] = 'All four options are required.'; }
        if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) { $errors['correct_option'] = 'Select the correct option.'; }
        if ($mark <= 0) { $errors['mark'] = 'Mark must be greater than zero.'; }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $payload = [
            'exam_id' => $examId, 'question_text' => $text, 'option_a' => $optionA, 'option_b' => $optionB,
            'option_c' => $optionC, 'option_d' => $optionD, 'correct_option' => $correct, 'mark' => $mark,
        ];

        if ($id) {
            $before = $this->db->fetchOne('SELECT * FROM cbt_questions WHERE id = :id AND exam_id = :exam_id', ['id' => $id, 'exam_id' => $examId]);
            if ($before === null) {
                return ['success' => false, 'message' => 'Question not found.'];
            }
            $sets = implode(', ', array_map(static fn (string $k): string => "{$k} = :{$k}", array_keys($payload)));
            $this->db->execute("UPDATE cbt_questions SET {$sets} WHERE id = :id", array_merge($payload, ['id' => $id]));
            $this->audit($actor, 'cbt', 'cbt.question.updated', 'cbt_questions', $id, $before, $payload);
        } else {
            $nextOrder = (int) ($this->db->fetchOne('SELECT COALESCE(MAX(sort_order), 0) + 1 AS n FROM cbt_questions WHERE exam_id = :id', ['id' => $examId])['n'] ?? 1);
            $payload['sort_order'] = $nextOrder;
            $columns = implode(', ', array_keys($payload));
            $placeholders = implode(', ', array_map(static fn (string $k): string => ":{$k}", array_keys($payload)));
            $this->db->execute("INSERT INTO cbt_questions ({$columns}) VALUES ({$placeholders})", $payload);
            $newId = (int) $this->db->lastInsertId();
            $this->audit($actor, 'cbt', 'cbt.question.created', 'cbt_questions', $newId, null, $payload);
        }

        $this->syncQuestionCount($examId);

        return ['success' => true, 'message' => $id ? 'Question updated successfully.' : 'Question added successfully.'];
    }

    public function deleteQuestion(int $id, ?array $actor, ?int $restrictToTeacherId = null): array
    {
        $before = $this->db->fetchOne(
            'SELECT q.*, e.teacher_id, e.status AS exam_status FROM cbt_questions q INNER JOIN cbt_exams e ON e.id = q.exam_id WHERE q.id = :id',
            ['id' => $id]
        );
        if ($before === null || ($restrictToTeacherId && (int) $before['teacher_id'] !== $restrictToTeacherId)) {
            return ['success' => false, 'message' => 'Question not found.'];
        }
        if (in_array($before['exam_status'], ['active', 'completed'], true)) {
            return ['success' => false, 'message' => 'Questions cannot be changed while the exam is active or completed.'];
        }
        $this->db->execute('DELETE FROM cbt_questions WHERE id = :id', ['id' => $id]);
        $this->audit($actor, 'cbt', 'cbt.question.deleted', 'cbt_questions', $id, $before, null);
        $this->syncQuestionCount((int) $before['exam_id']);

        return ['success' => true, 'message' => 'Question deleted successfully.'];
    }

    private function syncQuestionCount(int $examId): void
    {
        $this->db->execute(
            'UPDATE cbt_exams SET number_of_questions = (SELECT COUNT(*) FROM cbt_questions WHERE exam_id = :id) WHERE id = :id2',
            ['id' => $examId, 'id2' => $examId]
        );
    }

    // ------------------------------------------------------------------
    // Student attempt lifecycle
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function availableExamsForStudent(int $studentId): array
    {
        $enrollment = $this->db->fetchOne(
            'SELECT * FROM student_enrollments WHERE student_id = :sid AND status = "active" ORDER BY id DESC LIMIT 1',
            ['sid' => $studentId]
        );
        if ($enrollment === null) {
            return [];
        }

        $exams = $this->db->fetchAll(
            "SELECT ce.*, sub.name AS subject_name, c.name AS class_name,
                    (SELECT COUNT(*) FROM cbt_questions q WHERE q.exam_id = ce.id) AS question_count,
                    (SELECT COUNT(*) FROM cbt_attempts a WHERE a.exam_id = ce.id AND a.student_id = :sid1 AND a.status IN ('submitted','auto_submitted')) AS attempts_used,
                    (SELECT id FROM cbt_attempts a WHERE a.exam_id = ce.id AND a.student_id = :sid2 AND a.status = 'in_progress' LIMIT 1) AS in_progress_attempt_id
             FROM cbt_exams ce
             INNER JOIN subjects sub ON sub.id = ce.subject_id
             INNER JOIN classes c ON c.id = ce.class_id
             WHERE ce.status = 'active' AND ce.session_id = :session_id AND ce.term_id = :term_id AND ce.class_id = :class_id
                AND (ce.section_id IS NULL OR ce.section_id = :section_id)
             ORDER BY ce.created_at DESC",
            [
                'sid1' => $studentId, 'sid2' => $studentId, 'session_id' => $enrollment['session_id'], 'term_id' => $this->currentTermId() ?: 0,
                'class_id' => $enrollment['class_id'], 'section_id' => $enrollment['section_id'],
            ]
        );

        return $exams;
    }

    /** @return array<string,mixed> Completed count, best score, and average duration for this student's own attempts. */
    public function studentAttemptStats(int $studentId): array
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) completed, COALESCE(MAX(percentage), 0) best FROM cbt_attempts WHERE student_id = :sid AND status IN ('submitted','auto_submitted')",
            ['sid' => $studentId]
        );

        return ['completed' => (int) ($row['completed'] ?? 0), 'best_score' => round((float) ($row['best'] ?? 0), 1)];
    }

    /** @return array<string,mixed> */
    public function findOrStartAttempt(int $examId, int $studentId): array
    {
        $exam = $this->db->fetchOne('SELECT * FROM cbt_exams WHERE id = :id', ['id' => $examId]);
        if ($exam === null || $exam['status'] !== 'active') {
            return ['success' => false, 'message' => 'This exam is not currently available.'];
        }

        $inProgress = $this->db->fetchOne(
            "SELECT * FROM cbt_attempts WHERE exam_id = :eid AND student_id = :sid AND status = 'in_progress'",
            ['eid' => $examId, 'sid' => $studentId]
        );
        if ($inProgress) {
            return ['success' => true, 'attempt' => $inProgress, 'exam' => $exam];
        }

        $usedAttempts = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM cbt_attempts WHERE exam_id = :eid AND student_id = :sid AND status IN ('submitted','auto_submitted')",
            ['eid' => $examId, 'sid' => $studentId]
        )['c'] ?? 0);
        if ($usedAttempts >= (int) $exam['maximum_attempts']) {
            return ['success' => false, 'reason' => 'max_attempts', 'message' => 'You have used all of your attempts for this exam.'];
        }

        $questionCount = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM cbt_questions WHERE exam_id = :id', ['id' => $examId])['c'] ?? 0);
        if ($questionCount === 0) {
            return ['success' => false, 'message' => 'This exam has no questions yet.'];
        }

        $this->db->execute(
            "INSERT INTO cbt_attempts (exam_id, student_id, started_at, status, ip_address, user_agent) VALUES (:eid, :sid, :started, 'in_progress', :ip, :ua)",
            [
                'eid' => $examId, 'sid' => $studentId, 'started' => date('Y-m-d H:i:s'),
                'ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45), 'ua' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
        $attemptId = (int) $this->db->lastInsertId();
        $attempt = $this->db->fetchOne('SELECT * FROM cbt_attempts WHERE id = :id', ['id' => $attemptId]);

        return ['success' => true, 'attempt' => $attempt, 'exam' => $exam];
    }

    /** @return array<string,mixed>|null Most recent attempt (any status) for this exam/student pair. */
    public function latestAttempt(int $examId, int $studentId): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM cbt_attempts WHERE exam_id = :eid AND student_id = :sid ORDER BY id DESC LIMIT 1',
            ['eid' => $examId, 'sid' => $studentId]
        );
    }

    /** @return array<string,mixed>|null */
    public function attemptWithExam(int $attemptId): ?array
    {
        return $this->db->fetchOne(
            'SELECT a.*, e.title, e.duration_minutes, e.pass_mark, e.randomize_questions, e.randomize_answers, e.show_result_immediately, e.allow_review, e.status AS exam_status
             FROM cbt_attempts a INNER JOIN cbt_exams e ON e.id = a.exam_id WHERE a.id = :id',
            ['id' => $attemptId]
        );
    }

    public function attemptDeadline(array $attempt): int
    {
        return strtotime($attempt['started_at']) + ((int) $attempt['duration_minutes'] * 60);
    }

    public function isAttemptExpired(array $attempt): bool
    {
        return $attempt['status'] === 'in_progress' && time() > $this->attemptDeadline($attempt);
    }

    /** @return array<int,array<string,mixed>> Questions in display order with answer options shuffled per-attempt when enabled. */
    public function questionsForAttempt(int $attemptId, array $attempt): array
    {
        $questions = $this->db->fetchAll('SELECT * FROM cbt_questions WHERE exam_id = :id ORDER BY sort_order ASC, id ASC', ['id' => $attempt['exam_id']]);
        $answers = $this->db->fetchAll('SELECT question_id, selected_option FROM cbt_attempt_answers WHERE attempt_id = :id', ['id' => $attemptId]);
        $answerMap = array_column($answers, 'selected_option', 'question_id');

        if ($attempt['randomize_questions']) {
            $questions = $this->stableShuffle($questions, $attemptId);
        }

        foreach ($questions as &$question) {
            $options = [
                ['letter' => 'A', 'text' => $question['option_a']], ['letter' => 'B', 'text' => $question['option_b']],
                ['letter' => 'C', 'text' => $question['option_c']], ['letter' => 'D', 'text' => $question['option_d']],
            ];
            if ($attempt['randomize_answers']) {
                $options = $this->stableShuffle($options, $attemptId + (int) $question['id']);
            }
            $question['display_options'] = $options;
            $question['selected_option'] = $answerMap[$question['id']] ?? null;
        }

        return $questions;
    }

    /** @param array<int,mixed> $items @return array<int,mixed> */
    private function stableShuffle(array $items, int $seed): array
    {
        mt_srand($seed);
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return array_values($items);
    }

    public function saveAnswer(int $attemptId, int $questionId, ?string $selectedOption): array
    {
        $attempt = $this->attemptWithExam($attemptId);
        if ($attempt === null || $attempt['status'] !== 'in_progress') {
            return ['success' => false, 'message' => 'This attempt is no longer active.'];
        }
        if ($this->isAttemptExpired($attempt)) {
            $this->submitAttempt($attemptId, true);

            return ['success' => false, 'message' => 'Time is up. Your exam was submitted automatically.'];
        }

        $selectedOption = $selectedOption !== null && in_array(strtoupper($selectedOption), ['A', 'B', 'C', 'D'], true) ? strtoupper($selectedOption) : null;

        $this->db->execute(
            'INSERT INTO cbt_attempt_answers (attempt_id, question_id, selected_option, answered_at) VALUES (:aid, :qid, :opt, :now)
             ON DUPLICATE KEY UPDATE selected_option = VALUES(selected_option), answered_at = VALUES(answered_at)',
            ['aid' => $attemptId, 'qid' => $questionId, 'opt' => $selectedOption, 'now' => date('Y-m-d H:i:s')]
        );

        return ['success' => true, 'message' => 'Answer saved.'];
    }

    /** Auto-marks the attempt: compares each answer to its question's correct_option and computes score/percentage/grade. */
    public function submitAttempt(int $attemptId, bool $isTimeout = false): array
    {
        $attempt = $this->db->fetchOne('SELECT * FROM cbt_attempts WHERE id = :id', ['id' => $attemptId]);
        if ($attempt === null) {
            return ['success' => false, 'message' => 'Attempt not found.'];
        }
        if ($attempt['status'] !== 'in_progress') {
            return ['success' => true, 'message' => 'This attempt was already submitted.'];
        }

        $questions = $this->db->fetchAll('SELECT id, correct_option, mark FROM cbt_questions WHERE exam_id = :id', ['id' => $attempt['exam_id']]);
        $answers = $this->db->fetchAll('SELECT * FROM cbt_attempt_answers WHERE attempt_id = :id', ['id' => $attemptId]);
        $answerMap = array_column($answers, 'selected_option', 'question_id');

        $totalPossible = 0.0;
        $score = 0.0;

        $this->db->beginTransaction();
        try {
            foreach ($questions as $question) {
                $totalPossible += (float) $question['mark'];
                $selected = $answerMap[$question['id']] ?? null;
                $isCorrect = $selected !== null && $selected === $question['correct_option'];
                $awarded = $isCorrect ? (float) $question['mark'] : 0.0;
                $score += $awarded;

                $this->db->execute(
                    'INSERT INTO cbt_attempt_answers (attempt_id, question_id, selected_option, is_correct, mark_awarded, answered_at)
                     VALUES (:aid, :qid, :opt, :correct, :awarded, :now)
                     ON DUPLICATE KEY UPDATE is_correct = VALUES(is_correct), mark_awarded = VALUES(mark_awarded)',
                    ['aid' => $attemptId, 'qid' => $question['id'], 'opt' => $selected, 'correct' => $isCorrect ? 1 : 0, 'awarded' => $awarded, 'now' => date('Y-m-d H:i:s')]
                );
            }

            $percentage = $totalPossible > 0 ? round(($score / $totalPossible) * 100, 2) : 0.0;
            $grade = $this->gradeForPercentage($percentage);

            $this->db->execute(
                'UPDATE cbt_attempts SET ended_at = :ended, score = :score, percentage = :pct, grade = :grade, status = :status WHERE id = :id',
                [
                    'ended' => date('Y-m-d H:i:s'), 'score' => $score, 'pct' => $percentage, 'grade' => $grade,
                    'status' => $isTimeout ? 'auto_submitted' : 'submitted', 'id' => $attemptId,
                ]
            );

            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);

            return ['success' => false, 'message' => 'Unable to submit this attempt right now.'];
        }

        return ['success' => true, 'message' => 'Exam submitted successfully.', 'score' => $score, 'percentage' => $percentage, 'grade' => $grade];
    }

    private function gradeForPercentage(float $percentage): ?string
    {
        $grades = $this->db->fetchAll('SELECT * FROM grade_settings WHERE status = "active" ORDER BY min_score DESC');
        foreach ($grades as $grade) {
            if ($percentage >= (float) $grade['min_score'] && $percentage <= (float) $grade['max_score']) {
                return $grade['grade'];
            }
        }

        return null;
    }

    /** @return array<string,mixed>|null Full attempt detail with per-question review data. */
    public function attemptResult(int $attemptId): ?array
    {
        $attempt = $this->db->fetchOne(
            "SELECT a.*, e.title AS exam_title, e.pass_mark, e.allow_review, sub.name AS subject_name,
                    st.first_name, st.last_name, st.registration_no
             FROM cbt_attempts a
             INNER JOIN cbt_exams e ON e.id = a.exam_id
             INNER JOIN subjects sub ON sub.id = e.subject_id
             INNER JOIN students st ON st.id = a.student_id
             WHERE a.id = :id",
            ['id' => $attemptId]
        );
        if ($attempt === null) {
            return null;
        }

        $review = $this->db->fetchAll(
            'SELECT q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.mark,
                    aa.selected_option, aa.is_correct, aa.mark_awarded
             FROM cbt_questions q
             LEFT JOIN cbt_attempt_answers aa ON aa.question_id = q.id AND aa.attempt_id = :id
             WHERE q.exam_id = :exam_id ORDER BY q.sort_order ASC, q.id ASC',
            ['id' => $attemptId, 'exam_id' => $attempt['exam_id']]
        );

        $attempt['review'] = $review;

        return $attempt;
    }

    // ------------------------------------------------------------------
    // Attempts listing (admin/teacher)
    // ------------------------------------------------------------------

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function listAttempts(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE, ?int $restrictToTeacherId = null): array
    {
        $where = ["a.status IN ('submitted','auto_submitted')"];
        $params = [];

        if ($restrictToTeacherId) {
            $where[] = 'e.teacher_id = :teacher_scope';
            $params['teacher_scope'] = $restrictToTeacherId;
        }
        if (($sessionId = $this->intFilter($filters['session_id'] ?? 0)) !== null) { $where[] = 'e.session_id = :session_id'; $params['session_id'] = $sessionId; }
        if (($termId = $this->intFilter($filters['term_id'] ?? 0)) !== null) { $where[] = 'e.term_id = :term_id'; $params['term_id'] = $termId; }
        if (($classId = $this->intFilter($filters['class_id'] ?? 0)) !== null) { $where[] = 'e.class_id = :class_id'; $params['class_id'] = $classId; }
        if (($subjectId = $this->intFilter($filters['subject_id'] ?? 0)) !== null) { $where[] = 'e.subject_id = :subject_id'; $params['subject_id'] = $subjectId; }
        if (($examId = $this->intFilter($filters['exam_id'] ?? 0)) !== null) { $where[] = 'a.exam_id = :exam_id'; $params['exam_id'] = $examId; }
        if (($status = trim((string) ($filters['status'] ?? ''))) === 'passed') { $where[] = 'a.percentage >= e.pass_mark'; }
        if ($status === 'failed') { $where[] = 'a.percentage < e.pass_mark'; }
        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = "(st.first_name LIKE :search1 OR st.last_name LIKE :search2 OR st.registration_no LIKE :search3 OR e.title LIKE :search4)";
            $like = '%' . $search . '%';
            $params['search1'] = $like; $params['search2'] = $like; $params['search3'] = $like; $params['search4'] = $like;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT a.*, e.title AS exam_title, e.pass_mark, sub.name AS subject_name, c.name AS class_name,
                    s.name AS session_name, t.name AS term_name, st.first_name, st.last_name, st.registration_no
                 FROM cbt_attempts a
                 INNER JOIN cbt_exams e ON e.id = a.exam_id
                 INNER JOIN subjects sub ON sub.id = e.subject_id
                 INNER JOIN classes c ON c.id = e.class_id
                 INNER JOIN academic_sessions s ON s.id = e.session_id
                 INNER JOIN terms t ON t.id = e.term_id
                 INNER JOIN students st ON st.id = a.student_id
                 WHERE {$whereSql}
                 ORDER BY a.ended_at DESC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    // ------------------------------------------------------------------
    // Dashboard / reports
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function dashboardStats(?int $restrictToTeacherId = null): array
    {
        $where = $restrictToTeacherId ? 'WHERE teacher_id = :tid' : '';
        $params = $restrictToTeacherId ? ['tid' => $restrictToTeacherId] : [];

        $exams = $this->db->fetchAll("SELECT status FROM cbt_exams {$where}", $params);
        $counts = array_count_values(array_column($exams, 'status'));

        $attemptWhere = ["a.status IN ('submitted','auto_submitted')"];
        if ($restrictToTeacherId) {
            $attemptWhere[] = 'e.teacher_id = :tid';
        }
        $totals = $this->db->fetchOne(
            'SELECT COUNT(*) attempts, COALESCE(AVG(a.percentage), 0) avg_pct,
                SUM(CASE WHEN a.percentage >= e.pass_mark THEN 1 ELSE 0 END) passed
             FROM cbt_attempts a INNER JOIN cbt_exams e ON e.id = a.exam_id
             WHERE ' . implode(' AND ', $attemptWhere),
            $params
        ) ?? ['attempts' => 0, 'avg_pct' => 0, 'passed' => 0];

        $totalQuestions = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM cbt_questions q INNER JOIN cbt_exams e ON e.id = q.exam_id" . ($restrictToTeacherId ? ' WHERE e.teacher_id = :tid' : ''),
            $params
        )['c'] ?? 0);

        return [
            'total_exams' => count($exams),
            'active_exams' => $counts['active'] ?? 0,
            'completed_exams' => $counts['completed'] ?? 0,
            'draft_exams' => $counts['draft'] ?? 0,
            'published_exams' => $counts['published'] ?? 0,
            'archived_exams' => $counts['archived'] ?? 0,
            'total_questions' => $totalQuestions,
            'total_attempts' => (int) $totals['attempts'],
            'average_score' => round((float) $totals['avg_pct'], 1),
            'pass_rate' => $totals['attempts'] > 0 ? round(((int) $totals['passed'] / (int) $totals['attempts']) * 100, 1) : 0.0,
        ];
    }

    /** @return array<int,array<string,mixed>> Recent CBT-related audit activity. */
    public function recentActivity(int $limit = 8): array
    {
        return $this->db->fetchAll(
            'SELECT al.*, u.username AS actor_username FROM audit_logs al
             LEFT JOIN users u ON u.id = al.actor_user_id
             WHERE al.module = "cbt" ORDER BY al.id DESC LIMIT :lim',
            ['lim' => $limit]
        );
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> */
    public function reportsSummary(array $filters = [], ?int $restrictToTeacherId = null): array
    {
        $where = ["a.status IN ('submitted','auto_submitted')"];
        $params = [];
        if ($restrictToTeacherId) { $where[] = 'e.teacher_id = :tid'; $params['tid'] = $restrictToTeacherId; }

        $whereSql = implode(' AND ', $where);

        $scores = $this->db->fetchOne(
            "SELECT COALESCE(MAX(a.percentage),0) highest, COALESCE(MIN(a.percentage),0) lowest, COALESCE(AVG(a.percentage),0) average,
                SUM(CASE WHEN a.percentage >= e.pass_mark THEN 1 ELSE 0 END) passed, COUNT(*) total
             FROM cbt_attempts a INNER JOIN cbt_exams e ON e.id = a.exam_id WHERE {$whereSql}",
            $params
        );

        $bestClass = $this->db->fetchOne(
            "SELECT c.name, AVG(a.percentage) avg_pct FROM cbt_attempts a
             INNER JOIN cbt_exams e ON e.id = a.exam_id INNER JOIN classes c ON c.id = e.class_id
             WHERE {$whereSql} GROUP BY c.id ORDER BY avg_pct DESC LIMIT 1",
            $params
        );

        return [
            'highest' => round((float) $scores['highest'], 1),
            'lowest' => round((float) $scores['lowest'], 1),
            'average' => round((float) $scores['average'], 1),
            'pass_rate' => $scores['total'] > 0 ? round(((int) $scores['passed'] / (int) $scores['total']) * 100, 1) : 0.0,
            'best_class' => $bestClass['name'] ?? '-',
            'total_attempts' => (int) $scores['total'],
        ];
    }

    /** @return array<int,array<string,mixed>> Attempt counts per class for a simple participation chart. */
    public function participationByClass(?int $restrictToTeacherId = null): array
    {
        $where = ["a.status IN ('submitted','auto_submitted')"];
        $params = [];
        if ($restrictToTeacherId) { $where[] = 'e.teacher_id = :tid'; $params['tid'] = $restrictToTeacherId; }

        return $this->db->fetchAll(
            'SELECT c.name, COUNT(*) attempts FROM cbt_attempts a
             INNER JOIN cbt_exams e ON e.id = a.exam_id INNER JOIN classes c ON c.id = e.class_id
             WHERE ' . implode(' AND ', $where) . ' GROUP BY c.id ORDER BY c.name ASC',
            $params
        );
    }

    /** @return array<int,array<string,mixed>> Exams created per month for the given year. */
    public function monthlyExamsChart(int $year, ?int $restrictToTeacherId = null): array
    {
        $where = ['YEAR(created_at) = :year'];
        $params = ['year' => $year];
        if ($restrictToTeacherId) { $where[] = 'teacher_id = :tid'; $params['tid'] = $restrictToTeacherId; }

        $rows = $this->db->fetchAll(
            'SELECT MONTH(created_at) AS m, COUNT(*) AS c FROM cbt_exams WHERE ' . implode(' AND ', $where) . ' GROUP BY MONTH(created_at)',
            $params
        );
        $byMonth = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $byMonth[(int) $row['m']] = (int) $row['c'];
        }

        return $byMonth;
    }

    /** @return array{best:?array<string,mixed>,lowest:?array<string,mixed>} Best/lowest performing subjects by average percentage. */
    public function subjectPerformance(?int $restrictToTeacherId = null): array
    {
        $where = ["a.status IN ('submitted','auto_submitted')"];
        $params = [];
        if ($restrictToTeacherId) { $where[] = 'e.teacher_id = :tid'; $params['tid'] = $restrictToTeacherId; }

        $rows = $this->db->fetchAll(
            'SELECT sub.name, AVG(a.percentage) avg_pct FROM cbt_attempts a
             INNER JOIN cbt_exams e ON e.id = a.exam_id INNER JOIN subjects sub ON sub.id = e.subject_id
             WHERE ' . implode(' AND ', $where) . ' GROUP BY sub.id ORDER BY avg_pct DESC',
            $params
        );

        return ['best' => $rows[0] ?? null, 'lowest' => $rows ? end($rows) : null];
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    /** @return array<string,mixed> */
    public function generalSettings(): array
    {
        $all = $this->settings->all();
        $get = static fn (string $key, mixed $default) => $all[$key]['value'] ?? $default;

        return [
            'pass_mark' => (int) $get('cbt.default_pass_mark', 50),
            'default_duration' => (int) $get('cbt.default_duration_minutes', 30),
            'maximum_attempts' => (int) $get('cbt.maximum_attempts', 1),
            'randomize_questions' => (bool) $get('cbt.randomize_questions', true),
            'randomize_answers' => (bool) $get('cbt.randomize_answers', true),
            'auto_submit' => (bool) $get('cbt.auto_submit', true),
            'show_result_immediately' => (bool) $get('cbt.show_results_immediately', true),
            'allow_review' => (bool) $get('cbt.allow_review_after_exam', true),
            'fullscreen_mode' => (bool) $get('cbt.fullscreen_mode', true),
            'prevent_multiple_login' => (bool) $get('cbt.prevent_multiple_login', true),
            'auto_logout' => (bool) $get('cbt.auto_logout', true),
            'browser_restrictions' => (bool) $get('cbt.browser_restrictions', false),
        ];
    }

    public function saveGeneralSettings(array $data, ?array $actor): array
    {
        $before = $this->generalSettings();
        $new = [
            'cbt.default_pass_mark' => ['value' => (int) ($data['pass_mark'] ?? 50), 'type' => 'number', 'group' => 'cbt'],
            'cbt.default_duration_minutes' => ['value' => (int) ($data['default_duration'] ?? 30), 'type' => 'number', 'group' => 'cbt'],
            'cbt.maximum_attempts' => ['value' => (int) ($data['maximum_attempts'] ?? 1), 'type' => 'number', 'group' => 'cbt'],
            'cbt.randomize_questions' => ['value' => !empty($data['randomize_questions']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.randomize_answers' => ['value' => !empty($data['randomize_answers']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.auto_submit' => ['value' => !empty($data['auto_submit']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.show_results_immediately' => ['value' => !empty($data['show_result_immediately']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.allow_review_after_exam' => ['value' => !empty($data['allow_review']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.fullscreen_mode' => ['value' => !empty($data['fullscreen_mode']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.prevent_multiple_login' => ['value' => !empty($data['prevent_multiple_login']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.auto_logout' => ['value' => !empty($data['auto_logout']), 'type' => 'boolean', 'group' => 'cbt'],
            'cbt.browser_restrictions' => ['value' => !empty($data['browser_restrictions']), 'type' => 'boolean', 'group' => 'cbt'],
        ];
        $this->settings->upsertMany($new, isset($actor['id']) ? (int) $actor['id'] : null);
        $this->settings->audit($actor, 'cbt', $before, $this->generalSettings());

        return ['success' => true, 'message' => 'CBT settings saved successfully.'];
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
