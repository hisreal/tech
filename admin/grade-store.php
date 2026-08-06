<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\ResultService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: result-settings.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: result-settings.php');
    exit;
}

$resultService = new ResultService();
$id = (int) ($_POST['id'] ?? 0) ?: null;
$result = $resultService->saveGrade($_POST, $id, sms_current_user());

$message = $result['message'] . (!$result['success'] && !empty($result['errors']) ? ' ' . implode(' ', $result['errors']) : '');
sms_flash_set($result['success'] ? 'success' : 'error', $message);
header('Location: result-settings.php');
exit;
