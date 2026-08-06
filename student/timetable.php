<?php require_once('includes/header.php'); ?>

<?php

use App\Services\TimetableService;

$timetableService = new TimetableService();
$currentUser = sms_current_user();
$classInfo = $timetableService->classForStudentUser((int) $currentUser['id']);

$sessionId = $timetableService->currentSessionId();
$termId = $timetableService->currentTermId();
$workingDays = $timetableService->workingDays();

$entries = $classInfo ? $timetableService->grid([
	'session_id' => $sessionId,
	'term_id' => $termId,
	'class_id' => $classInfo['class_id'],
	'section_id' => $classInfo['section_id'],
]) : [];

$slotsMap = [];
foreach ($entries as $row) {
	$start = substr((string) $row['start_time'], 0, 5);
	$end = substr((string) $row['end_time'], 0, 5);
	$key = $start . '-' . $end;
	if (!isset($slotsMap[$key])) {
		$slotsMap[$key] = ['time' => $start . ' - ' . $end, 'start' => $start];
		foreach ($workingDays as $day) {
			$slotsMap[$key][strtolower($day)] = null;
		}
	}
	$teacherName = trim(($row['teacher_first_name'] ?? '') . ' ' . ($row['teacher_last_name'] ?? ''));
	$slotsMap[$key][strtolower((string) $row['day_name'])] = [$row['subject_name'], $teacherName ?: 'Unassigned', $row['venue_name'] ?? 'Not assigned'];
}

$timeSlots = array_values($slotsMap);
usort($timeSlots, static fn (array $a, array $b): int => strcmp($a['start'], $b['start']));

function renderSubjectCard($lesson) {
	if ($lesson === null) {
		return '<div class="subject-card text-muted"><strong>Free Period</strong></div>';
	}
	return '<div class="subject-card"><strong>' . htmlspecialchars((string) $lesson[0], ENT_QUOTES, 'UTF-8') . '</strong><span><i class="fa-solid fa-user-tie me-1"></i>' . htmlspecialchars((string) $lesson[1], ENT_QUOTES, 'UTF-8') . '</span><span><i class="fa-solid fa-location-dot me-1"></i>' . htmlspecialchars((string) $lesson[2], ENT_QUOTES, 'UTF-8') . '</span></div>';
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
					<?php foreach ($workingDays as $day): ?><th><?php echo sms_e($day); ?></th><?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($timeSlots as $slot): ?>
					<tr>
						<td class="timetable-time" data-label="Time"><i class="fa-regular fa-clock me-1"></i><?php echo sms_e($slot['time']); ?></td>
						<?php foreach ($workingDays as $day): ?><td data-label="<?php echo sms_e($day); ?>"><?php echo renderSubjectCard($slot[strtolower($day)]); ?></td><?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				<?php if (!$timeSlots): ?><tr><td colspan="<?php echo count($workingDays) + 1; ?>" class="text-center text-muted py-4">No timetable entries have been published for your class yet.</td></tr><?php endif; ?>
			</tbody>
		</table>
	</section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>

