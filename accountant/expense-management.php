<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\FinanceService;

$financeService = new FinanceService();
$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');
require_once('includes/expense-styles.php');

$categories = ['Staff Salary', 'Electricity', 'Water Bill', 'Internet', 'Fuel', 'Transportation', 'Office Supplies', 'Examination Materials', 'Laboratory Equipment', 'Library', 'School Maintenance', 'Building Repairs', 'Cleaning Materials', 'Security', 'ICT Equipment', 'Sports', 'Furniture', 'Printing & Stationery', 'Miscellaneous'];
$methods = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'pos' => 'POS', 'cheque' => 'Cheque', 'online_payment' => 'Online Payment'];
$statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paid' => 'Paid'];
$today = date('Y-m-d');

$editingExpense = null;
if (($_GET['edit'] ?? '') !== '') {
    $editingExpense = $financeService->findExpense((int) $_GET['edit']);
}

$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$filterCategory = trim((string) ($_GET['category'] ?? ''));
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$filterSearch = trim((string) ($_GET['search'] ?? ''));

$allExpenses = $financeService->listExpenses(['date_from' => $dateFrom, 'date_to' => $dateTo, 'category' => $filterCategory, 'status' => $filterStatus], 1000);
$expenses = $filterSearch === '' ? $allExpenses : array_values(array_filter($allExpenses, static function ($e) use ($filterSearch) {
    return stripos($e['description'] . ' ' . $e['expense_no'], $filterSearch) !== false;
}));

$monthTotal = array_sum(array_map(static fn ($e) => (float) $e['amount'], array_filter($allExpenses, static fn ($e) => substr($e['expense_date'], 0, 7) === date('Y-m'))));
$todayTotal = array_sum(array_map(static fn ($e) => (float) $e['amount'], array_filter($allExpenses, static fn ($e) => $e['expense_date'] === $today)));
$sessionTotal = array_sum(array_map(static fn ($e) => (float) $e['amount'], $allExpenses));

$categoryTotals = [];
foreach ($allExpenses as $e) {
    $categoryTotals[$e['category']] = ($categoryTotals[$e['category']] ?? 0) + (float) $e['amount'];
}
arsort($categoryTotals);
$categoryTotals = array_slice($categoryTotals, 0, 8, true);

