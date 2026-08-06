<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\FinanceService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'fee-collection.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: fee-collection.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$financeService = new FinanceService();
$studentId = (int) ($_POST['student_id'] ?? 0);
$invoiceId = (int) ($_POST['invoice_id'] ?? 0);

$result = $financeService->collectPayment($studentId, $invoiceId, $_POST, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: ' . $redirectUrl);
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: ' . $redirectUrl);
exit;
