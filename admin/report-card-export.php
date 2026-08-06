<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\ResultService;

$resultService = new ResultService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$sessionId = (int) ($_GET['session_id'] ?? 0);
$termId = (int) ($_GET['term_id'] ?? 0);
$query = trim((string) ($_GET['q'] ?? ''));

$student = $query !== '' ? $resultService->findStudentByQuery($query) : null;
$card = ($student && $sessionId && $termId) ? $resultService->reportCard((int) $student['id'], $sessionId, $termId) : null;

if (!$card) {
    header('Location: report-cards.php');
    exit;
}

$headers = ['Subject', 'CA1', 'CA2', 'CA3', 'Exam', 'Practical', 'Total', 'Grade', 'Remark'];
$rows = [];
foreach ($card['subjects'] as $subject) {
    $rows[] = [$subject['subject_name'], $subject['ca1'], $subject['ca2'], $subject['ca3'], $subject['exam'], $subject['practical'], $subject['total'], $subject['grade'], $subject['remark']];
}

$summary = [
    'Student' => $card['student']['first_name'] . ' ' . $card['student']['last_name'],
    'Reg. No.' => $card['student']['registration_no'],
    'Session' => $card['session_name'],
    'Term' => $card['term_name'],
    'Average' => $card['summary']['average_score'] ?? '-',
    'Grade' => $card['summary']['grade'] ?? '-',
    'Position' => $card['summary']['position_in_class'] ?? '-',
];

$filename = 'report_card_' . $card['student']['registration_no'];

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Report Card', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Student Report Card', '', $headers, $rows, $summary);
    ReportExporter::pdf($filename, $html);
}

$csvHeaders = ['Report Card', $card['student']['first_name'] . ' ' . $card['student']['last_name'], $card['student']['registration_no']];
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, $csvHeaders);
fputcsv($out, ['Session', $card['session_name'], 'Term', $card['term_name']]);
fputcsv($out, []);
fputcsv($out, $headers);
foreach ($rows as $row) {
    fputcsv($out, $row);
}
fputcsv($out, []);
fputcsv($out, ['Average', $card['summary']['average_score'] ?? '-', 'Grade', $card['summary']['grade'] ?? '-', 'Position', $card['summary']['position_in_class'] ?? '-']);
fputcsv($out, ['Teacher Remark', $card['teacher_remark'] ?? '-']);
fputcsv($out, ['Principal Remark', $card['principal_remark'] ?? '-']);
fclose($out);
exit;
