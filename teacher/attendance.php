<?php require_once('includes/header.php'); ?>

<?php

use App\Services\AttendanceService;
use App\Services\TeacherService;

$teacherService = new TeacherService();
$attendanceService = new AttendanceService();
$currentUser = sms_current_user();
$teacherId = $teacherService->teacherIdForUser((int) $currentUser['id']);
$staff = $teacherId ? $teacherService->find($teacherId) : null;
$myClasses = $staff ? $staff['classes'] : [];
$classIds = array_map(static fn (array $c): int => (int) $c['class_id'], $myClasses);

$today = date('Y-m-d');
$schoolName = sms_config('school_name', 'School');

$markSelection = trim((string) ($_GET['mark_class'] ?? ''));
$markClass = 0;
$markSection = 0;
if ($markSelection !== '' && str_contains($markSelection, ':')) {
	[$markClass, $markSection] = array_map('intval', explode(':', $markSelection, 2));
}
$markDate = trim((string) ($_GET['mark_date'] ?? $today));

$ownsSelectedClass = false;
foreach ($myClasses as $class) {
	if ((int) $class['class_id'] === $markClass && (int) $class['id'] === $markSection) {
		$ownsSelectedClass = true;
		break;
	}
}

$sessionId = $attendanceService->currentSessionId();
$termId = $attendanceService->currentTermId();
$studentRoster = [];
$existingMarks = [];
if ($ownsSelectedClass && $sessionId) {
	$studentRoster = $attendanceService->studentRoster($sessionId, $markClass, $markSection ?: null);
	$existingMarks = $attendanceService->existingStudentMarksForDate($markClass, $markSection ?: null, $markDate);
}

$statusOptions = ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused', 'leave' => 'Leave'];

$hSearch = trim((string) ($_GET['h_search'] ?? ''));
$hClassSel = trim((string) ($_GET['h_class'] ?? ''));
$hStatus = trim((string) ($_GET['h_status'] ?? ''));
$hDateFrom = trim((string) ($_GET['h_date_from'] ?? ''));
$hDateTo = trim((string) ($_GET['h_date_to'] ?? ''));
$hPage = max(1, (int) ($_GET['h_page'] ?? 1));

$historyFilters = ['search' => $hSearch, 'status' => $hStatus, 'date_from' => $hDateFrom, 'date_to' => $hDateTo, 'class_ids' => $classIds];
if ($hClassSel !== '' && str_contains($hClassSel, ':')) {
	[$fClass, $fSection] = array_map('intval', explode(':', $hClassSel, 2));
	$historyFilters['class_id'] = $fClass;
	$historyFilters['section_id'] = $fSection;
}
$historyResult = $classIds ? $attendanceService->listStudentAttendance($historyFilters, $hPage, 10) : ['data' => [], 'meta' => ['total' => 0, 'page' => 1, 'last_page' => 1]];

$todayRecords = $classIds ? $attendanceService->listStudentAttendance(['class_ids' => $classIds, 'date' => $today], 1, 1000)['data'] : [];
$presentToday = count(array_filter($todayRecords, static fn ($r) => $r['status'] === 'present'));
$absentToday = count(array_filter($todayRecords, static fn ($r) => $r['status'] === 'absent'));
$totalToday = count($todayRecords);

