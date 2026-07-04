<?php require_once('includes/header.php'); ?>

<?php
// Student fee placeholder data. Replace these arrays with database queries for sessions, fee structure, payments, and student balances later.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$classes = ['JSS 1A', 'JSS 1B', 'JSS 2A', 'SS 1 Science', 'SS 3 Arts'];
$sections = ['Science', 'Commercial', 'Arts'];
$feeBreakdown = [
	['item' => 'Tuition Fee', 'amount' => 80000],
	['item' => 'Examination Fee', 'amount' => 5000],
	['item' => 'Sports Fee', 'amount' => 3000],
	['item' => 'Library Fee', 'amount' => 2000],
	['item' => 'Development Levy', 'amount' => 10000]
];
$students = [
	['passport' => '../assets/img/students/student-01.jpg', 'reg_no' => 'BFSS/SS1/001', 'name' => 'Musa Ibrahim', 'class' => 'SS 1 Science', 'section' => 'Science', 'session' => '2025/2026', 'term' => 'First Term', 'total' => 100000, 'paid' => 100000],
	['passport' => '../assets/img/students/student-02.jpg', 'reg_no' => 'BFSS/JSS1A/014', 'name' => 'Aisha Bello', 'class' => 'JSS 1A', 'section' => 'Arts', 'session' => '2025/2026', 'term' => 'First Term', 'total' => 95000, 'paid' => 55000],
	['passport' => '../assets/img/students/student-03.jpg', 'reg_no' => 'BFSS/JSS2A/022', 'name' => 'Daniel Okafor', 'class' => 'JSS 2A', 'section' => 'Commercial', 'session' => '2025/2026', 'term' => 'Second Term', 'total' => 98000, 'paid' => 0],
	['passport' => '../assets/img/students/student-04.jpg', 'reg_no' => 'BFSS/SS3ART/006', 'name' => 'Maryam Musa', 'class' => 'SS 3 Arts', 'section' => 'Arts', 'session' => '2026/2027', 'term' => 'First Term', 'total' => 120000, 'paid' => 90000],
	['passport' => '../assets/img/students/student-05.jpg', 'reg_no' => 'BFSS/JSS1B/031', 'name' => 'Samuel Adeyemi', 'class' => 'JSS 1B', 'section' => 'Science', 'session' => '2025/2026', 'term' => 'Third Term', 'total' => 90000, 'paid' => 90000],
	['passport' => '../assets/img/students/student-06.jpg', 'reg_no' => 'BFSS/SS1SCI/019', 'name' => 'Fatima Abdullahi', 'class' => 'SS 1 Science', 'section' => 'Science', 'session' => '2025/2026', 'term' => 'First Term', 'total' => 100000, 'paid' => 30000]
];
function feeMoney($amount) { return '₦' . number_format((float) $amount); }
function feeValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function feeStatus($total, $paid) { if ($paid >= $total) { return 'Paid'; } if ($paid > 0) { return 'Partially Paid'; } return 'Outstanding'; }
$totalStudents = count($students);
$paidStudents = count(array_filter($students, fn($student) => feeStatus($student['total'], $student['paid']) === 'Paid'));
$outstandingCount = $totalStudents - $paidStudents;
$totalOutstanding = array_reduce($students, fn($sum, $student) => $sum + max(0, $student['total'] - $student['paid']), 0);
?>

