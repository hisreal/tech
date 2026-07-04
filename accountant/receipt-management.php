<?php require_once('includes/header.php'); ?>

<?php
// Receipt management placeholder data. Replace with receipt/payment database queries during backend integration.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$classes = ['JSS 1A', 'JSS 2B', 'SS 1 Science'];
$methods = ['Cash', 'Bank Transfer', 'POS', 'Online Payment'];
$school = ['name' => 'Brighter Future Standard School, Katsina', 'address' => 'Along Old KTTV, Gawu Road, Near Layout Primary School, Katsina', 'phone' => '08169192710, 08158592533', 'email' => 'accounts@bfss.edu.ng', 'logo' => '../assets/img/logo/school-logo.png'];
$accountantName = 'John Ibrahim';
$receipts = [
	['receipt' => 'RCP-2026-000123', 'date' => '2026-07-05', 'reg_no' => 'REG001', 'student' => 'Musa Ibrahim', 'class' => 'SS 1 Science', 'passport' => '../assets/img/students/student-01.jpg', 'session' => '2025/2026', 'term' => 'First Term', 'amount' => 85000, 'method' => 'Bank Transfer', 'status' => 'Paid', 'reference' => 'BT-887721', 'items' => [['item' => 'Tuition Fee', 'amount' => 80000], ['item' => 'Examination Fee', 'amount' => 5000]]],
	['receipt' => 'RCP-2026-000124', 'date' => '2026-07-06', 'reg_no' => 'REG014', 'student' => 'Aisha Bello', 'class' => 'JSS 2B', 'passport' => '../assets/img/students/student-02.jpg', 'session' => '2025/2026', 'term' => 'First Term', 'amount' => 40000, 'method' => 'POS', 'status' => 'Paid', 'reference' => 'POS-12045', 'items' => [['item' => 'Tuition Fee', 'amount' => 35000], ['item' => 'Sports Fee', 'amount' => 5000]]],
	['receipt' => 'RCP-2026-000125', 'date' => '2026-07-04', 'reg_no' => 'REG022', 'student' => 'Daniel Okafor', 'class' => 'JSS 1A', 'passport' => '../assets/img/students/student-03.jpg', 'session' => '2025/2026', 'term' => 'Second Term', 'amount' => 65000, 'method' => 'Cash', 'status' => 'Cancelled', 'reference' => 'CASH-VOID', 'items' => [['item' => 'School Fees', 'amount' => 65000]]],
	['receipt' => 'RCP-2026-000126', 'date' => '2026-07-03', 'reg_no' => 'REG031', 'student' => 'Maryam Musa', 'class' => 'SS 1 Science', 'passport' => '../assets/img/students/student-04.jpg', 'session' => '2026/2027', 'term' => 'First Term', 'amount' => 20000, 'method' => 'Online Payment', 'status' => 'Refunded', 'reference' => 'WEB-91120', 'items' => [['item' => 'Development Levy', 'amount' => 20000]]]
];
function receiptMoney($amount) { return '₦' . number_format((float) $amount); }
function receiptValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$totalReceipts = count($receipts);
$todayReceipts = count(array_filter($receipts, fn($r) => $r['date'] === '2026-07-06'));
$todayCollection = array_reduce(array_filter($receipts, fn($r) => $r['date'] === '2026-07-06' && $r['status'] === 'Paid'), fn($s, $r) => $s + $r['amount'], 0);
$monthlyCollection = array_reduce(array_filter($receipts, fn($r) => substr($r['date'], 0, 7) === '2026-07' && $r['status'] === 'Paid'), fn($s, $r) => $s + $r['amount'], 0);
?>

