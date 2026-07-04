<?php require_once('includes/header.php'); ?>

<?php
// Teacher timetable placeholder data. Replace these arrays with authenticated teacher/database values later.
$teacher = [
	'profile_picture' => '../assets/img/avatar/avatar-21.jpg',
	'full_name' => 'Mr. Adewale Olumide Johnson',
	'staff_id' => 'TCH001',
	'department' => 'Science',
	'academic_session' => '2025/2026',
	'term' => 'First Term'
];

$timetable = [
	['day' => 'Monday', 'start' => '08:00', 'end' => '09:00', 'time' => '8:00 AM - 9:00 AM', 'class' => 'SS 1 Science', 'subject' => 'Mathematics', 'venue' => 'Room 12'],
	['day' => 'Monday', 'start' => '11:00', 'end' => '12:00', 'time' => '11:00 AM - 12:00 PM', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'venue' => 'Computer Lab'],
	['day' => 'Tuesday', 'start' => '09:00', 'end' => '10:00', 'time' => '9:00 AM - 10:00 AM', 'class' => 'JSS 1A', 'subject' => 'Mathematics', 'venue' => 'Room 5'],
	['day' => 'Tuesday', 'start' => '12:00', 'end' => '13:00', 'time' => '12:00 PM - 1:00 PM', 'class' => 'SS 2 Science', 'subject' => 'Physics', 'venue' => 'Science Laboratory'],
	['day' => 'Wednesday', 'start' => '08:00', 'end' => '09:00', 'time' => '8:00 AM - 9:00 AM', 'class' => 'SS 2 Science', 'subject' => 'Physics', 'venue' => 'Science Laboratory'],
	['day' => 'Wednesday', 'start' => '10:00', 'end' => '11:00', 'time' => '10:00 AM - 11:00 AM', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'venue' => 'Lab 1'],
	['day' => 'Thursday', 'start' => '09:00', 'end' => '10:00', 'time' => '9:00 AM - 10:00 AM', 'class' => 'SS 1 Science', 'subject' => 'Mathematics', 'venue' => 'Room 12'],
	['day' => 'Thursday', 'start' => '12:00', 'end' => '13:00', 'time' => '12:00 PM - 1:00 PM', 'class' => 'JSS 1A', 'subject' => 'Mathematics', 'venue' => 'Room 5'],
	['day' => 'Friday', 'start' => '08:00', 'end' => '09:00', 'time' => '8:00 AM - 9:00 AM', 'class' => 'SS 2 Science', 'subject' => 'Physics', 'venue' => 'Science Laboratory'],
	['day' => 'Friday', 'start' => '10:00', 'end' => '11:00', 'time' => '10:00 AM - 11:00 AM', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'venue' => 'Computer Lab']
];

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$classes = array_values(array_unique(array_column($timetable, 'class')));
$subjects = array_values(array_unique(array_column($timetable, 'subject')));