$trend = $financeService->monthlyTrend((int) date('Y'));
$monthlyExpenses = $trend['expenses'];
$maxMonthly = max(1, max($monthlyExpenses));
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function expMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<div class="expense-page">
    <section class="ex-hero"><div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Expense Management</div><span class="ex-kicker"><i class="fa-solid fa-file-invoice-dollar"></i> Expense Control</span><h3 class="mt-3 mb-2">Expense Management</h3><p class="text-muted mb-0">Record, monitor, and manage all school expenses.</p></section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-calendar-day"></i></span><h4><?php echo expMoney($todayTotal); ?></h4><p class="text-muted mb-0">Today's Expenses</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-calendar"></i></span><h4><?php echo expMoney($monthTotal); ?></h4><p class="text-muted mb-0">This Month's Expenses</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-chart-line"></i></span><h4><?php echo expMoney($sessionTotal); ?></h4><p class="text-muted mb-0">Total Expenses (Filtered)</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-list-check"></i></span><h4><?php echo count($allExpenses); ?> Expenses</h4><p class="text-muted mb-0">Total Expense Records</p></div></div>
    </section>

    <section class="ex-card">
        <h4 class="mb-3"><?php echo $editingExpense ? 'Edit Expense' : 'Add New Expense'; ?></h4>
        <form method="post" action="expense-store.php" class="row g-3" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <?php if ($editingExpense): ?><input type="hidden" name="id" value="<?php echo (int) $editingExpense['id']; ?>"><?php endif; ?>
            <div class="col-md-4"><label class="form-label">Expense Title</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-heading"></i></span><input type="text" class="form-control" name="title" required value="<?php echo sms_e($old['title'] ?? ($editingExpense['description'] ?? '')); ?>" placeholder="Purchase of Laboratory Equipment"></div><?php if (isset($errors['title'])): ?><span class="field-error"><?php echo sms_e($errors['title']); ?></span><?php endif; ?></div>
            <div class="col-md-4"><label class="form-label">Expense Category</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-tags"></i></span><input class="form-control" name="category" list="categoryList" required value="<?php echo sms_e($old['category'] ?? ($editingExpense['category'] ?? '')); ?>" placeholder="Select or type a category"><datalist id="categoryList"><?php foreach ($categories as $cat): ?><option value="<?php echo sms_e($cat); ?>"><?php endforeach; ?></datalist></div><?php if (isset($errors['category'])): ?><span class="field-error"><?php echo sms_e($errors['category']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Expense Amount</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" min="1" step="0.01" class="form-control" name="amount" required value="<?php echo sms_e($old['amount'] ?? ($editingExpense['amount'] ?? '')); ?>"></div><?php if (isset($errors['amount'])): ?><span class="field-error"><?php echo sms_e($errors['amount']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Payment Method</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-credit-card"></i></span><select class="form-select" name="method" required><?php foreach ($methods as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo ($old['method'] ?? ($editingExpense['payment_method'] ?? '')) === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div></div>
            <div class="col-md-3"><label class="form-label">Expense Date</label><input type="date" class="form-control" name="date" value="<?php echo sms_e($old['date'] ?? ($editingExpense['expense_date'] ?? $today)); ?>" style="padding-left:12px;" required><?php if (isset($errors['date'])): ?><span class="field-error"><?php echo sms_e($errors['date']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo ($old['status'] ?? ($editingExpense['status'] ?? 'draft')) === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Vendor / Supplier</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-store"></i></span><input type="text" class="form-control" name="vendor" value="<?php echo sms_e($old['vendor'] ?? ''); ?>" placeholder="ABC Stationery Ltd."></div></div>
            <div class="col-md-4"><label class="form-label">Upload Supporting Document</label><input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png" style="padding-left:12px;">
                <?php if ($editingExpense && !empty($editingExpense['attachment_path'])): ?><small class="text-muted d-block mt-1"><a href="../<?php echo sms_e(ltrim($editingExpense['attachment_path'], '/')); ?>" target="_blank">Current attachment</a> (upload a new file to replace)</small><?php else: ?><small class="text-muted">PDF, JPG, PNG accepted</small><?php endif; ?>
            </div>
            <div class="col-12"><div class="action-row"><button type="submit" class="btn ex-btn"><i class="fa-solid fa-floppy-disk me-2"></i><?php echo $editingExpense ? 'Update Expense' : 'Save Expense'; ?></button><a href="expense-management.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark me-2"></i>Cancel</a></div></div>
        </form>
    </section>

    <section class="ex-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Date Range</label><div class="d-flex gap-2"><input type="date" class="form-control" name="date_from" value="<?php echo sms_e($dateFrom); ?>" style="padding-left:12px;"><input type="date" class="form-control" name="date_to" value="<?php echo sms_e($dateTo); ?>" style="padding-left:12px;"></div></div>
            <div class="col-md-2"><label class="form-label">Category</label><select class="form-select" name="category" style="padding-left:12px;"><option value="">All</option><?php foreach ($categories as $category): ?><option <?php echo $filterCategory === $category ? 'selected' : ''; ?>><?php echo sms_e($category); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><option value="">All</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $filterStatus === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Search</label><input type="search" class="form-control" name="search" style="padding-left:12px;" value="<?php echo sms_e($filterSearch); ?>" placeholder="Title or expense no"></div>
            <div class="col-md-2"><button type="submit" class="btn ex-btn w-100">Search</button></div>
        </form>
    </section>

    <section class="row g-4">
        <div class="col-xl-7"><div class="ex-card"><h4 class="mb-1">Monthly Expenses</h4><p class="text-muted mb-0">Real spending trend for <?php echo date('Y'); ?>.</p><div class="chart-wrap"><?php foreach ($monthlyExpenses as $i => $value): ?><div class="chart-bar"><div class="bar-fill" style="height:<?php echo round(($value / $maxMonthly) * 100); ?>%"></div><span class="bar-label"><?php echo $months[$i]; ?></span></div><?php endforeach; ?></div></div></div>
        <div class="col-xl-5"><div class="ex-card"><h4 class="mb-3">Expense by Category</h4><div class="pie-list"><?php foreach ($categoryTotals as $cat => $value): ?><div class="pie-item"><strong><?php echo sms_e($cat); ?></strong><span><?php echo expMoney($value); ?></span></div><?php endforeach; ?><?php if (!$categoryTotals): ?><p class="text-muted mb-0">No expenses recorded yet.</p><?php endif; ?></div></div></div>
    </section>

    <section class="table-card"><div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Expense Records</h4><p class="text-muted mb-0"><?php echo count($expenses); ?> record(s) found.</p></div><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button></div>
        <div class="table-scroll"><table class="table expense-table align-middle"><thead><tr><th>Date</th><th>Expense No.</th><th>Title</th><th>Category</th><th>Amount</th><th>Payment Method</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?php echo sms_e($e['expense_date']); ?></td>
                    <td><?php echo sms_e($e['expense_no']); ?></td>
                    <td><?php echo sms_e($e['description']); ?></td>
                    <td><?php echo sms_e($e['category']); ?></td>
                    <td><?php echo expMoney($e['amount']); ?></td>
                    <td><?php echo sms_e(ucfirst(str_replace('_', ' ', $e['payment_method']))); ?></td>
                    <td><span class="status-badge status-<?php echo sms_e($e['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($e['status'])); ?></span></td>
                    <td>
                        <div class="action-row">
                            <a class="btn btn-sm btn-outline-success" href="expense-management.php?edit=<?php echo (int) $e['id']; ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                            <?php if (!empty($e['attachment_path'])): ?><a class="btn btn-sm btn-outline-info" href="../<?php echo sms_e(ltrim($e['attachment_path'], '/')); ?>" target="_blank"><i class="fa-solid fa-paperclip"></i> Attachment</a><?php endif; ?>
                            <form method="post" action="expense-delete.php" style="display:inline" onsubmit="return confirm('Delete this expense record?');">
                                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $e['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$expenses): ?><tr><td colspan="8" class="text-center text-muted fw-bold py-4">No expense records found.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
