<?php require_once('includes/header.php'); ?>

<?php
// Attendance placeholder data. Replace these arrays with teacher-scoped database queries later.
$schoolName = 'Brighter Future Standard School, Katsina';
$today = date('Y-m-d');
$assignedClasses = ['JSS 1A', 'JSS 2B', 'SS 1 Science'];
$studentsByClass = [
	'JSS 1A' => [
		['reg_no' => 'ST001', 'name' => 'Musa Ibrahim'],
		['reg_no' => 'ST002', 'name' => 'Aisha Bello'],
		['reg_no' => 'ST003', 'name' => 'Daniel Okafor'],
		['reg_no' => 'ST004', 'name' => 'Maryam Musa'],
		['reg_no' => 'ST005', 'name' => 'Samuel Adeyemi']
	],
	'JSS 2B' => [
		['reg_no' => 'ST011', 'name' => 'Ibrahim Sani'],
		['reg_no' => 'ST012', 'name' => 'Grace Emmanuel'],
		['reg_no' => 'ST013', 'name' => 'Chinedu Nwosu'],
		['reg_no' => 'ST014', 'name' => 'Hauwa Lawal']
	],
	'SS 1 Science' => [
		['reg_no' => 'ST021', 'name' => 'Fatima Abdullahi'],
		['reg_no' => 'ST022', 'name' => 'Joshua Martins'],
		['reg_no' => 'ST023', 'name' => 'Hauwa Ibrahim'],
		['reg_no' => 'ST024', 'name' => 'Peter Daniel']
	]
];
$attendanceHistory = [
	['date' => '2026-07-01', 'class' => 'JSS 1A', 'records' => ['ST001' => 'Present', 'ST002' => 'Absent', 'ST003' => 'Present', 'ST004' => 'Present', 'ST005' => 'Present']],
	['date' => '2026-06-30', 'class' => 'JSS 1A', 'records' => ['ST001' => 'Present', 'ST002' => 'Present', 'ST003' => 'Present', 'ST004' => 'Absent', 'ST005' => 'Present']],
	['date' => '2026-07-01', 'class' => 'JSS 2B', 'records' => ['ST011' => 'Present', 'ST012' => 'Present', 'ST013' => 'Absent', 'ST014' => 'Present']]
];
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

	.attendance-page .attendance-kicker,
	.attendance-page .control-icon,
	.attendance-page .summary-icon,
	.attendance-page .status-choice,
	.attendance-page .report-actions .btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.attendance-page .attendance-kicker {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--att-primary-soft);
		color: var(--att-primary-dark);
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.attendance-page .attendance-hero h3 {
		margin: 12px 0 8px;
		color: var(--att-ink);
		font-size: 26px;
		font-weight: 900;
	}

	.attendance-page .attendance-hero p {
		max-width: 780px;
		margin: 0;
		color: var(--att-muted);
	}

	.attendance-page .attendance-card,
	.attendance-page .table-card {
		border-radius: 24px;
		overflow: hidden;
	}

	.attendance-page .attendance-card {
		padding: 24px;
		margin-bottom: 22px;
	}

	.attendance-page .form-label {
		color: var(--att-ink);
		font-size: 13px;
		font-weight: 900;
	}

	.attendance-page .control-field {
		position: relative;
	}

	.attendance-page .control-icon {
		position: absolute;
		left: 14px;
		top: 50%;
		width: 22px;
		height: 22px;
		transform: translateY(-50%);
		color: var(--att-primary);
		pointer-events: none;
	}

	.attendance-page .form-select,
	.attendance-page .form-control {
		min-height: 50px;
		padding-left: 44px;
		border: 1px solid rgba(148, 163, 184, .32);
		border-radius: 15px;
		font-weight: 700;
		box-shadow: none;
	}

	.attendance-page .form-select:focus,
	.attendance-page .form-control:focus {
		border-color: rgba(15, 118, 110, .72);
		box-shadow: 0 0 0 4px rgba(15, 118, 110, .12);
	}

	.attendance-page .load-btn,
	.attendance-page .save-btn,
	.attendance-page .update-btn {
		min-height: 50px;
		border: 0;
		border-radius: 15px;
		background: linear-gradient(135deg, var(--att-primary), var(--att-primary-dark));
		color: #fff;
		font-weight: 900;
		box-shadow: 0 16px 34px rgba(15, 118, 110, .24);
	}

	.attendance-page .load-btn:hover,
	.attendance-page .save-btn:hover,
	.attendance-page .update-btn:hover {
		color: #fff;
		transform: translateY(-2px);
	}

	.attendance-page .summary-card {
		height: 100%;
		padding: 18px;
		border-radius: 20px;
	}

	.attendance-page .summary-icon {
		width: 42px;
		height: 42px;
		border-radius: 14px;
		background: var(--att-primary-soft);
		color: var(--att-primary);
	}

	.attendance-page .summary-icon.success { background: var(--att-success-soft); color: var(--att-success); }
	.attendance-page .summary-icon.danger { background: var(--att-danger-soft); color: var(--att-danger); }
	.attendance-page .summary-icon.blue { background: var(--att-blue-soft); color: var(--att-blue); }

	.attendance-page .summary-card h4 {
		margin: 10px 0 2px;
		font-weight: 900;
	}

	.attendance-page .notice {
		display: none;
		gap: 8px;
		align-items: center;
		padding: 12px 14px;
		border-radius: 14px;
		font-weight: 800;
		margin-bottom: 16px;
	}

	.attendance-page .notice.is-visible { display: flex; }
	.attendance-page .notice.success { color: var(--att-success); background: var(--att-success-soft); }
	.attendance-page .notice.error { color: var(--att-danger); background: var(--att-danger-soft); }

	.attendance-page .table-toolbar {
		padding: 18px 20px;
		border-bottom: 1px solid rgba(148, 163, 184, .2);
		background: linear-gradient(180deg, #f8fafc, #fff);
	}

	.attendance-page .table-scroll {
		max-height: 560px;
		overflow: auto;
	}

	.attendance-page .attendance-table {
		min-width: 760px;
		margin-bottom: 0;
	}

	.attendance-page .attendance-table thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		padding: 14px 12px;
		background: linear-gradient(135deg, var(--att-primary), var(--att-primary-dark));
		color: #fff;
		border: 0;
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.attendance-page .attendance-table td {
		padding: 12px;
		vertical-align: middle;
		border-color: rgba(148, 163, 184, .2);
		font-weight: 700;
	}

	.attendance-page .status-toggle {
		display: flex;
		gap: 8px;
		flex-wrap: wrap;
	}

	.attendance-page .status-choice {
		gap: 7px;
		padding: 8px 12px;
		border: 1px solid rgba(148, 163, 184, .26);
		border-radius: 999px;
		background: #fff;
		font-weight: 900;
		cursor: pointer;
	}

	.attendance-page .status-choice.present.active { color: var(--att-success); background: var(--att-success-soft); border-color: rgba(22, 163, 74, .35); }
	.attendance-page .status-choice.absent.active { color: var(--att-danger); background: var(--att-danger-soft); border-color: rgba(220, 38, 38, .35); }

	.attendance-page .history-table {
		min-width: 820px;
	}

	.attendance-page .report-actions {
		gap: 10px;
	}

	@media (max-width: 767.98px) {
		.attendance-page .attendance-hero,
		.attendance-page .attendance-card { padding: 20px; border-radius: 20px; }
		.attendance-page .attendance-hero h3 { font-size: 22px; }
		.attendance-page .report-actions,
		.attendance-page .report-actions .btn { width: 100%; }
	}
</style>

<div class="attendance-page">
	<!-- Page intro: explains the daily attendance workflow. -->
	<section class="attendance-hero">
		<span class="attendance-kicker"><i class="fa-solid fa-calendar-check"></i> Attendance Management</span>
		<h3>Daily Attendance & Reports</h3>
		<p>Select an assigned class and date, mark students present or absent, review history, edit records, and export attendance reports.</p>
	</section>

	<!-- Attendance controls: class and date selection before loading students. -->
	<section class="attendance-card">
		<div id="attendanceNotice" class="notice" role="alert"><i class="fa-solid fa-circle-info"></i><span></span></div>
		<form id="loadAttendanceForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-5">
				<label class="form-label" for="attendanceClass">Class Selection</label>
				<div class="control-field"><span class="control-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="attendanceClass" required><option value="">Select assigned class</option><?php foreach ($assignedClasses as $class): ?><option value="<?php echo $class; ?>"><?php echo $class; ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="attendanceDate">Date Selection</label>
				<div class="control-field"><span class="control-icon"><i class="fa-solid fa-calendar-day"></i></span><input type="date" class="form-control" id="attendanceDate" value="<?php echo $today; ?>" required></div>
			</div>
			<div class="col-md-3">
				<button type="submit" class="btn load-btn w-100"><i class="fa-solid fa-users-viewfinder me-2"></i>Load Students</button>
			</div>
		</form>
	</section>

	<!-- Attendance dashboard cards: recalculated whenever statuses change. -->
	<section class="row g-3 mb-4" aria-label="Attendance summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4 id="totalStudents">0</h4><p class="text-muted mb-0">Total Students</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-check"></i></span><h4 id="presentToday">0</h4><p class="text-muted mb-0">Present Today</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-times"></i></span><h4 id="absentToday">0</h4><p class="text-muted mb-0">Absent Today</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-percent"></i></span><h4 id="attendanceRate">0%</h4><p class="text-muted mb-0">Attendance Rate</p></div></div>
	</section>

	<!-- Daily attendance table: loaded after selecting class and date. -->
	<section class="table-card mb-4" id="dailyTableCard" style="display:none;">
		<div class="table-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h5 class="mb-1" id="dailyTitle">Daily Attendance</h5><p class="text-muted mb-0">Switch each student between Present and Absent.</p></div><div class="report-actions d-flex flex-wrap"><button type="button" class="btn save-btn" id="saveAttendanceBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Attendance</button><button type="button" class="btn update-btn" id="updateAttendanceBtn" style="display:none;"><i class="fa-solid fa-pen-to-square me-2"></i>Update Attendance</button></div></div>
		<div class="table-scroll"><table class="table attendance-table align-middle"><thead><tr><th>Registration Number</th><th>Student Name</th><th>Status</th></tr></thead><tbody id="dailyAttendanceBody"></tbody></table></div>
	</section>

	<!-- Attendance history and report filters: previous records can be edited or exported. -->
	<section class="attendance-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3"><div><h5 class="mb-1">Attendance History & Reports</h5><p class="text-muted mb-0">Filter previous records, edit attendance, and generate reports.</p></div><div class="report-actions d-flex flex-wrap"><button type="button" class="btn btn-outline-success" id="exportCsvBtn"><i class="fa-solid fa-file-csv me-2"></i>CSV</button><button type="button" class="btn btn-outline-danger" id="exportPdfBtn"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button></div></div>
		<div class="row g-3 mb-3">
			<div class="col-md-3"><label class="form-label" for="historyClass">Class</label><select class="form-select ps-3" id="historyClass"><option value="">All Classes</option><?php foreach ($assignedClasses as $class): ?><option value="<?php echo $class; ?>"><?php echo $class; ?></option><?php endforeach; ?></select></div>
			<div class="col-md-3"><label class="form-label" for="historyFrom">Date From</label><input type="date" class="form-control ps-3" id="historyFrom"></div>
			<div class="col-md-3"><label class="form-label" for="historyTo">Date To</label><input type="date" class="form-control ps-3" id="historyTo"></div>
			<div class="col-md-3"><label class="form-label" for="reportType">Report Type</label><select class="form-select ps-3" id="reportType"><option value="Daily">Daily</option><option value="Weekly">Weekly</option><option value="Monthly">Monthly</option></select></div>
		</div>
		<div class="table-scroll"><table class="table attendance-table history-table align-middle"><thead><tr><th>Date</th><th>Class</th><th>Present</th><th>Absent</th><th>Attendance Rate</th><th>Action</th></tr></thead><tbody id="historyBody"></tbody></table></div>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Attendance module behavior: load students, switch status, save/edit records, calculate rates, and export reports.
(function () {
	var schoolName = <?php echo json_encode($schoolName); ?>;
	var studentsByClass = <?php echo json_encode($studentsByClass); ?>;
	var records = <?php echo json_encode($attendanceHistory); ?>;
	var selectedClass = '';
	var selectedDate = '';
	var editingKey = '';

	function byId(id) { return document.getElementById(id); }
	function recordKey(className, date) { return className + '|' + date; }
	function findRecord(className, date) { return records.find(function (record) { return record.class === className && record.date === date; }); }
	function percent(present, total) { return total ? ((present / total) * 100).toFixed(1) + '%' : '0%'; }
	function showNotice(type, message) { var notice = byId('attendanceNotice'); notice.className = 'notice is-visible ' + type; notice.querySelector('span').textContent = message; }

	function studentPercentage(className, regNo) {
		var classRecords = records.filter(function (record) { return record.class === className && record.records[regNo]; });
		var present = classRecords.filter(function (record) { return record.records[regNo] === 'Present'; }).length;
		return percent(present, classRecords.length);
	}

	function calculateDailySummary() {
		var rows = document.querySelectorAll('#dailyAttendanceBody tr');
		var total = rows.length;
		var present = Array.prototype.filter.call(rows, function (row) { return row.getAttribute('data-status') === 'Present'; }).length;
		byId('totalStudents').textContent = total;
		byId('presentToday').textContent = present;
		byId('absentToday').textContent = total - present;
		byId('attendanceRate').textContent = percent(present, total);
	}

	function statusButtons(status) {
		return '<div class="status-toggle"><button type="button" class="status-choice present ' + (status === 'Present' ? 'active' : '') + '" data-status="Present"><i class="fa-solid fa-check"></i>Present</button><button type="button" class="status-choice absent ' + (status === 'Absent' ? 'active' : '') + '" data-status="Absent"><i class="fa-solid fa-times"></i>Absent</button></div>';
	}

	function renderStudents(className, date, existing) {
		var body = byId('dailyAttendanceBody');
		var students = studentsByClass[className] || [];
		body.innerHTML = students.map(function (student) {
			var status = existing && existing.records[student.reg_no] ? existing.records[student.reg_no] : 'Present';
			return '<tr data-reg="' + student.reg_no + '" data-status="' + status + '"><td>' + student.reg_no + '</td><td>' + student.name + '<br><small class="text-muted">Attendance: ' + studentPercentage(className, student.reg_no) + '</small></td><td>' + statusButtons(status) + '</td></tr>';
		}).join('');
		calculateDailySummary();
	}

	function currentDailyRecords() {
		var data = {};
		document.querySelectorAll('#dailyAttendanceBody tr').forEach(function (row) { data[row.getAttribute('data-reg')] = row.getAttribute('data-status'); });
		return data;
	}

	function summarize(record) {
		var values = Object.values(record.records);
		var present = values.filter(function (status) { return status === 'Present'; }).length;
		return { present: present, absent: values.length - present, rate: percent(present, values.length) };
	}

	function filteredRecords() {
		var className = byId('historyClass').value;
		var from = byId('historyFrom').value;
		var to = byId('historyTo').value;
		return records.filter(function (record) {
			return (!className || record.class === className) && (!from || record.date >= from) && (!to || record.date <= to);
		});
	}

	function renderHistory() {
		byId('historyBody').innerHTML = filteredRecords().map(function (record) {
			var summary = summarize(record);
			return '<tr><td>' + record.date + '</td><td>' + record.class + '</td><td>' + summary.present + '</td><td>' + summary.absent + '</td><td>' + summary.rate + '</td><td><button type="button" class="btn btn-sm btn-outline-success edit-record" data-class="' + record.class + '" data-date="' + record.date + '"><i class="fa-solid fa-pen"></i> Edit</button></td></tr>';
		}).join('');
	}

	function csvEscape(value) { return '"' + String(value).replace(/"/g, '""') + '"'; }
	function exportCsv() {
		var rows = [['School Name', schoolName], ['Report Type', byId('reportType').value], [], ['Date', 'Class', 'Registration Number', 'Student Name', 'Status', 'Attendance Percentage']];
		filteredRecords().forEach(function (record) {
			(studentsByClass[record.class] || []).forEach(function (student) { rows.push([record.date, record.class, student.reg_no, student.name, record.records[student.reg_no] || 'Absent', studentPercentage(record.class, student.reg_no)]); });
		});
		var csv = rows.map(function (row) { return row.map(csvEscape).join(','); }).join('\n');
		var link = document.createElement('a');
		link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
		link.download = 'Attendance_Report.csv';
		document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href);
	}

	function exportPdf() {
		var reportRows = [];
		filteredRecords().forEach(function (record) {
			(studentsByClass[record.class] || []).forEach(function (student) {
				reportRows.push('<tr><td>' + record.date + '</td><td>' + record.class + '</td><td>' + student.reg_no + '</td><td>' + student.name + '</td><td>' + (record.records[student.reg_no] || 'Absent') + '</td><td>' + studentPercentage(record.class, student.reg_no) + '</td></tr>');
			});
		});
		var dateRange = (byId('historyFrom').value || 'Start') + ' to ' + (byId('historyTo').value || 'End');
		var win = window.open('', '_blank');
		win.document.write('<html><head><title>Attendance Report</title><style>@page{size:A4;margin:14mm}body{font-family:Arial,sans-serif;color:#102a43}h2{text-transform:uppercase;margin-bottom:4px}p{margin:4px 0 14px}table{width:100%;border-collapse:collapse;font-size:12px}td,th{border:1px solid #999;padding:7px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>' + schoolName + '</h2><p>Report Type: ' + byId('reportType').value + ' | Date Range: ' + dateRange + '</p><table><thead><tr><th>Date</th><th>Class</th><th>Registration Number</th><th>Student Name</th><th>Status</th><th>Attendance %</th></tr></thead><tbody>' + reportRows.join('') + '</tbody></table></body></html>');
		win.document.close(); win.focus(); win.print();
	}

	document.addEventListener('DOMContentLoaded', function () {
		renderHistory();
		byId('loadAttendanceForm').addEventListener('submit', function (event) {
			event.preventDefault(); selectedClass = byId('attendanceClass').value; selectedDate = byId('attendanceDate').value; editingKey = '';
			if (!selectedClass || !selectedDate) { showNotice('error', 'Please select class and date before loading students.'); return; }
			var existing = findRecord(selectedClass, selectedDate);
			byId('dailyTitle').textContent = selectedClass + ' Attendance - ' + selectedDate;
			byId('dailyTableCard').style.display = 'block'; byId('saveAttendanceBtn').style.display = 'inline-flex'; byId('updateAttendanceBtn').style.display = 'none';
			renderStudents(selectedClass, selectedDate, existing || null);
			showNotice(existing ? 'error' : 'success', existing ? 'Attendance already exists for this class and date. Use Edit from history to update it.' : 'Students loaded. Mark attendance and save.');
		});

		document.addEventListener('click', function (event) {
			var statusButton = event.target.closest('.status-choice');
			if (statusButton) { var row = statusButton.closest('tr'); row.setAttribute('data-status', statusButton.getAttribute('data-status')); row.querySelectorAll('.status-choice').forEach(function (btn) { btn.classList.remove('active'); }); statusButton.classList.add('active'); calculateDailySummary(); }
			var editButton = event.target.closest('.edit-record');
			if (editButton) { selectedClass = editButton.getAttribute('data-class'); selectedDate = editButton.getAttribute('data-date'); var existing = findRecord(selectedClass, selectedDate); editingKey = recordKey(selectedClass, selectedDate); byId('attendanceClass').value = selectedClass; byId('attendanceDate').value = selectedDate; byId('dailyTitle').textContent = 'Editing ' + selectedClass + ' Attendance - ' + selectedDate; byId('dailyTableCard').style.display = 'block'; byId('saveAttendanceBtn').style.display = 'none'; byId('updateAttendanceBtn').style.display = 'inline-flex'; renderStudents(selectedClass, selectedDate, existing); window.scrollTo({ top: byId('dailyTableCard').offsetTop - 20, behavior: 'smooth' }); }
		});

		byId('saveAttendanceBtn').addEventListener('click', function () {
			if (findRecord(selectedClass, selectedDate)) { showNotice('error', 'Duplicate attendance prevented. This class and date already has a saved record.'); return; }
			records.push({ date: selectedDate, class: selectedClass, records: currentDailyRecords() }); renderHistory(); showNotice('success', 'Attendance saved successfully.');
		});
		byId('updateAttendanceBtn').addEventListener('click', function () { var record = findRecord(selectedClass, selectedDate); if (record) { record.records = currentDailyRecords(); renderHistory(); calculateDailySummary(); showNotice('success', 'Attendance updated successfully.'); } });
		['historyClass', 'historyFrom', 'historyTo'].forEach(function (id) { byId(id).addEventListener('change', renderHistory); });
		byId('exportCsvBtn').addEventListener('click', exportCsv);
		byId('exportPdfBtn').addEventListener('click', exportPdf);
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>