function teacherTimetableValue($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
	/* Teacher timetable module: scoped premium styling for weekly schedule, filters, and daily classes. */
	.teacher-timetable-page {
		--tt-primary: #0f766e;
		--tt-primary-dark: #115e59;
		--tt-primary-soft: rgba(15, 118, 110, .1);
		--tt-success: #16a34a;
		--tt-warning: #f59e0b;
		--tt-warning-soft: rgba(245, 158, 11, .14);
		--tt-blue: #2563eb;
		--tt-blue-soft: rgba(37, 99, 235, .1);
		--tt-ink: #10201d;
		--tt-muted: #64748b;
		--tt-border: rgba(15, 118, 110, .18);
		--tt-shadow: 0 22px 60px rgba(15, 23, 42, .09);
		padding-bottom: 34px;
	}

	.teacher-timetable-page .timetable-profile,
	.teacher-timetable-page .timetable-card,
	.teacher-timetable-page .today-card,
	.teacher-timetable-page .current-class-card {
		background: rgba(255, 255, 255, .98);
		border: 1px solid var(--tt-border);
		box-shadow: var(--tt-shadow);
	}

	.teacher-timetable-page .timetable-profile {
		position: relative;
		overflow: hidden;
		padding: 28px;
		border-radius: 26px;
		margin-bottom: 24px;
		background: linear-gradient(135deg, rgba(240, 253, 244, .98), rgba(255, 255, 255, .98));
	}

	.teacher-timetable-page .timetable-profile::after {
		content: "";
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top right, rgba(20, 184, 166, .16), transparent 36%), radial-gradient(circle at bottom left, rgba(37, 99, 235, .08), transparent 32%);
		pointer-events: none;
	}

	.teacher-timetable-page .timetable-profile > * {
		position: relative;
		z-index: 1;
	}

	.teacher-timetable-page .teacher-avatar {
		width: 92px;
		height: 92px;
		border-radius: 24px;
		object-fit: cover;
		border: 4px solid #fff;
		box-shadow: 0 18px 38px rgba(15, 118, 110, .2);
	}

	.teacher-timetable-page .profile-kicker,
	.teacher-timetable-page .filter-icon,
	.teacher-timetable-page .status-pill,
	.teacher-timetable-page .lesson-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.teacher-timetable-page .profile-kicker {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--tt-primary-soft);
		color: var(--tt-primary-dark);
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.teacher-timetable-page h3,
	.teacher-timetable-page h4,
	.teacher-timetable-page h5 {
		color: var(--tt-ink);
		font-weight: 900;
	}

	.teacher-timetable-page .meta-grid {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 10px;
		margin-top: 16px;
	}

	.teacher-timetable-page .meta-item {
		padding: 11px 13px;
		border-radius: 16px;
		background: rgba(255, 255, 255, .76);
		border: 1px solid rgba(15, 118, 110, .12);
	}

	.teacher-timetable-page .meta-item span {
		display: block;
		color: var(--tt-muted);
		font-size: 12px;
		font-weight: 800;
	}

	.teacher-timetable-page .meta-item strong {
		font-size: 14px;
		color: var(--tt-ink);
	}

	.teacher-timetable-page .timetable-card,
	.teacher-timetable-page .today-card,
	.teacher-timetable-page .current-class-card {
		border-radius: 24px;
		overflow: hidden;
	}

	.teacher-timetable-page .timetable-card,
	.teacher-timetable-page .today-card {
		padding: 22px;
		margin-bottom: 22px;
	}

	.teacher-timetable-page .current-class-card {
		padding: 18px;
		background: linear-gradient(135deg, rgba(15, 118, 110, .97), rgba(17, 94, 89, .96));
		color: #fff;
	}

	.teacher-timetable-page .current-class-card h5,
	.teacher-timetable-page .current-class-card p,
	.teacher-timetable-page .current-class-card small {
		color: #fff;
	}

	.teacher-timetable-page .lesson-icon {
		width: 42px;
		height: 42px;
		border-radius: 14px;
		background: rgba(255, 255, 255, .16);
	}

	.teacher-timetable-page .filter-field {
		position: relative;
	}

	.teacher-timetable-page .filter-icon {
		position: absolute;
		left: 14px;
		top: 50%;
		transform: translateY(-50%);
		color: var(--tt-primary);
		pointer-events: none;
	}

	.teacher-timetable-page .form-label {
		font-size: 13px;
		font-weight: 900;
		color: var(--tt-ink);
	}

	.teacher-timetable-page .form-select,
	.teacher-timetable-page .form-control {
		min-height: 50px;
		padding-left: 42px;
		border: 1px solid rgba(148, 163, 184, .32);
		border-radius: 15px;
		font-weight: 800;
		box-shadow: none;
	}

	.teacher-timetable-page .form-select:focus,
	.teacher-timetable-page .form-control:focus {
		border-color: rgba(15, 118, 110, .72);
		box-shadow: 0 0 0 4px rgba(15, 118, 110, .12);
	}

	.teacher-timetable-page .reset-filter-btn {
		min-height: 50px;
		border-radius: 15px;
		font-weight: 900;
	}

	.teacher-timetable-page .today-list {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
		gap: 14px;
	}

	.teacher-timetable-page .today-item {
		padding: 16px;
		border-radius: 18px;
		background: #f8fafc;
		border: 1px solid rgba(148, 163, 184, .24);
		transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
	}

	.teacher-timetable-page .today-item:hover {
		transform: translateY(-2px);
		border-color: rgba(15, 118, 110, .32);
		box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
	}

	.teacher-timetable-page .today-time {
		color: var(--tt-primary-dark);
		font-weight: 900;
	}

	.teacher-timetable-page .status-pill {
		gap: 6px;
		padding: 7px 10px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 900;
	}

	.teacher-timetable-page .status-current {
		background: rgba(22, 163, 74, .13);
		color: var(--tt-success);
	}

	.teacher-timetable-page .status-upcoming {
		background: var(--tt-warning-soft);
		color: #b45309;
	}

	.teacher-timetable-page .status-completed {
		background: var(--tt-blue-soft);
		color: var(--tt-blue);
	}

	.teacher-timetable-page .table-scroll {
		overflow-x: auto;
		border-radius: 20px;
		border: 1px solid rgba(148, 163, 184, .2);
	}

	.teacher-timetable-page .teacher-timetable-table {
		min-width: 860px;
		margin-bottom: 0;
	}

	.teacher-timetable-page .teacher-timetable-table thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		padding: 15px 14px;
		background: linear-gradient(135deg, var(--tt-primary), var(--tt-primary-dark));
		color: #fff;
		border: 0;
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.teacher-timetable-page .teacher-timetable-table td {
		padding: 15px 14px;
		border-color: rgba(148, 163, 184, .2);
		font-weight: 750;
		vertical-align: middle;
	}

	.teacher-timetable-page .schedule-row {
		transition: background .18s ease, transform .18s ease;
	}

	.teacher-timetable-page .schedule-row:hover {
		background: rgba(15, 118, 110, .04);
	}

	.teacher-timetable-page .schedule-row.is-current {
		background: rgba(22, 163, 74, .11);
		box-shadow: inset 4px 0 0 var(--tt-success);
	}

	.teacher-timetable-page .schedule-row.is-upcoming {
		background: rgba(245, 158, 11, .07);
	}

	.teacher-timetable-page .subject-chip {
		display: inline-flex;
		align-items: center;
		gap: 7px;
		padding: 8px 11px;
		border-radius: 999px;
		background: var(--tt-primary-soft);
		color: var(--tt-primary-dark);
		font-weight: 900;
	}

	.teacher-timetable-page .empty-state {
		display: none;
		padding: 28px;
		text-align: center;
		color: var(--tt-muted);
		font-weight: 800;
	}

	@media (max-width: 991.98px) {
		.teacher-timetable-page .meta-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 767.98px) {
		.teacher-timetable-page .timetable-profile,
		.teacher-timetable-page .timetable-card,
		.teacher-timetable-page .today-card {
			padding: 20px;
			border-radius: 20px;
		}

		.teacher-timetable-page .teacher-avatar {
			width: 78px;
			height: 78px;
		}

		.teacher-timetable-page .meta-grid {
			grid-template-columns: 1fr;
		}

		.teacher-timetable-page .teacher-timetable-table {
			min-width: 0;
			border-collapse: separate;
			border-spacing: 0 12px;
		}

		.teacher-timetable-page .teacher-timetable-table thead {
			display: none;
		}

		.teacher-timetable-page .teacher-timetable-table,
		.teacher-timetable-page .teacher-timetable-table tbody,
		.teacher-timetable-page .teacher-timetable-table tr,
		.teacher-timetable-page .teacher-timetable-table td {
			display: block;
			width: 100%;
		}

		.teacher-timetable-page .teacher-timetable-table tr {
			border: 1px solid rgba(15, 118, 110, .16);
			border-radius: 18px;
			background: #fff;
			overflow: hidden;
			box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
		}

		.teacher-timetable-page .teacher-timetable-table td {
			display: flex;
			justify-content: space-between;
			gap: 16px;
			padding: 12px 14px;
		}

		.teacher-timetable-page .teacher-timetable-table td::before {
			content: attr(data-label);
			min-width: 92px;
			color: var(--tt-muted);
			font-size: 12px;
			font-weight: 900;
			text-transform: uppercase;
		}
	}
