<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: payslips.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: payslips.php');
    exit;
}

$payrollService = new PayrollService();
$month = (int) ($_POST['period_month'] ?? 0);
$year = (int) ($_POST['period_year'] ?? 0);

if ($month < 1 || $month > 12 || $year < 2000) {
    sms_flash_set('error', 'Select a valid month and year.');
    header('Location: payslips.php');
    exit;
}

$runResult = $payrollService->findOrCreatePayrollRun($month, $year, sms_current_user());
$runId = (int) $runResult['run']['id'];
$generateResult = $payrollService->generatePayslips($runId, sms_current_user());

sms_flash_set($generateResult['success'] ? 'success' : 'error', $generateResult['message']);
header('Location: payslips.php?payroll_run_id=' . $runId);
exit;
