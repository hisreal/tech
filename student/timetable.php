<?php require_once('includes/header.php'); ?>

<?php
// Placeholder student and timetable data. Replace with database values when backend integration is ready.
$student = [
	'name' => 'Ajiboye Isreal Oluwaseun',
	'class' => 'SS 2',
	'section' => 'Science',
	'academic_session' => '2025/2026',
	'term' => 'Second Term'
];

$timeSlots = [
	['time' => '08:00 - 08:45', 'monday' => ['Mathematics', 'Mr. Adewale', 'Room 204'], 'tuesday' => ['English Language', 'Mrs. Johnson', 'Room 101'], 'wednesday' => ['Biology', 'Mrs. Okafor', 'Lab 2'], 'thursday' => ['Physics', 'Mr. Musa', 'Lab 1'], 'friday' => ['Chemistry', 'Dr. Bello', 'Lab 3']],
	['time' => '08:50 - 09:35', 'monday' => ['English Language', 'Mrs. Johnson', 'Room 101'], 'tuesday' => ['Mathematics', 'Mr. Adewale', 'Room 204'], 'wednesday' => ['Computer Science', 'Mr. Daniels', 'ICT Lab'], 'thursday' => ['Chemistry', 'Dr. Bello', 'Lab 3'], 'friday' => ['Civic Education', 'Mrs. Lawal', 'Room 108']],
	['time' => '09:40 - 10:25', 'monday' => ['Biology', 'Mrs. Okafor', 'Lab 2'], 'tuesday' => ['Physics', 'Mr. Musa', 'Lab 1'], 'wednesday' => ['Chemistry', 'Dr. Bello', 'Lab 3'], 'thursday' => ['Mathematics', 'Mr. Adewale', 'Room 204'], 'friday' => ['Computer Science', 'Mr. Daniels', 'ICT Lab']],
	['time' => '10:25 - 10:55', 'monday' => ['Break', 'Student Affairs', 'Courtyard'], 'tuesday' => ['Break', 'Student Affairs', 'Courtyard'], 'wednesday' => ['Break', 'Student Affairs', 'Courtyard'], 'thursday' => ['Break', 'Student Affairs', 'Courtyard'], 'friday' => ['Break', 'Student Affairs', 'Courtyard']],
	['time' => '11:00 - 11:45', 'monday' => ['Physics', 'Mr. Musa', 'Lab 1'], 'tuesday' => ['Chemistry', 'Dr. Bello', 'Lab 3'], 'wednesday' => ['Mathematics', 'Mr. Adewale', 'Room 204'], 'thursday' => ['English Language', 'Mrs. Johnson', 'Room 101'], 'friday' => ['Biology', 'Mrs. Okafor', 'Lab 2']],
	['time' => '11:50 - 12:35', 'monday' => ['Computer Science', 'Mr. Daniels', 'ICT Lab'], 'tuesday' => ['Civic Education', 'Mrs. Lawal', 'Room 108'], 'wednesday' => ['Physics', 'Mr. Musa', 'Lab 1'], 'thursday' => ['Biology', 'Mrs. Okafor', 'Lab 2'], 'friday' => ['Mathematics', 'Mr. Adewale', 'Room 204']],
	['time' => '12:40 - 01:25', 'monday' => ['Civic Education', 'Mrs. Lawal', 'Room 108'], 'tuesday' => ['Biology', 'Mrs. Okafor', 'Lab 2'], 'wednesday' => ['English Language', 'Mrs. Johnson', 'Room 101'], 'thursday' => ['Computer Science', 'Mr. Daniels', 'ICT Lab'], 'friday' => ['Chemistry', 'Dr. Bello', 'Lab 3']]
];

function renderSubjectCard($lesson) {
	return '<div class="subject-card"><strong>' . $lesson[0] . '</strong><span><i class="fa-solid fa-user-tie me-1"></i>' . $lesson[1] . '</span><span><i class="fa-solid fa-location-dot me-1"></i>' . $lesson[2] . '</span></div>';
}
?>

<div class="student-portal-module">
	<!-- Timetable hero: identifies the current student's class schedule context. -->
	<section class="portal-hero">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div>
				<span class="portal-kicker mb-3"><i class="fa-solid fa-calendar-days"></i> Weekly Timetable</span>
				<h3 class="mb-2">Class Schedule</h3>
				<p class="text-muted mb-0">A printable weekly timetable template for the current academic term.</p>
			</div>
			<button type="button" class="btn btn-success rounded-pill portal-print-btn" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print Timetable</button>
		</div>
	</section>

	

	<!-- Weekly timetable table: horizontally scrolls on mobile and is optimized for printing. -->
	<section class="portal-card timetable-table-wrap" aria-label="Weekly class timetable">
		<div class="timetable-scroll-hint"><i class="fa-solid fa-arrows-left-right"></i> Swipe horizontally on tablets to view all days</div>
		<table class="table timetable-table align-middle">
			<thead>
				<tr>
					<th>Time</th>
					<th>Monday</th>
					<th>Tuesday</th>
					<th>Wednesday</th>
					<th>Thursday</th>
					<th>Friday</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($timeSlots as $slot): ?>
					<tr>
						<td class="timetable-time" data-label="Time"><i class="fa-regular fa-clock me-1"></i><?php echo $slot['time']; ?></td>
						<td data-label="Monday"><?php echo renderSubjectCard($slot['monday']); ?></td>
						<td data-label="Tuesday"><?php echo renderSubjectCard($slot['tuesday']); ?></td>
						<td data-label="Wednesday"><?php echo renderSubjectCard($slot['wednesday']); ?></td>
						<td data-label="Thursday"><?php echo renderSubjectCard($slot['thursday']); ?></td>
						<td data-label="Friday"><?php echo renderSubjectCard($slot['friday']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>

