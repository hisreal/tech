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
if (!$teacherId) {
    sms_flash_set('error', 'Your teacher profile is not linked to a staff record.');
    header('Location: cbt-management.php');
    exit;
}

$result = $cbtService->saveExam($_POST, null, sms_current_user(), $teacherId);

$message = $result['message'] . (!$result['success'] && !empty($result['errors']) ? ' ' . implode(' ', $result['errors']) : '');
sms_flash_set($result['success'] ? 'success' : 'error', $message);
header('Location: ' . (!empty($result['id']) ? 'cbt-management.php?exam_id=' . (int) $result['id'] : 'cbt-management.php'));
exit;
