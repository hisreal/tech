<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('teacher');

use App\Services\CBTService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: cbt-management.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: cbt-management.php');
    exit;
}

$cbtService = new CBTService();
$teacherId = $cbtService->teacherIdForUser((int) sms_current_user()['id']);
$result = $teacherId
    ? $cbtService->deleteExam((int) ($_POST['id'] ?? 0), sms_current_user(), $teacherId)
    : ['success' => false, 'message' => 'Your teacher profile is not linked to a staff record.'];

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: cbt-management.php');
exit;
