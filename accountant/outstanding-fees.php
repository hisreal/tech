<?php require_once('includes/header.php'); ?>

<?php
// Outstanding fees placeholder data. Replace with database-backed student fee balances and transactions later.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$classes = ['JSS 1A', 'JSS 2B', 'SS 1 Science', 'SS 2 Science'];
$students = [
	['passport' => '../assets/img/students/student-01.jpg', 'reg_no' => 'BFSS/SS1/001', 'name' => 'Musa Ibrahim', 'class' => 'SS 1 Science', 'session' => '2025/2026', 'term' => 'First Term', 'total' => 120000, 'paid' => 70000, 'fees' => [['item' => 'Tuition Fee', 'amount' => 80000, 'status' => 'Paid'], ['item' => 'Examination Fee', 'amount' => 5000, 'status' => 'Outstanding'], ['item' => 'Laboratory Fee', 'amount' => 10000, 'status' => 'Outstanding'], ['item' => 'Development Levy', 'amount' => 20000, 'status' => 'Partially Paid']]],
	['passport' => '../assets/img/students/student-02.jpg', 'reg_no' => 'BFSS/JSS2B/014', 'name' => 'Aisha Bello', 'class' => 'JSS 2B', 'session' => '2025/2026', 'term' => 'First Term', 'total' => 95000, 'paid' => 30000, 'fees' => [['item' => 'Tuition Fee', 'amount' => 70000, 'status' => 'Partially Paid'], ['item' => 'Examination Fee', 'amount' => 5000, 'status' => 'Outstanding'], ['item' => 'Library Fee', 'amount' => 5000, 'status' => 'Outstanding']]],
	['passport' => '../assets/img/students/student-03.jpg', 'reg_no' => 'BFSS/JSS1A/022', 'name' => 'Daniel Okafor', 'class' => 'JSS 1A', 'session' => '2025/2026', 'term' => 'Second Term', 'total' => 98000, 'paid' => 0, 'fees' => [['item' => 'Tuition Fee', 'amount' => 80000, 'status' => 'Outstanding'], ['item' => 'Sports Fee', 'amount' => 3000, 'status' => 'Outstanding'], ['item' => 'Development Levy', 'amount' => 15000, 'status' => 'Outstanding']]],
	['passport' => '../assets/img/students/student-04.jpg', 'reg_no' => 'BFSS/SS2SCI/031', 'name' => 'Maryam Musa', 'class' => 'SS 2 Science', 'session' => '2026/2027', 'term' => 'First Term', 'total' => 130000, 'paid' => 85000, 'fees' => [['item' => 'Tuition Fee', 'amount' => 90000, 'status' => 'Paid'], ['item' => 'Laboratory Fee', 'amount' => 15000, 'status' => 'Partially Paid'], ['item' => 'Development Levy', 'amount' => 25000, 'status' => 'Outstanding']]],
	['passport' => '../assets/img/students/student-05.jpg', 'reg_no' => 'BFSS/JSS1A/044', 'name' => 'Samuel Adeyemi', 'class' => 'JSS 1A', 'session' => '2025/2026', 'term' => 'Third Term', 'total' => 90000, 'paid' => 45000, 'fees' => [['item' => 'Tuition Fee', 'amount' => 70000, 'status' => 'Partially Paid'], ['item' => 'Examination Fee', 'amount' => 5000, 'status' => 'Outstanding'], ['item' => 'Development Levy', 'amount' => 15000, 'status' => 'Outstanding']]]
];
$classBalances = ['JSS 1' => 450000, 'JSS 2' => 520000, 'JSS 3' => 380000, 'SS 1' => 780000, 'SS 2' => 640000, 'SS 3' => 530000];
function ofMoney($amount) { return '₦' . number_format((float) $amount); }
function ofValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function ofBalance($student) { return max(0, (float) $student['total'] - (float) $student['paid']); }
function ofStatus($student) { if ($student['paid'] >= $student['total']) return 'Fully Paid'; if ($student['paid'] > 0) return 'Partially Paid'; return 'Outstanding'; }
$balances = array_map('ofBalance', $students);
$totalOutstanding = array_sum($balances);
$partiallyPaid = count(array_filter($students, fn($s) => ofStatus($s) === 'Partially Paid'));
$fullyUnpaid = count(array_filter($students, fn($s) => ofStatus($s) === 'Outstanding'));
?>

