<?php require_once('includes/header.php'); ?>

<?php
// Payment history placeholder data. Replace with database queries for transactions, students, receipts, and fee items later.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$classes = ['JSS 1A', 'JSS 2B', 'SS 1 Science'];
$methods = ['Cash', 'Bank Transfer', 'POS', 'Online Payment'];
$statuses = ['Paid', 'Pending', 'Failed', 'Refunded'];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthlyCollections = [850000, 1120000, 980000, 1300000, 1450000, 1180000, 1545000, 990000, 1260000, 1390000, 1200000, 1620000];
$transactions = [
	['txn' => 'TXN-00001', 'receipt' => 'RCP-00001', 'date' => '2026-07-05', 'reg_no' => 'REG001', 'student' => 'Musa Ibrahim', 'class' => 'SS 1 Science', 'passport' => '../assets/img/students/student-01.jpg', 'session' => '2025/2026', 'term' => 'First Term', 'type' => 'School Fees', 'amount' => 85000, 'method' => 'Bank Transfer', 'status' => 'Paid', 'balance' => 35000, 'accountant' => 'John Ibrahim', 'reference' => 'BT-887721', 'items' => [['item' => 'Tuition Fee', 'amount' => 80000], ['item' => 'Examination Fee', 'amount' => 5000]]],
	['txn' => 'TXN-00002', 'receipt' => 'RCP-00002', 'date' => '2026-07-06', 'reg_no' => 'REG014', 'student' => 'Aisha Bello', 'class' => 'JSS 2B', 'passport' => '../assets/img/students/student-02.jpg', 'session' => '2025/2026', 'term' => 'First Term', 'type' => 'Tuition Fee', 'amount' => 40000, 'method' => 'POS', 'status' => 'Paid', 'balance' => 55000, 'accountant' => 'John Ibrahim', 'reference' => 'POS-12045', 'items' => [['item' => 'Tuition Fee', 'amount' => 40000]]],
	['txn' => 'TXN-00003', 'receipt' => 'RCP-00003', 'date' => '2026-07-06', 'reg_no' => 'REG022', 'student' => 'Daniel Okafor', 'class' => 'JSS 1A', 'passport' => '../assets/img/students/student-03.jpg', 'session' => '2025/2026', 'term' => 'Second Term', 'type' => 'Development Levy', 'amount' => 20000, 'method' => 'Cash', 'status' => 'Pending', 'balance' => 78000, 'accountant' => 'John Ibrahim', 'reference' => 'CASH-PENDING', 'items' => [['item' => 'Development Levy', 'amount' => 20000]]],
	['txn' => 'TXN-00004', 'receipt' => 'RCP-00004', 'date' => '2026-06-29', 'reg_no' => 'REG031', 'student' => 'Maryam Musa', 'class' => 'SS 1 Science', 'passport' => '../assets/img/students/student-04.jpg', 'session' => '2026/2027', 'term' => 'First Term', 'type' => 'Transport Fee', 'amount' => 25000, 'method' => 'Online Payment', 'status' => 'Refunded', 'balance' => 95000, 'accountant' => 'John Ibrahim', 'reference' => 'WEB-9012', 'items' => [['item' => 'Transport Fee', 'amount' => 25000]]],
	['txn' => 'TXN-00005', 'receipt' => 'RCP-00005', 'date' => '2026-07-04', 'reg_no' => 'REG044', 'student' => 'Samuel Adeyemi', 'class' => 'JSS 1A', 'passport' => '../assets/img/students/student-05.jpg', 'session' => '2025/2026', 'term' => 'Third Term', 'type' => 'Hostel Fee', 'amount' => 60000, 'method' => 'Bank Transfer', 'status' => 'Failed', 'balance' => 60000, 'accountant' => 'John Ibrahim', 'reference' => 'BT-FAILED', 'items' => [['item' => 'Hostel Fee', 'amount' => 60000]]]
];
function phMoney($amount) { return '₦' . number_format((float) $amount); }
function phValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$totalPayments = count($transactions);
$todayCollections = array_reduce(array_filter($transactions, fn($t) => $t['date'] === '2026-07-06' && $t['status'] === 'Paid'), fn($s, $t) => $s + $t['amount'], 0);
$monthlyTotal = array_reduce(array_filter($transactions, fn($t) => substr($t['date'], 0, 7) === '2026-07' && $t['status'] === 'Paid'), fn($s, $t) => $s + $t['amount'], 0);
$outstandingBalance = array_reduce($transactions, fn($s, $t) => $s + $t['balance'], 0);
$todayTransactions = array_filter($transactions, fn($t) => $t['date'] === '2026-07-06');
?>

