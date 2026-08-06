<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\CBTService;

$cbtService = new CBTService();
$filters = [
    'session_id' => (string) ($_GET['session_id'] ?? ''), 'term_id' => (string) ($_GET['term_id'] ?? ''),
    'class_id' => (string) ($_GET['class_id'] ?? ''), 'subject_id' => (string) ($_GET['subject_id'] ?? ''),
    'exam_id' => (string) ($_GET['exam_id'] ?? ''), 'status' => (string) ($_GET['status'] ?? ''), 'search' => (string) ($_GET['search'] ?? ''),
];
$rows = $cbtService->listAttempts($filters, 1, 5000)['data'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="cbt_attempts_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Student', 'Registration Number', 'Class', 'Subject', 'Exam', 'Started', 'Submitted', 'Score', 'Grade', 'Percentage', 'Status']);
foreach ($rows as $row) {
    $passed = (float) $row['percentage'] >= (float) $row['pass_mark'];
    fputcsv($out, [
        $row['first_name'] . ' ' . $row['last_name'], $row['registration_no'], $row['class_name'], $row['subject_name'], $row['exam_title'],
        $row['started_at'], $row['ended_at'], $row['score'], $row['grade'], $row['percentage'], $passed ? 'Passed' : 'Failed',
    ]);
}
fclose($out);
exit;