<style>
	/* Outstanding fees module: scoped ERP finance UI for balances, filters, reports, and charts. */
	.outstanding-page { --out-primary:#0f766e; --out-primary-dark:#115e59; --out-primary-soft:rgba(15,118,110,.1); --out-success:#16a34a; --out-success-soft:rgba(22,163,74,.12); --out-warning:#f59e0b; --out-warning-soft:rgba(245,158,11,.14); --out-danger:#dc2626; --out-danger-soft:rgba(220,38,38,.1); --out-blue:#2563eb; --out-blue-soft:rgba(37,99,235,.1); --out-ink:#10201d; --out-muted:#64748b; --out-border:rgba(15,118,110,.18); --out-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.outstanding-page .out-hero,.outstanding-page .out-card,.outstanding-page .summary-card,.outstanding-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--out-border); box-shadow:var(--out-shadow); }
	.outstanding-page .out-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.outstanding-page .breadcrumb-line { color:var(--out-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.outstanding-page .breadcrumb-line a { color:var(--out-primary-dark); text-decoration:none; }
	.outstanding-page .out-kicker,.outstanding-page .field-icon,.outstanding-page .summary-icon,.outstanding-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.outstanding-page .out-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--out-primary-soft); color:var(--out-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.outstanding-page h3,.outstanding-page h4,.outstanding-page h5 { color:var(--out-ink); font-weight:900; }
	.outstanding-page .out-card,.outstanding-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.outstanding-page .out-card { padding:24px; }
	.outstanding-page .form-label { color:var(--out-ink); font-size:13px; font-weight:900; }
	.outstanding-page .field-wrap { position:relative; }
	.outstanding-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--out-primary); pointer-events:none; }
	.outstanding-page .form-select,.outstanding-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.outstanding-page .form-select:focus,.outstanding-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.outstanding-page .out-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--out-primary),var(--out-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.outstanding-page .out-btn:hover { color:#fff; transform:translateY(-2px); }
	.outstanding-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.outstanding-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.outstanding-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--out-primary-soft); color:var(--out-primary); }
	.outstanding-page .summary-icon.success{background:var(--out-success-soft);color:var(--out-success)}.outstanding-page .summary-icon.warning{background:var(--out-warning-soft);color:#b45309}.outstanding-page .summary-icon.danger{background:var(--out-danger-soft);color:var(--out-danger)}.outstanding-page .summary-icon.blue{background:var(--out-blue-soft);color:var(--out-blue)}
	.outstanding-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.outstanding-page .chart-wrap { height:230px; display:flex; align-items:end; gap:14px; padding-top:20px; border-bottom:1px solid rgba(148,163,184,.22); }
	.outstanding-page .chart-bar { flex:1; min-width:24px; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:8px; height:100%; }
	.outstanding-page .bar-fill { width:100%; max-width:44px; border-radius:10px 10px 4px 4px; background:linear-gradient(180deg,#f97316,#dc2626); }
	.outstanding-page .bar-label { color:var(--out-muted); font-size:12px; font-weight:900; }
	.outstanding-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.outstanding-page .table-scroll { max-height:640px; overflow:auto; }
	.outstanding-page .out-table { min-width:1120px; margin-bottom:0; }
	.outstanding-page .out-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--out-primary),var(--out-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.outstanding-page .out-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.outstanding-page .student-passport { width:46px; height:46px; border-radius:14px; object-fit:cover; border:2px solid #fff; box-shadow:0 8px 18px rgba(15,23,42,.12); }
	.outstanding-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.outstanding-page .status-fully-paid{color:var(--out-success);background:var(--out-success-soft)}.outstanding-page .status-partially-paid{color:#b45309;background:var(--out-warning-soft)}.outstanding-page .status-outstanding{color:var(--out-danger);background:var(--out-danger-soft)}
	.outstanding-page .row-actions,.outstanding-page .quick-actions { display:flex; gap:7px; flex-wrap:wrap; }
	.outstanding-page .pagination-wrap { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:14px 20px; border-top:1px solid rgba(148,163,184,.2); }
	.outstanding-page .page-btn { border:1px solid rgba(15,118,110,.2); color:var(--out-primary-dark); border-radius:10px; background:#fff; padding:7px 11px; font-weight:900; }
	.outstanding-page .page-btn.active { background:var(--out-primary); color:#fff; }
	.out-detail-photo { width:86px; height:86px; border-radius:22px; object-fit:cover; box-shadow:0 12px 28px rgba(15,23,42,.14); }
	@media(max-width:767.98px){ .outstanding-page .out-hero,.outstanding-page .out-card{padding:20px;border-radius:20px}.outstanding-page .row-actions,.outstanding-page .row-actions .btn,.outstanding-page .quick-actions .btn,.outstanding-page .out-btn{width:100%}.outstanding-page .pagination-wrap{align-items:flex-start;flex-direction:column}.outstanding-page .chart-wrap{gap:6px} }
</style>

<?php $maxClassBalance = max($classBalances); ?>
<div class="outstanding-page">
	<!-- Page header and breadcrumb. -->
	<section class="out-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Outstanding Fees</div>
		<span class="out-kicker"><i class="fa-solid fa-scale-unbalanced"></i> Outstanding Fees</span>
		<h3 class="mt-3 mb-2">Outstanding Fees</h3>
		<p class="text-muted mb-0">View and manage students with outstanding fee balances.</p>
	</section>

	<!-- Dashboard summary cards. -->
	<section class="row g-3 mb-4" aria-label="Outstanding fee summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo ofMoney($totalOutstanding); ?></h4><p class="text-muted mb-0">Total Outstanding Amount</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-users"></i></span><h4><?php echo count($students); ?> Students</h4><p class="text-muted mb-0">Students with Outstanding Fees</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-user-clock"></i></span><h4><?php echo $partiallyPaid; ?> Students</h4><p class="text-muted mb-0">Partially Paid Students</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-user-xmark"></i></span><h4><?php echo $fullyUnpaid; ?> Students</h4><p class="text-muted mb-0">Fully Unpaid Students</p></div></div>
	</section>

	<!-- Search and filter panel. -->
	<section class="out-card">
		<form id="outstandingFilterForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-3"><label class="form-label" for="sessionFilter">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionFilter"><option value="">All Sessions</option><?php foreach ($sessions as $session): ?><option><?php echo ofValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="termFilter">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termFilter"><option value="">All Terms</option><?php foreach ($terms as $term): ?><option><?php echo ofValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="classFilter">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classFilter"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option><?php echo ofValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="statusFilter">Payment Status</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-circle-check"></i></span><select class="form-select" id="statusFilter"><option value="">All Outstanding</option><option>Outstanding</option><option>Partially Paid</option><option>Fully Unpaid</option></select></div></div>
			<div class="col-md-3"><label class="form-label" for="minBalance">Minimum Balance</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" class="form-control" id="minBalance" placeholder="0"></div></div>
			<div class="col-md-3"><label class="form-label" for="maxBalance">Maximum Balance</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" class="form-control" id="maxBalance" placeholder="100000"></div></div>
			<div class="col-md-4"><label class="form-label" for="searchInput">Search Student</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-magnifying-glass"></i></span><input type="search" class="form-control" id="searchInput" placeholder="Student name or registration number"></div></div>
			<div class="col-md-2"><button type="submit" class="btn out-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
			<div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="resetFilters"><i class="fa-solid fa-rotate-left me-2"></i>Reset</button></div>
			<div class="col-md-2"><button type="button" class="btn btn-outline-success w-100" id="exportResults"><i class="fa-solid fa-file-export me-2"></i>Export</button></div>
		</form>
	</section>

	<!-- Outstanding balance analysis and class chart. -->
	<section class="row g-4">
		<div class="col-xl-5"><div class="out-card"><h4 class="mb-3">Outstanding Balance Analysis</h4><div class="row g-3"><div class="col-6"><strong>Highest Outstanding</strong><h5 id="highestBalance"><?php echo ofMoney(max($balances)); ?></h5></div><div class="col-6"><strong>Lowest Outstanding</strong><h5 id="lowestBalance"><?php echo ofMoney(min($balances)); ?></h5></div><div class="col-6"><strong>Average Outstanding</strong><h5 id="averageBalance"><?php echo ofMoney($totalOutstanding / count($balances)); ?></h5></div><div class="col-6"><strong>Total Outstanding</strong><h5 id="analysisTotal"><?php echo ofMoney($totalOutstanding); ?></h5></div></div></div></div>
		<div class="col-xl-7"><div class="out-card"><h4 class="mb-1">Outstanding Fees by Class</h4><p class="text-muted mb-0">Placeholder balance distribution by class group.</p><div class="chart-wrap"><?php foreach ($classBalances as $label => $value): ?><div class="chart-bar" title="<?php echo ofValue($label . ': ' . ofMoney($value)); ?>"><div class="bar-fill" style="height:<?php echo round(($value / $maxClassBalance) * 100); ?>%"></div><span class="bar-label"><?php echo ofValue($label); ?></span></div><?php endforeach; ?></div></div></div>
	</section>

	<!-- Quick actions and report generation controls. -->
	<section class="out-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Quick Actions & Reports</h4><p class="text-muted mb-0">Generate reports by class, session, term, balance amount, or payment status.</p></div><div class="quick-actions"><button type="button" class="btn out-btn"><i class="fa-solid fa-money-bill-transfer me-2"></i>Collect Payment</button><button type="button" class="btn btn-outline-success" id="exportCsv"><i class="fa-solid fa-file-csv me-2"></i>CSV</button><button type="button" class="btn btn-outline-success" id="exportExcel"><i class="fa-solid fa-file-excel me-2"></i>Excel</button><button type="button" class="btn btn-outline-danger" id="exportPdf"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button><button type="button" class="btn btn-outline-secondary" id="printList"><i class="fa-solid fa-print me-2"></i>Print</button><button type="button" class="btn btn-outline-warning" id="sendReminder"><i class="fa-solid fa-bell me-2"></i>Send Reminder</button></div></div>
	</section>

	<!-- Outstanding fees table with pagination and row actions. -->
	<section class="table-card">
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Outstanding Fee Records</h4><p class="text-muted mb-0">Monitor unpaid and partially paid fee balances.</p></div><div class="d-flex align-items-center gap-2"><label class="form-label mb-0" for="pageSize">Records</label><select class="form-select" id="pageSize" style="width:100px;padding-left:12px;"><option>10</option><option>25</option><option>50</option><option>100</option></select></div></div>
		<div class="table-scroll"><table class="table out-table align-middle"><thead><tr><th>Passport</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Total Fees</th><th>Amount Paid</th><th>Outstanding Balance</th><th>Payment Status</th><th>Actions</th></tr></thead><tbody id="outstandingBody"></tbody></table></div>
		<div class="pagination-wrap"><span class="text-muted fw-bold" id="recordInfo">Showing records</span><div id="pagination" class="d-flex gap-2 flex-wrap"></div></div>
	</section>
</div>

<!-- Outstanding fee details modal. -->
<div class="modal fade" id="outstandingModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden;"><div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Outstanding Fee Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="outstandingDetailsBody"></div></div></div></div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Outstanding fees behavior: filtering, analysis, details modal, exports, printing, and pagination.
(function () {
	var students = <?php echo json_encode($students); ?>;
	var filtered = students.slice();
	var currentPage = 1;
	function byId(id) { return document.getElementById(id); }
	function money(amount) { return '₦' + Number(amount || 0).toLocaleString(); }
	function balance(s) { return Math.max(0, Number(s.total) - Number(s.paid)); }
	function statusFor(s) { if (Number(s.paid) >= Number(s.total)) return 'Fully Paid'; if (Number(s.paid) > 0) return 'Partially Paid'; return 'Outstanding'; }
	function statusClass(status) { return status.toLowerCase().replace(/\s+/g, '-'); }
	function csvEscape(v) { return '"' + String(v).replace(/"/g, '""') + '"'; }
	function rowsForExport(rows) { return [['Registration No.','Student Name','Class','Session','Term','Total Fees','Amount Paid','Outstanding Balance','Payment Status']].concat(rows.map(function (s) { return [s.reg_no,s.name,s.class,s.session,s.term,s.total,s.paid,balance(s),statusFor(s)]; })); }
	function downloadCsv(rows, filename) { var csv = rowsForExport(rows).map(function (row) { return row.map(csvEscape).join(','); }).join('\n'); var link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type:'text/csv;charset=utf-8;' })); link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href); }
	function updateAnalysis() { var balances = filtered.map(balance); var total = balances.reduce(function (s, v) { return s + v; }, 0); byId('highestBalance').textContent = money(balances.length ? Math.max.apply(Math, balances) : 0); byId('lowestBalance').textContent = money(balances.length ? Math.min.apply(Math, balances) : 0); byId('averageBalance').textContent = money(balances.length ? total / balances.length : 0); byId('analysisTotal').textContent = money(total); }
	function renderTable() { var size = parseInt(byId('pageSize').value, 10); var pages = Math.max(1, Math.ceil(filtered.length / size)); if (currentPage > pages) currentPage = pages; var start = (currentPage - 1) * size; var rows = filtered.slice(start, start + size); byId('outstandingBody').innerHTML = rows.map(function (s) { var st = statusFor(s); return '<tr><td><img src="' + s.passport + '" class="student-passport" alt="Passport"></td><td>' + s.reg_no + '</td><td>' + s.name + '</td><td>' + s.class + '</td><td>' + money(s.total) + '</td><td>' + money(s.paid) + '</td><td>' + money(balance(s)) + '</td><td><span class="status-badge status-' + statusClass(st) + '"><i class="fa-solid fa-circle"></i>' + st + '</span></td><td><div class="row-actions"><button type="button" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-user"></i> Profile</button><button type="button" class="btn btn-sm btn-outline-success details-btn" data-reg="' + s.reg_no + '"><i class="fa-solid fa-file-invoice"></i> Fee Details</button><button type="button" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-money-bill-transfer"></i> Collect</button><button type="button" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-clock-rotate-left"></i> History</button><button type="button" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-file-lines"></i> Statement</button></div></td></tr>'; }).join('') || '<tr><td colspan="9" class="text-center text-muted fw-bold py-4">No outstanding records found.</td></tr>'; byId('recordInfo').textContent = 'Showing ' + (filtered.length ? start + 1 : 0) + ' - ' + Math.min(start + size, filtered.length) + ' of ' + filtered.length + ' records'; byId('pagination').innerHTML = '<button type="button" class="page-btn" data-page="' + Math.max(1, currentPage - 1) + '">Previous</button>' + Array.from({ length: pages }, function (_, i) { return '<button type="button" class="page-btn ' + (currentPage === i + 1 ? 'active' : '') + '" data-page="' + (i + 1) + '">' + (i + 1) + '</button>'; }).join('') + '<button type="button" class="page-btn" data-page="' + Math.min(pages, currentPage + 1) + '">Next</button>'; updateAnalysis(); }
	function applyFilters() { var session = byId('sessionFilter').value, term = byId('termFilter').value, klass = byId('classFilter').value, status = byId('statusFilter').value, min = parseFloat(byId('minBalance').value), max = parseFloat(byId('maxBalance').value), search = byId('searchInput').value.toLowerCase().trim(); filtered = students.filter(function (s) { var st = statusFor(s); var compareStatus = status === 'Fully Unpaid' ? 'Outstanding' : status; var bal = balance(s); return (!session || s.session === session) && (!term || s.term === term) && (!klass || s.class === klass) && (!compareStatus || st === compareStatus) && (isNaN(min) || bal >= min) && (isNaN(max) || bal <= max) && (!search || s.name.toLowerCase().indexOf(search) !== -1 || s.reg_no.toLowerCase().indexOf(search) !== -1); }); currentPage = 1; renderTable(); }
	function showDetails(reg) { var s = students.find(function (item) { return item.reg_no === reg; }); if (!s) return; var rows = s.fees.map(function (fee) { return '<tr><td>' + fee.item + '</td><td>' + money(fee.amount) + '</td><td><span class="status-badge status-' + statusClass(fee.status) + '">' + fee.status + '</span></td></tr>'; }).join(''); byId('outstandingDetailsBody').innerHTML = '<div class="d-flex align-items-center gap-3 flex-wrap mb-3"><img src="' + s.passport + '" class="out-detail-photo" alt="Passport"><div><h4 class="mb-1">' + s.name + '</h4><p class="text-muted mb-0">' + s.reg_no + ' | ' + s.class + ' | ' + s.session + ' | ' + s.term + '</p></div></div><div class="table-responsive"><table class="table"><thead><tr><th>Fee Item</th><th>Amount</th><th>Status</th></tr></thead><tbody>' + rows + '</tbody></table></div><div class="row g-3"><div class="col-md-4"><div class="p-3 rounded-3 bg-light"><strong>Total Fees</strong><h5>' + money(s.total) + '</h5></div></div><div class="col-md-4"><div class="p-3 rounded-3 bg-light"><strong>Amount Paid</strong><h5>' + money(s.paid) + '</h5></div></div><div class="col-md-4"><div class="p-3 rounded-3 bg-light"><strong>Outstanding Balance</strong><h5>' + money(balance(s)) + '</h5></div></div></div>'; new bootstrap.Modal(document.getElementById('outstandingModal')).show(); }
	function printRows(rows) { var body = rowsForExport(rows).slice(1).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join(''); var win = window.open('', '_blank'); win.document.write('<html><head><title>Outstanding Fees</title><style>@page{size:A4 landscape;margin:12mm}body{font-family:Arial,sans-serif}table{width:100%;border-collapse:collapse;font-size:11px}td,th{border:1px solid #777;padding:6px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>Outstanding Fees Report</h2><table><thead><tr><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Session</th><th>Term</th><th>Total Fees</th><th>Amount Paid</th><th>Outstanding Balance</th><th>Status</th></tr></thead><tbody>' + body + '</tbody></table></body></html>'); win.document.close(); win.focus(); win.print(); }
	document.addEventListener('DOMContentLoaded', renderTable);
	byId('outstandingFilterForm').addEventListener('submit', function (e) { e.preventDefault(); applyFilters(); });
	byId('resetFilters').addEventListener('click', function () { byId('outstandingFilterForm').reset(); applyFilters(); });
	byId('pageSize').addEventListener('change', function () { currentPage = 1; renderTable(); });
	byId('pagination').addEventListener('click', function (e) { if (e.target.classList.contains('page-btn')) { currentPage = parseInt(e.target.getAttribute('data-page'), 10); renderTable(); } });
	document.addEventListener('click', function (e) { var details = e.target.closest('.details-btn'); if (details) showDetails(details.getAttribute('data-reg')); });
	byId('exportCsv').addEventListener('click', function () { downloadCsv(filtered, 'Outstanding_Fees.csv'); });
	byId('exportExcel').addEventListener('click', function () { downloadCsv(filtered, 'Outstanding_Fees.xls'); });
	byId('exportPdf').addEventListener('click', function () { printRows(filtered); });
	byId('printList').addEventListener('click', function () { printRows(filtered); });
	byId('exportResults').addEventListener('click', function () { downloadCsv(filtered, 'Filtered_Outstanding_Fees.csv'); });
	byId('sendReminder').addEventListener('click', function () { alert('Payment reminder placeholder: SMS/email integration can be connected later.'); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
