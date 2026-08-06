<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\CBTService;

$examId = (int) ($_POST['exam_id'] ?? 0);
$redirectUrl = 'cbt-questions.php?exam_id=' . $examId;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: cbt-questions.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$cbtService = new CBTService();
$id = (int) ($_POST['id'] ?? 0) ?: null;
$result = $cbtService->saveQuestion($_POST, $id, $examId, sms_current_user());

$message = $result['message'] . (!$result['success'] && !empty($result['errors']) ? ' ' . implode(' ', $result['errors']) : '');
sms_flash_set($result['success'] ? 'success' : 'error', $message);
header('Location: ' . $redirectUrl);
exit;
