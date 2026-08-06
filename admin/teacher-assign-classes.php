<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\TeacherService;

$teacherId = (int) ($_POST['teacher_id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: teachers.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: assign-classes.php?teacher_id=' . $teacherId);
    exit;
}

$teacherService = new TeacherService();
$sectionIds = array_map('intval', (array) ($_POST['classes'] ?? []));
$result = $teacherService->assignClasses($teacherId, $sectionIds, sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . ($result['success'] ? 'teacher-profile.php?teacher_id=' . $teacherId : 'assign-classes.php?teacher_id=' . $teacherId));
exit;
