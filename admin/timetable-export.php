<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\TimetableService;

$timetableService = new TimetableService();

$filters = [
    'session_id' => $_GET['session_id'] ?? '', 'term_id' => $_GET['term_id'] ?? '', 'class_id' => $_GET['class_id'] ?? '',
    'section_id' => $_GET['section_id'] ?? '', 'teacher_id' => $_GET['teacher_id'] ?? '', 'department_id' => $_GET['department_id'] ?? '',
    'day' => $_GET['day'] ?? '', 'status' => $_GET['status'] ?? '', 'search' => $_GET['search'] ?? '',
];

$rows = $timetableService->list($filters, 1, 100000)['data'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="timetable_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Day', 'Start', 'End', 'Subject', 'Teacher', 'Class', 'Section', 'Venue', 'Session', 'Term', 'Status']);
foreach ($rows as $row) {
    fputcsv($out, [
        $row['day_name'], substr($row['start_time'], 0, 5), substr($row['end_time'], 0, 5), $row['subject_name'],
        trim($row['teacher_first_name'] . ' ' . $row['teacher_last_name']), $row['class_name'], $row['section_name'] ?? '',
        $row['venue_name'] ?? '', $row['session_name'], $row['term_name'], ucfirst($row['status']),
    ]);
}
fclose($out);
exit;
