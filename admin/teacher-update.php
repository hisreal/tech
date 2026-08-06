<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\TeacherService;

$teacherId = (int) ($_POST['teacher_id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: teachers.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: edit-teacher.php?teacher_id=' . $teacherId);
    exit;
}

$teacherService = new TeacherService();
$result = $teacherService->update($teacherId, $_POST, $_FILES, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: edit-teacher.php?teacher_id=' . $teacherId);
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: teacher-profile.php?teacher_id=' . $teacherId);
exit;
