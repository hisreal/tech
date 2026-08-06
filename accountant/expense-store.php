<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\FinanceService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: expense-management.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: expense-management.php');
    exit;
}

$financeService = new FinanceService();
$id = (int) ($_POST['id'] ?? 0) ?: null;
$file = $_FILES['attachment'] ?? null;
$result = $financeService->saveExpense($_POST, $file, $id, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: expense-management.php' . ($id ? '?edit=' . $id : ''));
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: expense-management.php');
exit;
