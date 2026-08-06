<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\CBTService;

$cbtService = new CBTService();
$filters = [
    'session_id' => (string) ($_GET['session_id'] ?? ''), 'term_id' => (string) ($_GET['term_id'] ?? ''),
    'class_id' => (string) ($_GET['class_id'] ?? ''), 'subject_id' => (string) ($_GET['subject_id'] ?? ''),
    'status' => (string) ($_GET['status'] ?? ''), 'search' => (string) ($_GET['search'] ?? ''),
];
$rows = $cbtService->listExams($filters, 1, 5000)['data'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="cbt_exams_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Title', 'Subject', 'Teacher', 'Class', 'Session', 'Term', 'Duration (mins)', 'Questions', 'Attempts', 'Average %', 'Status']);
foreach ($rows as $row) {
    fputcsv($out, [
        $row['title'], $row['subject_name'], trim(($row['teacher_first_name'] ?? '') . ' ' . ($row['teacher_last_name'] ?? '')),
        $row['class_name'] . ($row['section_name'] ? ' - ' . $row['section_name'] : ''), $row['session_name'], $row['term_name'],
        $row['duration_minutes'], $row['question_count'], $row['attempt_count'], $row['average_percentage'], ucfirst($row['status']),
    ]);
}
fclose($out);
exit;