</style>

<div class="teacher-timetable-page">
	
	<!-- Current class alert: updated by JavaScript based on the current day and time. -->
	<section class="current-class-card mb-4" id="currentClassCard" aria-live="polite">
		<div class="d-flex align-items-center gap-3">
			<span class="lesson-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
			<div>
				<small class="fw-bold text-uppercase">Current Class</small>
				<h5 class="mb-1" id="currentClassTitle">Checking schedule...</h5>
				<p class="mb-0" id="currentClassMeta">Your ongoing or next class will appear here.</p>
			</div>
		</div>
	</section>

	<!-- Filter panel: allows teachers to narrow timetable by day, class, or subject. -->
	<section class="timetable-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div>
				<h4 class="mb-1">Weekly Teaching Schedule</h4>
				<p class="text-muted mb-0">Filter your assigned timetable by day, class, or subject.</p>
			</div>
			<button type="button" class="btn btn-outline-success reset-filter-btn" id="resetFilters"><i class="fa-solid fa-rotate-left me-2"></i>Reset</button>
		</div>
		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label" for="dayFilter">Day</label>
				<div class="filter-field"><span class="filter-icon"><i class="fa-solid fa-calendar-day"></i></span><select class="form-select" id="dayFilter"><option value="">All Days</option><?php foreach ($days as $day): ?><option value="<?php echo teacherTimetableValue($day); ?>"><?php echo teacherTimetableValue($day); ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="classFilter">Class</label>
				<div class="filter-field"><span class="filter-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classFilter"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option value="<?php echo teacherTimetableValue($class); ?>"><?php echo teacherTimetableValue($class); ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="subjectFilter">Subject</label>
				<div class="filter-field"><span class="filter-icon"><i class="fa-solid fa-book-open"></i></span><select class="form-select" id="subjectFilter"><option value="">All Subjects</option><?php foreach ($subjects as $subject): ?><option value="<?php echo teacherTimetableValue($subject); ?>"><?php echo teacherTimetableValue($subject); ?></option><?php endforeach; ?></select></div>
			</div>
		</div>
	</section>

	<!-- Today's classes: JavaScript shows only lessons matching the current weekday. -->
	<section class="today-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
			<div>
				<h5 class="mb-1">Today's Classes</h5>
				<p class="text-muted mb-0" id="todayLabel">Your teaching activities for today.</p>
			</div>
		</div>
		<div class="today-list" id="todayList"></div>
	</section>

	<!-- Timetable table: desktop table with mobile card transformation. -->
	<section class="timetable-card">
		<div class="table-scroll">
			<table class="table teacher-timetable-table align-middle">
				<thead>
					<tr>
						<th>Time</th>
						<th>Day</th>
						<th>Class</th>
						<th>Subject</th>
						<th>Venue</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody id="timetableBody">
					<?php foreach ($timetable as $lesson): ?>
						<tr class="schedule-row" data-day="<?php echo teacherTimetableValue($lesson['day']); ?>" data-class="<?php echo teacherTimetableValue($lesson['class']); ?>" data-subject="<?php echo teacherTimetableValue($lesson['subject']); ?>" data-start="<?php echo teacherTimetableValue($lesson['start']); ?>" data-end="<?php echo teacherTimetableValue($lesson['end']); ?>">
							<td data-label="Time"><i class="fa-regular fa-clock me-2 text-success"></i><?php echo teacherTimetableValue($lesson['time']); ?></td>
							<td data-label="Day"><?php echo teacherTimetableValue($lesson['day']); ?></td>
							<td data-label="Class"><?php echo teacherTimetableValue($lesson['class']); ?></td>
							<td data-label="Subject"><span class="subject-chip"><i class="fa-solid fa-book"></i><?php echo teacherTimetableValue($lesson['subject']); ?></span></td>
							<td data-label="Venue"><i class="fa-solid fa-location-dot me-2 text-success"></i><?php echo teacherTimetableValue($lesson['venue']); ?></td>
							<td data-label="Status"><span class="status-pill" data-status-pill>Scheduled</span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="empty-state" id="emptyState"><i class="fa-solid fa-magnifying-glass me-2"></i>No timetable records match the selected filters.</div>
		</div>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Teacher timetable behavior: filter rows, highlight current/upcoming lessons, and render today's schedule.