<style>
	/* Student fee management: scoped premium finance styles for search, records, actions, and exports. */
	.student-fees-page { --fee-primary:#0f766e; --fee-primary-dark:#115e59; --fee-primary-soft:rgba(15,118,110,.1); --fee-success:#16a34a; --fee-success-soft:rgba(22,163,74,.12); --fee-warning:#f59e0b; --fee-warning-soft:rgba(245,158,11,.14); --fee-danger:#dc2626; --fee-danger-soft:rgba(220,38,38,.1); --fee-blue:#2563eb; --fee-blue-soft:rgba(37,99,235,.1); --fee-ink:#10201d; --fee-muted:#64748b; --fee-border:rgba(15,118,110,.18); --fee-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.student-fees-page .fee-hero,.student-fees-page .fee-card,.student-fees-page .summary-card,.student-fees-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--fee-border); box-shadow:var(--fee-shadow); }
	.student-fees-page .fee-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.student-fees-page .breadcrumb-line { color:var(--fee-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.student-fees-page .breadcrumb-line a { color:var(--fee-primary-dark); text-decoration:none; }
	.student-fees-page .fee-kicker,.student-fees-page .field-icon,.student-fees-page .summary-icon,.student-fees-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.student-fees-page .fee-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--fee-primary-soft); color:var(--fee-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.student-fees-page h3,.student-fees-page h4,.student-fees-page h5 { color:var(--fee-ink); font-weight:900; }
	.student-fees-page .fee-card,.student-fees-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.student-fees-page .fee-card { padding:24px; }
	.student-fees-page .form-label { color:var(--fee-ink); font-size:13px; font-weight:900; }
	.student-fees-page .field-wrap { position:relative; }
	.student-fees-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fee-primary); pointer-events:none; }
	.student-fees-page .form-select,.student-fees-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.student-fees-page .form-select:focus,.student-fees-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.student-fees-page .search-btn,.student-fees-page .export-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--fee-primary),var(--fee-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.student-fees-page .search-btn:hover,.student-fees-page .export-btn:hover { color:#fff; transform:translateY(-2px); }
	.student-fees-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.student-fees-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.student-fees-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--fee-primary-soft); color:var(--fee-primary); }
	.student-fees-page .summary-icon.success{background:var(--fee-success-soft);color:var(--fee-success)} .student-fees-page .summary-icon.warning{background:var(--fee-warning-soft);color:#b45309} .student-fees-page .summary-icon.danger{background:var(--fee-danger-soft);color:var(--fee-danger)}
	.student-fees-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.student-fees-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.student-fees-page .table-scroll { max-height:620px; overflow:auto; }
	.student-fees-page .fee-table { min-width:1120px; margin-bottom:0; }
	.student-fees-page .fee-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--fee-primary),var(--fee-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.student-fees-page .fee-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.student-fees-page .fee-table tbody tr:hover { background:rgba(15,118,110,.04); }
	.student-fees-page .student-passport { width:46px; height:46px; border-radius:14px; object-fit:cover; border:2px solid #fff; box-shadow:0 8px 18px rgba(15,23,42,.12); }
	.student-fees-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.student-fees-page .status-paid{color:var(--fee-success);background:var(--fee-success-soft)} .student-fees-page .status-partially-paid{color:#b45309;background:var(--fee-warning-soft)} .student-fees-page .status-outstanding{color:var(--fee-danger);background:var(--fee-danger-soft)}
	.student-fees-page .row-actions { display:flex; gap:7px; flex-wrap:wrap; }
	.student-fees-page .bulk-actions { display:flex; gap:10px; flex-wrap:wrap; }
	.student-fees-page .pagination-wrap { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:14px 20px; border-top:1px solid rgba(148,163,184,.2); }
	.student-fees-page .page-btn { border:1px solid rgba(15,118,110,.2); color:var(--fee-primary-dark); border-radius:10px; background:#fff; padding:7px 11px; font-weight:900; }
	.student-fees-page .page-btn.active { background:var(--fee-primary); color:#fff; }
	.fee-detail-photo { width:86px; height:86px; border-radius:22px; object-fit:cover; box-shadow:0 12px 28px rgba(15,23,42,.14); }
	@media(max-width:767.98px){ .student-fees-page .fee-hero,.student-fees-page .fee-card{padding:20px;border-radius:20px}.student-fees-page .bulk-actions,.student-fees-page .bulk-actions .btn,.student-fees-page .row-actions .btn,.student-fees-page .export-btn{width:100%}.student-fees-page .pagination-wrap{align-items:flex-start;flex-direction:column} }
</style>

<div class="student-fees-page">
	<!-- Page header and breadcrumb for the accountant fee workflow. -->
	<section class="fee-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Student Fee Management</div>
		<span class="fee-kicker"><i class="fa-solid fa-money-check-dollar"></i> Finance Module</span>
		<h3 class="mt-3 mb-2">Student Fee Management</h3>
		<p class="text-muted mb-0">View, search, and manage student fee records.</p>
	</section>

	<!-- Search and filter card for sessions, classes, statuses, and student lookup. -->
	<section class="fee-card">
		<form id="feeFilterForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-3"><label class="form-label" for="sessionFilter">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionFilter"><option value="">All Sessions</option><?php foreach ($sessions as $session): ?><option value="<?php echo feeValue($session); ?>"><?php echo feeValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="termFilter">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termFilter"><option value="">All Terms</option><?php foreach ($terms as $term): ?><option value="<?php echo feeValue($term); ?>"><?php echo feeValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="classFilter">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classFilter"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option value="<?php echo feeValue($class); ?>"><?php echo feeValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="sectionFilter">Section</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-users-rectangle"></i></span><select class="form-select" id="sectionFilter"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option value="<?php echo feeValue($section); ?>"><?php echo feeValue($section); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="statusFilter">Payment Status</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-circle-check"></i></span><select class="form-select" id="statusFilter"><option value="">All Students</option><option value="Paid">Paid</option><option value="Partially Paid">Partially Paid</option><option value="Outstanding">Outstanding</option></select></div></div>
			<div class="col-md-5"><label class="form-label" for="searchInput">Search Student</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-magnifying-glass"></i></span><input type="search" class="form-control" id="searchInput" placeholder="Registration number or student name"></div></div>
			<div class="col-md-2"><button type="submit" class="btn search-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
			<div class="col-md-2"><button type="button" class="btn btn-outline-secondary w-100" id="resetFilters"><i class="fa-solid fa-rotate-left me-2"></i>Reset Filters</button></div>
		</form>
	</section>

	<!-- Payment summary cards update with the current filtered data. -->
	<section class="row g-3 mb-4" aria-label="Payment summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4 id="summaryTotal"><?php echo $totalStudents; ?></h4><p class="text-muted mb-0">Total Students</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-user-check"></i></span><h4 id="summaryPaid"><?php echo $paidStudents; ?></h4><p class="text-muted mb-0">Students Paid</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-user-clock"></i></span><h4 id="summaryOutstanding"><?php echo $outstandingCount; ?></h4><p class="text-muted mb-0">Outstanding Students</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-scale-unbalanced"></i></span><h4 id="summaryBalance"><?php echo feeMoney($totalOutstanding); ?></h4><p class="text-muted mb-0">Total Outstanding Fees</p></div></div>
	</section>

	<!-- Student fee table with bulk actions, exports, pagination, and row actions. -->
	<section class="table-card">
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Student Fee Records</h4><p class="text-muted mb-0">Select students for bulk exports, statements, printouts, or reminders.</p></div><div class="bulk-actions"><button type="button" class="btn btn-outline-success" id="bulkExport"><i class="fa-solid fa-file-export me-2"></i>Export Selected</button><button type="button" class="btn btn-outline-secondary" id="bulkPrint"><i class="fa-solid fa-print me-2"></i>Print Fee List</button><button type="button" class="btn btn-outline-primary" id="bulkStatements"><i class="fa-solid fa-file-invoice me-2"></i>Generate Fee Statements</button><button type="button" class="btn btn-outline-warning" id="bulkReminder"><i class="fa-solid fa-bell me-2"></i>Send Payment Reminder</button></div></div>
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div class="bulk-actions"><button type="button" class="btn export-btn" id="exportCsv"><i class="fa-solid fa-file-csv me-2"></i>CSV</button><button type="button" class="btn export-btn" id="exportExcel"><i class="fa-solid fa-file-excel me-2"></i>Excel</button><button type="button" class="btn export-btn" id="exportPdf"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button><button type="button" class="btn btn-outline-secondary" id="printTable"><i class="fa-solid fa-print me-2"></i>Print</button></div><div class="d-flex align-items-center gap-2"><label class="form-label mb-0" for="pageSize">Records</label><select class="form-select" id="pageSize" style="width:100px;padding-left:12px;"><option>10</option><option>25</option><option>50</option><option>100</option></select></div></div>
		<div class="table-scroll"><table class="table fee-table align-middle"><thead><tr><th><input type="checkbox" id="selectAll"></th><th>Passport</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Total Fees</th><th>Amount Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead><tbody id="feeTableBody"></tbody></table></div>
		<div class="pagination-wrap"><span class="text-muted fw-bold" id="recordInfo">Showing records</span><div id="pagination" class="d-flex gap-2 flex-wrap"></div></div>
	</section>
</div>

<!-- Fee details modal: shows student information and reusable fee breakdown. -->
<div class="modal fade" id="feeDetailsModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden;"><div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Student Fee Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="feeDetailsBody"></div></div></div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Student fee management behavior: filters, pagination, detail modal, bulk actions, exports, and printing.
(function () {
	var students = <?php echo json_encode($students); ?>;
	var feeBreakdown = <?php echo json_encode($feeBreakdown); ?>;
	var filtered = students.slice();
	var currentPage = 1;
	function byId(id) { return document.getElementById(id); }
	function money(amount) { return '₦' + Number(amount || 0).toLocaleString(); }
	function statusFor(student) { if (Number(student.paid) >= Number(student.total)) return 'Paid'; if (Number(student.paid) > 0) return 'Partially Paid'; return 'Outstanding'; }
	function statusClass(status) { return status.toLowerCase().replace(/\s+/g, '-'); }
	function escapeCsv(value) { return '"' + String(value).replace(/"/g, '""') + '"'; }
	function selectedStudents() { var ids = Array.prototype.map.call(document.querySelectorAll('.row-check:checked'), function (box) { return box.value; }); return filtered.filter(function (student) { return ids.indexOf(student.reg_no) !== -1; }); }
	function rowsForExport(rows) { return [['Registration No.', 'Student Name', 'Class', 'Session', 'Term', 'Total Fees', 'Amount Paid', 'Balance', 'Status']].concat(rows.map(function (s) { return [s.reg_no, s.name, s.class, s.session, s.term, s.total, s.paid, Number(s.total) - Number(s.paid), statusFor(s)]; })); }
	function downloadCsv(rows, filename) { var csv = rowsForExport(rows).map(function (row) { return row.map(escapeCsv).join(','); }).join('\n'); var link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' })); link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href); }
	function updateSummary() { var paid = filtered.filter(function (s) { return statusFor(s) === 'Paid'; }).length; var balance = filtered.reduce(function (sum, s) { return sum + Math.max(0, Number(s.total) - Number(s.paid)); }, 0); byId('summaryTotal').textContent = filtered.length; byId('summaryPaid').textContent = paid; byId('summaryOutstanding').textContent = filtered.length - paid; byId('summaryBalance').textContent = money(balance); }
	function renderTable() { var size = parseInt(byId('pageSize').value, 10); var pages = Math.max(1, Math.ceil(filtered.length / size)); if (currentPage > pages) currentPage = pages; var start = (currentPage - 1) * size; var pageRows = filtered.slice(start, start + size); byId('feeTableBody').innerHTML = pageRows.map(function (s) { var bal = Number(s.total) - Number(s.paid); var st = statusFor(s); return '<tr><td><input type="checkbox" class="row-check" value="' + s.reg_no + '"></td><td><img src="' + s.passport + '" class="student-passport" alt="Passport"></td><td>' + s.reg_no + '</td><td>' + s.name + '</td><td>' + s.class + '</td><td>' + money(s.total) + '</td><td>' + money(s.paid) + '</td><td>' + money(bal) + '</td><td><span class="status-badge status-' + statusClass(st) + '"><i class="fa-solid fa-circle"></i>' + st + '</span></td><td><div class="row-actions"><button type="button" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-user"></i> View Profile</button><button type="button" class="btn btn-sm btn-outline-success details-btn" data-reg="' + s.reg_no + '"><i class="fa-solid fa-file-invoice"></i> Fee Details</button><button type="button" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-money-bill-transfer"></i> Collect Payment</button><button type="button" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-clock-rotate-left"></i> History</button></div></td></tr>'; }).join('') || '<tr><td colspan="10" class="text-center text-muted fw-bold py-4">No student fee records found.</td></tr>'; byId('recordInfo').textContent = 'Showing ' + (filtered.length ? start + 1 : 0) + ' - ' + Math.min(start + size, filtered.length) + ' of ' + filtered.length + ' records'; byId('pagination').innerHTML = Array.from({ length: pages }, function (_, i) { return '<button type="button" class="page-btn ' + (currentPage === i + 1 ? 'active' : '') + '" data-page="' + (i + 1) + '">' + (i + 1) + '</button>'; }).join(''); updateSummary(); byId('selectAll').checked = false; }
	function applyFilters() { var session = byId('sessionFilter').value, term = byId('termFilter').value, klass = byId('classFilter').value, section = byId('sectionFilter').value, status = byId('statusFilter').value, search = byId('searchInput').value.toLowerCase().trim(); filtered = students.filter(function (s) { return (!session || s.session === session) && (!term || s.term === term) && (!klass || s.class === klass) && (!section || s.section === section) && (!status || statusFor(s) === status) && (!search || s.reg_no.toLowerCase().indexOf(search) !== -1 || s.name.toLowerCase().indexOf(search) !== -1); }); currentPage = 1; renderTable(); }
	function showDetails(regNo) { var s = students.find(function (item) { return item.reg_no === regNo; }); if (!s) return; var breakdown = feeBreakdown.map(function (fee) { return '<tr><td>' + fee.item + '</td><td>' + money(fee.amount) + '</td></tr>'; }).join(''); var bal = Number(s.total) - Number(s.paid); byId('feeDetailsBody').innerHTML = '<div class="d-flex align-items-center gap-3 flex-wrap mb-3"><img src="' + s.passport + '" class="fee-detail-photo" alt="Passport"><div><h4 class="mb-1">' + s.name + '</h4><p class="text-muted mb-0">' + s.reg_no + ' | ' + s.class + ' | ' + s.session + ' | ' + s.term + '</p></div></div><div class="table-responsive"><table class="table"><thead><tr><th>Fee Item</th><th>Amount</th></tr></thead><tbody>' + breakdown + '</tbody></table></div><div class="row g-3"><div class="col-md-4"><div class="p-3 rounded-3 bg-light"><strong>Total Fees</strong><h5>' + money(s.total) + '</h5></div></div><div class="col-md-4"><div class="p-3 rounded-3 bg-light"><strong>Amount Paid</strong><h5>' + money(s.paid) + '</h5></div></div><div class="col-md-4"><div class="p-3 rounded-3 bg-light"><strong>Outstanding Balance</strong><h5>' + money(bal) + '</h5></div></div></div>'; new bootstrap.Modal(document.getElementById('feeDetailsModal')).show(); }
	function printRows(rows) { var body = rowsForExport(rows).slice(1).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join(''); var win = window.open('', '_blank'); win.document.write('<html><head><title>Student Fee List</title><style>@page{size:A4 landscape;margin:12mm}body{font-family:Arial,sans-serif;color:#10201d}h2{text-transform:uppercase}table{width:100%;border-collapse:collapse;font-size:11px}td,th{border:1px solid #777;padding:6px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>Student Fee Management</h2><table><thead><tr><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Session</th><th>Term</th><th>Total Fees</th><th>Amount Paid</th><th>Balance</th><th>Status</th></tr></thead><tbody>' + body + '</tbody></table></body></html>'); win.document.close(); win.focus(); win.print(); }
	document.addEventListener('DOMContentLoaded', renderTable);
	byId('feeFilterForm').addEventListener('submit', function (event) { event.preventDefault(); applyFilters(); });
	byId('resetFilters').addEventListener('click', function () { byId('feeFilterForm').reset(); applyFilters(); });
	byId('pageSize').addEventListener('change', function () { currentPage = 1; renderTable(); });
	byId('pagination').addEventListener('click', function (event) { if (event.target.classList.contains('page-btn')) { currentPage = parseInt(event.target.getAttribute('data-page'), 10); renderTable(); } });
	byId('selectAll').addEventListener('change', function () { document.querySelectorAll('.row-check').forEach(function (box) { box.checked = byId('selectAll').checked; }); });
	document.addEventListener('click', function (event) { var details = event.target.closest('.details-btn'); if (details) showDetails(details.getAttribute('data-reg')); });
	byId('exportCsv').addEventListener('click', function () { downloadCsv(filtered, 'Student_Fee_Records.csv'); });
	byId('exportExcel').addEventListener('click', function () { downloadCsv(filtered, 'Student_Fee_Records.xls'); });
	byId('exportPdf').addEventListener('click', function () { printRows(filtered); });
	byId('printTable').addEventListener('click', function () { printRows(filtered); });
	byId('bulkExport').addEventListener('click', function () { var rows = selectedStudents(); if (!rows.length) { alert('Please select at least one student.'); return; } downloadCsv(rows, 'Selected_Student_Fees.csv'); });
	byId('bulkPrint').addEventListener('click', function () { var rows = selectedStudents(); if (!rows.length) { alert('Please select at least one student.'); return; } printRows(rows); });
	byId('bulkStatements').addEventListener('click', function () { alert('Fee statements generated for selected students. This is ready for database/PDF integration.'); });
	byId('bulkReminder').addEventListener('click', function () { alert('Payment reminder placeholder: SMS/email integration can be connected later.'); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
