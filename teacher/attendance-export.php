<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('teacher');

use App\Helpers\ReportExporter;
use App\Services\AttendanceService;
use App\Services\TeacherService;

$teacherService = new TeacherService();
$attendanceService = new AttendanceService();
$currentUser = sms_current_user();
$teacherId = $teacherService->teacherIdForUser((int) $currentUser['id']);
$staff = $teacherId ? $teacherService->find($teacherId) : null;
$classIds = $staff ? array_map(static fn (array $c): int => (int) $c['class_id'], $staff['classes']) : [];

$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$filters = [
    'search' => trim((string) ($_GET['h_search'] ?? '')),
    'status' => trim((string) ($_GET['h_status'] ?? '')),
    'date_from' => trim((string) ($_GET['h_date_from'] ?? '')),
    'date_to' => trim((string) ($_GET['h_date_to'] ?? '')),
    'class_ids' => $classIds,
];
$hClassSel = trim((string) ($_GET['h_class'] ?? ''));
if ($hClassSel !== '' && str_contains($hClassSel, ':')) {
    [$fClass, $fSection] = array_map('intval', explode(':', $hClassSel, 2));
    $filters['class_id'] = $fClass;
    $filters['section_id'] = $fSection;
}

$rows = $classIds ? $attendanceService->listStudentAttendance($filters, 1, 5000)['data'] : [];

$headers = ['Date', 'Reg No.', 'Student', 'Class', 'Status', 'Notes'];
$data = [];
foreach ($rows as $row) {
    $data[] = [
        $row['attendance_date'], $row['registration_no'], trim($row['first_name'] . ' ' . $row['last_name']),
        $row['class_name'] . ($row['section_name'] ? ' - ' . $row['section_name'] : ''), ucfirst($row['status']), $row['notes'] ?? '',
    ];
}

$filename = 'my_class_attendance_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Attendance', $headers, $data);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Class Attendance Report', '', $headers, $data);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $data);
