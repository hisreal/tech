<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\ResultService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'score-entry.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $redirectUrl);
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$resultService = new ResultService();
$result = $resultService->submitBatch((int) ($_POST['batch_id'] ?? 0), sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