<style>
	/* Payment history module: scoped ERP-style audit trail, filters, charts, and transaction tables. */
	.payment-history-page { --pay-primary:#0f766e; --pay-primary-dark:#115e59; --pay-primary-soft:rgba(15,118,110,.1); --pay-success:#16a34a; --pay-success-soft:rgba(22,163,74,.12); --pay-warning:#f59e0b; --pay-warning-soft:rgba(245,158,11,.14); --pay-danger:#dc2626; --pay-danger-soft:rgba(220,38,38,.1); --pay-blue:#2563eb; --pay-blue-soft:rgba(37,99,235,.1); --pay-ink:#10201d; --pay-muted:#64748b; --pay-border:rgba(15,118,110,.18); --pay-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.payment-history-page .pay-hero,.payment-history-page .pay-card,.payment-history-page .summary-card,.payment-history-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--pay-border); box-shadow:var(--pay-shadow); }
	.payment-history-page .pay-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.payment-history-page .breadcrumb-line { color:var(--pay-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.payment-history-page .breadcrumb-line a { color:var(--pay-primary-dark); text-decoration:none; }
	.payment-history-page .pay-kicker,.payment-history-page .field-icon,.payment-history-page .summary-icon,.payment-history-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.payment-history-page .pay-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--pay-primary-soft); color:var(--pay-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.payment-history-page h3,.payment-history-page h4,.payment-history-page h5 { color:var(--pay-ink); font-weight:900; }
	.payment-history-page .pay-card,.payment-history-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.payment-history-page .pay-card { padding:24px; }
	.payment-history-page .form-label { color:var(--pay-ink); font-size:13px; font-weight:900; }
	.payment-history-page .field-wrap { position:relative; }
	.payment-history-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--pay-primary); pointer-events:none; }
	.payment-history-page .form-select,.payment-history-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.payment-history-page .form-select:focus,.payment-history-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.payment-history-page .pay-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--pay-primary),var(--pay-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.payment-history-page .pay-btn:hover { color:#fff; transform:translateY(-2px); }
	.payment-history-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.payment-history-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.payment-history-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--pay-primary-soft); color:var(--pay-primary); }
	.payment-history-page .summary-icon.success{background:var(--pay-success-soft);color:var(--pay-success)}.payment-history-page .summary-icon.warning{background:var(--pay-warning-soft);color:#b45309}.payment-history-page .summary-icon.danger{background:var(--pay-danger-soft);color:var(--pay-danger)}.payment-history-page .summary-icon.blue{background:var(--pay-blue-soft);color:var(--pay-blue)}
	.payment-history-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.payment-history-page .chart-wrap { height:230px; display:flex; align-items:end; gap:10px; padding-top:20px; border-bottom:1px solid rgba(148,163,184,.22); }
	.payment-history-page .chart-bar { flex:1; min-width:18px; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:8px; height:100%; }
	.payment-history-page .bar-fill { width:100%; max-width:34px; border-radius:10px 10px 4px 4px; background:linear-gradient(180deg,var(--pay-primary),var(--pay-primary-dark)); }
	.payment-history-page .bar-label { color:var(--pay-muted); font-size:12px; font-weight:900; }
	.payment-history-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.payment-history-page .table-scroll { max-height:640px; overflow:auto; }
	.payment-history-page .payment-table { min-width:1280px; margin-bottom:0; }
	.payment-history-page .payment-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--pay-primary),var(--pay-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.payment-history-page .payment-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.payment-history-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.payment-history-page .status-paid{color:var(--pay-success);background:var(--pay-success-soft)}.payment-history-page .status-pending{color:#b45309;background:var(--pay-warning-soft)}.payment-history-page .status-failed{color:var(--pay-danger);background:var(--pay-danger-soft)}.payment-history-page .status-refunded{color:var(--pay-blue);background:var(--pay-blue-soft)}
	.payment-history-page .row-actions { display:flex; gap:7px; flex-wrap:wrap; }
	.payment-history-page .pagination-wrap { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:14px 20px; border-top:1px solid rgba(148,163,184,.2); }
	.payment-history-page .page-btn { border:1px solid rgba(15,118,110,.2); color:var(--pay-primary-dark); border-radius:10px; background:#fff; padding:7px 11px; font-weight:900; }
	.payment-history-page .page-btn.active { background:var(--pay-primary); color:#fff; }
	.payment-detail-photo { width:74px; height:74px; border-radius:18px; object-fit:cover; box-shadow:0 12px 28px rgba(15,23,42,.14); }
	@media(max-width:767.98px){ .payment-history-page .pay-hero,.payment-history-page .pay-card{padding:20px;border-radius:20px}.payment-history-page .row-actions,.payment-history-page .row-actions .btn,.payment-history-page .pay-btn{width:100%}.payment-history-page .pagination-wrap{align-items:flex-start;flex-direction:column}.payment-history-page .chart-wrap{gap:6px} }
</style>

<?php $maxCollection = max($monthlyCollections); ?>
<div class="payment-history-page">
	<!-- Page header and breadcrumb for the payment audit trail. -->
	<section class="pay-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payment History</div>
		<span class="pay-kicker"><i class="fa-solid fa-clock-rotate-left"></i> Finance Audit Trail</span>
		<h3 class="mt-3 mb-2">Payment History</h3>
		<p class="text-muted mb-0">View, search, filter, and monitor all student payment transactions.</p>
	</section>

	<!-- Search and filter controls for transaction audit records. -->
	<section class="pay-card">
		<form id="paymentFilterForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-3"><label class="form-label" for="sessionFilter">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionFilter"><option value="">All Sessions</option><?php foreach ($sessions as $session): ?><option><?php echo phValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="termFilter">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termFilter"><option value="">All Terms</option><?php foreach ($terms as $term): ?><option><?php echo phValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="classFilter">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classFilter"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option><?php echo phValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="statusFilter">Payment Status</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-circle-check"></i></span><select class="form-select" id="statusFilter"><option value="">All</option><?php foreach ($statuses as $status): ?><option><?php echo phValue($status); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="methodFilter">Payment Method</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-credit-card"></i></span><select class="form-select" id="methodFilter"><option value="">All Methods</option><?php foreach ($methods as $method): ?><option><?php echo phValue($method); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label">Date Range</label><div class="d-flex gap-2"><input type="date" class="form-control" id="fromDate" style="padding-left:12px;"><input type="date" class="form-control" id="toDate" style="padding-left:12px;"></div></div>
			<div class="col-md-4"><label class="form-label" for="searchInput">Search Student / Reg No / Receipt</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-magnifying-glass"></i></span><input type="search" class="form-control" id="searchInput" placeholder="Student name, registration number, or receipt number"></div></div>
			<div class="col-md-2"><button type="submit" class="btn pay-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
			<div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="resetFilters"><i class="fa-solid fa-rotate-left me-2"></i>Reset</button></div>
			<div class="col-md-2"><button type="button" class="btn btn-outline-success w-100" id="exportResults"><i class="fa-solid fa-file-export me-2"></i>Export</button></div>
		</form>
	</section>

	<!-- Summary cards for payment volume, collections, and balances. -->
	<section class="row g-3 mb-4" aria-label="Payment summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-list-check"></i></span><h4><?php echo number_format($totalPayments); ?></h4><p class="text-muted mb-0">Total Payments</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo phMoney($todayCollections); ?></h4><p class="text-muted mb-0">Today's Collections</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-calendar-check"></i></span><h4><?php echo phMoney($monthlyTotal); ?></h4><p class="text-muted mb-0">Monthly Collections</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-scale-unbalanced"></i></span><h4><?php echo phMoney($outstandingBalance); ?></h4><p class="text-muted mb-0">Outstanding Balance</p></div></div>
	</section>

	<!-- Daily collection summary and monthly collection chart. -->
	<section class="row g-4">
		<div class="col-xl-5"><div class="pay-card"><h4 class="mb-3">Daily Collection Summary</h4><?php $dailyMethods = ['Cash', 'Bank Transfer', 'POS', 'Online Payment']; ?><div class="row g-3"><div class="col-6"><strong>Total Transactions</strong><h5><?php echo count($todayTransactions); ?></h5></div><?php foreach ($dailyMethods as $method): ?><div class="col-6"><strong><?php echo phValue($method); ?></strong><h5><?php echo count(array_filter($todayTransactions, fn($t) => $t['method'] === $method)); ?></h5></div><?php endforeach; ?><div class="col-12"><strong>Total Amount Collected</strong><h4><?php echo phMoney($todayCollections); ?></h4></div></div></div></div>
		<div class="col-xl-7"><div class="pay-card"><h4 class="mb-1">Monthly Collection Chart</h4><p class="text-muted mb-0">Placeholder monthly payment collections.</p><div class="chart-wrap"><?php foreach ($monthlyCollections as $index => $value): ?><div class="chart-bar" title="<?php echo phValue($months[$index] . ': ' . phMoney($value)); ?>"><div class="bar-fill" style="height:<?php echo round(($value / $maxCollection) * 100); ?>%"></div><span class="bar-label"><?php echo $months[$index]; ?></span></div><?php endforeach; ?></div></div></div>
	</section>

	<!-- Payment history table with export, print, pagination, and action buttons. -->
	<section class="table-card">
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Payment Transactions</h4><p class="text-muted mb-0">Complete audit trail of every payment made to the school.</p></div><div class="row-actions"><button type="button" class="btn pay-btn" id="exportCsv"><i class="fa-solid fa-file-csv me-2"></i>CSV</button><button type="button" class="btn pay-btn" id="exportExcel"><i class="fa-solid fa-file-excel me-2"></i>Excel</button><button type="button" class="btn pay-btn" id="exportPdf"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button><button type="button" class="btn btn-outline-secondary" id="printList"><i class="fa-solid fa-print me-2"></i>Print</button><select class="form-select" id="pageSize" style="width:100px;padding-left:12px;"><option>10</option><option>25</option><option>50</option><option>100</option></select></div></div>
		<div class="table-scroll"><table class="table payment-table align-middle"><thead><tr><th>Transaction ID</th><th>Receipt No.</th><th>Date</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Payment Type</th><th>Amount</th><th>Payment Method</th><th>Status</th><th>Actions</th></tr></thead><tbody id="paymentBody"></tbody></table></div>
		<div class="pagination-wrap"><span class="text-muted fw-bold" id="recordInfo">Showing payments</span><div id="pagination" class="d-flex gap-2 flex-wrap"></div></div>
	</section>
</div>

<!-- Payment details modal: shows student, transaction, and fee breakdown details. -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden;"><div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white"><i class="fa-solid fa-circle-info me-2"></i>Payment Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="paymentDetailsBody"></div></div></div></div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Payment history behavior: filters, pagination, detail modal, exports, print, and receipt actions.
(function () {
	var transactions = <?php echo json_encode($transactions); ?>;
	var filtered = transactions.slice();
	var currentPage = 1;
	function byId(id) { return document.getElementById(id); }
	function money(amount) { return '₦' + Number(amount || 0).toLocaleString(); }
	function statusClass(status) { return status.toLowerCase(); }
	function csvEscape(v) { return '"' + String(v).replace(/"/g, '""') + '"'; }
	function rowsForExport(rows) { return [['Transaction ID','Receipt No.','Date','Registration No.','Student Name','Class','Payment Type','Amount','Payment Method','Status']].concat(rows.map(function (r) { return [r.txn,r.receipt,r.date,r.reg_no,r.student,r.class,r.type,r.amount,r.method,r.status]; })); }
	function downloadCsv(rows, filename) { var csv = rowsForExport(rows).map(function (row) { return row.map(csvEscape).join(','); }).join('\n'); var link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type:'text/csv;charset=utf-8;' })); link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href); }
	function renderTable() { var size = parseInt(byId('pageSize').value, 10); var pages = Math.max(1, Math.ceil(filtered.length / size)); if (currentPage > pages) currentPage = pages; var start = (currentPage - 1) * size; var rows = filtered.slice(start, start + size); byId('paymentBody').innerHTML = rows.map(function (r) { return '<tr><td>' + r.txn + '</td><td>' + r.receipt + '</td><td>' + r.date + '</td><td>' + r.reg_no + '</td><td>' + r.student + '</td><td>' + r.class + '</td><td>' + r.type + '</td><td>' + money(r.amount) + '</td><td>' + r.method + '</td><td><span class="status-badge status-' + statusClass(r.status) + '"><i class="fa-solid fa-circle"></i>' + r.status + '</span></td><td><div class="row-actions"><button type="button" class="btn btn-sm btn-outline-primary details-btn" data-txn="' + r.txn + '"><i class="fa-solid fa-eye"></i> Details</button><button type="button" class="btn btn-sm btn-outline-success receipt-btn" data-txn="' + r.txn + '"><i class="fa-solid fa-receipt"></i> Receipt</button><button type="button" class="btn btn-sm btn-outline-dark print-btn" data-txn="' + r.txn + '"><i class="fa-solid fa-print"></i> Print</button><button type="button" class="btn btn-sm btn-outline-secondary pdf-btn" data-txn="' + r.txn + '"><i class="fa-solid fa-download"></i> PDF</button><button type="button" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-user"></i> Profile</button></div></td></tr>'; }).join('') || '<tr><td colspan="11" class="text-center text-muted fw-bold py-4">No payment transactions found.</td></tr>'; byId('recordInfo').textContent = 'Showing ' + (filtered.length ? start + 1 : 0) + ' - ' + Math.min(start + size, filtered.length) + ' of ' + filtered.length + ' transactions'; byId('pagination').innerHTML = '<button type="button" class="page-btn" data-page="' + Math.max(1, currentPage - 1) + '">Previous</button>' + Array.from({ length: pages }, function (_, i) { return '<button type="button" class="page-btn ' + (currentPage === i + 1 ? 'active' : '') + '" data-page="' + (i + 1) + '">' + (i + 1) + '</button>'; }).join('') + '<button type="button" class="page-btn" data-page="' + Math.min(pages, currentPage + 1) + '">Next</button>'; }
	function showDetails(txn) { var t = transactions.find(function (item) { return item.txn === txn; }); if (!t) return; var items = t.items.map(function (item) { return '<tr><td>' + item.item + '</td><td>' + money(item.amount) + '</td></tr>'; }).join(''); byId('paymentDetailsBody').innerHTML = '<div class="row g-4"><div class="col-md-5"><h5>Student Information</h5><div class="d-flex gap-3 align-items-center"><img src="' + t.passport + '" class="payment-detail-photo" alt="Student"><div><h5 class="mb-1">' + t.student + '</h5><p class="mb-1 text-muted">' + t.reg_no + '</p><p class="mb-0 text-muted">' + t.class + '</p></div></div></div><div class="col-md-7"><h5>Payment Information</h5><div class="row g-2"><div class="col-sm-6"><strong>Transaction ID</strong><p>' + t.txn + '</p></div><div class="col-sm-6"><strong>Receipt Number</strong><p>' + t.receipt + '</p></div><div class="col-sm-6"><strong>Payment Date</strong><p>' + t.date + '</p></div><div class="col-sm-6"><strong>Payment Type</strong><p>' + t.type + '</p></div><div class="col-sm-6"><strong>Payment Method</strong><p>' + t.method + '</p></div><div class="col-sm-6"><strong>Amount Paid</strong><p>' + money(t.amount) + '</p></div><div class="col-sm-6"><strong>Outstanding Balance</strong><p>' + money(t.balance) + '</p></div><div class="col-sm-6"><strong>Session / Term</strong><p>' + t.session + ' | ' + t.term + '</p></div><div class="col-sm-6"><strong>Accountant</strong><p>' + t.accountant + '</p></div><div class="col-sm-6"><strong>Reference</strong><p>' + t.reference + '</p></div></div></div></div><h5 class="mt-4">Fee Breakdown</h5><div class="table-responsive"><table class="table"><thead><tr><th>Fee Item</th><th>Amount</th></tr></thead><tbody>' + items + '</tbody><tfoot><tr><th>Total Amount Paid</th><th>' + money(t.amount) + '</th></tr></tfoot></table></div>'; new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show(); }
	function applyFilters() { var session = byId('sessionFilter').value, term = byId('termFilter').value, klass = byId('classFilter').value, status = byId('statusFilter').value, method = byId('methodFilter').value, from = byId('fromDate').value, to = byId('toDate').value, search = byId('searchInput').value.toLowerCase().trim(); filtered = transactions.filter(function (t) { return (!session || t.session === session) && (!term || t.term === term) && (!klass || t.class === klass) && (!status || t.status === status) && (!method || t.method === method) && (!from || t.date >= from) && (!to || t.date <= to) && (!search || t.student.toLowerCase().indexOf(search) !== -1 || t.reg_no.toLowerCase().indexOf(search) !== -1 || t.receipt.toLowerCase().indexOf(search) !== -1); }); currentPage = 1; renderTable(); }
	function printRows(rows) { var body = rowsForExport(rows).slice(1).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join(''); var win = window.open('', '_blank'); win.document.write('<html><head><title>Payment History</title><style>@page{size:A4 landscape;margin:12mm}body{font-family:Arial,sans-serif}table{width:100%;border-collapse:collapse;font-size:11px}td,th{border:1px solid #777;padding:6px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>Payment History</h2><table><thead><tr><th>Transaction ID</th><th>Receipt No.</th><th>Date</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Payment Type</th><th>Amount</th><th>Payment Method</th><th>Status</th></tr></thead><tbody>' + body + '</tbody></table></body></html>'); win.document.close(); win.focus(); win.print(); }
	document.addEventListener('DOMContentLoaded', renderTable);
	byId('paymentFilterForm').addEventListener('submit', function (e) { e.preventDefault(); applyFilters(); });
	byId('resetFilters').addEventListener('click', function () { byId('paymentFilterForm').reset(); applyFilters(); });
	byId('pageSize').addEventListener('change', function () { currentPage = 1; renderTable(); });
	byId('pagination').addEventListener('click', function (e) { if (e.target.classList.contains('page-btn')) { currentPage = parseInt(e.target.getAttribute('data-page'), 10); renderTable(); } });
	document.addEventListener('click', function (e) { var details = e.target.closest('.details-btn'), receipt = e.target.closest('.receipt-btn'), print = e.target.closest('.print-btn'), pdf = e.target.closest('.pdf-btn'); if (details) showDetails(details.getAttribute('data-txn')); if (receipt || print || pdf) { var id = (receipt || print || pdf).getAttribute('data-txn'); var t = transactions.find(function (item) { return item.txn === id; }); if (t) { printRows([t]); } } });
	byId('exportCsv').addEventListener('click', function () { downloadCsv(filtered, 'Payment_History.csv'); });
	byId('exportExcel').addEventListener('click', function () { downloadCsv(filtered, 'Payment_History.xls'); });
	byId('exportPdf').addEventListener('click', function () { printRows(filtered); });
	byId('printList').addEventListener('click', function () { printRows(filtered); });
	byId('exportResults').addEventListener('click', function () { downloadCsv(filtered, 'Filtered_Payment_History.csv'); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
