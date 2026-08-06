<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\AttendanceService;

$attendanceService = new AttendanceService();
$reportType = (string) ($_GET['report_type'] ?? 'Daily Attendance Report');
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$report = $attendanceService->generateReport([
    'report_type' => $reportType,
    'scope' => $_GET['scope'] ?? 'all',
    'session_id' => $_GET['session_id'] ?? '',
    'term_id' => $_GET['term_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'class_id' => $_GET['class_id'] ?? '',
    'section_id' => $_GET['section_id'] ?? '',
    'department_id' => $_GET['department_id'] ?? '',
    'search' => $_GET['search'] ?? '',
]);

$headers = ['Group/Date', 'Category', 'Present', 'Absent', 'Late/Excused/Leave', 'Total', 'Rate'];
$rows = [];
foreach ($report['rows'] as $row) {
    $rows[] = [$row['label'], $row['category'], $row['present'], $row['absent'], $row['other'], $row['total'], $row['rate']];
}
$rows[] = ['Totals', '', $report['totals']['present'], $report['totals']['absent'], $report['totals']['other'], $report['totals']['records'], $report['totals']['rate']];

$filename = 'attendance_' . preg_replace('/[^a-z0-9]+/i', '_', $reportType) . '_' . date('Ymd_His');
$dateFrom = (string) ($_GET['date_from'] ?? '');
$dateTo = (string) ($_GET['date_to'] ?? '');

if ($format === 'excel') {
    ReportExporter::excel($filename, $reportType, $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml($reportType, $dateFrom . ' to ' . $dateTo, $headers, $rows, [
        'Total Present' => $report['totals']['present'], 'Total Absent' => $report['totals']['absent'], 'Attendance Rate' => $report['totals']['rate'],
    ]);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $rows);
