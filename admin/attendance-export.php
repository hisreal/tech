<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

$attendanceService = new AttendanceService();
$type = (string) ($_GET['type'] ?? 'student');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_' . $type . '_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');

if ($type === 'teacher') {
    $rows = $attendanceService->listTeacherAttendance([
        'search' => $_GET['t_search'] ?? '', 'department_id' => $_GET['t_department'] ?? '', 'status' => $_GET['t_status'] ?? '',
        'date' => $_GET['t_date'] ?? '', 'date_from' => $_GET['t_date_from'] ?? '', 'date_to' => $_GET['t_date_to'] ?? '',
    ], 1, 100000)['data'];

    fputcsv($out, ['Date', 'Staff ID', 'Teacher Name', 'Department', 'Check-in', 'Check-out', 'Status', 'Notes']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['attendance_date'], $row['staff_no'], trim($row['first_name'] . ' ' . $row['last_name']),
            $row['department_name'] ?? 'Unassigned', $row['check_in'] ?? '', $row['check_out'] ?? '', ucfirst($row['status']), $row['notes'] ?? '',
        ]);
    }
} else {
    $rows = $attendanceService->listStudentAttendance([
        'search' => $_GET['s_search'] ?? '', 'class_id' => $_GET['s_class'] ?? '', 'section_id' => $_GET['s_section'] ?? '',
        'status' => $_GET['s_status'] ?? '', 'date' => $_GET['s_date'] ?? '', 'date_from' => $_GET['s_date_from'] ?? '', 'date_to' => $_GET['s_date_to'] ?? '',
    ], 1, 100000)['data'];

    fputcsv($out, ['Date', 'Registration No.', 'Student Name', 'Class', 'Section', 'Status', 'Marked By', 'Notes']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['attendance_date'], $row['registration_no'], trim($row['first_name'] . ' ' . $row['last_name']),
            $row['class_name'], $row['section_name'] ?? '', ucfirst($row['status']), $row['marked_by_name'] ?? 'System', $row['notes'] ?? '',
        ]);
    }
}

fclose($out);
exit;
