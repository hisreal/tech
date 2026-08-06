<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AccountantService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: accountants.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: accountants.php');
    exit;
}

$accountantService = new AccountantService();
$result = $accountantService->delete((int) ($_POST['accountant_id'] ?? 0), sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: accountants.php');
exit;
