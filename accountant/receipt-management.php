<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\FinanceService;

$financeService = new FinanceService();
require_once('includes/header.php');
require_once('includes/receipt-styles.php');

$search = trim((string) ($_GET['search'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));

$all = $financeService->listReceipts(['date_from' => $dateFrom, 'date_to' => $dateTo], 1000);
$receipts = $search === '' ? $all : array_values(array_filter($all, static function ($r) use ($search) {
    return stripos($r['receipt_no'] . ' ' . $r['registration_no'] . ' ' . $r['first_name'] . ' ' . $r['last_name'], $search) !== false;
}));

$today = date('Y-m-d');
$todayReceipts = count(array_filter($all, static fn ($r) => substr((string) $r['issued_at'], 0, 10) === $today));
$todayCollection = array_sum(array_map(static fn ($r) => (float) $r['amount'], array_filter($all, static fn ($r) => substr((string) $r['issued_at'], 0, 10) === $today && $r['status'] === 'paid')));
$monthCollection = array_sum(array_map(static fn ($r) => (float) $r['amount'], array_filter($all, static fn ($r) => substr((string) $r['issued_at'], 0, 7) === date('Y-m') && $r['status'] === 'paid')));

function rcMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<div class="receipt-page">
    <section class="rc-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Receipt Management</div>
        <span class="rc-kicker"><i class="fa-solid fa-receipt"></i> Receipt Archive</span>
        <h3 class="mt-3 mb-2">Receipt Management</h3>
        <p class="text-muted mb-0">View, search, print, and void student payment receipts.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="rc-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Receipt No., Reg No., or Student Name</label><input type="text" class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Search receipts"></div>
            <div class="col-md-3"><label class="form-label">Date From</label><input type="date" class="form-control" name="date_from" value="<?php echo sms_e($dateFrom); ?>"></div>
            <div class="col-md-3"><label class="form-label">Date To</label><input type="date" class="form-control" name="date_to" value="<?php echo sms_e($dateTo); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn rc-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
        </form>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-receipt"></i></span><h4><?php echo number_format(count($all)); ?></h4><p class="text-muted mb-0">Total Receipts</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-calendar-day"></i></span><h4><?php echo number_format($todayReceipts); ?></h4><p class="text-muted mb-0">Today's Receipts</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo rcMoney($todayCollection); ?></h4><p class="text-muted mb-0">Today's Collection</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-chart-line"></i></span><h4><?php echo rcMoney($monthCollection); ?></h4><p class="text-muted mb-0">Monthly Collection</p></div></div>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Receipt List</h4><p class="text-muted mb-0"><?php echo count($receipts); ?> receipt(s) found.</p></div><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print List</button></div>
        <div class="table-scroll">
            <table class="table receipt-table align-middle">
                <thead><tr><th>Receipt No.</th><th>Date</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Amount Paid</th><th>Payment Method</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($receipts as $r): ?>
                        <tr>
                            <td><?php echo sms_e($r['receipt_no']); ?></td>
                            <td><?php echo sms_e(substr((string) $r['issued_at'], 0, 16)); ?></td>
                            <td><?php echo sms_e($r['registration_no']); ?></td>
                            <td><?php echo sms_e(trim($r['first_name'] . ' ' . $r['last_name'])); ?></td>
                            <td><?php echo sms_e($r['class_name'] ?? '-'); ?></td>
                            <td><?php echo rcMoney($r['amount']); ?></td>
                            <td><?php echo sms_e(ucfirst(str_replace('_', ' ', $r['payment_method']))); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($r['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($r['status'])); ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="receipt-print.php?id=<?php echo (int) $r['id']; ?>" target="_blank"><i class="fa-solid fa-eye"></i> View</a>
                                    <a class="btn btn-sm btn-outline-dark" href="receipt-print.php?id=<?php echo (int) $r['id']; ?>" target="_blank"><i class="fa-solid fa-print"></i> Print</a>
                                    <?php if ($r['status'] === 'paid'): ?>
                                    <form method="post" action="receipt-void.php" style="display:inline" onsubmit="return confirm('Void this receipt and reverse the payment?');">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-ban"></i> Void</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$receipts): ?><tr><td colspan="9" class="text-center text-muted fw-bold py-4">No receipt records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
