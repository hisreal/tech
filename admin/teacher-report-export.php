<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\TeacherService;

$teacherService = new TeacherService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$departmentId = (int) ($_GET['department_id'] ?? 0);

$summary = $teacherService->reportsSummary(['department_id' => $departmentId]);

$headers = ['Department', 'Total', 'Active'];
$rows = [];
foreach ($summary['by_department'] as $row) {
    $rows[] = [$row['department_name'], $row['total'], $row['active_count']];
}

$filename = 'teacher_report_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Teacher Report', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Teacher Report', 'Staffing by department', $headers, $rows, [
        'Total Teachers' => $summary['total_teachers'],
        'With Subjects Assigned' => $summary['with_subjects'],
        'With Classes Assigned' => $summary['with_classes'],
    ]);
    ReportExporter::pdf($filename, $html);
}

ReportExporter::csv($filename, $headers, $rows);
