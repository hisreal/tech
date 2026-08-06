<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\ResultService;

$resultService = new ResultService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$sessionId = (int) ($_GET['session_id'] ?? 0);
$termId = (int) ($_GET['term_id'] ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);
$sectionId = (int) ($_GET['section_id'] ?? 0);

if (!$sessionId || !$termId || !$classId) {
    header('Location: results.php');
    exit;
}

$broadsheet = $resultService->broadsheet($sessionId, $termId, $classId, $sectionId ?: null);

$headers = ['Position', 'Reg. No.', 'Student'];
foreach ($broadsheet['subjects'] as $subject) {
    $headers[] = $subject['name'];
}
$headers[] = 'Total';
$headers[] = 'Average';
$headers[] = 'Grade';

$rows = [];
foreach ($broadsheet['rows'] as $row) {
    $line = [
        $row['summary']['position_in_class'] ?? '-', $row['student']['registration_no'],
        $row['student']['first_name'] . ' ' . $row['student']['last_name'],
    ];
    foreach ($broadsheet['subjects'] as $subject) {
        $line[] = $row['scores'][$subject['id']]['total'] ?? '-';
    }
    $line[] = $row['summary']['total_score'] ?? '-';
    $line[] = $row['summary']['average_score'] ?? '-';
    $line[] = $row['summary']['grade'] ?? '-';
    $rows[] = $line;
}

$filename = 'broadsheet_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Broadsheet', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Class Broadsheet', '', $headers, $rows);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $rows);
