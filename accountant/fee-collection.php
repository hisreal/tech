<?php require_once('includes/header.php'); ?>

<?php
// Fee collection placeholder data. Replace with database-backed student, fee, and payment records later.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$classes = ['JSS 1A', 'JSS 2B', 'SS 1 Science'];
$today = date('Y-m-d');
$students = [
	[
		'passport' => '../assets/img/students/student-01.jpg', 'name' => 'Musa Ibrahim', 'reg_no' => 'BFSS/SS1/001', 'class' => 'SS 1 Science', 'section' => 'Science', 'session' => '2025/2026', 'term' => 'First Term', 'guardian' => 'Alh. Ibrahim Musa', 'total' => 120000, 'paid' => 70000,
		'fees' => [['item' => 'Tuition Fee', 'amount' => 80000, 'status' => 'Paid'], ['item' => 'Examination Fee', 'amount' => 5000, 'status' => 'Paid'], ['item' => 'Laboratory Fee', 'amount' => 10000, 'status' => 'Outstanding'], ['item' => 'Library Fee', 'amount' => 5000, 'status' => 'Outstanding'], ['item' => 'Development Levy', 'amount' => 20000, 'status' => 'Outstanding']],
		'payments' => [['receipt' => 'RCP001', 'date' => '2026-07-05', 'type' => 'Tuition Fee', 'amount' => 50000, 'method' => 'Bank Transfer', 'status' => 'Paid'], ['receipt' => 'RCP002', 'date' => '2026-07-06', 'type' => 'Examination Fee', 'amount' => 20000, 'method' => 'POS', 'status' => 'Paid']]
	],
	[
		'passport' => '../assets/img/students/student-02.jpg', 'name' => 'Aisha Bello', 'reg_no' => 'BFSS/JSS2B/014', 'class' => 'JSS 2B', 'section' => 'Arts', 'session' => '2025/2026', 'term' => 'First Term', 'guardian' => 'Mrs. Bello Amina', 'total' => 95000, 'paid' => 30000,
		'fees' => [['item' => 'Tuition Fee', 'amount' => 70000, 'status' => 'Partially Paid'], ['item' => 'Examination Fee', 'amount' => 5000, 'status' => 'Outstanding'], ['item' => 'Sports Fee', 'amount' => 3000, 'status' => 'Outstanding'], ['item' => 'Library Fee', 'amount' => 2000, 'status' => 'Outstanding'], ['item' => 'Development Levy', 'amount' => 15000, 'status' => 'Outstanding']],
		'payments' => [['receipt' => 'RCP010', 'date' => '2026-07-03', 'type' => 'School Fees', 'amount' => 30000, 'method' => 'Cash', 'status' => 'Paid']]
	]
];
$stats = ['today_collections' => 450000, 'payments_today' => 18, 'outstanding_fees' => 3250000, 'students_paid_today' => 42];
function fcMoney($amount) { return '₦' . number_format((float) $amount); }
function fcValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>

