<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

$payrollService = new PayrollService();
require_once('includes/header.php');
require_once('includes/payroll-styles.php');

$runs = $payrollService->listPayrollRuns();
$staffList = $payrollService->staffForSelect();
$months = PayrollService::months();
$statuses = ['draft' => 'Draft', 'generated' => 'Generated', 'paid' => 'Paid', 'cancelled' => 'Cancelled'];

$filterRun = trim((string) ($_GET['payroll_run_id'] ?? ''));
$filterStaff = trim((string) ($_GET['staff_id'] ?? ''));
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$filterSearch = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $payrollService->listPayslips(['payroll_run_id' => $filterRun, 'staff_id' => $filterStaff, 'status' => $filterStatus, 'search' => $filterSearch], $page, 10);

function prMoney4($amount) { return '₦' . number_format((float) $amount, 2); }
function prPayslipQuery(array $overrides = []): string { return 'payslips.php?' . http_build_query(array_merge($_GET, $overrides)); }
?>
<div class="payroll-page">
    <section class="pr-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payroll Management <i class="fa-solid fa-chevron-right mx-2"></i> Payslips</div>
        <span class="pr-kicker"><i class="fa-solid fa-file-invoice"></i> Payroll Administration</span>
        <h3 class="mt-3 mb-2">Payslips</h3>
        <p class="text-muted mb-0">Generate a payroll run for a month, then view, print, and pay each staff member's payslip.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="notice is-visible <?php echo $type === 'error' ? 'error' : 'success'; ?>"><i class="fa-solid fa-circle-info"></i><span><?php echo sms_e($message); ?></span></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-calendar-days"></i></span><h4><?php echo count($runs); ?></h4><p class="text-muted mb-0">Payroll Runs</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-file-invoice"></i></span><h4><?php echo (int) $result['meta']['total']; ?></h4><p class="text-muted mb-0">Payslips (filtered)</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-users"></i></span><h4><?php echo count($staffList); ?></h4><p class="text-muted mb-0">Active Staff</p></div></div>
    </section>

    <section class="pr-card">
        <h4 class="mb-3">Generate Payroll Run</h4>
        <p class="text-muted">Select a month and year to create (or reopen) a payroll run, then generate payslips for every staff member with an active salary structure.</p>
        <form method="post" action="payroll-generate.php" class="row g-3">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <div class="col-md-4"><label class="form-label">Month</label><select class="form-select" name="period_month" style="padding-left:12px;" required><?php foreach ($months as $num => $label): ?><option value="<?php echo $num; ?>" <?php echo (int) date('n') === $num ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Year</label><input type="number" class="form-control" name="period_year" style="padding-left:12px;" value="<?php echo date('Y'); ?>" required></div>
            <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn pr-btn w-100"><i class="fa-solid fa-gears me-2"></i>Generate Payslips</button></div>
        </form>
    </section>

    <section class="table-card">
        <div class="toolbar"><h4 class="mb-1">Payroll Runs</h4><p class="text-muted mb-0">Each run covers one calendar month.</p></div>
        <div class="table-scroll">
            <table class="table pr-table align-middle">
                <thead><tr><th>Period</th><th>Status</th><th>Payslips</th><th>Total Net Pay</th><th>Processed</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($runs as $run): ?>
                        <tr>
                            <td><?php echo sms_e(PayrollService::monthName((int) $run['period_month']) . ' ' . $run['period_year']); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($run['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($run['status'])); ?></span></td>
                            <td><?php echo (int) $run['payslip_count']; ?></td>
                            <td><?php echo prMoney4($run['total_net_pay']); ?></td>
                            <td><?php echo $run['processed_at'] ? sms_e(date('Y-m-d H:i', strtotime($run['processed_at']))) : '-'; ?></td>
                            <td><a class="btn btn-sm btn-outline-secondary" href="<?php echo sms_e(prPayslipQuery(['payroll_run_id' => $run['id']])); ?>"><i class="fa-solid fa-eye"></i> View Payslips</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$runs): ?><tr><td colspan="6" class="text-center text-muted fw-bold py-4">No payroll runs yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="pr-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Payroll Run</label><select class="form-select" name="payroll_run_id" style="padding-left:12px;"><option value="">All Runs</option><?php foreach ($runs as $run): ?><option value="<?php echo (int) $run['id']; ?>" <?php echo $filterRun === (string) $run['id'] ? 'selected' : ''; ?>><?php echo sms_e(PayrollService::monthName((int) $run['period_month']) . ' ' . $run['period_year']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Staff</label><select class="form-select" name="staff_id" style="padding-left:12px;"><option value="">All</option><?php foreach ($staffList as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $filterStaff === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['first_name'] . ' ' . $s['last_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><option value="">All</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $filterStatus === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Search</label><input class="form-control" name="search" style="padding-left:12px;" placeholder="Staff name" value="<?php echo sms_e($filterSearch); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn pr-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
        </form>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Payslips</h4><p class="text-muted mb-0"><?php echo (int) $result['meta']['total']; ?> record(s) found.</p></div><a class="btn btn-outline-secondary" href="payslips.php"><i class="fa-solid fa-rotate-left me-2"></i>Reset Filters</a></div>
        <div class="table-scroll">
            <table class="table pr-table align-middle">
                <thead><tr><th>Staff</th><th>Period</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($result['data'] as $row): ?>
                        <tr>
                            <td><?php echo sms_e($row['first_name'] . ' ' . $row['last_name']); ?><br><small class="text-muted"><?php echo sms_e($row['staff_no']); ?></small></td>
                            <td><?php echo sms_e(PayrollService::monthName((int) $row['period_month']) . ' ' . $row['period_year']); ?></td>
                            <td><?php echo prMoney4($row['basic_salary']); ?></td>
                            <td><?php echo prMoney4($row['total_allowances']); ?></td>
                            <td><?php echo prMoney4($row['total_deductions']); ?></td>
                            <td><strong><?php echo prMoney4($row['net_pay']); ?></strong></td>
                            <td><span class="status-badge status-<?php echo sms_e($row['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($row['status'])); ?></span></td>
                            <td>
                                <div class="action-row">
                                    <a class="btn btn-sm btn-outline-secondary" href="payslip-print.php?id=<?php echo (int) $row['id']; ?>" target="_blank"><i class="fa-solid fa-print"></i> View</a>
                                    <?php if ($row['status'] === 'generated'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success pay-btn" data-id="<?php echo (int) $row['id']; ?>" data-name="<?php echo sms_e($row['first_name'] . ' ' . $row['last_name']); ?>" data-net="<?php echo sms_e((string) $row['net_pay']); ?>"><i class="fa-solid fa-money-bill"></i> Pay</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$result['data']): ?><tr><td colspan="8" class="text-center text-muted fw-bold py-4">No payslips found. Generate a payroll run above.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($result['meta']['last_page'] > 1): ?>
        <div class="toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted fw-bold"><?php echo (int) $result['meta']['total']; ?> record(s) &middot; page <?php echo (int) $result['meta']['page']; ?> of <?php echo (int) $result['meta']['last_page']; ?></span>
            <div class="d-flex gap-2 flex-wrap">
                <?php for ($p = 1; $p <= $result['meta']['last_page']; $p++): ?>
                    <a class="btn btn-sm <?php echo $p === (int) $result['meta']['page'] ? 'pr-btn' : 'btn-outline-secondary'; ?>" href="<?php echo sms_e(prPayslipQuery(['page' => $p])); ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="payslip-pay.php">
                <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white">Record Payment</h5><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="payId">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <p class="fw-bold" id="payDetails"></p>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Payment Method</label><select class="form-select" name="payment_method" style="padding-left:12px;"><option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option><option value="cheque">Cheque</option></select></div>
                        <div class="col-md-6"><label class="form-label">Reference No.</label><input class="form-control" name="reference_no" style="padding-left:12px;" placeholder="Optional"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" style="padding-left:12px;"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn pr-btn">Confirm Payment</button></div>
            </form>
        </div>
    </div>
</div>

</div>
</div>
<script data-cfasync="false" type="text/javascript">
(function(){
    var modalEl = document.getElementById('payModal');
    function getModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }
    document.querySelectorAll('.pay-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.getElementById('payId').value = btn.dataset.id;
            document.getElementById('payDetails').textContent = 'Pay ' + btn.dataset.name + ' ₦' + Number(btn.dataset.net).toLocaleString(undefined, {minimumFractionDigits:2});
            var modal = getModal(); if (modal) { modal.show(); }
        });
    });
})();
</script>
<?php require_once('includes/footer.php'); ?>
