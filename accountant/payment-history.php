<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\FinanceService;

$financeService = new FinanceService();
require_once('includes/header.php');
require_once('includes/receipt-styles.php');

$sessions = $financeService->sessionsForSelect();
$terms = $financeService->termsForSelect();
$classes = $financeService->classesForSelect();

$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$termId = trim((string) ($_GET['term_id'] ?? ''));
$classId = trim((string) ($_GET['class_id'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));

$all = $financeService->paymentHistory([
    'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'date_from' => $dateFrom, 'date_to' => $dateTo,
], 1000);
$transactions = $search === '' ? $all : array_values(array_filter($all, static function ($t) use ($search) {
    return stripos($t['transaction_no'] . ' ' . $t['registration_no'] . ' ' . $t['first_name'] . ' ' . $t['last_name'], $search) !== false;
}));

$today = date('Y-m-d');
$todayCollections = array_sum(array_map(static fn ($t) => (float) $t['amount'], array_filter($all, static fn ($t) => substr((string) $t['payment_date'], 0, 10) === $today && $t['status'] === 'paid')));
$monthlyTotal = array_sum(array_map(static fn ($t) => (float) $t['amount'], array_filter($all, static fn ($t) => substr((string) $t['payment_date'], 0, 7) === date('Y-m') && $t['status'] === 'paid')));
$outstandingBalance = array_sum(array_map(static fn ($t) => (float) ($t['balance'] ?? 0), $all));

function phMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<div class="payment-history-page">
    <section class="rc-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payment History</div>
        <span class="rc-kicker"><i class="fa-solid fa-clock-rotate-left"></i> Payment Audit Trail</span>
        <h3 class="mt-3 mb-2">Payment History</h3>
        <p class="text-muted mb-0">Search and review the full record of student fee payments.</p>
    </section>

    <section class="rc-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Transaction No., Reg No., or Name</label><input type="text" class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Search"></div>
            <div class="col-md-2"><label class="form-label">Session</label><select class="form-select" name="session_id"><option value="">All</option><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Term</label><select class="form-select" name="term_id"><option value="">All</option><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (string) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Class</label><select class="form-select" name="class_id"><option value="">All</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo $classId === (string) $c['id'] ? 'selected' : ''; ?>><?php echo sms_e($c['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Date Range</label><div class="d-flex gap-2"><input type="date" class="form-control" name="date_from" value="<?php echo sms_e($dateFrom); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo sms_e($dateTo); ?>"></div></div>
            <div class="col-md-4"><button type="submit" class="btn rc-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
            <div class="col-md-4"><a class="btn btn-outline-secondary w-100" href="payment-history.php"><i class="fa-solid fa-rotate-left me-2"></i>Reset</a></div>
        </form>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-receipt"></i></span><h4><?php echo number_format(count($all)); ?></h4><p class="text-muted mb-0">Total Payments</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo phMoney($todayCollections); ?></h4><p class="text-muted mb-0">Today's Collections</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-chart-line"></i></span><h4><?php echo phMoney($monthlyTotal); ?></h4><p class="text-muted mb-0">Monthly Total</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-scale-unbalanced"></i></span><h4><?php echo phMoney($outstandingBalance); ?></h4><p class="text-muted mb-0">Invoice Balance (Filtered)</p></div></div>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Transaction List</h4><p class="text-muted mb-0"><?php echo count($transactions); ?> transaction(s) found.</p></div><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button></div>
        <div class="table-scroll">
            <table class="table receipt-table align-middle">
                <thead><tr><th>Txn No.</th><th>Date</th><th>Reg No.</th><th>Student</th><th>Class</th><th>Type</th><th>Amount</th><th>Method</th><th>Status</th><th>Receipt</th></tr></thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo sms_e($t['transaction_no']); ?></td>
                            <td><?php echo sms_e(substr((string) $t['payment_date'], 0, 16)); ?></td>
                            <td><?php echo sms_e($t['registration_no']); ?></td>
                            <td><?php echo sms_e(trim($t['first_name'] . ' ' . $t['last_name'])); ?></td>
                            <td><?php echo sms_e($t['class_name'] ?? '-'); ?></td>
                            <td><?php echo sms_e($t['payment_type']); ?></td>
                            <td><?php echo phMoney($t['amount']); ?></td>
                            <td><?php echo sms_e(ucfirst(str_replace('_', ' ', $t['payment_method']))); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($t['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($t['status'])); ?></span></td>
                            <td><?php if (!empty($t['receipt_no'])): ?><a class="btn btn-sm btn-outline-primary" href="receipt-print.php?payment_id=<?php echo (int) $t['id']; ?>" target="_blank"><?php echo sms_e($t['receipt_no']); ?></a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$transactions): ?><tr><td colspan="10" class="text-center text-muted fw-bold py-4">No payment records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
