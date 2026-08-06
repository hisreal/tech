<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('teacher');

use App\Services\AttendanceService;
use App\Services\TeacherService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: attendance.php');
    exit;
}

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'attendance.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$teacherService = new TeacherService();
$currentUser = sms_current_user();
$teacherId = $teacherService->teacherIdForUser((int) $currentUser['id']);

$classId = (int) ($_POST['class_id'] ?? 0);
$sectionId = (int) ($_POST['section_id'] ?? 0);

$owns = $teacherId ? $teacherService->find($teacherId) : null;
$ownsClass = false;
if ($owns) {
    foreach ($owns['classes'] as $class) {
        if ((int) $class['class_id'] === $classId && (int) $class['id'] === $sectionId) {
            $ownsClass = true;
            break;
        }
    }
}

if (!$teacherId || !$ownsClass) {
    sms_flash_set('error', 'You are not assigned to that class.');
    header('Location: ' . $redirectUrl);
    exit;
}

$attendanceService = new AttendanceService();
$statuses = array_map('strval', (array) ($_POST['status'] ?? []));
$notes = array_map('strval', (array) ($_POST['notes'] ?? []));

$result = $attendanceService->markStudentAttendance(
    (int) ($_POST['session_id'] ?? 0),
    (int) ($_POST['term_id'] ?? 0),
    $classId,
    $sectionId ?: null,
    (string) ($_POST['attendance_date'] ?? ''),
    $statuses,
    $notes,
    $currentUser
);

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
