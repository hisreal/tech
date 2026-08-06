<?php require_once('includes/header.php'); ?>

<?php

use App\Services\FinanceService;

$financeService = new FinanceService();

$sessions = $financeService->sessionsForSelect();
$terms = $financeService->termsForSelect();
$classes = $financeService->classesForSelect();

$sessionId = (int) ($_GET['session_id'] ?? $financeService->currentSessionId() ?? 0);
$termId = (int) ($_GET['term_id'] ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);
$status = trim((string) ($_GET['status'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $financeService->listAllInvoices([
	'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'status' => $status, 'search' => $search,
], $page, 15);
$invoices = $result['data'];
$meta = $result['meta'];

$totalStudents = (int) $meta['total'];
$paidStudents = 0;
$outstandingCount = 0;
$totalOutstanding = 0.0;
foreach ($invoices as $inv) {
	if ((float) $inv['balance'] <= 0) { $paidStudents++; } else { $outstandingCount++; $totalOutstanding += (float) $inv['balance']; }
}

function feeMoney($amount) { return '₦' . number_format((float) $amount); }
function feeValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function feeStatusLabel(float $balance, float $paid): string { if ($balance <= 0) { return 'Paid'; } if ($paid > 0) { return 'Partially Paid'; } return 'Outstanding'; }

function sms_sf_query(array $overrides = []): string
{
	return 'student-fees.php?' . http_build_query(array_merge($_GET, $overrides));
}
?>

<style>
	/* Student fee management: scoped premium finance styles for search, records, actions, and exports. */
	.student-fees-page { --fee-primary:#0f766e; --fee-primary-dark:#115e59; --fee-primary-soft:rgba(15,118,110,.1); --fee-success:#16a34a; --fee-success-soft:rgba(22,163,74,.12); --fee-warning:#f59e0b; --fee-warning-soft:rgba(245,158,11,.14); --fee-danger:#dc2626; --fee-danger-soft:rgba(220,38,38,.1); --fee-blue:#2563eb; --fee-blue-soft:rgba(37,99,235,.1); --fee-ink:#10201d; --fee-muted:#64748b; --fee-border:rgba(15,118,110,.18); --fee-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.student-fees-page .fee-hero,.student-fees-page .fee-card,.student-fees-page .summary-card,.student-fees-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--fee-border); box-shadow:var(--fee-shadow); }
	.student-fees-page .fee-hero { padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.student-fees-page .breadcrumb-line { color:var(--fee-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
	.student-fees-page .breadcrumb-line a { color:var(--fee-primary-dark); text-decoration:none; }
	.student-fees-page .fee-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--fee-primary-soft); color:var(--fee-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.student-fees-page h3,.student-fees-page h4,.student-fees-page h5 { color:var(--fee-ink); font-weight:900; }
	.student-fees-page .fee-card,.student-fees-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.student-fees-page .fee-card { padding:24px; }
	.student-fees-page .form-label { color:var(--fee-ink); font-size:13px; font-weight:900; }
	.student-fees-page .form-select,.student-fees-page .form-control { min-height:48px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.student-fees-page .form-select:focus,.student-fees-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.student-fees-page .search-btn,.student-fees-page .export-btn { min-height:46px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--fee-primary),var(--fee-primary-dark)); color:#fff; font-weight:900; box-shadow:0 14px 30px rgba(15,118,110,.22); }
	.student-fees-page .search-btn:hover,.student-fees-page .export-btn:hover { color:#fff; transform:translateY(-2px); }
	.student-fees-page .summary-card { height:100%; padding:18px; border-radius:20px; }
	.student-fees-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--fee-primary-soft); color:var(--fee-primary); display:inline-flex; align-items:center; justify-content:center; }
	.student-fees-page .summary-icon.success{background:var(--fee-success-soft);color:var(--fee-success)} .student-fees-page .summary-icon.warning{background:var(--fee-warning-soft);color:#b45309} .student-fees-page .summary-icon.danger{background:var(--fee-danger-soft);color:var(--fee-danger)}
	.student-fees-page .summary-card h4 { margin:12px 0 2px; font-size:24px; font-weight:900; }
	.student-fees-page .toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.student-fees-page .table-scroll { max-height:620px; overflow:auto; }
	.student-fees-page .fee-table { min-width:1000px; margin-bottom:0; }
	.student-fees-page .fee-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--fee-primary),var(--fee-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.student-fees-page .fee-table td { padding:12px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.student-fees-page .fee-table tbody tr:hover { background:rgba(15,118,110,.04); }
	.student-fees-page .student-passport { width:46px; height:46px; border-radius:14px; object-fit:cover; border:2px solid #fff; box-shadow:0 8px 18px rgba(15,23,42,.12); }
	.student-fees-page .status-badge { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.student-fees-page .status-paid{color:var(--fee-success);background:var(--fee-success-soft)} .student-fees-page .status-partially-paid{color:#b45309;background:var(--fee-warning-soft)} .student-fees-page .status-outstanding{color:var(--fee-danger);background:var(--fee-danger-soft)}
	.student-fees-page .pagination-wrap { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:14px 20px; border-top:1px solid rgba(148,163,184,.2); }
	.student-fees-page .page-btn { border:1px solid rgba(15,118,110,.2); color:var(--fee-primary-dark); border-radius:10px; background:#fff; padding:7px 11px; font-weight:900; text-decoration:none; }
	.student-fees-page .page-btn.active { background:var(--fee-primary); color:#fff; }
	@media(max-width:767.98px){ .student-fees-page .fee-hero,.student-fees-page .fee-card{padding:20px;border-radius:20px}.student-fees-page .pagination-wrap{align-items:flex-start;flex-direction:column} }
</style>

<div class="student-fees-page">
	<section class="fee-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Student Fee Management</div>
		<span class="fee-kicker"><i class="fa-solid fa-money-check-dollar"></i> Finance Module</span>
		<h3 class="mt-3 mb-2">Student Fee Management</h3>
		<p class="text-muted mb-0">View, search, and manage student fee records.</p>
	</section>

	<section class="fee-card">
		<form method="get" class="row g-3 align-items-end">
			<div class="col-md-3"><label class="form-label">Academic Session</label><select class="form-select" name="session_id"><option value="">All Sessions</option><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo feeValue($s['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-3"><label class="form-label">Term</label><select class="form-select" name="term_id"><option value="">All Terms</option><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo feeValue($t['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo $classId === (int) $c['id'] ? 'selected' : ''; ?>><?php echo feeValue($c['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-3"><label class="form-label">Payment Status</label><select class="form-select" name="status"><option value="">All Students</option><option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option><option value="partial" <?php echo $status === 'partial' ? 'selected' : ''; ?>>Partially Paid</option><option value="outstanding" <?php echo $status === 'outstanding' ? 'selected' : ''; ?>>Outstanding</option></select></div>
			<div class="col-md-8"><label class="form-label">Search Student</label><input type="search" class="form-control" name="search" value="<?php echo feeValue($search); ?>" placeholder="Registration number or student name"></div>
			<div class="col-md-2"><button type="submit" class="btn search-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
			<div class="col-md-2"><a href="student-fees.php" class="btn btn-outline-secondary w-100"><i class="fa-solid fa-rotate-left me-2"></i>Reset</a></div>
		</form>
	</section>

	<section class="row g-3 mb-4" aria-label="Payment summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4><?php echo number_format($totalStudents); ?></h4><p class="text-muted mb-0">Total Students (this page)</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-user-check"></i></span><h4><?php echo number_format($paidStudents); ?></h4><p class="text-muted mb-0">Students Paid (this page)</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-user-clock"></i></span><h4><?php echo number_format($outstandingCount); ?></h4><p class="text-muted mb-0">Outstanding (this page)</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-scale-unbalanced"></i></span><h4><?php echo feeMoney($totalOutstanding); ?></h4><p class="text-muted mb-0">Outstanding Fees (this page)</p></div></div>
	</section>

	<section class="table-card">
		<div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div><h4 class="mb-1">Student Fee Records</h4><p class="text-muted mb-0"><?php echo number_format($meta['total']); ?> record(s) match your filters.</p></div>
			<div class="d-flex flex-wrap gap-2">
				<a class="btn export-btn" href="student-fees-export.php?<?php echo feeValue(sms_sf_query(['format' => 'pdf'])); ?>"><i class="fa-solid fa-file-pdf me-2"></i>PDF</a>
				<a class="btn export-btn" href="student-fees-export.php?<?php echo feeValue(sms_sf_query(['format' => 'excel'])); ?>"><i class="fa-solid fa-file-excel me-2"></i>Excel</a>
				<a class="btn export-btn" href="student-fees-export.php?<?php echo feeValue(sms_sf_query(['format' => 'csv'])); ?>"><i class="fa-solid fa-file-csv me-2"></i>CSV</a>
				<button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button>
			</div>
		</div>
		<div class="table-scroll"><table class="table fee-table align-middle"><thead><tr><th>Passport</th><th>Registration No.</th><th>Student Name</th><th>Class</th><th>Total Fees</th><th>Amount Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead><tbody>
			<?php foreach ($invoices as $inv): ?>
				<?php $balance = (float) $inv['balance']; $statusLabel = feeStatusLabel($balance, (float) $inv['amount_paid']); ?>
				<tr>
					<td><img src="<?php echo feeValue($inv['passport_path'] ? '../' . ltrim((string) $inv['passport_path'], './') : '../assets/img/students/student-01.jpg'); ?>" class="student-passport" alt="Passport"></td>
					<td><?php echo feeValue($inv['registration_no']); ?></td>
					<td><?php echo feeValue($inv['first_name'] . ' ' . $inv['last_name']); ?></td>
					<td><?php echo feeValue($inv['class_name'] . ($inv['section_name'] ? ' - ' . $inv['section_name'] : '')); ?></td>
					<td><?php echo feeMoney($inv['total_amount']); ?></td>
					<td><?php echo feeMoney($inv['amount_paid']); ?></td>
					<td><?php echo feeMoney($balance); ?></td>
					<td><span class="status-badge status-<?php echo feeValue(strtolower(str_replace(' ', '-', $statusLabel))); ?>"><i class="fa-solid fa-circle"></i><?php echo feeValue($statusLabel); ?></span></td>
					<td><a class="btn btn-sm btn-outline-warning" href="fee-collection.php?student=<?php echo feeValue($inv['registration_no']); ?>"><i class="fa-solid fa-money-bill-transfer"></i> Collect</a></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$invoices): ?><tr><td colspan="9" class="text-center text-muted fw-bold py-4">No student fee records found.</td></tr><?php endif; ?>
		</tbody></table></div>
		<div class="pagination-wrap">
			<span class="text-muted fw-bold"><?php echo number_format($meta['total']); ?> record(s) - page <?php echo (int) $meta['page']; ?> of <?php echo (int) $meta['last_page']; ?></span>
			<?php if ($meta['last_page'] > 1): ?>
				<div class="d-flex gap-2 flex-wrap">
					<?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
						<a class="page-btn <?php echo $p === (int) $meta['page'] ? 'active' : ''; ?>" href="<?php echo feeValue(sms_sf_query(['page' => $p])); ?>"><?php echo $p; ?></a>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>

</div>
</div>

<?php require_once('includes/footer.php'); ?>
