<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: attendance-records.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: attendance-records.php');
    exit;
}

$attendanceService = new AttendanceService();
$id = (int) ($_POST['attendance_id'] ?? 0);
$type = (string) ($_POST['attendance_type'] ?? '');
$status = (string) ($_POST['status'] ?? '');
$notes = (string) ($_POST['notes'] ?? '');

$result = $type === 'teacher'
    ? $attendanceService->updateTeacherRecord($id, $status, $notes, sms_current_user())
    : $attendanceService->updateStudentRecord($id, $status, $notes, sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: attendance-records.php');
exit;
