<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: attendance-records.php');
    exit;
}

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'attendance-records.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '') . '#markTeacher';

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$attendanceService = new AttendanceService();
$entries = (array) ($_POST['entries'] ?? []);

$result = $attendanceService->markTeacherAttendance((string) ($_POST['attendance_date'] ?? ''), $entries, sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
