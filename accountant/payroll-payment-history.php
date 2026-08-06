<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

$payrollService = new PayrollService();
require_once('includes/header.php');
require_once('includes/payroll-styles.php');

$staffList = $payrollService->staffForSelect();
$methods = ['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'cheque' => 'Cheque'];

$filterStaff = trim((string) ($_GET['staff_id'] ?? ''));
$filterMethod = trim((string) ($_GET['payment_method'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$filterSearch = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $payrollService->listPaymentHistory([
    'staff_id' => $filterStaff, 'payment_method' => $filterMethod, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $filterSearch,
], $page, 15);

$totalPaid = array_sum(array_column($result['data'], 'amount'));

function prMoney5($amount) { return '₦' . number_format((float) $amount, 2); }
function prPayHistQuery(array $overrides = []): string { return 'payroll-payment-history.php?' . http_build_query(array_merge($_GET, $overrides)); }
?>
<div class="payroll-page">
    <section class="pr-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payroll Management <i class="fa-solid fa-chevron-right mx-2"></i> Payment History</div>
        <span class="pr-kicker"><i class="fa-solid fa-clock-rotate-left"></i> Payroll Administration</span>
        <h3 class="mt-3 mb-2">Payment History</h3>
        <p class="text-muted mb-0">Complete real log of every salary payment made, with method, reference, and who processed it.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="notice is-visible <?php echo $type === 'error' ? 'error' : 'success'; ?>"><i class="fa-solid fa-circle-info"></i><span><?php echo sms_e($message); ?></span></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-receipt"></i></span><h4><?php echo (int) $result['meta']['total']; ?></h4><p class="text-muted mb-0">Payments (filtered)</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo prMoney5($totalPaid); ?></h4><p class="text-muted mb-0">Total (this page)</p></div></div>
    </section>

    <section class="pr-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Staff</label><select class="form-select" name="staff_id" style="padding-left:12px;"><option value="">All</option><?php foreach ($staffList as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $filterStaff === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['first_name'] . ' ' . $s['last_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Method</label><select class="form-select" name="payment_method" style="padding-left:12px;"><option value="">All</option><?php foreach ($methods as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $filterMethod === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">From</label><input type="date" class="form-control" name="date_from" style="padding-left:12px;" value="<?php echo sms_e($dateFrom); ?>"></div>
            <div class="col-md-2"><label class="form-label">To</label><input type="date" class="form-control" name="date_to" style="padding-left:12px;" value="<?php echo sms_e($dateTo); ?>"></div>
            <div class="col-md-2"><label class="form-label">Search</label><input class="form-control" name="search" style="padding-left:12px;" placeholder="Staff or reference" value="<?php echo sms_e($filterSearch); ?>"></div>
            <div class="col-md-1"><button type="submit" class="btn pr-btn w-100"><i class="fa-solid fa-search"></i></button></div>
        </form>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Payment Log</h4><p class="text-muted mb-0"><?php echo (int) $result['meta']['total']; ?> record(s) found.</p></div><div class="bulk-actions"><a class="btn btn-outline-secondary" href="payroll-payment-history-export.php?<?php echo sms_e(http_build_query($_GET)); ?>"><i class="fa-solid fa-file-csv me-2"></i>CSV</a><a class="btn btn-outline-secondary" href="payroll-payment-history.php"><i class="fa-solid fa-rotate-left me-2"></i>Reset</a></div></div>
        <div class="table-scroll">
            <table class="table pr-table align-middle">
                <thead><tr><th>Date Paid</th><th>Staff</th><th>Period</th><th>Amount</th><th>Method</th><th>Reference</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($result['data'] as $row): ?>
                        <tr>
                            <td><?php echo sms_e(date('Y-m-d H:i', strtotime($row['paid_at']))); ?></td>
                            <td><?php echo sms_e($row['first_name'] . ' ' . $row['last_name']); ?><br><small class="text-muted"><?php echo sms_e($row['staff_no']); ?></small></td>
                            <td><?php echo sms_e(PayrollService::monthName((int) $row['period_month']) . ' ' . $row['period_year']); ?></td>
                            <td><strong><?php echo prMoney5($row['amount']); ?></strong></td>
                            <td><?php echo sms_e(ucfirst(str_replace('_', ' ', $row['payment_method']))); ?></td>
                            <td><?php echo sms_e($row['reference_no'] ?? '-'); ?></td>
                            <td><a class="btn btn-sm btn-outline-secondary" href="payslip-print.php?id=<?php echo (int) $row['payslip_id']; ?>" target="_blank"><i class="fa-solid fa-print"></i> Payslip</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$result['data']): ?><tr><td colspan="7" class="text-center text-muted fw-bold py-4">No payments recorded yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($result['meta']['last_page'] > 1): ?>
        <div class="toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted fw-bold"><?php echo (int) $result['meta']['total']; ?> record(s) &middot; page <?php echo (int) $result['meta']['page']; ?> of <?php echo (int) $result['meta']['last_page']; ?></span>
            <div class="d-flex gap-2 flex-wrap">
                <?php for ($p = 1; $p <= $result['meta']['last_page']; $p++): ?>
                    <a class="btn btn-sm <?php echo $p === (int) $result['meta']['page'] ? 'pr-btn' : 'btn-outline-secondary'; ?>" href="<?php echo sms_e(prPayHistQuery(['page' => $p])); ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
