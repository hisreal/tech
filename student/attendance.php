<?php require_once('includes/header.php'); ?>

<?php

use App\Services\AttendanceService;

$attendanceService = new AttendanceService();
$currentUser = sms_current_user();
$studentId = $attendanceService->studentIdForUser((int) $currentUser['id']);

$records = [];
if ($studentId) {
	$result = $attendanceService->listStudentAttendance(['student_id' => $studentId], 1, 1000);
	$records = $result['data'] ?? [];
}

function studentAttValue($value)
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.attendance-page .status-icon.late,
.attendance-page .status-icon.excused,
.attendance-page .status-icon.leave { background: rgba(245, 158, 11, .13); color: #b45309; }
.attendance-page .remarks-badge.late,
.attendance-page .remarks-badge.excused,
.attendance-page .remarks-badge.leave { background: rgba(245, 158, 11, .13); color: #b45309; }
</style>

<div class="row attendance-page">
	<div class="col-lg-12 mx-auto">
		<!-- Attendance summary dashboard: JavaScript keeps these values in sync with the records. -->
		<section  class="row g-3 mb-4" aria-label="Attendance summary">
			<div class="col-sm-6 col-xl-3">
				<div class="attendance-card summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="summary-icon primary"><i class="fa-solid fa-calendar-days"></i></span>
						<span class="text-muted small">Total Days</span>
					</div>
					<h3 class="mb-0" id="totalDays">0</h3>
					<p class="text-muted mb-0">School days recorded</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-3">
				<div class="attendance-card summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="summary-icon success"><i class="fa-solid fa-check"></i></span>
						<span class="text-muted small">Present</span>
					</div>
					<h3 class="mb-0" id="presentDays">0</h3>
					<p class="text-muted mb-0">Days attended</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-3">
				<div class="attendance-card summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="summary-icon danger"><i class="fa-solid fa-times"></i></span>
						<span class="text-muted small">Absent</span>
					</div>
					<h3 class="mb-0" id="absentDays">0</h3>
					<p class="text-muted mb-0">Days missed</p>
				</div>
			</div>
			<div class="col-sm-6 col-xl-3">
				<div class="attendance-card summary-card">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<span class="summary-icon info"><i class="fa-solid fa-chart-line"></i></span>
						<span class="text-muted small">Rate</span>
					</div>
					<h3 class="mb-0" id="attendanceRate">0%</h3>
					<p class="text-muted mb-0">Current percentage</p>
				</div>
			</div>
		</section>

		<!-- Attendance spreadsheet table: includes search, filtering, sorting, sticky header, and responsive scroll. -->
		<section class="attendance-table-card mb-4">
			<div class="attendance-toolbar d-flex align-items-center justify-content-between flex-wrap">
				<div>
					<h5 class="mb-1 fs-18">Attendance History</h5>
					<p class="text-muted mb-0">Search, filter, and sort daily attendance records.</p>
				</div>
				<div class="d-flex align-items-center flex-wrap gap-2">
					<div class="input-icon">
						<span class="input-icon-addon"><i class="isax isax-search-normal-14"></i></span>
						<input type="text" class="form-control form-control-md" id="attendanceSearch" placeholder="Search by date">
					</div>
					<select class="form-select form-control-md" id="dateSort" aria-label="Sort attendance by date">
						<option value="desc">Newest first</option>
						<option value="asc">Oldest first</option>
					</select>
				</div>
			</div>
			<div class="px-3 pt-3 d-flex flex-wrap gap-2">
				<button type="button" class="filter-btn active" data-filter="all">All</button>
				<button type="button" class="filter-btn" data-filter="present">Present</button>
				<button type="button" class="filter-btn" data-filter="absent">Absent</button>
				<button type="button" class="filter-btn" data-filter="late">Late</button>
				<button type="button" class="filter-btn" data-filter="excused">Excused</button>
				<button type="button" class="filter-btn" data-filter="leave">Leave</button>
			</div>
			<div class="attendance-table-wrap mt-3">
				<table class="table attendance-table align-middle" id="attendanceTable">
					<thead>
						<tr>
							<th>Date</th>
							<th>Day</th>
							<th>Status</th>
							<th>Remarks</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($records as $record): ?>
							<?php
							$status = (string) $record['status'];
							$date = (string) $record['attendance_date'];
							$displayDate = date('d/m/Y', strtotime($date));
							$dayName = date('l', strtotime($date));
							$notes = trim((string) ($record['notes'] ?? ''));
							$remarks = $notes !== '' ? $notes : ucfirst($status);
							?>
							<tr data-date="<?php echo studentAttValue($date); ?>" data-status="<?php echo studentAttValue($status); ?>">
								<td><?php echo studentAttValue($displayDate); ?></td>
								<td><?php echo studentAttValue($dayName); ?></td>
								<td><span class="status-icon <?php echo studentAttValue($status); ?>" data-bs-toggle="tooltip" title="<?php echo studentAttValue(ucfirst($status)); ?>"><i class="fa-solid <?php echo $status === 'present' ? 'fa-check' : ($status === 'absent' ? 'fa-times' : 'fa-clock'); ?>"></i></span></td>
								<td><span class="remarks-badge <?php echo studentAttValue($status); ?>"><?php echo studentAttValue($remarks); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="empty-state text-center" id="attendanceEmptyState">
					<i class="fa-solid fa-magnifying-glass fs-24 d-block mb-2"></i>
					No attendance records match your search.
				</div>
			</div>
		</section>
	</div>
</div>
</div>
</div>

<!-- Attendance page behavior: search, filters, date sorting, tooltips, and live summary calculations. -->
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const table = document.getElementById('attendanceTable');
		const tbody = table.querySelector('tbody');
		const searchInput = document.getElementById('attendanceSearch');
		const sortSelect = document.getElementById('dateSort');
		const filterButtons = document.querySelectorAll('.filter-btn');
		const emptyState = document.getElementById('attendanceEmptyState');
		const allRows = Array.from(tbody.querySelectorAll('tr'));
		let activeFilter = 'all';

		function getDisplayDate(row) {
			return row.cells[0].textContent.trim().toLowerCase();
		}

		function formatRate(present, total) {
			return total ? ((present / total) * 100).toFixed(1) : '0.0';
		}

		function setSummary(rows) {
			const total = rows.length;
			const present = rows.filter(row => row.dataset.status === 'present').length;
			const absent = rows.filter(row => row.dataset.status === 'absent').length;
			const rate = formatRate(present, total);

			document.getElementById('totalDays').textContent = total;
			document.getElementById('presentDays').textContent = present;
			document.getElementById('absentDays').textContent = absent;
			document.getElementById('attendanceRate').textContent = rate + '%';
		}

		function sortRows() {
			const direction = sortSelect.value;
			const sortedRows = Array.from(tbody.querySelectorAll('tr')).sort((a, b) => {
				const aDate = new Date(a.dataset.date);
				const bDate = new Date(b.dataset.date);
				return direction === 'asc' ? aDate - bDate : bDate - aDate;
			});

			sortedRows.forEach(row => tbody.appendChild(row));
		}

		function applyControls() {
			const query = searchInput.value.trim().toLowerCase();
			const visibleRows = [];

			allRows.forEach(row => {
				const matchesStatus = activeFilter === 'all' || row.dataset.status === activeFilter;
				const matchesDate = !query || getDisplayDate(row).includes(query) || row.dataset.date.includes(query);
				const shouldShow = matchesStatus && matchesDate;

				row.style.display = shouldShow ? '' : 'none';
				if (shouldShow) {
					visibleRows.push(row);
				}
			});

			emptyState.style.display = visibleRows.length ? 'none' : 'block';
			setSummary(visibleRows);
		}

		filterButtons.forEach(button => {
			button.addEventListener('click', function () {
				filterButtons.forEach(item => item.classList.remove('active'));
				button.classList.add('active');
				activeFilter = button.dataset.filter;
				applyControls();
			});
		});

		searchInput.addEventListener('input', applyControls);
		sortSelect.addEventListener('change', function () {
			sortRows();
			applyControls();
		});

		if (window.bootstrap && bootstrap.Tooltip) {
			Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(element => new bootstrap.Tooltip(element));
		}

		sortRows();
		applyControls();
	});
</script>

<?php require_once('includes/footer.php'); ?>
