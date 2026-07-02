<?php require_once('includes/header.php'); ?>

<?php
// Fee structure placeholder data. Replace these arrays with database queries for future integration.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$classes = ['JSS 1A', 'JSS 1B', 'JSS 2A', 'SS 1 Science', 'SS 2 Arts'];
$sections = ['Science', 'Commercial', 'Arts'];
$categories = ['Tuition Fee', 'Registration Fee', 'Examination Fee', 'Laboratory Fee', 'Library Fee', 'Sports Fee', 'Development Levy', 'ICT Fee', 'Hostel Fee', 'Transport Fee', 'PTA Levy', 'Other'];
$structures = [
	['id' => 1, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 1 Science', 'section' => 'Science', 'category' => 'Tuition Fee', 'amount' => 80000, 'due' => '2026-09-15', 'description' => 'Core tuition payment', 'status' => 'Active'],
	['id' => 2, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 1 Science', 'section' => 'Science', 'category' => 'Examination Fee', 'amount' => 5000, 'due' => '2026-09-15', 'description' => 'Term examination fee', 'status' => 'Active'],
	['id' => 3, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 1 Science', 'section' => 'Science', 'category' => 'ICT Fee', 'amount' => 10000, 'due' => '2026-09-15', 'description' => 'Computer lab and CBT access', 'status' => 'Active'],
	['id' => 4, 'session' => '2025/2026', 'term' => 'Second Term', 'class' => 'JSS 2A', 'section' => 'Arts', 'category' => 'Library Fee', 'amount' => 3000, 'due' => '2026-12-05', 'description' => 'Library services', 'status' => 'Inactive'],
	['id' => 5, 'session' => '2026/2027', 'term' => 'First Term', 'class' => 'SS 2 Arts', 'section' => 'Arts', 'category' => 'Development Levy', 'amount' => 20000, 'due' => '2027-09-20', 'description' => 'Infrastructure levy', 'status' => 'Active']
];
function fsMoney($amount) { return '₦' . number_format((float) $amount); }
function fsValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>

<style>
	/* Fee structure module: scoped ERP-style setup, table, copy, preview, and export controls. */
	.fee-structure-page { --fs-primary:#0f766e; --fs-primary-dark:#115e59; --fs-primary-soft:rgba(15,118,110,.1); --fs-success:#16a34a; --fs-success-soft:rgba(22,163,74,.12); --fs-warning:#f59e0b; --fs-warning-soft:rgba(245,158,11,.14); --fs-danger:#dc2626; --fs-danger-soft:rgba(220,38,38,.1); --fs-blue:#2563eb; --fs-blue-soft:rgba(37,99,235,.1); --fs-ink:#10201d; --fs-muted:#64748b; --fs-border:rgba(15,118,110,.18); --fs-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.fee-structure-page .fs-hero,.fee-structure-page .fs-card,.fee-structure-page .summary-card,.fee-structure-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--fs-border); box-shadow:var(--fs-shadow); }
	.fee-structure-page .fs-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.fee-structure-page .breadcrumb-line { color:var(--fs-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.fee-structure-page .breadcrumb-line a { color:var(--fs-primary-dark); text-decoration:none; }
	.fee-structure-page .fs-kicker,.fee-structure-page .field-icon,.fee-structure-page .summary-icon,.fee-structure-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.fee-structure-page .fs-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--fs-primary-soft); color:var(--fs-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-structure-page h3,.fee-structure-page h4,.fee-structure-page h5 { color:var(--fs-ink); font-weight:900; }
	.fee-structure-page .fs-card,.fee-structure-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.fee-structure-page .fs-card { padding:24px; }
	.fee-structure-page .form-label { color:var(--fs-ink); font-size:13px; font-weight:900; }
	.fee-structure-page .field-wrap { position:relative; }
	.fee-structure-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--fs-primary); pointer-events:none; }
	.fee-structure-page .form-select,.fee-structure-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.fee-structure-page textarea.form-control { padding:14px; min-height:92px; }
	.fee-structure-page .form-select:focus,.fee-structure-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.fee-structure-page .fs-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--fs-primary),var(--fs-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.fee-structure-page .fs-btn:hover { color:#fff; transform:translateY(-2px); }
	.fee-structure-page .notice { display:none; gap:8px; align-items:center; padding:12px 14px; border-radius:14px; font-weight:800; margin-bottom:16px; }
	.fee-structure-page .notice.is-visible{display:flex}.fee-structure-page .notice.success{color:var(--fs-success);background:var(--fs-success-soft)}.fee-structure-page .notice.error{color:var(--fs-danger);background:var(--fs-danger-soft)}
	.fee-structure-page .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
	.fee-structure-page .summary-card:hover { transform:translateY(-3px); box-shadow:0 20px 42px rgba(15,23,42,.12); }
	.fee-structure-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--fs-primary-soft); color:var(--fs-primary); }
	.fee-structure-page .summary-icon.success{background:var(--fs-success-soft);color:var(--fs-success)}.fee-structure-page .summary-icon.warning{background:var(--fs-warning-soft);color:#b45309}.fee-structure-page .summary-icon.blue{background:var(--fs-blue-soft);color:var(--fs-blue)}
	.fee-structure-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.fee-structure-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.fee-structure-page .table-scroll { max-height:620px; overflow:auto; }
	.fee-structure-page .structure-table { min-width:1060px; margin-bottom:0; }
	.fee-structure-page .structure-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--fs-primary),var(--fs-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.fee-structure-page .structure-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.fee-structure-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.fee-structure-page .status-active{color:var(--fs-success);background:var(--fs-success-soft)}.fee-structure-page .status-inactive{color:var(--fs-danger);background:var(--fs-danger-soft)}
	.fee-structure-page .action-row,.fee-structure-page .bulk-actions { display:flex; gap:7px; flex-wrap:wrap; }
	@media(max-width:767.98px){ .fee-structure-page .fs-hero,.fee-structure-page .fs-card{padding:20px;border-radius:20px}.fee-structure-page .action-row,.fee-structure-page .action-row .btn,.fee-structure-page .bulk-actions .btn,.fee-structure-page .fs-btn{width:100%} }
</style>

<div class="fee-structure-page">
	<!-- Page header and breadcrumb. -->
	<section class="fs-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Fee Structure Management</div>
		<span class="fs-kicker"><i class="fa-solid fa-sliders"></i> Fee Administration</span>
		<h3 class="mt-3 mb-2">Fee Structure Management</h3>
		<p class="text-muted mb-0">Create, manage, and maintain school fee structures for different classes, terms, and academic sessions.</p>
	</section>

	<div id="feeStructureNotice" class="notice" role="alert"><i class="fa-solid fa-circle-info"></i><span></span></div>

	<!-- Dashboard summary cards. -->
	<section class="row g-3 mb-4" aria-label="Fee structure summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-calendar-check"></i></span><h4><?php echo count($sessions); ?> Active Sessions</h4><p class="text-muted mb-0">Academic Sessions</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-file-invoice-dollar"></i></span><h4 id="structureCount"><?php echo count($structures); ?></h4><p class="text-muted mb-0">Fee Structures</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-school"></i></span><h4><?php echo count($classes); ?> Classes</h4><p class="text-muted mb-0">Classes Covered</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-tags"></i></span><h4><?php echo count($categories); ?> Categories</h4><p class="text-muted mb-0">Fee Categories</p></div></div>
	</section>

	<!-- Create/update fee structure form. -->
	<section class="fs-card">
		<h4 class="mb-3">Create Fee Structure</h4>
		<form id="feeStructureForm" class="row g-3" novalidate>
			<input type="hidden" id="editingId" value="">
			<div class="col-md-3"><label class="form-label" for="sessionInput">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionInput" required><option value="">Select session</option><?php foreach ($sessions as $session): ?><option><?php echo fsValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="termInput">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termInput" required><option value="">Select term</option><?php foreach ($terms as $term): ?><option><?php echo fsValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="classInput">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classInput" required><option value="">Select class</option><?php foreach ($classes as $class): ?><option><?php echo fsValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="sectionInput">Section</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-users-rectangle"></i></span><select class="form-select" id="sectionInput"><option value="">Optional</option><?php foreach ($sections as $section): ?><option><?php echo fsValue($section); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-4"><label class="form-label" for="categoryInput">Fee Category</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-tag"></i></span><select class="form-select" id="categoryInput" required><option value="">Select category</option><?php foreach ($categories as $category): ?><option><?php echo fsValue($category); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-4" id="customCategoryWrap" style="display:none;"><label class="form-label" for="customCategoryInput">Custom Fee Category</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-pen"></i></span><input type="text" class="form-control" id="customCategoryInput" placeholder="Enter custom category"></div></div>
			<div class="col-md-2"><label class="form-label" for="amountInput">Fee Amount</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" min="1" class="form-control" id="amountInput" required></div></div>
			<div class="col-md-2"><label class="form-label" for="dueInput">Due Date</label><input type="date" class="form-control" id="dueInput" style="padding-left:12px;" required></div>
			<div class="col-md-3"><label class="form-label" for="statusInput">Status</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-toggle-on"></i></span><select class="form-select" id="statusInput"><option>Active</option><option>Inactive</option></select></div></div>
			<div class="col-md-9"><label class="form-label" for="descriptionInput">Description</label><textarea class="form-control" id="descriptionInput" placeholder="Optional notes about this fee item"></textarea></div>
			<div class="col-12"><div class="action-row"><button type="button" class="btn fs-btn" id="saveBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Fee Structure</button><button type="button" class="btn fs-btn" id="updateBtn" style="display:none;"><i class="fa-solid fa-pen-to-square me-2"></i>Update Fee Structure</button><button type="reset" class="btn btn-outline-secondary" id="resetFormBtn"><i class="fa-solid fa-rotate-left me-2"></i>Reset Form</button><button type="button" class="btn btn-outline-danger" id="cancelBtn"><i class="fa-solid fa-xmark me-2"></i>Cancel</button></div></div>
		</form>
	</section>

	<!-- Search/filter and copy fee structure panel. -->
	<section class="fs-card">
		<div class="row g-3 align-items-end">
			<div class="col-md-2"><label class="form-label" for="filterSession">Session</label><select class="form-select" id="filterSession" style="padding-left:12px;"><option value="">All</option><?php foreach ($sessions as $session): ?><option><?php echo fsValue($session); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label" for="filterTerm">Term</label><select class="form-select" id="filterTerm" style="padding-left:12px;"><option value="">All</option><?php foreach ($terms as $term): ?><option><?php echo fsValue($term); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label" for="filterClass">Class</label><select class="form-select" id="filterClass" style="padding-left:12px;"><option value="">All</option><?php foreach ($classes as $class): ?><option><?php echo fsValue($class); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label" for="filterCategory">Fee Category</label><select class="form-select" id="filterCategory" style="padding-left:12px;"><option value="">All</option><?php foreach ($categories as $category): ?><option><?php echo fsValue($category); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label" for="filterStatus">Status</label><select class="form-select" id="filterStatus" style="padding-left:12px;"><option value="">All</option><option>Active</option><option>Inactive</option></select></div>
			<div class="col-md-2"><label class="form-label" for="filterSearch">Search</label><input type="search" class="form-control" id="filterSearch" style="padding-left:12px;" placeholder="Class or category"></div>
			<div class="col-12"><div class="action-row"><button type="button" class="btn fs-btn" id="searchBtn"><i class="fa-solid fa-search me-2"></i>Search</button><button type="button" class="btn btn-outline-secondary" id="resetFilters"><i class="fa-solid fa-rotate-left me-2"></i>Reset Filters</button><button type="button" class="btn btn-outline-primary" id="copyBtn"><i class="fa-solid fa-copy me-2"></i>Copy Selected to 2026/2027 First Term</button></div></div>
		</div>
	</section>

	<!-- Fee structure table with bulk actions. -->
	<section class="table-card">
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Fee Structure Table</h4><p class="text-muted mb-0">Manage, duplicate, activate, deactivate, export, and print fee structures.</p></div><div class="bulk-actions"><button type="button" class="btn btn-outline-success" id="activateSelected">Activate Selected</button><button type="button" class="btn btn-outline-warning" id="deactivateSelected">Deactivate Selected</button><button type="button" class="btn btn-outline-danger" id="deleteSelected">Delete Selected</button><button type="button" class="btn btn-outline-primary" id="exportSelected">Export Selected</button><button type="button" class="btn fs-btn" id="exportCsv">CSV</button><button type="button" class="btn fs-btn" id="exportExcel">Excel</button><button type="button" class="btn fs-btn" id="exportPdf">PDF</button><button type="button" class="btn btn-outline-secondary" id="printBtn">Print Fee Structure</button></div></div>
		<div class="table-scroll"><table class="table structure-table align-middle"><thead><tr><th><input type="checkbox" id="selectAll"></th><th>Session</th><th>Term</th><th>Class</th><th>Fee Category</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead><tbody id="structureBody"></tbody></table></div>
	</section>
</div>

<!-- Fee structure preview modal. -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden;"><div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white"><i class="fa-solid fa-eye me-2"></i>Fee Structure Preview</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="previewBody"></div></div></div></div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Fee structure behavior: create, update, filter, preview, duplicate, bulk actions, copy, export, and print.
(function () {
	var structures = <?php echo json_encode($structures); ?>;
	var filtered = structures.slice();
	var nextId = 100;
	function byId(id) { return document.getElementById(id); }
	function money(amount) { return '₦' + Number(amount || 0).toLocaleString(); }
	function showNotice(type, message) { var n = byId('feeStructureNotice'); n.className = 'notice is-visible ' + type; n.querySelector('span').textContent = message; window.scrollTo({ top: n.offsetTop - 20, behavior: 'smooth' }); }
	function statusClass(status) { return status.toLowerCase(); }
	function selectedIds() { return Array.prototype.map.call(document.querySelectorAll('.row-check:checked'), function (box) { return Number(box.value); }); }
	function selectedRows() { var ids = selectedIds(); return structures.filter(function (item) { return ids.indexOf(item.id) !== -1; }); }
	function csvEscape(v) { return '"' + String(v).replace(/"/g, '""') + '"'; }
	function rowsForExport(rows) { return [['Session','Term','Class','Section','Fee Category','Amount','Due Date','Status']].concat(rows.map(function (r) { return [r.session,r.term,r.class,r.section,r.category,r.amount,r.due,r.status]; })); }
	function downloadCsv(rows, filename) { var csv = rowsForExport(rows).map(function (row) { return row.map(csvEscape).join(','); }).join('\n'); var link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type:'text/csv;charset=utf-8;' })); link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href); }
	function formData() { var category = byId('categoryInput').value === 'Other' ? byId('customCategoryInput').value.trim() : byId('categoryInput').value; return { session: byId('sessionInput').value, term: byId('termInput').value, class: byId('classInput').value, section: byId('sectionInput').value, category: category, amount: Number(byId('amountInput').value), due: byId('dueInput').value, description: byId('descriptionInput').value, status: byId('statusInput').value }; }
	function validate(data) { if (!data.session || !data.term || !data.class || !data.category || !data.due) { showNotice('error', 'Please complete session, term, class, category, and due date.'); return false; } if (!data.amount || data.amount <= 0) { showNotice('error', 'Fee amount must be a positive number.'); return false; } return true; }
	function resetEditor() { byId('feeStructureForm').reset(); byId('editingId').value = ''; byId('updateBtn').style.display = 'none'; byId('saveBtn').style.display = 'inline-flex'; byId('customCategoryWrap').style.display = 'none'; }
	function renderTable() { byId('structureBody').innerHTML = filtered.map(function (r) { return '<tr><td><input type="checkbox" class="row-check" value="' + r.id + '"></td><td>' + r.session + '</td><td>' + r.term + '</td><td>' + r.class + '</td><td>' + r.category + '</td><td>' + money(r.amount) + '</td><td>' + r.due + '</td><td><span class="status-badge status-' + statusClass(r.status) + '"><i class="fa-solid fa-circle"></i>' + r.status + '</span></td><td><div class="action-row"><button type="button" class="btn btn-sm btn-outline-primary view-row" data-id="' + r.id + '"><i class="fa-solid fa-eye"></i> View</button><button type="button" class="btn btn-sm btn-outline-success edit-row" data-id="' + r.id + '"><i class="fa-solid fa-pen"></i> Edit</button><button type="button" class="btn btn-sm btn-outline-secondary duplicate-row" data-id="' + r.id + '"><i class="fa-solid fa-copy"></i> Duplicate</button><button type="button" class="btn btn-sm btn-outline-warning toggle-row" data-id="' + r.id + '"><i class="fa-solid fa-toggle-on"></i> Toggle</button><button type="button" class="btn btn-sm btn-outline-danger delete-row" data-id="' + r.id + '"><i class="fa-solid fa-trash"></i> Delete</button></div></td></tr>'; }).join('') || '<tr><td colspan="9" class="text-center text-muted fw-bold py-4">No fee structures found.</td></tr>'; byId('structureCount').textContent = structures.length; byId('selectAll').checked = false; }
	function applyFilters() { var session = byId('filterSession').value, term = byId('filterTerm').value, klass = byId('filterClass').value, category = byId('filterCategory').value, status = byId('filterStatus').value, search = byId('filterSearch').value.toLowerCase().trim(); filtered = structures.filter(function (r) { return (!session || r.session === session) && (!term || r.term === term) && (!klass || r.class === klass) && (!category || r.category === category) && (!status || r.status === status) && (!search || r.class.toLowerCase().indexOf(search) !== -1 || r.category.toLowerCase().indexOf(search) !== -1); }); renderTable(); }
	function showPreview(row) { var rows = structures.filter(function (r) { return r.session === row.session && r.term === row.term && r.class === row.class; }); var total = rows.reduce(function (s, r) { return s + Number(r.amount); }, 0); byId('previewBody').innerHTML = '<p><strong>Session:</strong> ' + row.session + ' | <strong>Term:</strong> ' + row.term + ' | <strong>Class:</strong> ' + row.class + '</p><div class="table-responsive"><table class="table"><thead><tr><th>Fee Item</th><th>Amount</th></tr></thead><tbody>' + rows.map(function (r) { return '<tr><td>' + r.category + '</td><td>' + money(r.amount) + '</td></tr>'; }).join('') + '</tbody></table></div><div class="row g-3"><div class="col-md-6"><div class="p-3 rounded-3 bg-light"><strong>Total Fees</strong><h5>' + money(total) + '</h5></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-light"><strong>Number of Fee Items</strong><h5>' + rows.length + '</h5></div></div></div>'; new bootstrap.Modal(document.getElementById('previewModal')).show(); }
	function printRows(rows) { var body = rowsForExport(rows).slice(1).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join(''); var win = window.open('', '_blank'); win.document.write('<html><head><title>Fee Structure</title><style>@page{size:A4 landscape;margin:12mm}body{font-family:Arial,sans-serif}table{width:100%;border-collapse:collapse;font-size:11px}td,th{border:1px solid #777;padding:6px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>Fee Structure Management</h2><table><thead><tr><th>Session</th><th>Term</th><th>Class</th><th>Section</th><th>Fee Category</th><th>Amount</th><th>Due Date</th><th>Status</th></tr></thead><tbody>' + body + '</tbody></table></body></html>'); win.document.close(); win.focus(); win.print(); }
	document.addEventListener('DOMContentLoaded', renderTable);
	byId('categoryInput').addEventListener('change', function () { byId('customCategoryWrap').style.display = this.value === 'Other' ? 'block' : 'none'; });
	byId('saveBtn').addEventListener('click', function () { var data = formData(); if (!validate(data)) return; data.id = nextId++; structures.unshift(data); filtered = structures.slice(); renderTable(); resetEditor(); showNotice('success', 'Fee structure saved successfully.'); });
	byId('updateBtn').addEventListener('click', function () { var id = Number(byId('editingId').value), data = formData(); if (!validate(data)) return; var idx = structures.findIndex(function (r) { return r.id === id; }); if (idx >= 0) { data.id = id; structures[idx] = data; filtered = structures.slice(); renderTable(); resetEditor(); showNotice('success', 'Fee structure updated successfully.'); } });
	byId('cancelBtn').addEventListener('click', resetEditor);
	byId('searchBtn').addEventListener('click', applyFilters);
	byId('resetFilters').addEventListener('click', function () { ['filterSession','filterTerm','filterClass','filterCategory','filterStatus','filterSearch'].forEach(function (id) { byId(id).value = ''; }); filtered = structures.slice(); renderTable(); });
	byId('selectAll').addEventListener('change', function () { document.querySelectorAll('.row-check').forEach(function (box) { box.checked = byId('selectAll').checked; }); });
	document.addEventListener('click', function (e) { var btn = e.target.closest('button[data-id]'); if (!btn) return; var id = Number(btn.getAttribute('data-id')), row = structures.find(function (r) { return r.id === id; }); if (!row) return; if (btn.classList.contains('view-row')) showPreview(row); if (btn.classList.contains('edit-row')) { byId('editingId').value = row.id; byId('sessionInput').value = row.session; byId('termInput').value = row.term; byId('classInput').value = row.class; byId('sectionInput').value = row.section; byId('categoryInput').value = row.category; byId('amountInput').value = row.amount; byId('dueInput').value = row.due; byId('descriptionInput').value = row.description || ''; byId('statusInput').value = row.status; byId('saveBtn').style.display = 'none'; byId('updateBtn').style.display = 'inline-flex'; window.scrollTo({ top: byId('feeStructureForm').offsetTop - 20, behavior: 'smooth' }); } if (btn.classList.contains('duplicate-row')) { var copy = Object.assign({}, row, { id: nextId++ }); structures.unshift(copy); applyFilters(); showNotice('success', 'Fee structure duplicated successfully.'); } if (btn.classList.contains('toggle-row')) { row.status = row.status === 'Active' ? 'Inactive' : 'Active'; renderTable(); } if (btn.classList.contains('delete-row')) { if (confirm('Delete this fee structure?')) { structures = structures.filter(function (r) { return r.id !== id; }); applyFilters(); } } });
	function bulkStatus(status) { selectedIds().forEach(function (id) { var row = structures.find(function (r) { return r.id === id; }); if (row) row.status = status; }); applyFilters(); }
	byId('activateSelected').addEventListener('click', function () { bulkStatus('Active'); });
	byId('deactivateSelected').addEventListener('click', function () { bulkStatus('Inactive'); });
	byId('deleteSelected').addEventListener('click', function () { var ids = selectedIds(); if (ids.length && confirm('Delete selected fee structures?')) { structures = structures.filter(function (r) { return ids.indexOf(r.id) === -1; }); applyFilters(); } });
	byId('copyBtn').addEventListener('click', function () { selectedRows().forEach(function (row) { var copy = Object.assign({}, row, { id: nextId++, session: '2026/2027', term: 'First Term' }); structures.unshift(copy); }); filtered = structures.slice(); renderTable(); showNotice('success', 'Selected fee structures copied to 2026/2027 First Term.'); });
	byId('exportSelected').addEventListener('click', function () { downloadCsv(selectedRows(), 'Selected_Fee_Structures.csv'); });
	byId('exportCsv').addEventListener('click', function () { downloadCsv(filtered, 'Fee_Structures.csv'); });
	byId('exportExcel').addEventListener('click', function () { downloadCsv(filtered, 'Fee_Structures.xls'); });
	byId('exportPdf').addEventListener('click', function () { printRows(filtered); });
	byId('printBtn').addEventListener('click', function () { printRows(filtered); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
