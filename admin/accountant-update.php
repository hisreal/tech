<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\AccountantService;

$accountantId = (int) ($_POST['accountant_id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: accountants.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: edit-accountant.php?accountant_id=' . $accountantId);
    exit;
}

$accountantService = new AccountantService();
$result = $accountantService->update($accountantId, $_POST, $_FILES, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: edit-accountant.php?accountant_id=' . $accountantId);
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: accountant-profile.php?accountant_id=' . $accountantId);
exit;