function teacherAttValue($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sms_tatt_query(array $overrides = []): string
{
	return http_build_query(array_merge($_GET, $overrides));
}
?>

<style>
	/* Teacher attendance module: scoped green dashboard styling for daily marking and history reports. */
	.attendance-page {
		--att-primary: #0f766e;
		--att-primary-dark: #115e59;
		--att-primary-soft: rgba(15, 118, 110, .11);
		--att-success: #16a34a;
		--att-success-soft: rgba(22, 163, 74, .12);
		--att-danger: #dc2626;
		--att-danger-soft: rgba(220, 38, 38, .1);
		--att-blue: #2563eb;
		--att-blue-soft: rgba(37, 99, 235, .1);
		--att-warning: #f59e0b;
		--att-warning-soft: rgba(245, 158, 11, .13);
		--att-ink: #10201d;
		--att-muted: #64748b;
		--att-border: rgba(15, 118, 110, .18);
		--att-shadow: 0 22px 60px rgba(15, 23, 42, .09);
		padding-bottom: 34px;
	}

	.attendance-page .attendance-hero,
	.attendance-page .attendance-card,
	.attendance-page .summary-card,
	.attendance-page .table-card {
		background: rgba(255, 255, 255, .97);
		border: 1px solid var(--att-border);
		box-shadow: var(--att-shadow);
	}

	.attendance-page .attendance-hero {
		padding: 26px;
		border-radius: 24px;
		margin-bottom: 22px;
		background: linear-gradient(135deg, rgba(240, 253, 244, .96), rgba(255, 255, 255, .98));
	}

	.attendance-page .attendance-kicker {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--att-primary-soft);
		color: var(--att-primary-dark);
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.attendance-page .attendance-hero h3 { margin: 12px 0 8px; color: var(--att-ink); font-size: 26px; font-weight: 900; }
	.attendance-page .attendance-hero p { max-width: 780px; margin: 0; color: var(--att-muted); }
	.attendance-page .attendance-card, .attendance-page .table-card { border-radius: 24px; overflow: hidden; }
	.attendance-page .attendance-card { padding: 24px; margin-bottom: 22px; }
	.attendance-page .form-label { color: var(--att-ink); font-size: 13px; font-weight: 900; }
	.attendance-page .form-select, .attendance-page .form-control { min-height: 50px; border: 1px solid rgba(148, 163, 184, .32); border-radius: 15px; font-weight: 700; box-shadow: none; }
	.attendance-page .form-select:focus, .attendance-page .form-control:focus { border-color: rgba(15, 118, 110, .72); box-shadow: 0 0 0 4px rgba(15, 118, 110, .12); }
	.attendance-page .load-btn, .attendance-page .save-btn { min-height: 50px; border: 0; border-radius: 15px; background: linear-gradient(135deg, var(--att-primary), var(--att-primary-dark)); color: #fff; font-weight: 900; box-shadow: 0 16px 34px rgba(15, 118, 110, .24); }
	.attendance-page .load-btn:hover, .attendance-page .save-btn:hover { color: #fff; transform: translateY(-2px); }
	.attendance-page .summary-card { height: 100%; padding: 18px; border-radius: 20px; }
	.attendance-page .summary-icon { width: 42px; height: 42px; border-radius: 14px; background: var(--att-primary-soft); color: var(--att-primary); display: inline-flex; align-items: center; justify-content: center; }
	.attendance-page .summary-icon.success { background: var(--att-success-soft); color: var(--att-success); }
	.attendance-page .summary-icon.danger { background: var(--att-danger-soft); color: var(--att-danger); }
	.attendance-page .summary-icon.blue { background: var(--att-blue-soft); color: var(--att-blue); }
	.attendance-page .summary-card h4 { margin: 10px 0 2px; font-weight: 900; }
	.attendance-page .table-toolbar { padding: 18px 20px; border-bottom: 1px solid rgba(148, 163, 184, .2); background: linear-gradient(180deg, #f8fafc, #fff); }
	.attendance-page .table-scroll { max-height: 560px; overflow: auto; }
	.attendance-page .attendance-table { min-width: 760px; margin-bottom: 0; }
	.attendance-page .attendance-table thead th { position: sticky; top: 0; z-index: 2; padding: 14px 12px; background: linear-gradient(135deg, var(--att-primary), var(--att-primary-dark)); color: #fff; border: 0; font-size: 12px; font-weight: 900; text-transform: uppercase; }
	.attendance-page .attendance-table td { padding: 12px; vertical-align: middle; border-color: rgba(148, 163, 184, .2); font-weight: 700; }
	.attendance-page .report-actions { gap: 10px; }
	.attendance-page .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; border-radius: 999px; font-size: 12px; font-weight: 900; }
	.attendance-page .status-present { color: var(--att-success); background: var(--att-success-soft); }
	.attendance-page .status-absent { color: var(--att-danger); background: var(--att-danger-soft); }
	.attendance-page .status-late,.attendance-page .status-excused,.attendance-page .status-leave { color: #b45309; background: var(--att-warning-soft); }

	@media (max-width: 767.98px) {
		.attendance-page .attendance-hero, .attendance-page .attendance-card { padding: 20px; border-radius: 20px; }
		.attendance-page .attendance-hero h3 { font-size: 22px; }
		.attendance-page .report-actions, .attendance-page .report-actions .btn { width: 100%; }
	}
</style>

<div class="attendance-page">
	<?php foreach (sms_flash() as $type => $messages): ?>
		<?php foreach ($messages as $message): ?>
			<div class="alert alert-<?php echo $type === 'error' ? 'danger' : teacherAttValue($type); ?>" role="alert"><?php echo teacherAttValue($message); ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>

	<section class="attendance-hero">
		<span class="attendance-kicker"><i class="fa-solid fa-calendar-check"></i> Attendance Management</span>
		<h3>Daily Attendance & Reports</h3>
		<p>Select an assigned class and date, mark students present or absent, review history, edit records, and export attendance reports.</p>
	</section>

	<section class="attendance-card">
		<form method="get" class="row g-3 align-items-end" novalidate>
			<div class="col-md-5">
				<label class="form-label" for="attendanceClass">Class Selection</label>
				<select class="form-select" id="attendanceClass" name="mark_class" required>
					<option value="">Select assigned class</option>
					<?php foreach ($myClasses as $class): ?>
						<option value="<?php echo teacherAttValue($class['class_id'] . ':' . $class['id']); ?>" <?php echo $markSelection === $class['class_id'] . ':' . $class['id'] ? 'selected' : ''; ?>><?php echo teacherAttValue($class['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="attendanceDate">Date Selection</label>
				<input type="date" class="form-control" id="attendanceDate" name="mark_date" max="<?php echo teacherAttValue($today); ?>" value="<?php echo teacherAttValue($markDate); ?>" required>
			</div>
			<div class="col-md-3">
				<button type="submit" class="btn load-btn w-100"><i class="fa-solid fa-users-viewfinder me-2"></i>Load Students</button>
			</div>
		</form>
		<?php if (!$myClasses): ?><p class="text-muted mt-3 mb-0">You have no assigned classes yet. Contact the administrator.</p><?php endif; ?>
	</section>

	<section class="row g-3 mb-4" aria-label="Attendance summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4><?php echo (int) $totalToday; ?></h4><p class="text-muted mb-0">Total Marked Today</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-check"></i></span><h4><?php echo (int) $presentToday; ?></h4><p class="text-muted mb-0">Present Today</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-times"></i></span><h4><?php echo (int) $absentToday; ?></h4><p class="text-muted mb-0">Absent Today</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-percent"></i></span><h4><?php echo $totalToday ? round(($presentToday / $totalToday) * 100, 1) : 0; ?>%</h4><p class="text-muted mb-0">Attendance Rate</p></div></div>
	</section>

	<?php if ($ownsSelectedClass): ?>
	<section class="table-card mb-4">
		<?php if (!$studentRoster): ?>
			<div class="p-4 text-center text-muted">No active students are enrolled in this class for the current session.</div>
		<?php else: ?>
			<form method="post" action="attendance-mark-students.php">
				<input type="hidden" name="_token" value="<?php echo teacherAttValue(sms_csrf_token()); ?>">
				<input type="hidden" name="session_id" value="<?php echo (int) $sessionId; ?>">
				<input type="hidden" name="term_id" value="<?php echo (int) $termId; ?>">
				<input type="hidden" name="class_id" value="<?php echo (int) $markClass; ?>">
				<input type="hidden" name="section_id" value="<?php echo (int) $markSection; ?>">
				<input type="hidden" name="attendance_date" value="<?php echo teacherAttValue($markDate); ?>">
				<input type="hidden" name="redirect_query" value="<?php echo teacherAttValue(http_build_query($_GET)); ?>">
				<div class="table-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3">
					<div><h5 class="mb-1">Daily Attendance - <?php echo teacherAttValue($markDate); ?></h5><p class="text-muted mb-0"><?php echo count($studentRoster); ?> student(s)<?php echo $existingMarks ? ' - already marked; saving will update existing records' : ''; ?></p></div>
					<div class="report-actions d-flex flex-wrap"><button type="button" class="btn btn-outline-success" id="markAllPresent"><i class="fa-solid fa-check-double me-2"></i>Mark All Present</button><button type="submit" class="btn save-btn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Attendance</button></div>
				</div>
				<div class="table-scroll"><table class="table attendance-table align-middle"><thead><tr><th>Registration Number</th><th>Student Name</th><th>Status</th><th>Notes</th></tr></thead><tbody>
					<?php foreach ($studentRoster as $student): ?>
						<?php $existing = $existingMarks[(int) $student['id']] ?? null; ?>
						<tr>
							<td><?php echo teacherAttValue($student['registration_no']); ?></td>
							<td><?php echo teacherAttValue(trim($student['first_name'] . ' ' . $student['last_name'])); ?></td>
							<td><select class="form-select mark-status" name="status[<?php echo (int) $student['id']; ?>]"><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo teacherAttValue($value); ?>" <?php echo ($existing['status'] ?? 'present') === $value ? 'selected' : ''; ?>><?php echo teacherAttValue($label); ?></option><?php endforeach; ?></select></td>
							<td><input class="form-control" name="notes[<?php echo (int) $student['id']; ?>]" value="<?php echo teacherAttValue($existing['notes'] ?? ''); ?>" placeholder="Optional"></td>
						</tr>
					<?php endforeach; ?>
				</tbody></table></div>
			</form>
		<?php endif; ?>
	</section>
	<?php elseif ($markSelection !== ''): ?>
		<div class="alert alert-danger">You are not assigned to that class.</div>
	<?php endif; ?>

	<section class="attendance-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div><h5 class="mb-1">Attendance History & Reports</h5><p class="text-muted mb-0">Filter previous records, edit attendance, and export reports.</p></div>
			<div class="report-actions d-flex flex-wrap">
				<a class="btn btn-outline-success" href="attendance-export.php?<?php echo teacherAttValue(sms_tatt_query(['format' => 'csv'])); ?>"><i class="fa-solid fa-file-csv me-2"></i>CSV</a>
				<a class="btn btn-outline-danger" href="attendance-export.php?<?php echo teacherAttValue(sms_tatt_query(['format' => 'pdf'])); ?>"><i class="fa-solid fa-file-pdf me-2"></i>PDF</a>
			</div>
		</div>
		<form method="get" class="row g-3 mb-3">
			<input type="hidden" name="mark_class" value="<?php echo teacherAttValue($markSelection); ?>">
			<input type="hidden" name="mark_date" value="<?php echo teacherAttValue($markDate); ?>">
			<div class="col-md-3"><label class="form-label">Student</label><input class="form-control" name="h_search" value="<?php echo teacherAttValue($hSearch); ?>" placeholder="Name or reg. no."></div>
			<div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="h_class"><option value="">All My Classes</option><?php foreach ($myClasses as $class): ?><option value="<?php echo teacherAttValue($class['class_id'] . ':' . $class['id']); ?>" <?php echo $hClassSel === $class['class_id'] . ':' . $class['id'] ? 'selected' : ''; ?>><?php echo teacherAttValue($class['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="h_status"><option value="">All Statuses</option><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo teacherAttValue($value); ?>" <?php echo $hStatus === $value ? 'selected' : ''; ?>><?php echo teacherAttValue($label); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label">From</label><input class="form-control" type="date" name="h_date_from" value="<?php echo teacherAttValue($hDateFrom); ?>"></div>
			<div class="col-md-2"><label class="form-label">To</label><input class="form-control" type="date" name="h_date_to" value="<?php echo teacherAttValue($hDateTo); ?>"></div>
			<div class="col-12 d-flex gap-2"><button class="btn load-btn" type="submit"><i class="fa-solid fa-search me-2"></i>Search</button><a class="btn btn-outline-secondary" href="attendance.php">Reset</a></div>
		</form>
		<div class="table-scroll"><table class="table attendance-table history-table align-middle"><thead><tr><th>Date</th><th>Reg No.</th><th>Student</th><th>Class</th><th>Status</th><th>Notes</th><th>Action</th></tr></thead><tbody>
			<?php foreach ($historyResult['data'] as $record): ?>
				<tr>
					<td><?php echo teacherAttValue($record['attendance_date']); ?></td>
					<td><?php echo teacherAttValue($record['registration_no']); ?></td>
					<td><?php echo teacherAttValue(trim($record['first_name'] . ' ' . $record['last_name'])); ?></td>
					<td><?php echo teacherAttValue($record['class_name'] . ($record['section_name'] ? ' - ' . $record['section_name'] : '')); ?></td>
					<td><span class="status-badge status-<?php echo teacherAttValue($record['status']); ?>"><?php echo teacherAttValue(ucfirst($record['status'])); ?></span></td>
					<td><?php echo teacherAttValue($record['notes'] ?? ''); ?></td>
					<td>
						<div class="d-flex gap-1">
							<button class="btn btn-sm btn-outline-success edit-record" type="button" data-bs-toggle="modal" data-bs-target="#editAttendanceModal" data-id="<?php echo (int) $record['id']; ?>" data-status="<?php echo teacherAttValue($record['status']); ?>" data-notes="<?php echo teacherAttValue($record['notes'] ?? ''); ?>"><i class="fa-solid fa-pen"></i></button>
							<form method="post" action="attendance-delete.php" onsubmit="return confirm('Delete this attendance record?');">
								<input type="hidden" name="_token" value="<?php echo teacherAttValue(sms_csrf_token()); ?>">
								<input type="hidden" name="attendance_id" value="<?php echo (int) $record['id']; ?>">
								<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
							</form>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$historyResult['data']): ?><tr><td colspan="7" class="text-center text-muted py-4">No attendance records match your search.</td></tr><?php endif; ?>
		</tbody></table></div>
		<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
			<span class="text-muted fw-bold"><?php echo (int) $historyResult['meta']['total']; ?> record(s) - page <?php echo (int) $historyResult['meta']['page']; ?> of <?php echo (int) $historyResult['meta']['last_page']; ?></span>
			<?php if ($historyResult['meta']['last_page'] > 1): ?>
				<div class="d-flex gap-2 flex-wrap">
					<?php for ($p = 1; $p <= $historyResult['meta']['last_page']; $p++): ?>
						<a class="btn btn-sm <?php echo $p === (int) $historyResult['meta']['page'] ? 'btn-success' : 'btn-outline-secondary'; ?>" href="attendance.php?<?php echo teacherAttValue(sms_tatt_query(['h_page' => $p])); ?>"><?php echo $p; ?></a>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>

<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<form class="modal-content" method="post" action="attendance-update.php">
			<div class="modal-header"><h5 class="modal-title">Edit Attendance Record</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<input type="hidden" name="_token" value="<?php echo teacherAttValue(sms_csrf_token()); ?>">
				<input type="hidden" name="attendance_id" id="attendanceRecordId">
				<label>Status</label>
				<select class="form-select mb-3" name="status" id="attendanceRecordStatus" required><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo teacherAttValue($value); ?>"><?php echo teacherAttValue($label); ?></option><?php endforeach; ?></select>
				<label>Notes</label>
				<textarea class="form-control" name="notes" id="attendanceRecordNotes"></textarea>
			</div>
			<div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" type="submit">Update Attendance</button></div>
		</form>
	</div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function () {
	var markAllPresent = document.getElementById('markAllPresent');
	if (markAllPresent) {
		markAllPresent.addEventListener('click', function () {
			document.querySelectorAll('.mark-status').forEach(function (select) { select.value = 'present'; });
		});
	}
	document.querySelectorAll('.edit-record').forEach(function (button) {
		button.addEventListener('click', function () {
			document.getElementById('attendanceRecordId').value = button.dataset.id || '';
			document.getElementById('attendanceRecordStatus').value = button.dataset.status || 'present';
			document.getElementById('attendanceRecordNotes').value = button.dataset.notes || '';
		});
	});
})();
</script>

<?php require_once('includes/footer.php'); ?>
