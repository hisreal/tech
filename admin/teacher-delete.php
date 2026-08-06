<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\TeacherService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: teachers.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: teachers.php');
    exit;
}

$teacherService = new TeacherService();
$result = $teacherService->delete((int) ($_POST['teacher_id'] ?? 0), sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: teachers.php');
exit;
