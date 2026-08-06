<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\FinanceService;

$financeService = new FinanceService();

$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');
require_once('includes/fee-collection-styles.php');

$sessions = $financeService->sessionsForSelect();
$terms = $financeService->termsForSelect();
$currentSessionId = $financeService->currentSessionId();
$currentTermId = $financeService->currentTermId();

$query = trim((string) ($_GET['q'] ?? ''));
$sessionId = (int) ($_GET['session_id'] ?? $currentSessionId);
$termId = (int) ($_GET['term_id'] ?? $currentTermId);

$summary = $query !== '' ? $financeService->findStudentForFees($query, $sessionId, $termId) : null;

$today = date('Y-m-d');
$paymentTypes = ['School Fees', 'Tuition Fee', 'Examination Fee', 'Transport Fee', 'Hostel Fee', 'Development Levy', 'Other'];
$methods = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'pos' => 'POS', 'online_payment' => 'Online Payment', 'cheque' => 'Cheque'];

$todayStats = $financeService->summary($today, $today);

function fcMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<div class="fee-collection-page">
    <section class="fc-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Fee Collection</div>
        <span class="fc-kicker"><i class="fa-solid fa-cash-register"></i> Finance Module</span>
        <h3 class="mt-3 mb-2">Fee Collection</h3>
        <p class="text-muted mb-0">Search a student, review their fee balance, and record payments with real receipts.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo fcMoney($todayStats['revenue']); ?></h4><p class="text-muted mb-0">Today's Collections</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon blue"><i class="fa-solid fa-user-check"></i></span><h4><?php echo number_format($todayStats['students_paid']); ?></h4><p class="text-muted mb-0">Students Fully Paid (All Time)</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon danger"><i class="fa-solid fa-scale-unbalanced"></i></span><h4><?php echo fcMoney($todayStats['outstanding']); ?></h4><p class="text-muted mb-0">Total Outstanding Fees</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon warning"><i class="fa-solid fa-user-clock"></i></span><h4><?php echo number_format($todayStats['students_outstanding']); ?></h4><p class="text-muted mb-0">Students With Balances</p></div></div>
    </section>

    <section class="fc-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Registration Number / Student Name</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-magnifying-glass"></i></span><input type="text" class="form-control" name="q" value="<?php echo sms_e($query); ?>" placeholder="Search by reg no or name" required></div></div>
            <div class="col-md-3"><label class="form-label">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" name="session_id"><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div></div>
            <div class="col-md-3"><label class="form-label">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" name="term_id"><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div></div>
            <div class="col-md-2"><button type="submit" class="btn fc-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
        </form>
    </section>

    <?php if ($query !== '' && $summary === null): ?>
        <div class="alert alert-danger">No matching student fee record found for "<?php echo sms_e($query); ?>", or the student has no fee structure defined for this class/session/term.</div>
    <?php endif; ?>

    <?php if ($summary !== null): ?>
        <?php
        $student = $summary['student'];
        $invoice = $summary['invoice'];
        $fullName = trim($student['first_name'] . ' ' . $student['last_name']);
        $photo = !empty($student['passport_path']) ? '../' . ltrim((string) $student['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
        $balance = $invoice ? (float) $invoice['balance'] : 0;
        ?>
        <section class="row g-4">
            <div class="col-xl-7"><div class="fc-card h-100">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <img src="<?php echo sms_e($photo); ?>" class="student-photo" alt="Student passport">
                    <div>
                        <h4 class="mb-1"><?php echo sms_e($fullName); ?></h4>
                        <p class="text-muted mb-0"><?php echo sms_e($student['registration_no']); ?> | <?php echo sms_e($summary['enrollment']['class_name'] ?? 'Unassigned'); ?> <?php echo sms_e($summary['enrollment']['section_name'] ?? ''); ?></p>
                        <p class="text-muted mb-0">Session: <?php echo sms_e($summary['session_name']); ?> | Term: <?php echo sms_e($summary['term_name']); ?></p>
                    </div>
                </div>
            </div></div>
            <div class="col-xl-5"><div class="fc-card h-100">
                <h4 class="mb-3">Fee Summary</h4>
                <?php if ($invoice === null): ?>
                    <p class="text-muted">No fee structure has been defined for this student's class in the selected session/term.</p>
                <?php else: ?>
                    <table class="table"><tbody>
                        <tr><td>Total Fees</td><td class="fw-bold"><?php echo fcMoney($invoice['total_amount']); ?></td></tr>
                        <tr><td>Amount Paid</td><td class="fw-bold"><?php echo fcMoney($invoice['amount_paid']); ?></td></tr>
                        <tr><td>Outstanding Balance</td><td><div class="balance-highlight <?php echo $balance <= 0 ? 'paid' : ''; ?>"><?php echo fcMoney($balance); ?></div></td></tr>
                    </tbody></table>
                <?php endif; ?>
            </div></div>
        </section>

        <?php if ($invoice !== null): ?>
            <section class="table-card"><div class="p-3"><h4 class="mb-1">Fee Breakdown</h4><p class="text-muted mb-0">Applicable fee items and payment status.</p></div>
                <div class="table-scroll"><table class="table fc-table align-middle"><thead><tr><th>Fee Item</th><th>Amount</th><th>Status</th></tr></thead><tbody>
                    <?php foreach ($summary['items'] as $item): ?>
                        <tr><td><?php echo sms_e($item['item_name']); ?></td><td><?php echo fcMoney($item['amount']); ?></td><td><span class="status-badge status-<?php echo sms_e($item['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($item['status'])); ?></span></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            </section>

            <?php if ($balance > 0): ?>
            <section class="fc-card">
                <h4 class="mb-3">Payment Collection Form</h4>
                <form method="post" action="fee-collect-payment.php" class="row g-3">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="student_id" value="<?php echo (int) $student['id']; ?>">
                    <input type="hidden" name="invoice_id" value="<?php echo (int) $invoice['id']; ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <div class="col-md-6"><label class="form-label">Payment Date</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar-day"></i></span><input type="date" class="form-control" name="payment_date" value="<?php echo sms_e($old['payment_date'] ?? $today); ?>" max="<?php echo sms_e($today); ?>" required></div><?php if (isset($errors['payment_date'])): ?><span class="field-error"><?php echo sms_e($errors['payment_date']); ?></span><?php endif; ?></div>
                    <div class="col-md-6"><label class="form-label">Payment Type</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-list"></i></span><select class="form-select" name="payment_type" required><?php foreach ($paymentTypes as $type): ?><option <?php echo ($old['payment_type'] ?? 'School Fees') === $type ? 'selected' : ''; ?>><?php echo sms_e($type); ?></option><?php endforeach; ?></select></div></div>
                    <div class="col-md-6"><label class="form-label">Amount Paying</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" min="1" max="<?php echo sms_e((string) $balance); ?>" step="0.01" class="form-control" name="amount" value="<?php echo sms_e($old['amount'] ?? ''); ?>" placeholder="Max <?php echo fcMoney($balance); ?>" required></div><?php if (isset($errors['amount'])): ?><span class="field-error"><?php echo sms_e($errors['amount']); ?></span><?php endif; ?></div>
                    <div class="col-md-6"><label class="form-label">Payment Method</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-credit-card"></i></span><select class="form-select" name="method" required><?php foreach ($methods as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo ($old['method'] ?? '') === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div><?php if (isset($errors['method'])): ?><span class="field-error"><?php echo sms_e($errors['method']); ?></span><?php endif; ?></div>
                    <div class="col-md-12"><label class="form-label">Transaction Reference</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-hashtag"></i></span><input type="text" class="form-control" name="reference" value="<?php echo sms_e($old['reference'] ?? ''); ?>" placeholder="Required for non-cash payments"></div><?php if (isset($errors['reference'])): ?><span class="field-error"><?php echo sms_e($errors['reference']); ?></span><?php endif; ?></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" placeholder="Optional payment notes"><?php echo sms_e($old['notes'] ?? ''); ?></textarea></div>
                    <div class="col-12"><div class="action-row"><button type="submit" class="btn fc-btn"><i class="fa-solid fa-cash-register me-2"></i>Collect Payment</button></div></div>
                </form>
            </section>
            <?php endif; ?>
        <?php endif; ?>

        <section class="table-card"><div class="p-3"><h4 class="mb-1">Recent Payment History</h4><p class="text-muted mb-0">Receipts and payment records for this student.</p></div>
            <div class="table-scroll"><table class="table fc-table align-middle"><thead><tr><th>Receipt No.</th><th>Date</th><th>Payment Type</th><th>Amount</th><th>Method</th><th>Action</th></tr></thead><tbody>
                <?php foreach ($summary['payments'] as $payment): ?>
                    <tr><td><?php echo sms_e($payment['receipt_no'] ?? '-'); ?></td><td><?php echo sms_e(substr($payment['payment_date'], 0, 10)); ?></td><td><?php echo sms_e($payment['payment_type']); ?></td><td><?php echo fcMoney($payment['amount']); ?></td><td><?php echo sms_e(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?></td><td><?php if (!empty($payment['receipt_no'])): ?><a class="btn btn-sm btn-outline-primary" href="receipt-print.php?payment_id=<?php echo (int) $payment['id']; ?>" target="_blank">View Receipt</a><?php endif; ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$summary['payments']): ?><tr><td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
    <?php endif; ?>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
