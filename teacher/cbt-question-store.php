<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('teacher');

use App\Services\CBTService;

$examId = (int) ($_POST['exam_id'] ?? 0);
$redirectUrl = 'cbt-management.php?exam_id=' . $examId;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: cbt-management.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$cbtService = new CBTService();
$teacherId = $cbtService->teacherIdForUser((int) sms_current_user()['id']);
$result = $teacherId
    ? $cbtService->saveQuestion($_POST, null, $examId, sms_current_user(), $teacherId)
    : ['success' => false, 'message' => 'Your teacher profile is not linked to a staff record.'];

$message = $result['message'] . (!$result['success'] && !empty($result['errors']) ? ' ' . implode(' ', $result['errors']) : '');
sms_flash_set($result['success'] ? 'success' : 'error', $message);
header('Location: ' . $redirectUrl);
exit;
