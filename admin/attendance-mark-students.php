<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: attendance-records.php');
    exit;
}

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'attendance-records.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '') . '#markStudent';

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$attendanceService = new AttendanceService();
$statuses = array_map('strval', (array) ($_POST['status'] ?? []));
$notes = array_map('strval', (array) ($_POST['notes'] ?? []));

$result = $attendanceService->markStudentAttendance(
    (int) ($_POST['session_id'] ?? 0),
    (int) ($_POST['term_id'] ?? 0),
    (int) ($_POST['class_id'] ?? 0),
    (int) ($_POST['section_id'] ?? 0) ?: null,
    (string) ($_POST['attendance_date'] ?? ''),
    $statuses,
    $notes,
    sms_current_user()
);

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
