<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: allowances.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: allowances.php');
    exit;
}

$payrollService = new PayrollService();
$id = (int) ($_POST['id'] ?? 0) ?: null;
$result = $payrollService->saveAllowanceType($_POST, $id, sms_current_user());

$message = $result['message'] . (!$result['success'] && !empty($result['errors']) ? ' ' . implode(' ', $result['errors']) : '');
sms_flash_set($result['success'] ? 'success' : 'error', $message);
header('Location: allowances.php');
exit;
