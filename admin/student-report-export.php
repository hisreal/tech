<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\StudentService;

$studentService = new StudentService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$sessionId = (int) ($_GET['session_id'] ?? $studentService->currentSessionId() ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);

$summary = $studentService->reportsSummary(['session_id' => $sessionId, 'class_id' => $classId]);

$headers = ['Class', 'Section', 'Total', 'Male', 'Female'];
$rows = [];
foreach ($summary['by_class'] as $row) {
    $rows[] = [$row['class_name'], $row['section_name'] ?? 'Whole Class', $row['total'], $row['male_count'], $row['female_count']];
}

$filename = 'student_report_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Student Report', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Student Report', 'Enrollment by class/section', $headers, $rows, [
        'Total Enrolled' => $summary['total_enrolled'],
        'With Guardian' => $summary['with_guardian'],
        'Without Guardian' => $summary['without_guardian'],
    ]);
    ReportExporter::pdf($filename, $html);
}

ReportExporter::csv($filename, $headers, $rows);
