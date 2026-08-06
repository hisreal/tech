<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\ResultService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'results.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: results.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$resultService = new ResultService();
$ids = array_map('intval', (array) ($_POST['ids'] ?? []));
$action = (string) ($_POST['bulk_action'] ?? '');

if (!$ids) {
    sms_flash_set('error', 'Please select at least one result batch.');
    header('Location: ' . $redirectUrl);
    exit;
}

$result = $resultService->bulkBatchAction($ids, $action, sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
