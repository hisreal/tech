<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

$payrollService = new PayrollService();
require_once('includes/header.php');
require_once('includes/payroll-styles.php');

$runs = $payrollService->listPayrollRuns();
$filterRun = (int) ($_GET['payroll_run_id'] ?? 0) ?: null;

$summary = $payrollService->reportsSummary($filterRun);
$maxDept = $summary['by_department'] ? max(array_column($summary['by_department'], 'total')) : 1;
$maxMonthly = $summary['monthly_trend'] ? max(array_column($summary['monthly_trend'], 'total')) : 1;

function prMoney6($amount) { return '₦' . number_format((float) $amount, 2); }
?>
<div class="payroll-page">
    <section class="pr-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payroll Management <i class="fa-solid fa-chevron-right mx-2"></i> Reports</div>
        <span class="pr-kicker"><i class="fa-solid fa-chart-pie"></i> Payroll Administration</span>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="mt-3 mb-2">Payroll Reports</h3>
                <p class="text-muted mb-0">Real aggregated payroll cost analysis across departments and months.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn pr-btn" href="payroll-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'pdf']))); ?>"><i class="fa-solid fa-file-pdf me-2"></i>PDF</a>
                <a class="btn pr-btn" href="payroll-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'excel']))); ?>"><i class="fa-solid fa-file-excel me-2"></i>Excel</a>
                <a class="btn pr-btn" href="payroll-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv me-2"></i>CSV</a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button>
            </div>
        </div>
    </section>

    <section class="pr-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Payroll Run</label><select class="form-select" name="payroll_run_id" style="padding-left:12px;"><option value="">All Runs (All Time)</option><?php foreach ($runs as $run): ?><option value="<?php echo (int) $run['id']; ?>" <?php echo $filterRun === (int) $run['id'] ? 'selected' : ''; ?>><?php echo sms_e(PayrollService::monthName((int) $run['period_month']) . ' ' . $run['period_year']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><button type="submit" class="btn pr-btn w-100"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate</button></div>
            <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="payroll-reports.php">Reset</a></div>
        </form>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-money-bill-wave"></i></span><h4><?php echo prMoney6($summary['total_basic']); ?></h4><p class="text-muted mb-0">Total Basic Salary</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-hand-holding-dollar"></i></span><h4><?php echo prMoney6($summary['total_allowances']); ?></h4><p class="text-muted mb-0">Total Allowances</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-hand-holding-hand"></i></span><h4><?php echo prMoney6($summary['total_deductions']); ?></h4><p class="text-muted mb-0">Total Deductions</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo prMoney6($summary['total_net_pay']); ?></h4><p class="text-muted mb-0">Total Net Pay (<?php echo $summary['staff_count']; ?> staff)</p></div></div>
    </section>

    <section class="row g-4">
        <div class="col-xl-6"><div class="pr-card"><h4 class="mb-3">Payroll Cost by Department</h4><div class="mini-list">
            <?php foreach ($summary['by_department'] as $dept): ?><div class="mini-item"><span><?php echo sms_e($dept['department']); ?></span><strong><?php echo prMoney6($dept['total']); ?></strong></div><?php endforeach; ?>
            <?php if (!$summary['by_department']): ?><p class="text-muted mb-0">No payslips recorded yet.</p><?php endif; ?>
        </div></div></div>
        <div class="col-xl-6"><div class="pr-card"><h4 class="mb-1">Department Cost Chart</h4><p class="text-muted mb-0">Relative net pay by department.</p>
            <div class="chart-wrap"><?php foreach ($summary['by_department'] as $dept): ?><div class="chart-bar"><div class="bar-fill" style="height:<?php echo round(($dept['total'] / $maxDept) * 100); ?>%" title="<?php echo prMoney6($dept['total']); ?>"></div><span class="bar-label"><?php echo sms_e($dept['department']); ?></span></div><?php endforeach; ?></div>
            <?php if (!$summary['by_department']): ?><p class="text-muted mt-3 mb-0">No data to chart yet.</p><?php endif; ?>
        </div></div>
    </section>

    <section class="pr-card">
        <h4 class="mb-1">Monthly Payroll Trend</h4>
        <p class="text-muted mb-0">Total net pay for the last 12 payroll runs.</p>
        <div class="chart-wrap"><?php foreach ($summary['monthly_trend'] as $month): ?><div class="chart-bar"><div class="bar-fill" style="height:<?php echo round(($month['total'] / $maxMonthly) * 100); ?>%" title="<?php echo prMoney6($month['total']); ?>"></div><span class="bar-label"><?php echo sms_e(substr(PayrollService::monthName((int) $month['period_month']), 0, 3) . ' ' . substr((string) $month['period_year'], -2)); ?></span></div><?php endforeach; ?></div>
        <?php if (!$summary['monthly_trend']): ?><p class="text-muted mt-3 mb-0">No payroll runs processed yet.</p><?php endif; ?>
    </section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
