<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Models\SettingsModel;
use App\Services\FinanceService;

$financeService = new FinanceService();
require_once('includes/header.php');
require_once('includes/financial-reports-styles.php');

$dateFrom = trim((string) ($_GET['date_from'] ?? date('Y-m-01')));
$dateTo = trim((string) ($_GET['date_to'] ?? date('Y-m-d')));

$summary = $financeService->summary($dateFrom, $dateTo);
$incomeItems = $financeService->incomeByCategory($dateFrom, $dateTo);
$expenseItems = $financeService->expenseByCategory($dateFrom, $dateTo);
$methodBreakdown = $financeService->paymentMethodBreakdown($dateFrom, $dateTo);
$outstandingClass = $financeService->outstandingByClass();
$trend = $financeService->monthlyTrend((int) date('Y', strtotime($dateFrom)));
$transactions = $financeService->transactionsFeed($dateFrom, $dateTo, 200);

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$maxChart = max(1, max(array_merge($trend['revenue'], $trend['expenses'])));
$maxOutstanding = $outstandingClass ? max($outstandingClass) : 1;

$health = $summary['net_income'] > 5000000 ? 'Excellent' : ($summary['net_income'] > 1000000 ? 'Good' : ($summary['net_income'] > 0 ? 'Fair' : 'Needs Attention'));

$settings = (new SettingsModel())->all();
$schoolName = $settings['school.name']['value'] ?? 'School Management System';

function frMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<div class="financial-reports-page">
    <section class="fr-hero"><div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Financial Reports</div><span class="fr-kicker"><i class="fa-solid fa-chart-pie"></i> Executive Finance</span><h3 class="mt-3 mb-2">Financial Reports</h3><p class="text-muted mb-0">Real revenue, expense, and outstanding fee analysis for the selected date range.</p></section>

    <section class="fr-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Date From</label><input type="date" class="form-control" name="date_from" value="<?php echo sms_e($dateFrom); ?>"></div>
            <div class="col-md-4"><label class="form-label">Date To</label><input type="date" class="form-control" name="date_to" value="<?php echo sms_e($dateTo); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn fr-btn w-100"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate</button></div>
            <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="financial-reports.php">Reset</a></div>
        </form>
    </section>

    <section class="row g-3 mb-4">
        <?php $cards = [
            ['Total Revenue', frMoney($summary['revenue']), 'fa-sack-dollar', 'success'],
            ['Total Expenses', frMoney($summary['expenses']), 'fa-file-invoice-dollar', 'danger'],
            ['Net Income', frMoney($summary['net_income']), 'fa-chart-line', 'blue'],
            ['Outstanding Fees', frMoney($summary['outstanding']), 'fa-scale-unbalanced', 'warning'],
            ['Students Fully Paid', number_format($summary['students_paid']), 'fa-user-check', 'success'],
            ['Students With Balances', number_format($summary['students_outstanding']), 'fa-user-clock', 'warning'],
        ]; foreach ($cards as $c): ?>
            <div class="col-sm-6 col-xl-3"><div class="kpi-card"><span class="kpi-icon <?php echo $c[3]; ?>"><i class="fa-solid <?php echo $c[2]; ?>"></i></span><h4><?php echo sms_e($c[1]); ?></h4><p class="text-muted mb-0"><?php echo sms_e($c[0]); ?></p></div></div>
        <?php endforeach; ?>
        <div class="col-sm-6 col-xl-3"><div class="kpi-card"><span class="kpi-icon success"><i class="fa-solid fa-heart-pulse"></i></span><h4><span class="health-badge"><?php echo sms_e($health); ?></span></h4><p class="text-muted mb-0">Financial Health</p></div></div>
    </section>

    <section class="row g-4">
        <div class="col-xl-6"><div class="fr-card"><h4 class="mb-3">Income by Category</h4><div class="mini-list">
            <?php foreach ($incomeItems as $label => $value): ?><div class="mini-item"><span><?php echo sms_e($label); ?></span><strong><?php echo frMoney($value); ?></strong></div><?php endforeach; ?>
            <?php if (!$incomeItems): ?><p class="text-muted mb-0">No income recorded in this range.</p><?php endif; ?>
            <?php if ($incomeItems): ?><div class="mini-item"><span>Total Income</span><strong><?php echo frMoney(array_sum($incomeItems)); ?></strong></div><?php endif; ?>
        </div></div></div>
        <div class="col-xl-6"><div class="fr-card"><h4 class="mb-3">Expense by Category</h4><div class="mini-list">
            <?php foreach ($expenseItems as $label => $value): ?><div class="mini-item"><span><?php echo sms_e($label); ?></span><strong><?php echo frMoney($value); ?></strong></div><?php endforeach; ?>
            <?php if (!$expenseItems): ?><p class="text-muted mb-0">No approved expenses in this range.</p><?php endif; ?>
            <?php if ($expenseItems): ?><div class="mini-item"><span>Total Expenses</span><strong><?php echo frMoney(array_sum($expenseItems)); ?></strong></div><?php endif; ?>
        </div></div></div>
    </section>

    <section class="row g-4">
        <div class="col-xl-8"><div class="fr-card"><h4 class="mb-1">Revenue vs Expense Chart</h4><p class="text-muted mb-0">Monthly comparison for <?php echo date('Y', strtotime($dateFrom)); ?>.</p>
            <div class="chart-wrap"><?php foreach ($months as $i => $m): ?><div class="chart-bar"><div class="bar-pair"><div class="bar-fill bar-income" style="height:<?php echo round(($trend['revenue'][$i] / $maxChart) * 100); ?>%" title="Revenue: <?php echo frMoney($trend['revenue'][$i]); ?>"></div><div class="bar-fill bar-expense" style="height:<?php echo round(($trend['expenses'][$i] / $maxChart) * 100); ?>%" title="Expenses: <?php echo frMoney($trend['expenses'][$i]); ?>"></div></div><span class="bar-label"><?php echo $m; ?></span></div><?php endforeach; ?></div>
        </div></div>
        <div class="col-xl-4"><div class="fr-card"><h4 class="mb-3">Income by Payment Method</h4><div class="mini-list">
            <?php foreach ($methodBreakdown as $method => $value): ?><div class="mini-item"><span><?php echo sms_e(ucfirst(str_replace('_', ' ', $method))); ?></span><strong><?php echo frMoney($value); ?></strong></div><?php endforeach; ?>
            <?php if (!$methodBreakdown): ?><p class="text-muted mb-0">No payments in this range.</p><?php endif; ?>
        </div></div></div>
    </section>

    <section class="row g-4">
        <div class="col-xl-12"><div class="fr-card"><h4 class="mb-1">Outstanding Fees Analysis</h4><p class="text-muted mb-3">Outstanding amount by class (all-time, not date-range scoped).</p>
            <div class="chart-wrap"><?php foreach ($outstandingClass as $class => $value): ?><div class="chart-bar"><div class="bar-fill bar-expense" style="height:<?php echo round(($value / $maxOutstanding) * 100); ?>%"></div><span class="bar-label"><?php echo sms_e($class); ?></span></div><?php endforeach; ?></div>
            <?php if (!$outstandingClass): ?><p class="text-muted mt-3 mb-0">No outstanding balances recorded.</p><?php endif; ?>
            <div class="mini-list mt-3"><div class="mini-item"><span>Total Outstanding Amount</span><strong><?php echo frMoney($summary['outstanding']); ?></strong></div></div>
        </div></div>
    </section>

    <section class="table-card"><div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Recent Financial Transactions</h4><p class="text-muted mb-0"><?php echo count($transactions); ?> transaction(s) in range.</p></div><div class="d-flex gap-2"><a class="btn fr-btn" href="financial-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'pdf']))); ?>"><i class="fa-solid fa-file-pdf me-2"></i>PDF</a><a class="btn fr-btn" href="financial-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'excel']))); ?>"><i class="fa-solid fa-file-excel me-2"></i>Excel</a><a class="btn fr-btn" href="financial-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv me-2"></i>CSV</a><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button></div></div>
        <div class="table-scroll"><table class="table fr-table align-middle"><thead><tr><th>Date</th><th>Transaction ID</th><th>Description</th><th>Category</th><th>Amount</th><th>Type</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($transactions as $t): ?>
                <tr><td><?php echo sms_e($t['date']); ?></td><td><?php echo sms_e($t['id']); ?></td><td><?php echo sms_e($t['description']); ?></td><td><?php echo sms_e($t['category']); ?></td><td><?php echo frMoney($t['amount']); ?></td><td><?php echo sms_e($t['type']); ?></td><td><span class="status-badge status-<?php echo sms_e(strtolower($t['status'])); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e($t['status']); ?></span></td></tr>
            <?php endforeach; ?>
            <?php if (!$transactions): ?><tr><td colspan="7" class="text-center text-muted fw-bold py-4">No transactions found.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