(function () {
	var timetable = <?php echo json_encode($timetable); ?>;
	var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	var now = new Date();
	var currentDay = dayNames[now.getDay()];
	var currentMinutes = now.getHours() * 60 + now.getMinutes();

	function byId(id) { return document.getElementById(id); }
	function toMinutes(value) { var parts = value.split(':'); return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10); }
	function lessonStatus(lesson) {
		if (lesson.day !== currentDay) { return 'Scheduled'; }
		if (currentMinutes >= toMinutes(lesson.start) && currentMinutes < toMinutes(lesson.end)) { return 'Current'; }
		if (currentMinutes < toMinutes(lesson.start)) { return 'Upcoming'; }
		return 'Completed';
	}

	function statusMarkup(status) {
		var icon = status === 'Current' ? 'fa-circle-play' : (status === 'Upcoming' ? 'fa-hourglass-half' : (status === 'Completed' ? 'fa-circle-check' : 'fa-calendar-check'));
		var cls = status === 'Current' ? 'status-current' : (status === 'Upcoming' ? 'status-upcoming' : (status === 'Completed' ? 'status-completed' : ''));
		return '<span class="status-pill ' + cls + '"><i class="fa-solid ' + icon + '"></i>' + status + '</span>';
	}

	function findLessonFromRow(row) {
		return timetable.find(function (lesson) {
			return lesson.day === row.getAttribute('data-day') && lesson.class === row.getAttribute('data-class') && lesson.subject === row.getAttribute('data-subject') && lesson.start === row.getAttribute('data-start');
		});
	}

	function updateStatuses() {
		var currentLesson = null;
		var upcomingLesson = null;
		document.querySelectorAll('.schedule-row').forEach(function (row) {
			var lesson = findLessonFromRow(row);
			var status = lesson ? lessonStatus(lesson) : 'Scheduled';
			row.classList.toggle('is-current', status === 'Current');
			row.classList.toggle('is-upcoming', status === 'Upcoming');
			var pill = row.querySelector('[data-status-pill]');
			if (pill) { pill.outerHTML = statusMarkup(status); }
			if (status === 'Current') { currentLesson = lesson; }
			if (!upcomingLesson && status === 'Upcoming') { upcomingLesson = lesson; }
		});

		var featured = currentLesson || upcomingLesson;
		byId('currentClassTitle').textContent = featured ? featured.class + ' ' + featured.subject : 'No active class right now';
		byId('currentClassMeta').textContent = featured ? featured.day + ' | ' + featured.time + ' | ' + featured.venue : 'Your timetable has no current or upcoming class for this moment.';
	}

	function renderToday() {
		var todayLessons = timetable.filter(function (lesson) { return lesson.day === currentDay; });
		byId('todayLabel').textContent = currentDay + ' teaching activities';
		byId('todayList').innerHTML = todayLessons.length ? todayLessons.map(function (lesson) {
			return '<article class="today-item"><div class="today-time"><i class="fa-regular fa-clock me-2"></i>' + lesson.time + '</div><h5 class="mb-1 mt-2">' + lesson.class + ' ' + lesson.subject + '</h5><p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>' + lesson.venue + '</p></article>';
		}).join('') : '<div class="today-item"><h5 class="mb-1">No classes today</h5><p class="text-muted mb-0">There are no assigned lessons for ' + currentDay + '.</p></div>';
	}

	function filterRows() {
		var day = byId('dayFilter').value;
		var className = byId('classFilter').value;
		var subject = byId('subjectFilter').value;
		var visible = 0;
		document.querySelectorAll('.schedule-row').forEach(function (row) {
			var show = (!day || row.getAttribute('data-day') === day) && (!className || row.getAttribute('data-class') === className) && (!subject || row.getAttribute('data-subject') === subject);
			row.style.display = show ? '' : 'none';
			if (show) { visible += 1; }
		});
		byId('emptyState').style.display = visible ? 'none' : 'block';
	}

	document.addEventListener('DOMContentLoaded', function () {
		updateStatuses();
		renderToday();
		['dayFilter', 'classFilter', 'subjectFilter'].forEach(function (id) { byId(id).addEventListener('change', filterRows); });
		byId('resetFilters').addEventListener('click', function () { byId('dayFilter').value = ''; byId('classFilter').value = ''; byId('subjectFilter').value = ''; filterRows(); });
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>
