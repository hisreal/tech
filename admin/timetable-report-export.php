<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\TimetableService;

$timetableService = new TimetableService();
$sessionId = ($_GET['session_id'] ?? '') !== '' ? (int) $_GET['session_id'] : $timetableService->currentSessionId();
$termId = ($_GET['term_id'] ?? '') !== '' ? (int) $_GET['term_id'] : $timetableService->currentTermId();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="timetable_reports_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');

fputcsv($out, ['Teacher Workload']);
fputcsv($out, ['Teacher', 'Staff No', 'Periods', 'Hours', 'Subjects', 'Classes']);
foreach ($timetableService->teacherWorkloadReport($sessionId, $termId) as $row) {
    fputcsv($out, [$row['teacher_name'], $row['staff_no'], $row['periods'], round((float) $row['hours'], 1), $row['subject_count'], $row['class_count']]);
}

fputcsv($out, []);
fputcsv($out, ['Class Schedule Summary']);
fputcsv($out, ['Class', 'Periods', 'Hours', 'Subjects']);
foreach ($timetableService->classScheduleReport($sessionId, $termId) as $row) {
    fputcsv($out, [$row['class_name'], $row['periods'], round((float) $row['hours'], 1), $row['subject_count']]);
}

fputcsv($out, []);
fputcsv($out, ['Venue Utilization']);
fputcsv($out, ['Venue', 'Bookings', 'Hours']);
foreach ($timetableService->venueUtilizationReport($sessionId, $termId) as $row) {
    fputcsv($out, [$row['venue_name'], $row['bookings'], round((float) $row['hours'], 1)]);
}

fclose($out);
exit;
