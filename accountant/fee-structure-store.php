<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\FinanceService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: fee-structure.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: fee-structure.php');
    exit;
}

$financeService = new FinanceService();
$id = (int) ($_POST['id'] ?? 0) ?: null;
$result = $financeService->saveFeeStructure($_POST, $id, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: fee-structure.php');
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: fee-structure.php');
exit;