<style>
	/* Fee collection module: scoped finance UI for search, payment collection, receipts, and history. */
	.fee-collection-page { --fc-primary:#0f766e; --fc-primary-dark:#115e59; --fc-primary-soft:rgba(15,118,110,.1); --fc-success:#16a34a; --fc-success-soft:rgba(22,163,74,.12); --fc-warning:#f59e0b; --fc-warning-soft:rgba(245,158,11,.14); --fc-danger:#dc2626; --fc-danger-soft:rgba(220,38,38,.1); --fc-blue:#2563eb; --fc-blue-soft:rgba(37,99,235,.1); --fc-ink:#10201d; --fc-muted:#64748b; --fc-border:rgba(15,118,110,.18); --fc-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.fee-collection-page .fc-hero,.fee-collection-page .fc-card,.fee-collection-page .stat-card,.fee-collection-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--fc-border); box-shadow:var(--fc-shadow); }
	.fee-collection-page .fc-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.fee-collection-page .breadcrumb-line { color:var(--fc-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.fee-collection-page .breadcrumb-line a { color:var(--fc-primary-dark); text-decoration:none; }
	.fee-collection-page .fc-kicker,.fee-collection-page .field-icon,.fee-collection-page .stat-icon,.fee-collection-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.fee-collection-page .fc-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--fc-primary-soft); color:var(--fc-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-collection-page h3,.fee-collection-page h4,.fee-collection-page h5 { color:var(--fc-ink); font-weight:900; }
	.fee-collection-page .fc-card,.fee-collection-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.fee-collection-page .fc-card { padding:24px; }
	.fee-collection-page .form-label { color:var(--fc-ink); font-size:13px; font-weight:900; }
	.fee-collection-page .field-wrap { position:relative; }
	.fee-collection-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fc-primary); pointer-events:none; }
	.fee-collection-page .form-select,.fee-collection-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.fee-collection-page textarea.form-control { padding:14px; min-height:96px; }
	.fee-collection-page .form-select:focus,.fee-collection-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.fee-collection-page .fc-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--fc-primary),var(--fc-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.fee-collection-page .fc-btn:hover { color:#fff; transform:translateY(-2px); }
	.fee-collection-page .notice { display:none; gap:8px; align-items:center; padding:12px 14px; border-radius:14px; font-weight:800; margin-bottom:16px; }
	.fee-collection-page .notice.is-visible{display:flex}.fee-collection-page .notice.success{color:var(--fc-success);background:var(--fc-success-soft)}.fee-collection-page .notice.error{color:var(--fc-danger);background:var(--fc-danger-soft)}
	.fee-collection-page .stat-card { height:100%; padding:18px; border-radius:20px; }
	.fee-collection-page .stat-icon { width:42px; height:42px; border-radius:14px; background:var(--fc-primary-soft); color:var(--fc-primary); }
	.fee-collection-page .stat-icon.success{background:var(--fc-success-soft);color:var(--fc-success)}.fee-collection-page .stat-icon.warning{background:var(--fc-warning-soft);color:#b45309}.fee-collection-page .stat-icon.danger{background:var(--fc-danger-soft);color:var(--fc-danger)}.fee-collection-page .stat-icon.blue{background:var(--fc-blue-soft);color:var(--fc-blue)}
	.fee-collection-page .stat-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.fee-collection-page .student-photo { width:92px; height:92px; border-radius:24px; object-fit:cover; border:4px solid #fff; box-shadow:0 14px 28px rgba(15,23,42,.14); }
	.fee-collection-page .balance-highlight { padding:18px; border-radius:20px; background:var(--fc-danger-soft); color:var(--fc-danger); font-weight:900; }
	.fee-collection-page .table-scroll { overflow:auto; }
	.fee-collection-page .fc-table { min-width:760px; margin-bottom:0; }
	.fee-collection-page .fc-table thead th { padding:14px 12px; background:linear-gradient(135deg,var(--fc-primary),var(--fc-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-collection-page .fc-table td { padding:13px 12px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.fee-collection-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.fee-collection-page .status-paid{color:var(--fc-success);background:var(--fc-success-soft)}.fee-collection-page .status-partially-paid{color:#b45309;background:var(--fc-warning-soft)}.fee-collection-page .status-outstanding{color:var(--fc-danger);background:var(--fc-danger-soft)}
	.fee-collection-page .action-row { display:flex; gap:10px; flex-wrap:wrap; }
	@media(max-width:767.98px){ .fee-collection-page .fc-hero,.fee-collection-page .fc-card{padding:20px;border-radius:20px}.fee-collection-page .action-row,.fee-collection-page .action-row .btn{width:100%}.fee-collection-page .student-photo{width:78px;height:78px} }
</style>

<div class="fee-collection-page">
	<!-- Page header and breadcrumb for the accountant collection workflow. -->
	<section class="fc-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Fee Collection</div>
		<span class="fc-kicker"><i class="fa-solid fa-cash-register"></i> Finance Module</span>
		<h3 class="mt-3 mb-2">Fee Collection</h3>
		<p class="text-muted mb-0">Record and manage student fee payments, generate receipts, and update payment records.</p>
	</section>

	<div id="collectionNotice" class="notice" role="alert"><i class="fa-solid fa-circle-info"></i><span></span></div>

	<!-- Quick statistics cards for daily fee collection monitoring. -->
	<section class="row g-3 mb-4" aria-label="Fee collection statistics">
		<div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo fcMoney($stats['today_collections']); ?></h4><p class="text-muted mb-0">Today's Collections</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon blue"><i class="fa-solid fa-receipt"></i></span><h4><?php echo number_format($stats['payments_today']); ?></h4><p class="text-muted mb-0">Payments Received Today</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon danger"><i class="fa-solid fa-scale-unbalanced"></i></span><h4><?php echo fcMoney($stats['outstanding_fees']); ?></h4><p class="text-muted mb-0">Total Outstanding Fees</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon warning"><i class="fa-solid fa-user-check"></i></span><h4><?php echo number_format($stats['students_paid_today']); ?></h4><p class="text-muted mb-0">Students Paid Today</p></div></div>
	</section>

	<!-- Student search card: filters prepare the selected student fee record. -->
	<section class="fc-card">
		<form id="studentSearchForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-4"><label class="form-label" for="searchInput">Registration Number / Student Name</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-magnifying-glass"></i></span><input type="search" class="form-control" id="searchInput" placeholder="Search by reg no or name"></div></div>
			<div class="col-md-2"><label class="form-label" for="sessionFilter">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionFilter"><option value="">Session</option><?php foreach ($sessions as $session): ?><option><?php echo fcValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-2"><label class="form-label" for="termFilter">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termFilter"><option value="">Term</option><?php foreach ($terms as $term): ?><option><?php echo fcValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-2"><label class="form-label" for="classFilter">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classFilter"><option value="">Class</option><?php foreach ($classes as $class): ?><option><?php echo fcValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-2"><button type="submit" class="btn fc-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
		</form>
	</section>

	<div id="studentWorkspace" style="display:none;">
		<!-- Selected student profile and fee summary. -->
		<section class="row g-4">
			<div class="col-xl-7"><div class="fc-card h-100"><div id="studentProfile"></div></div></div>
			<div class="col-xl-5"><div class="fc-card h-100"><h4 class="mb-3">Fee Summary</h4><div id="feeSummary"></div></div></div>
		</section>

		<!-- Fee item breakdown. -->
		<section class="table-card"><div class="p-3"><h4 class="mb-1">Fee Breakdown</h4><p class="text-muted mb-0">Applicable fee items and payment status.</p></div><div class="table-scroll"><table class="table fc-table align-middle"><thead><tr><th>Fee Item</th><th>Amount</th><th>Status</th></tr></thead><tbody id="feeBreakdownBody"></tbody></table></div></section>

		<!-- Payment collection form and live payment summary. -->
		<section class="row g-4">
			<div class="col-xl-7"><div class="fc-card"><h4 class="mb-3">Payment Collection Form</h4><form id="paymentForm" class="row g-3" novalidate><div class="col-md-6"><label class="form-label" for="paymentDate">Payment Date</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar-day"></i></span><input type="date" class="form-control" id="paymentDate" value="<?php echo fcValue($today); ?>" required></div></div><div class="col-md-6"><label class="form-label" for="paymentType">Payment Type</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-list"></i></span><select class="form-select" id="paymentType" required><option value="">Select payment type</option><option>School Fees</option><option>Tuition Fee</option><option>Examination Fee</option><option>Transport Fee</option><option>Hostel Fee</option><option>Development Levy</option><option>Other</option></select></div></div><div class="col-md-6"><label class="form-label" for="amountPaying">Amount Paying</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" min="1" class="form-control" id="amountPaying" placeholder="0" required></div></div><div class="col-md-6"><label class="form-label" for="paymentMethod">Payment Method</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-credit-card"></i></span><select class="form-select" id="paymentMethod" required><option value="">Select method</option><option>Cash</option><option>Bank Transfer</option><option>POS</option><option>Online Payment</option></select></div></div><div class="col-md-12"><label class="form-label" for="transactionRef">Transaction Reference</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-hashtag"></i></span><input type="text" class="form-control" id="transactionRef" placeholder="Bank, POS, or online payment reference"></div></div><div class="col-12"><label class="form-label" for="remarksInput">Remarks</label><textarea class="form-control" id="remarksInput" placeholder="Optional payment notes"></textarea></div></form><div class="action-row mt-3"><button type="button" class="btn fc-btn" id="collectPaymentBtn"><i class="fa-solid fa-cash-register me-2"></i>Collect Payment</button><button type="button" class="btn btn-outline-success" id="saveDraftBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Draft</button><button type="button" class="btn btn-outline-secondary" id="cancelBtn"><i class="fa-solid fa-xmark me-2"></i>Cancel</button><button type="button" class="btn btn-outline-primary" id="generateReceiptBtn" disabled><i class="fa-solid fa-receipt me-2"></i>Generate Receipt</button><button type="button" class="btn btn-outline-dark" id="printReceiptBtn" disabled><i class="fa-solid fa-print me-2"></i>Print Receipt</button></div></div></div>
			<div class="col-xl-5"><div class="fc-card"><h4 class="mb-3">Payment Summary</h4><div id="paymentSummary"></div></div></div>
		</section>

		<!-- Recent payment history for the selected student. -->
		<section class="table-card"><div class="p-3"><h4 class="mb-1">Recent Payment History</h4><p class="text-muted mb-0">Receipts and payment records for the selected student.</p></div><div class="table-scroll"><table class="table fc-table align-middle"><thead><tr><th>Receipt No.</th><th>Date</th><th>Payment Type</th><th>Amount</th><th>Method</th><th>Status</th><th>Action</th></tr></thead><tbody id="paymentHistoryBody"></tbody></table></div></section>
	</div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Fee collection behavior: search, live balance calculation, validation, receipt generation, and history updates.
(function () {
	var students = <?php echo json_encode($students); ?>;
	var selected = null;
	var lastReceipt = null;
	function byId(id) { return document.getElementById(id); }
	function money(amount) { return '₦' + Number(amount || 0).toLocaleString(); }
	function balance() { return Math.max(0, Number(selected.total) - Number(selected.paid)); }
	function showNotice(type, message) { var n = byId('collectionNotice'); n.className = 'notice is-visible ' + type; n.querySelector('span').textContent = message; window.scrollTo({ top: n.offsetTop - 20, behavior: 'smooth' }); }
	function statusClass(status) { return status.toLowerCase().replace(/\s+/g, '-'); }
	function renderSelected() { byId('studentWorkspace').style.display = 'block'; byId('studentProfile').innerHTML = '<div class="d-flex align-items-center gap-3 flex-wrap"><img src="' + selected.passport + '" class="student-photo" alt="Student passport"><div><h4 class="mb-1">' + selected.name + '</h4><p class="text-muted mb-0">' + selected.reg_no + ' | ' + selected.class + ' | ' + selected.section + '</p><p class="text-muted mb-0">Session: ' + selected.session + ' | Term: ' + selected.term + '</p><p class="text-muted mb-0">Guardian: ' + selected.guardian + '</p></div></div>'; byId('feeSummary').innerHTML = '<table class="table"><tbody><tr><td>Total Fees</td><td class="fw-bold">' + money(selected.total) + '</td></tr><tr><td>Amount Paid</td><td class="fw-bold">' + money(selected.paid) + '</td></tr><tr><td>Outstanding Balance</td><td><div class="balance-highlight">' + money(balance()) + '</div></td></tr></tbody></table>'; byId('feeBreakdownBody').innerHTML = selected.fees.map(function (fee) { return '<tr><td>' + fee.item + '</td><td>' + money(fee.amount) + '</td><td><span class="status-badge status-' + statusClass(fee.status) + '"><i class="fa-solid fa-circle"></i>' + fee.status + '</span></td></tr>'; }).join(''); renderHistory(); updatePaymentSummary(); }
	function renderHistory() { byId('paymentHistoryBody').innerHTML = selected.payments.map(function (p) { return '<tr><td>' + p.receipt + '</td><td>' + p.date + '</td><td>' + p.type + '</td><td>' + money(p.amount) + '</td><td>' + p.method + '</td><td><span class="status-badge status-paid"><i class="fa-solid fa-circle"></i>' + p.status + '</span></td><td><div class="action-row"><button type="button" class="btn btn-sm btn-outline-primary receipt-action" data-receipt="' + p.receipt + '">View Receipt</button><button type="button" class="btn btn-sm btn-outline-dark receipt-action" data-receipt="' + p.receipt + '">Print Receipt</button><button type="button" class="btn btn-sm btn-outline-success receipt-action" data-receipt="' + p.receipt + '">Download Receipt</button></div></td></tr>'; }).join(''); }
	function updatePaymentSummary() { var current = Number(byId('amountPaying').value || 0); var remaining = Math.max(0, balance() - current); byId('paymentSummary').innerHTML = '<table class="table"><tbody><tr><td>Total Fees</td><td class="fw-bold">' + money(selected ? selected.total : 0) + '</td></tr><tr><td>Amount Previously Paid</td><td class="fw-bold">' + money(selected ? selected.paid : 0) + '</td></tr><tr><td>Current Payment</td><td class="fw-bold">' + money(current) + '</td></tr><tr><td>Remaining Balance After Payment</td><td><div class="balance-highlight">' + money(remaining) + '</div></td></tr></tbody></table>'; }
	function validatePayment() { if (!selected) { showNotice('error', 'Please search and load a student first.'); return false; } var amount = Number(byId('amountPaying').value || 0); if (!byId('paymentDate').value || !byId('paymentType').value || !byId('paymentMethod').value) { showNotice('error', 'Please complete payment date, payment type, and payment method.'); return false; } if (amount <= 0) { showNotice('error', 'Payment amount must be a positive number.'); return false; } if (amount > balance()) { showNotice('error', 'Payment amount cannot exceed the outstanding balance.'); return false; } if (byId('paymentMethod').value !== 'Cash' && !byId('transactionRef').value.trim()) { showNotice('error', 'Transaction reference is required for non-cash payments.'); return false; } return true; }
	function receiptHtml(payment) { return '<html><head><title>Fee Receipt</title><style>@page{size:A4;margin:16mm}body{font-family:Arial,sans-serif;color:#10201d}.head{text-align:center;border-bottom:3px solid #0f766e;padding-bottom:10px;margin-bottom:18px}table{width:100%;border-collapse:collapse}td{padding:8px;border-bottom:1px solid #ddd}.total{font-size:20px;font-weight:800;color:#0f766e}</style></head><body><div class="head"><h2>BRIGHTER FUTURE STANDARD SCHOOL, KATSINA</h2><p>Official School Fee Receipt</p></div><table><tbody><tr><td>Receipt No.</td><td>' + payment.receipt + '</td></tr><tr><td>Student</td><td>' + selected.name + '</td></tr><tr><td>Registration No.</td><td>' + selected.reg_no + '</td></tr><tr><td>Class</td><td>' + selected.class + '</td></tr><tr><td>Date</td><td>' + payment.date + '</td></tr><tr><td>Payment Type</td><td>' + payment.type + '</td></tr><tr><td>Method</td><td>' + payment.method + '</td></tr><tr><td>Amount Paid</td><td class="total">' + money(payment.amount) + '</td></tr><tr><td>Balance</td><td>' + money(balance()) + '</td></tr></tbody></table></body></html>'; }
	function printReceipt(payment) { var win = window.open('', '_blank'); win.document.write(receiptHtml(payment)); win.document.close(); win.focus(); win.print(); }
	byId('studentSearchForm').addEventListener('submit', function (event) { event.preventDefault(); var q = byId('searchInput').value.toLowerCase().trim(), session = byId('sessionFilter').value, term = byId('termFilter').value, klass = byId('classFilter').value; selected = students.find(function (s) { return (!q || s.reg_no.toLowerCase().indexOf(q) !== -1 || s.name.toLowerCase().indexOf(q) !== -1) && (!session || s.session === session) && (!term || s.term === term) && (!klass || s.class === klass); }) || null; if (!selected) { showNotice('error', 'No matching student fee record found.'); return; } showNotice('success', 'Student fee information loaded successfully.'); lastReceipt = null; byId('printReceiptBtn').disabled = true; byId('generateReceiptBtn').disabled = true; renderSelected(); });
	byId('amountPaying').addEventListener('input', updatePaymentSummary);
	byId('collectPaymentBtn').addEventListener('click', function () { if (!validatePayment()) return; var amount = Number(byId('amountPaying').value); var payment = { receipt: 'RCP' + String(Date.now()).slice(-6), date: byId('paymentDate').value, type: byId('paymentType').value, amount: amount, method: byId('paymentMethod').value, status: 'Paid' }; selected.paid = Number(selected.paid) + amount; selected.payments.unshift(payment); lastReceipt = payment; byId('printReceiptBtn').disabled = false; byId('generateReceiptBtn').disabled = false; byId('paymentForm').reset(); byId('paymentDate').value = '<?php echo fcValue($today); ?>'; renderSelected(); showNotice('success', 'Payment collected successfully. Outstanding balance updated.'); });
	byId('saveDraftBtn').addEventListener('click', function () { showNotice('success', 'Payment draft saved locally. This is ready for database integration.'); });
	byId('cancelBtn').addEventListener('click', function () { byId('paymentForm').reset(); byId('paymentDate').value = '<?php echo fcValue($today); ?>'; updatePaymentSummary(); });
	byId('generateReceiptBtn').addEventListener('click', function () { if (lastReceipt) printReceipt(lastReceipt); });
	byId('printReceiptBtn').addEventListener('click', function () { if (lastReceipt) printReceipt(lastReceipt); });
	document.addEventListener('click', function (event) { var btn = event.target.closest('.receipt-action'); if (!btn || !selected) return; var payment = selected.payments.find(function (p) { return p.receipt === btn.getAttribute('data-receipt'); }); if (payment) printReceipt(payment); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
