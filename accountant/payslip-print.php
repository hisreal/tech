<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Database;
use App\Models\SettingsModel;
use App\Services\PayrollService;

$payrollService = new PayrollService();
$db = Database::getInstance();

$payslipId = (int) ($_GET['id'] ?? 0);
$payslip = $payslipId ? $payrollService->findPayslip($payslipId) : null;

if ($payslip === null) {
    http_response_code(404);
    echo 'Payslip not found.';
    exit;
}

$settings = (new SettingsModel())->all();
$schoolName = $settings['school.name']['value'] ?? 'School Management System';
$schoolAddress = $settings['school.address']['value'] ?? '';
$schoolPhone = $settings['school.phone']['value'] ?? '';
$logoPath = $settings['school.logo']['value'] ?? '';
$logoUrl = $logoPath ? '../' . ltrim((string) $logoPath, '/') : '';

function psMoney($amount) { return '₦' . number_format((float) $amount, 2); }
function psValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$allowanceItems = array_filter($payslip['items'], static fn ($i) => $i['item_type'] === 'allowance');
$deductionItems = array_filter($payslip['items'], static fn ($i) => $i['item_type'] === 'deduction');
$period = PayrollService::monthName((int) $payslip['period_month']) . ' ' . $payslip['period_year'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payslip - <?php echo psValue($payslip['first_name'] . ' ' . $payslip['last_name'] . ' - ' . $period); ?></title>
<style>
@page { size: A4; margin: 16mm; }
body { font-family: Arial, sans-serif; color: #10201d; }
.head { text-align: center; border-bottom: 3px solid #0f766e; padding-bottom: 10px; margin-bottom: 18px; }
.head img { width: 76px; height: 76px; object-fit: contain; }
table { width: 100%; border-collapse: collapse; }
td, th { padding: 8px; border-bottom: 1px solid #ddd; }
.total { font-size: 20px; font-weight: 800; color: #0f766e; }
.row { display: flex; gap: 20px; margin-top: 20px; }
.col { flex: 1; }
.status-paid { color: #16a34a; font-weight: 900; text-transform: uppercase; }
.status-generated { color: #b45309; font-weight: 900; text-transform: uppercase; }
.print-bar { text-align: center; margin-bottom: 16px; }
@media print { .print-bar { display: none; } }
</style>
</head>
<body>
<div class="print-bar"><button onclick="window.print()">Print</button></div>
<div class="head">
    <?php if ($logoUrl): ?><img src="<?php echo psValue($logoUrl); ?>" alt="Logo"><?php endif; ?>
    <h2 style="margin:8px 0 2px;"><?php echo psValue($schoolName); ?></h2>
    <p style="margin:0;"><?php echo psValue($schoolAddress); ?></p>
    <p style="margin:0;"><?php echo psValue($schoolPhone); ?></p>
    <p style="margin:8px 0 0;font-weight:900;">Payslip for <?php echo psValue($period); ?> <span class="status-<?php echo psValue($payslip['status']); ?>">(<?php echo psValue(ucfirst($payslip['status'])); ?>)</span></p>
</div>
<div class="row">
    <div class="col">
        <h5>Staff Information</h5>
        <p><strong><?php echo psValue($payslip['first_name'] . ' ' . $payslip['last_name']); ?></strong></p>
        <p><?php echo psValue($payslip['staff_no']); ?> &middot; <?php echo psValue(ucfirst($payslip['staff_type'])); ?></p>
        <p><?php echo psValue($payslip['designation'] ?? ''); ?><?php echo $payslip['department_name'] ? ' - ' . psValue($payslip['department_name']) : ''; ?></p>
    </div>
    <div class="col">
        <h5>Payslip Information</h5>
        <p><strong>Period:</strong> <?php echo psValue($period); ?></p>
        <p><strong>Status:</strong> <?php echo psValue(ucfirst($payslip['status'])); ?></p>
        <?php if ($payslip['paid_at']): ?><p><strong>Paid On:</strong> <?php echo psValue(substr((string) $payslip['paid_at'], 0, 16)); ?></p><?php endif; ?>
    </div>
</div>

<h5 style="margin-top:20px;">Earnings</h5>
<table>
<thead><tr><th style="text-align:left;background:#0f766e;color:#fff;">Item</th><th style="text-align:right;background:#0f766e;color:#fff;">Amount</th></tr></thead>
<tbody>
<tr><td>Basic Salary</td><td style="text-align:right;"><?php echo psMoney($payslip['basic_salary']); ?></td></tr>
<?php foreach ($allowanceItems as $item): ?>
    <tr><td><?php echo psValue($item['label']); ?></td><td style="text-align:right;"><?php echo psMoney($item['amount']); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>

<h5 style="margin-top:20px;">Deductions</h5>
<table>
<thead><tr><th style="text-align:left;background:#dc2626;color:#fff;">Item</th><th style="text-align:right;background:#dc2626;color:#fff;">Amount</th></tr></thead>
<tbody>
<?php foreach ($deductionItems as $item): ?>
    <tr><td><?php echo psValue($item['label']); ?></td><td style="text-align:right;"><?php echo psMoney($item['amount']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$deductionItems): ?><tr><td colspan="2">No deductions</td></tr><?php endif; ?>
</tbody>
</table>

<div class="row">
    <div class="col"><strong>Gross Pay</strong><p><?php echo psMoney((float) $payslip['basic_salary'] + (float) $payslip['total_allowances']); ?></p></div>
    <div class="col"><strong>Total Deductions</strong><p><?php echo psMoney($payslip['total_deductions']); ?></p></div>
    <div class="col"><strong>Net Pay</strong><div class="total"><?php echo psMoney($payslip['net_pay']); ?></div></div>
</div>

<?php if ($payslip['payments']): ?>
<h5 style="margin-top:20px;">Payment Record</h5>
<table>
<thead><tr><th style="text-align:left;background:#0f766e;color:#fff;">Date</th><th style="text-align:left;background:#0f766e;color:#fff;">Method</th><th style="text-align:left;background:#0f766e;color:#fff;">Reference</th><th style="text-align:right;background:#0f766e;color:#fff;">Amount</th></tr></thead>
<tbody>
<?php foreach ($payslip['payments'] as $payment): ?>
    <tr><td><?php echo psValue(substr((string) $payment['paid_at'], 0, 16)); ?></td><td><?php echo psValue(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?></td><td><?php echo psValue($payment['reference_no'] ?? '-'); ?></td><td style="text-align:right;"><?php echo psMoney($payment['amount']); ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<p style="text-align:center;font-weight:800;margin-top:24px;">This is a computer-generated payslip.</p>
</body>
</html>
