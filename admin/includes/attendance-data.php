<?php
/**
 * Placeholder attendance datasets for the Admin Attendance Management module.
 * Replace these arrays with repository/database calls during backend integration.
 */

$attendanceSessions = ['2025/2026', '2026/2027'];
$attendanceTerms = ['First Term', 'Second Term', 'Third Term'];
$attendanceClasses = ['JSS 1A', 'JSS 2B', 'JSS 3A', 'SS 1 Science', 'SS 2 Science', 'SS 3 Arts'];
$attendanceSections = ['A', 'B', 'Science', 'Commercial', 'Arts'];
$attendanceDepartments = ['Science', 'Mathematics', 'Languages', 'Humanities', 'ICT'];
$attendanceStatuses = ['Present', 'Absent', 'Late', 'Excused', 'Leave'];

$studentAttendanceRecords = [
    ['id' => 1, 'date' => '2026-07-03', 'reg_no' => 'REG-2026-001', 'student_name' => 'Musa Ibrahim', 'class' => 'SS 2 Science', 'section' => 'Science', 'status' => 'Present', 'time_marked' => '08:05 AM', 'marked_by' => 'Mr. John Musa', 'remarks' => 'Marked during morning roll call.'],
    ['id' => 2, 'date' => '2026-07-03', 'reg_no' => 'REG-2026-002', 'student_name' => 'Aisha Bello', 'class' => 'SS 2 Science', 'section' => 'Science', 'status' => 'Absent', 'time_marked' => '08:07 AM', 'marked_by' => 'Mr. John Musa', 'remarks' => 'Parent notification pending.'],
    ['id' => 3, 'date' => '2026-07-03', 'reg_no' => 'REG-2026-003', 'student_name' => 'Daniel Okafor', 'class' => 'JSS 1A', 'section' => 'A', 'status' => 'Late', 'time_marked' => '08:32 AM', 'marked_by' => 'Mrs. Grace Audu', 'remarks' => 'Arrived after assembly.'],
    ['id' => 4, 'date' => '2026-07-03', 'reg_no' => 'REG-2026-004', 'student_name' => 'Fatima Sani', 'class' => 'JSS 2B', 'section' => 'B', 'status' => 'Present', 'time_marked' => '08:01 AM', 'marked_by' => 'Mr. Peter James', 'remarks' => 'Present.'],
    ['id' => 5, 'date' => '2026-07-03', 'reg_no' => 'REG-2026-005', 'student_name' => 'Chinedu Eze', 'class' => 'SS 1 Science', 'section' => 'Science', 'status' => 'Excused', 'time_marked' => '08:10 AM', 'marked_by' => 'Mrs. Hauwa Lawal', 'remarks' => 'Approved medical appointment.'],
    ['id' => 6, 'date' => '2026-07-03', 'reg_no' => 'REG-2026-006', 'student_name' => 'Maryam Yusuf', 'class' => 'SS 3 Arts', 'section' => 'Arts', 'status' => 'Present', 'time_marked' => '07:58 AM', 'marked_by' => 'Mr. Emmanuel Ade', 'remarks' => 'Present.'],
];

$teacherAttendanceRecords = [
    ['id' => 101, 'date' => '2026-07-03', 'staff_id' => 'TCH001', 'teacher_name' => 'John Musa', 'department' => 'Science', 'check_in' => '07:42 AM', 'check_out' => '03:08 PM', 'status' => 'Present', 'remarks' => 'On time.'],
    ['id' => 102, 'date' => '2026-07-03', 'staff_id' => 'TCH002', 'teacher_name' => 'Grace Audu', 'department' => 'Mathematics', 'check_in' => '07:55 AM', 'check_out' => '03:15 PM', 'status' => 'Present', 'remarks' => 'Handled JSS 1A roll call.'],
    ['id' => 103, 'date' => '2026-07-03', 'staff_id' => 'TCH003', 'teacher_name' => 'Peter James', 'department' => 'ICT', 'check_in' => '08:24 AM', 'check_out' => '03:00 PM', 'status' => 'Late', 'remarks' => 'Traffic delay logged.'],
    ['id' => 104, 'date' => '2026-07-03', 'staff_id' => 'TCH004', 'teacher_name' => 'Hauwa Lawal', 'department' => 'Languages', 'check_in' => '-', 'check_out' => '-', 'status' => 'Leave', 'remarks' => 'Approved annual leave.'],
    ['id' => 105, 'date' => '2026-07-03', 'staff_id' => 'TCH005', 'teacher_name' => 'Emmanuel Ade', 'department' => 'Humanities', 'check_in' => '-', 'check_out' => '-', 'status' => 'Absent', 'remarks' => 'Awaiting HR review.'],
];

$monthlyStudentSummary = [
    ['id' => 1, 'student_name' => 'Musa Ibrahim', 'reg_no' => 'REG-2026-001', 'class' => 'SS 2 Science', 'present' => 19, 'absent' => 1, 'late' => 0, 'percentage' => '95%'],
    ['id' => 2, 'student_name' => 'Aisha Bello', 'reg_no' => 'REG-2026-002', 'class' => 'SS 2 Science', 'present' => 17, 'absent' => 2, 'late' => 1, 'percentage' => '85%'],
    ['id' => 3, 'student_name' => 'Daniel Okafor', 'reg_no' => 'REG-2026-003', 'class' => 'JSS 1A', 'present' => 18, 'absent' => 0, 'late' => 2, 'percentage' => '90%'],
    ['id' => 4, 'student_name' => 'Fatima Sani', 'reg_no' => 'REG-2026-004', 'class' => 'JSS 2B', 'present' => 20, 'absent' => 0, 'late' => 0, 'percentage' => '100%'],
];

$monthlyTeacherSummary = [
    ['id' => 101, 'teacher_name' => 'John Musa', 'staff_id' => 'TCH001', 'department' => 'Science', 'present' => 20, 'absent' => 0, 'late' => 0, 'percentage' => '100%'],
    ['id' => 102, 'teacher_name' => 'Grace Audu', 'staff_id' => 'TCH002', 'department' => 'Mathematics', 'present' => 19, 'absent' => 0, 'late' => 1, 'percentage' => '95%'],
    ['id' => 103, 'teacher_name' => 'Peter James', 'staff_id' => 'TCH003', 'department' => 'ICT', 'present' => 18, 'absent' => 1, 'late' => 1, 'percentage' => '90%'],
];

$attendanceReportTypes = ['Student Attendance Report', 'Teacher Attendance Report', 'Daily Attendance Report', 'Weekly Attendance Report', 'Monthly Attendance Report', 'Class Attendance Report'];
$attendanceMonthlyTrend = [88, 91, 89, 93, 92, 94, 90, 95, 96, 94, 93, 95];
$attendanceClassBalances = ['JSS 1' => 91, 'JSS 2' => 87, 'JSS 3' => 90, 'SS 1' => 92, 'SS 2' => 89, 'SS 3' => 94];

function sms_attendance_count_status(array $records, string $status): int
{
    return count(array_filter($records, fn($record) => strcasecmp((string)($record['status'] ?? ''), $status) === 0));
}

function sms_attendance_rate(int $present, int $total): string
{
    if ($total <= 0) {
        return '0%';
    }

    return rtrim(rtrim(number_format(($present / $total) * 100, 1), '0'), '.') . '%';
}

function sms_attendance_status_class(string $status): string
{
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
?>