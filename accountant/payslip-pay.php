<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'payslips.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: payslips.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$payrollService = new PayrollService();
$result = $payrollService->recordPayment((int) ($_POST['id'] ?? 0), $_POST, sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
