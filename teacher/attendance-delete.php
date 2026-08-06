<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('teacher');

use App\Services\AttendanceService;
use App\Services\TeacherService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: attendance.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: attendance.php');
    exit;
}

$teacherService = new TeacherService();
$attendanceService = new AttendanceService();
$currentUser = sms_current_user();
$teacherId = $teacherService->teacherIdForUser((int) $currentUser['id']);
$recordId = (int) ($_POST['attendance_id'] ?? 0);

$record = $attendanceService->findStudentRecord($recordId);
$staff = $teacherId ? $teacherService->find($teacherId) : null;
$ownsClass = false;
if ($record && $staff) {
    foreach ($staff['classes'] as $class) {
        if ((int) $class['class_id'] === (int) $record['class_id'] && (int) $class['id'] === (int) ($record['section_id'] ?? 0)) {
            $ownsClass = true;
            break;
        }
    }
}

if (!$record || !$ownsClass) {
    sms_flash_set('error', 'You are not allowed to delete that attendance record.');
    header('Location: attendance.php');
    exit;
}

$result = $attendanceService->deleteStudentRecord($recordId, $currentUser);

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: attendance.php');
exit;
