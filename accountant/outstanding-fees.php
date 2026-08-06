<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\FinanceService;

$financeService = new FinanceService();
require_once('includes/header.php');
require_once('includes/outstanding-styles.php');

$sessions = $financeService->sessionsForSelect();
$terms = $financeService->termsForSelect();
$classes = $financeService->classesForSelect();

$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$termId = trim((string) ($_GET['term_id'] ?? ''));
$classId = trim((string) ($_GET['class_id'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));

$all = $financeService->listOutstanding(['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId], 2000);
$filtered = $search === '' ? $all : array_values(array_filter($all, static function ($row) use ($search) {
    return stripos($row['registration_no'] . ' ' . $row['first_name'] . ' ' . $row['last_name'], $search) !== false;
}));

$balances = array_map(static fn ($r) => (float) $r['balance'], $filtered);
$totalOutstanding = array_sum($balances);
$partiallyPaid = count(array_filter($filtered, static fn ($r) => $r['status'] === 'partial'));
$fullyUnpaid = count(array_filter($filtered, static fn ($r) => $r['status'] === 'unpaid'));

$classBalances = $financeService->outstandingByClass();
$maxClassBalance = $classBalances ? max($classBalances) : 1;

function ofMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<div class="outstanding-page">
    <section class="out-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Outstanding Fees</div>
        <span class="out-kicker"><i class="fa-solid fa-scale-unbalanced"></i> Outstanding Fees</span>
        <h3 class="mt-3 mb-2">Outstanding Fees</h3>
        <p class="text-muted mb-0">View and manage students with outstanding fee balances.</p>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo ofMoney($totalOutstanding); ?></h4><p class="text-muted mb-0">Total Outstanding Amount</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-users"></i></span><h4><?php echo count($filtered); ?> Students</h4><p class="text-muted mb-0">Students with Outstanding Fees</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-user-clock"></i></span><h4><?php echo $partiallyPaid; ?> Students</h4><p class="text-muted mb-0">Partially Paid Students</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-user-xmark"></i></span><h4><?php echo $fullyUnpaid; ?> Students</h4><p class="text-muted mb-0">Fully Unpaid Students</p></div></div>
    </section>

    <section class="out-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Academic Session</label><select class="form-select" name="session_id" style="padding-left:12px;"><option value="">All Sessions</option><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Term</label><select class="form-select" name="term_id" style="padding-left:12px;"><option value="">All Terms</option><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (string) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="class_id" style="padding-left:12px;"><option value="">All Classes</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo $classId === (string) $c['id'] ? 'selected' : ''; ?>><?php echo sms_e($c['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Search Student</label><input class="form-control" name="search" style="padding-left:12px;" placeholder="Name or reg no" value="<?php echo sms_e($search); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn out-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
            <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="outstanding-fees.php"><i class="fa-solid fa-rotate-left me-2"></i>Reset</a></div>
        </form>
    </section>

    <section class="row g-4">
        <div class="col-xl-5"><div class="out-card"><h4 class="mb-3">Outstanding Balance Analysis</h4><div class="row g-3">
            <div class="col-6"><strong>Highest Outstanding</strong><h5><?php echo ofMoney($balances ? max($balances) : 0); ?></h5></div>
            <div class="col-6"><strong>Lowest Outstanding</strong><h5><?php echo ofMoney($balances ? min($balances) : 0); ?></h5></div>
            <div class="col-6"><strong>Average Outstanding</strong><h5><?php echo ofMoney($balances ? $totalOutstanding / count($balances) : 0); ?></h5></div>
            <div class="col-6"><strong>Total Outstanding</strong><h5><?php echo ofMoney($totalOutstanding); ?></h5></div>
        </div></div></div>
        <div class="col-xl-7"><div class="out-card"><h4 class="mb-1">Outstanding Fees by Class</h4><p class="text-muted mb-0">Real balance distribution by class.</p>
            <div class="chart-wrap"><?php foreach ($classBalances as $label => $value): ?><div class="chart-bar" title="<?php echo sms_e($label . ': ' . ofMoney($value)); ?>"><div class="bar-fill" style="height:<?php echo round(($value / $maxClassBalance) * 100); ?>%"></div><span class="bar-label"><?php echo sms_e($label); ?></span></div><?php endforeach; ?></div>
            <?php if (!$classBalances): ?><p class="text-muted mt-3 mb-0">No outstanding balances recorded yet.</p><?php endif; ?>
        </div></div>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Outstanding Fee Records</h4><p class="text-muted mb-0"><?php echo count($filtered); ?> record(s) found.</p></div><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button></div>
        <div class="table-scroll">
            <table class="table out-table align-middle">
                <thead><tr><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Session</th><th>Term</th><th>Total Fees</th><th>Amount Paid</th><th>Outstanding Balance</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($filtered as $row): ?>
                        <tr>
                            <td><?php echo sms_e($row['registration_no']); ?></td>
                            <td><?php echo sms_e(trim($row['first_name'] . ' ' . $row['last_name'])); ?></td>
                            <td><?php echo sms_e($row['class_name']); ?></td>
                            <td><?php echo sms_e($row['session_name']); ?></td>
                            <td><?php echo sms_e($row['term_name']); ?></td>
                            <td><?php echo ofMoney($row['total_amount']); ?></td>
                            <td><?php echo ofMoney($row['amount_paid']); ?></td>
                            <td><?php echo ofMoney($row['balance']); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($row['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($row['status'])); ?></span></td>
                            <td><div class="row-actions"><a class="btn btn-sm btn-outline-success" href="fee-collection.php?q=<?php echo urlencode($row['registration_no']); ?>&session_id=<?php echo (int) $row['session_id']; ?>&term_id=<?php echo (int) $row['term_id']; ?>"><i class="fa-solid fa-money-bill-transfer"></i> Collect</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$filtered): ?><tr><td colspan="10" class="text-center text-muted fw-bold py-4">No outstanding records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