<style>
	/* Receipt management module: scoped premium finance dashboard styles. */
	.receipt-page { --rc-primary:#0f766e; --rc-primary-dark:#115e59; --rc-primary-soft:rgba(15,118,110,.1); --rc-success:#16a34a; --rc-success-soft:rgba(22,163,74,.12); --rc-warning:#f59e0b; --rc-warning-soft:rgba(245,158,11,.14); --rc-danger:#dc2626; --rc-danger-soft:rgba(220,38,38,.1); --rc-blue:#2563eb; --rc-blue-soft:rgba(37,99,235,.1); --rc-ink:#10201d; --rc-muted:#64748b; --rc-border:rgba(15,118,110,.18); --rc-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.receipt-page .rc-hero,.receipt-page .rc-card,.receipt-page .summary-card,.receipt-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--rc-border); box-shadow:var(--rc-shadow); }
	.receipt-page .rc-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.receipt-page .breadcrumb-line { color:var(--rc-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.receipt-page .breadcrumb-line a { color:var(--rc-primary-dark); text-decoration:none; }
	.receipt-page .rc-kicker,.receipt-page .field-icon,.receipt-page .summary-icon,.receipt-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.receipt-page .rc-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--rc-primary-soft); color:var(--rc-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.receipt-page h3,.receipt-page h4,.receipt-page h5 { color:var(--rc-ink); font-weight:900; }
	.receipt-page .rc-card,.receipt-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.receipt-page .rc-card { padding:24px; }
	.receipt-page .form-label { color:var(--rc-ink); font-size:13px; font-weight:900; }
	.receipt-page .field-wrap { position:relative; }
	.receipt-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--rc-primary); pointer-events:none; }
	.receipt-page .form-select,.receipt-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.receipt-page .form-select:focus,.receipt-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.receipt-page .rc-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--rc-primary),var(--rc-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.receipt-page .rc-btn:hover { color:#fff; transform:translateY(-2px); }
	.receipt-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.receipt-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.receipt-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--rc-primary-soft); color:var(--rc-primary); }
	.receipt-page .summary-icon.success{background:var(--rc-success-soft);color:var(--rc-success)}.receipt-page .summary-icon.warning{background:var(--rc-warning-soft);color:#b45309}.receipt-page .summary-icon.blue{background:var(--rc-blue-soft);color:var(--rc-blue)}
	.receipt-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.receipt-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.receipt-page .table-scroll { max-height:620px; overflow:auto; }
	.receipt-page .receipt-table { min-width:1060px; margin-bottom:0; }
	.receipt-page .receipt-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--rc-primary),var(--rc-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.receipt-page .receipt-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.receipt-page .receipt-table tbody tr:hover { background:rgba(15,118,110,.04); }
	.receipt-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.receipt-page .status-paid{color:var(--rc-success);background:var(--rc-success-soft)}.receipt-page .status-cancelled{color:var(--rc-danger);background:var(--rc-danger-soft)}.receipt-page .status-refunded{color:#b45309;background:var(--rc-warning-soft)}
	.receipt-page .row-actions { display:flex; gap:7px; flex-wrap:wrap; }
	.receipt-page .pagination-wrap { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:14px 20px; border-top:1px solid rgba(148,163,184,.2); }
	.receipt-page .page-btn { border:1px solid rgba(15,118,110,.2); color:var(--rc-primary-dark); border-radius:10px; background:#fff; padding:7px 11px; font-weight:900; }
	.receipt-page .page-btn.active { background:var(--rc-primary); color:#fff; }
	.receipt-preview { color:#10201d; }
	.receipt-preview .school-logo { width:76px; height:76px; object-fit:contain; }
	.receipt-preview .student-photo { width:70px; height:70px; border-radius:18px; object-fit:cover; }
	.receipt-preview .stamp-box { border:2px dashed #94a3b8; border-radius:14px; min-height:68px; display:flex; align-items:center; justify-content:center; color:#64748b; font-weight:800; }
	@media(max-width:767.98px){ .receipt-page .rc-hero,.receipt-page .rc-card{padding:20px;border-radius:20px}.receipt-page .row-actions,.receipt-page .row-actions .btn,.receipt-page .rc-btn{width:100%}.receipt-page .pagination-wrap{align-items:flex-start;flex-direction:column} }
</style>

<div class="receipt-page">
	<!-- Page header and breadcrumb for receipt archive workflow. -->
	<section class="rc-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Receipt Management</div>
		<span class="rc-kicker"><i class="fa-solid fa-receipt"></i> Receipt Archive</span>
		<h3 class="mt-3 mb-2">Receipt Management</h3>
		<p class="text-muted mb-0">View, search, print, download, and manage all student payment receipts.</p>
	</section>

	<!-- Receipt search and filter controls. -->
	<section class="rc-card">
		<form id="receiptFilterForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-3"><label class="form-label" for="receiptFilter">Receipt Number</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-hashtag"></i></span><input type="text" class="form-control" id="receiptFilter" placeholder="RCP-2026-000123"></div></div>
			<div class="col-md-3"><label class="form-label" for="studentFilter">Student Name</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-user-graduate"></i></span><input type="search" class="form-control" id="studentFilter" list="studentNames" placeholder="Search student"><datalist id="studentNames"><?php foreach ($receipts as $receipt): ?><option value="<?php echo receiptValue($receipt['student']); ?>"><?php endforeach; ?></datalist></div></div>
			<div class="col-md-3"><label class="form-label" for="regFilter">Registration Number</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-id-card"></i></span><input type="text" class="form-control" id="regFilter" placeholder="REG001"></div></div>
			<div class="col-md-3"><label class="form-label" for="sessionFilter">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionFilter"><option value="">All Sessions</option><?php foreach ($sessions as $session): ?><option><?php echo receiptValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="termFilter">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termFilter"><option value="">All Terms</option><?php foreach ($terms as $term): ?><option><?php echo receiptValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="classFilter">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classFilter"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option><?php echo receiptValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="methodFilter">Payment Method</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-credit-card"></i></span><select class="form-select" id="methodFilter"><option value="">All Methods</option><?php foreach ($methods as $method): ?><option><?php echo receiptValue($method); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label">Date Range</label><div class="d-flex gap-2"><input type="date" class="form-control" id="fromDate" style="padding-left:12px;"><input type="date" class="form-control" id="toDate" style="padding-left:12px;"></div></div>
			<div class="col-md-4"><button type="submit" class="btn rc-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
			<div class="col-md-4"><button type="button" class="btn btn-outline-secondary w-100" id="resetFilters"><i class="fa-solid fa-rotate-left me-2"></i>Reset Filters</button></div>
			<div class="col-md-4"><button type="button" class="btn btn-outline-success w-100" id="exportResults"><i class="fa-solid fa-file-export me-2"></i>Export Results</button></div>
		</form>
	</section>

	<!-- Receipt summary cards. -->
	<section class="row g-3 mb-4" aria-label="Receipt summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-receipt"></i></span><h4><?php echo number_format($totalReceipts); ?></h4><p class="text-muted mb-0">Total Receipts</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-calendar-day"></i></span><h4><?php echo number_format($todayReceipts); ?></h4><p class="text-muted mb-0">Today's Receipts</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo receiptMoney($todayCollection); ?></h4><p class="text-muted mb-0">Today's Collection</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-chart-line"></i></span><h4><?php echo receiptMoney($monthlyCollection); ?></h4><p class="text-muted mb-0">Monthly Collection</p></div></div>
	</section>

	<!-- Receipt table with exports, print, pagination, and row actions. -->
	<section class="table-card">
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Receipt List</h4><p class="text-muted mb-0">Search, verify, print, reprint, and export generated receipts.</p></div><div class="row-actions"><button type="button" class="btn rc-btn" id="exportCsv"><i class="fa-solid fa-file-csv me-2"></i>CSV</button><button type="button" class="btn rc-btn" id="exportExcel"><i class="fa-solid fa-file-excel me-2"></i>Excel</button><button type="button" class="btn rc-btn" id="exportPdf"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button><button type="button" class="btn btn-outline-secondary" id="printList"><i class="fa-solid fa-print me-2"></i>Print List</button><select class="form-select" id="pageSize" style="width:100px;padding-left:12px;"><option>10</option><option>25</option><option>50</option><option>100</option></select></div></div>
		<div class="table-scroll"><table class="table receipt-table align-middle"><thead><tr><th>Receipt No.</th><th>Date</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Amount Paid</th><th>Payment Method</th><th>Status</th><th>Actions</th></tr></thead><tbody id="receiptBody"></tbody></table></div>
		<div class="pagination-wrap"><span class="text-muted fw-bold" id="recordInfo">Showing receipts</span><div id="pagination" class="d-flex gap-2 flex-wrap"></div></div>
	</section>
</div>

<!-- Receipt preview modal: print-ready receipt details. -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden;"><div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white"><i class="fa-solid fa-receipt me-2"></i>Receipt Preview</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="receiptPreviewBody"></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button type="button" class="btn rc-btn" id="modalPrintBtn"><i class="fa-solid fa-print me-2"></i>Print Receipt</button></div></div></div></div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Receipt management behavior: filtering, pagination, preview, printing, reprinting, and exports.
(function () {
	var receipts = <?php echo json_encode($receipts); ?>;
	var school = <?php echo json_encode($school); ?>;
	var accountantName = <?php echo json_encode($accountantName); ?>;
	var filtered = receipts.slice();
	var currentPage = 1;
	var activeReceipt = null;
	function byId(id) { return document.getElementById(id); }
	function money(amount) { return '₦' + Number(amount || 0).toLocaleString(); }
	function statusClass(status) { return status.toLowerCase(); }
	function csvEscape(v) { return '"' + String(v).replace(/"/g, '""') + '"'; }
	function rowsForExport(rows) { return [['Receipt No.', 'Date', 'Registration No.', 'Student Name', 'Class', 'Amount Paid', 'Payment Method', 'Status']].concat(rows.map(function (r) { return [r.receipt, r.date, r.reg_no, r.student, r.class, r.amount, r.method, r.status]; })); }
	function downloadCsv(rows, filename) { var csv = rowsForExport(rows).map(function (row) { return row.map(csvEscape).join(','); }).join('\n'); var link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type:'text/csv;charset=utf-8;' })); link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href); }
	function renderTable() { var size = parseInt(byId('pageSize').value, 10); var pages = Math.max(1, Math.ceil(filtered.length / size)); if (currentPage > pages) currentPage = pages; var start = (currentPage - 1) * size; var pageRows = filtered.slice(start, start + size); byId('receiptBody').innerHTML = pageRows.map(function (r) { return '<tr><td>' + r.receipt + '</td><td>' + r.date + '</td><td>' + r.reg_no + '</td><td>' + r.student + '</td><td>' + r.class + '</td><td>' + money(r.amount) + '</td><td>' + r.method + '</td><td><span class="status-badge status-' + statusClass(r.status) + '"><i class="fa-solid fa-circle"></i>' + r.status + '</span></td><td><div class="row-actions"><button type="button" class="btn btn-sm btn-outline-primary view-receipt" data-receipt="' + r.receipt + '"><i class="fa-solid fa-eye"></i> View</button><button type="button" class="btn btn-sm btn-outline-dark print-receipt" data-receipt="' + r.receipt + '"><i class="fa-solid fa-print"></i> Print</button><button type="button" class="btn btn-sm btn-outline-success download-receipt" data-receipt="' + r.receipt + '"><i class="fa-solid fa-download"></i> PDF</button><button type="button" class="btn btn-sm btn-outline-secondary print-receipt" data-receipt="' + r.receipt + '"><i class="fa-solid fa-rotate"></i> Reprint</button><button type="button" class="btn btn-sm btn-outline-warning email-receipt"><i class="fa-solid fa-envelope"></i> Email</button><button type="button" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-user"></i> Profile</button></div></td></tr>'; }).join('') || '<tr><td colspan="9" class="text-center text-muted fw-bold py-4">No receipt records found.</td></tr>'; byId('recordInfo').textContent = 'Showing ' + (filtered.length ? start + 1 : 0) + ' - ' + Math.min(start + size, filtered.length) + ' of ' + filtered.length + ' receipts'; byId('pagination').innerHTML = Array.from({ length: pages }, function (_, i) { return '<button type="button" class="page-btn ' + (currentPage === i + 1 ? 'active' : '') + '" data-page="' + (i + 1) + '">' + (i + 1) + '</button>'; }).join(''); }
	function receiptHtml(r) { var items = r.items.map(function (item) { return '<tr><td>' + item.item + '</td><td>' + money(item.amount) + '</td></tr>'; }).join(''); return '<div class="receipt-preview"><div class="text-center mb-3"><img src="' + school.logo + '" class="school-logo" alt="Logo"><h3 class="mb-1">' + school.name + '</h3><p class="mb-0">' + school.address + '</p><p class="mb-0">' + school.phone + ' | ' + school.email + '</p></div><hr><div class="row g-3"><div class="col-md-6"><h5>Receipt Information</h5><p class="mb-1"><strong>Receipt No:</strong> ' + r.receipt + '</p><p class="mb-1"><strong>Payment Date:</strong> ' + r.date + '</p><p class="mb-1"><strong>Session:</strong> ' + r.session + '</p><p class="mb-1"><strong>Term:</strong> ' + r.term + '</p></div><div class="col-md-6"><h5>Student Information</h5><div class="d-flex gap-3 align-items-center"><img src="' + r.passport + '" class="student-photo" alt="Student"><div><p class="mb-1"><strong>' + r.student + '</strong></p><p class="mb-1">' + r.reg_no + '</p><p class="mb-0">' + r.class + '</p></div></div></div></div><h5 class="mt-4">Payment Details</h5><div class="table-responsive"><table class="table"><thead><tr><th>Fee Item</th><th>Amount</th></tr></thead><tbody>' + items + '</tbody></table></div><div class="row g-3 mt-2"><div class="col-md-4"><strong>Total Amount Paid</strong><h4>' + money(r.amount) + '</h4></div><div class="col-md-4"><strong>Payment Method</strong><h5>' + r.method + '</h5><p class="mb-0">Ref: ' + r.reference + '</p></div><div class="col-md-4"><strong>Received By</strong><h5>' + accountantName + '</h5></div></div><div class="row g-3 mt-4"><div class="col-md-6"><p class="mb-5">Authorized Signature</p><hr></div><div class="col-md-6"><div class="stamp-box">School Stamp</div></div></div><p class="text-center fw-bold mt-3">Thank you for your payment.</p></div>'; }
	function printReceipt(r) { var win = window.open('', '_blank'); win.document.write('<html><head><title>' + r.receipt + '</title><style>@page{size:A4;margin:14mm}body{font-family:Arial,sans-serif;color:#10201d}.text-center{text-align:center}.mb-0{margin-bottom:0}.mb-1{margin-bottom:4px}.mb-3{margin-bottom:14px}.mt-3{margin-top:14px}.mt-4{margin-top:20px}.school-logo{width:76px;height:76px;object-fit:contain}.student-photo{width:70px;height:70px;border-radius:12px;object-fit:cover}table{width:100%;border-collapse:collapse}td,th{border:1px solid #999;padding:8px;text-align:left}th{background:#0f766e;color:#fff}.row{display:flex;gap:20px}.col-md-4,.col-md-6{flex:1}.stamp-box{border:2px dashed #999;border-radius:12px;min-height:70px;display:flex;align-items:center;justify-content:center}</style></head><body>' + receiptHtml(r) + '</body></html>'); win.document.close(); win.focus(); win.print(); }
	function showReceipt(receiptNo) { activeReceipt = receipts.find(function (r) { return r.receipt === receiptNo; }); if (!activeReceipt) return; byId('receiptPreviewBody').innerHTML = receiptHtml(activeReceipt); new bootstrap.Modal(document.getElementById('receiptModal')).show(); }
	function applyFilters() { var receipt = byId('receiptFilter').value.toLowerCase().trim(), student = byId('studentFilter').value.toLowerCase().trim(), reg = byId('regFilter').value.toLowerCase().trim(), session = byId('sessionFilter').value, term = byId('termFilter').value, klass = byId('classFilter').value, method = byId('methodFilter').value, from = byId('fromDate').value, to = byId('toDate').value; filtered = receipts.filter(function (r) { return (!receipt || r.receipt.toLowerCase().indexOf(receipt) !== -1) && (!student || r.student.toLowerCase().indexOf(student) !== -1) && (!reg || r.reg_no.toLowerCase().indexOf(reg) !== -1) && (!session || r.session === session) && (!term || r.term === term) && (!klass || r.class === klass) && (!method || r.method === method) && (!from || r.date >= from) && (!to || r.date <= to); }); currentPage = 1; renderTable(); }
	function printList(rows) { var body = rowsForExport(rows).slice(1).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join(''); var win = window.open('', '_blank'); win.document.write('<html><head><title>Receipt List</title><style>@page{size:A4 landscape;margin:12mm}body{font-family:Arial,sans-serif}table{width:100%;border-collapse:collapse;font-size:11px}td,th{border:1px solid #777;padding:6px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>Receipt Management</h2><table><thead><tr><th>Receipt No.</th><th>Date</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Amount Paid</th><th>Payment Method</th><th>Status</th></tr></thead><tbody>' + body + '</tbody></table></body></html>'); win.document.close(); win.focus(); win.print(); }
	document.addEventListener('DOMContentLoaded', renderTable);
	byId('receiptFilterForm').addEventListener('submit', function (e) { e.preventDefault(); applyFilters(); });
	byId('resetFilters').addEventListener('click', function () { byId('receiptFilterForm').reset(); applyFilters(); });
	byId('pageSize').addEventListener('change', function () { currentPage = 1; renderTable(); });
	byId('pagination').addEventListener('click', function (e) { if (e.target.classList.contains('page-btn')) { currentPage = parseInt(e.target.getAttribute('data-page'), 10); renderTable(); } });
	document.addEventListener('click', function (e) { var view = e.target.closest('.view-receipt'), print = e.target.closest('.print-receipt'), download = e.target.closest('.download-receipt'), email = e.target.closest('.email-receipt'); if (view) showReceipt(view.getAttribute('data-receipt')); if (print) { var r = receipts.find(function (item) { return item.receipt === print.getAttribute('data-receipt'); }); if (r) printReceipt(r); } if (download) { var d = receipts.find(function (item) { return item.receipt === download.getAttribute('data-receipt'); }); if (d) printReceipt(d); } if (email) alert('Email receipt placeholder: email integration can be connected later.'); });
	byId('modalPrintBtn').addEventListener('click', function () { if (activeReceipt) printReceipt(activeReceipt); });
	byId('exportCsv').addEventListener('click', function () { downloadCsv(filtered, 'Receipt_Records.csv'); });
	byId('exportExcel').addEventListener('click', function () { downloadCsv(filtered, 'Receipt_Records.xls'); });
	byId('exportPdf').addEventListener('click', function () { printList(filtered); });
	byId('printList').addEventListener('click', function () { printList(filtered); });
	byId('exportResults').addEventListener('click', function () { downloadCsv(filtered, 'Filtered_Receipt_Records.csv'); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
