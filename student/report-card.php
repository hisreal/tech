<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('student');

use App\Models\SettingsModel;
use App\Services\ResultService;

$resultService = new ResultService();
$currentUser = sms_current_user();
$studentId = $resultService->studentIdForUser((int) $currentUser['id']);

$sessions = $resultService->sessionsForSelect();
$terms = $resultService->termsForSelect();

$sessionId = (int) ($_GET['session_id'] ?? $resultService->currentSessionId() ?? 0);
$termId = (int) ($_GET['term_id'] ?? $resultService->currentTermId() ?? 0);

$card = ($studentId && $sessionId && $termId) ? $resultService->reportCard($studentId, $sessionId, $termId) : null;
$hasResult = $card && ($card['subjects'] || $card['summary']);
$grades = $resultService->listGrades();

$settings = (new SettingsModel())->all();
$schoolName = $settings['school.name']['value'] ?? 'School Management System';
$schoolAddress = $settings['school.address']['value'] ?? '';
$schoolPhone = $settings['school.phone']['value'] ?? '';
$schoolType = $settings['school.type']['value'] ?? '';
$schoolMotto = $settings['school.motto']['value'] ?? '';
$logoPath = $settings['school.logo']['value'] ?? '';
$logoUrl = $logoPath ? '../' . ltrim((string) $logoPath, '/') : '../assets/img/logo/school-logo.png';

function rcValue($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if ($hasResult) {
    $student = $card['student'];
    $enrollment = $card['enrollment'] ?? [];
    $totalScore = array_sum(array_column($card['subjects'], 'total'));
    $average = (float) ($card['summary']['average_score'] ?? 0);
    $grade = $card['summary']['grade'] ?? '-';
    $promotionStatus = $grade === 'F' ? 'Not Promoted' : 'Promoted';
    $present = (int) ($card['summary']['attendance_present'] ?? 0);
    $absent = (int) ($card['summary']['attendance_absent'] ?? 0);
    $totalDays = $present + $absent;
    $attendanceRate = $totalDays > 0 ? round(($present / $totalDays) * 100, 1) : 0;
    $fullName = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
    $photoUrl = !empty($student['passport_path']) ? '../' . ltrim((string) $student['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Student Report Card<?php echo $hasResult ? ' - ' . rcValue($fullName) : ''; ?></title>
	<link rel="stylesheet" href="report-card.css">
	<style>
		.report-flash-wrap { max-width: 560px; margin: 60px auto; font-family: Arial, Helvetica, sans-serif; padding: 0 16px; }
		.report-flash { display: flex; align-items: flex-start; gap: 12px; padding: 16px 18px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 14px; margin-bottom: 20px; }
		.report-flash strong { display: block; margin-bottom: 2px; }
		.report-flash-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
		.report-flash-form select { padding: 9px 10px; border-radius: 8px; border: 1px solid #cbd5e1; }
		.report-flash-form button { padding: 9px 18px; border-radius: 8px; border: 0; background: #173f5f; color: #fff; font-weight: 700; cursor: pointer; }
	</style>
</head>
<body>
	<?php if (!$studentId): ?>
		<p style="text-align:center;margin-top:60px;">Student profile not found.</p>
	<?php elseif (!$hasResult): ?>
		<div class="report-flash-wrap">
			<div class="report-flash">
				<i class="fa-solid fa-circle-exclamation"></i>
				<div>
					<strong>No result found</strong>
					No published result was found for the selected academic session and term. Try a different session or term below.
				</div>
			</div>
			<form method="get" class="report-flash-form">
				<select name="session_id"><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo rcValue($s['name']); ?></option><?php endforeach; ?></select>
				<select name="term_id"><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo rcValue($t['name']); ?></option><?php endforeach; ?></select>
				<button type="submit">View</button>
			</form>
		</div>
	<?php else: ?>
	<!-- Report card shell: standalone A4-friendly document for screen preview and printing. -->
	<main class="report-page">
		<!-- Official school letterhead, populated from School Settings. -->
		<header class="school-letterhead" aria-label="<?php echo rcValue($schoolName); ?> letterhead">
			<table class="letterhead-table" aria-label="School report letterhead">
				<colgroup>
					<col class="letterhead-logo-col">
					<col class="letterhead-title-col">
					<col class="letterhead-passport-col">
				</colgroup>
				<tr>
					<td class="letterhead-logo-cell">
						<div class="bfss-logo">
							<img class="letterhead-logo" src="<?php echo rcValue($logoUrl); ?>" alt="School Logo">
						</div>
					</td>
					<td class="letterhead-title-cell">
						<div class="school-heading">
							<h1><?php echo rcValue(strtoupper($schoolName)); ?></h1>
							<?php if ($schoolType): ?><p style="font-size: 16px;" class="school-levels"><?php echo rcValue(strtoupper($schoolType)); ?></p><?php endif; ?>
							<?php if ($schoolMotto): ?><p class="motto">Motto: &quot;<?php echo rcValue($schoolMotto); ?>&quot;</p><?php endif; ?>
							<p class="address"><?php echo rcValue($schoolAddress); ?></p>
							<?php if ($schoolPhone): ?><p class="phone"><strong>GSM No:</strong> <?php echo rcValue($schoolPhone); ?></p><?php endif; ?>
						</div>
					</td>
					<td class="letterhead-passport-cell">
						<img class="student-passport" src="<?php echo rcValue($photoUrl); ?>" alt="Student Passport">
					</td>
				</tr>
			</table>
		</header>

		<center><h2 style="font-weight: 800; text-transform: uppercase;"><?php echo rcValue($card['term_name']); ?> Performance Report</h2></center>

		<!-- Student information -->
		<section class="student-profile-section " aria-label="Student information">
			<table class="student-profile-table">
				<tbody>
					<tr>
						<th>Student Name</th>
						<td><?php echo rcValue($fullName); ?></td>
						<th>Student ID</th>
						<td><?php echo rcValue($student['registration_no']); ?></td>
					</tr>
					<tr>
						<th>Class</th>
						<td><?php echo rcValue(($enrollment['class_name'] ?? '-') . (!empty($enrollment['section_name']) ? ' - ' . $enrollment['section_name'] : '')); ?></td>
						<th>Gender</th>
						<td><?php echo rcValue(ucfirst((string) ($student['gender'] ?? ''))); ?></td>
					</tr>
					<tr>
						<th>Term</th>
						<td><?php echo rcValue($card['term_name']); ?></td>
						<th>Academic Session</th>
						<td><?php echo rcValue($card['session_name']); ?></td>
					</tr>
					<tr>
						<th>Section</th>
						<td><?php echo rcValue($enrollment['section_name'] ?? 'Not assigned'); ?></td>
						<th>Status</th>
						<td><?php echo rcValue(ucfirst((string) ($student['status'] ?? ''))); ?></td>
					</tr>
				</tbody>
			</table>
		</section>

		<!-- Academic performance -->
		<section class="section-block">
			<div class="section-title">
				<h3>Academic Performance</h3>
				<span>Cognitive Domain</span>
			</div>
			<div class="table-wrap">
				<table class="performance-table">
					<thead>
						<tr>
							<th>Subject</th>
							<th>1st CA</th>
							<th>2nd CA</th>
							<th>3rd CA</th>
							<th>Exam</th>
							<th>Total</th>
							<th>Grade</th>
							<th>Position / Remark</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$card['subjects']): ?>
							<tr><td colspan="8" style="text-align:center;">No published subject results found for this term yet.</td></tr>
						<?php endif; ?>
						<?php foreach ($card['subjects'] as $subject): ?>
							<tr>
								<td><?php echo rcValue($subject['subject_name']); ?></td>
								<td><?php echo rcValue($subject['ca1']); ?></td>
								<td><?php echo rcValue($subject['ca2']); ?></td>
								<td><?php echo rcValue($subject['ca3']); ?></td>
								<td><?php echo rcValue($subject['exam']); ?></td>
								<td><?php echo rcValue($subject['total']); ?></td>
								<td><?php echo rcValue($subject['grade'] ?? '-'); ?></td>
								<td><?php echo rcValue($subject['remark'] ?? '-'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>

		<!-- Summary panels: attendance, scores, grading, and promotion information. -->
		<section class="summary-grid">
			<div class="summary-card">
				<h3>Term Performance Summary</h3>
				<div class="summary-line"><span>Total Score</span><strong><?php echo rcValue($totalScore); ?></strong></div>
				<div class="summary-line"><span>Average Score</span><strong><?php echo rcValue($average); ?>%</strong></div>
				<div class="summary-line"><span>Overall Grade</span><strong><?php echo rcValue($grade); ?></strong></div>
				<div class="summary-line highlight"><span>Promotion Status</span><strong><?php echo rcValue($promotionStatus); ?></strong></div>
			</div>
			<div class="summary-card">
				<h3>Attendance Summary</h3>
				<div class="summary-line"><span>Times School Opened</span><strong><?php echo rcValue($totalDays); ?></strong></div>
				<div class="summary-line"><span>Times Present</span><strong><?php echo rcValue($present); ?></strong></div>
				<div class="summary-line"><span>Times Absent</span><strong><?php echo rcValue($absent); ?></strong></div>
				<div class="summary-line"><span>Attendance Rate</span><strong><?php echo rcValue($attendanceRate); ?>%</strong></div>
			</div>
			<div class="summary-card grade-scale">
				<h3>Grade Scale</h3>
				<?php foreach ($grades as $gradeRow): ?>
					<p><strong><?php echo rcValue($gradeRow['grade']); ?></strong> <?php echo rcValue($gradeRow['min_score']); ?> - <?php echo rcValue($gradeRow['max_score']); ?> <?php echo rcValue($gradeRow['remark']); ?></p>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Comments: personalized teacher and principal remarks. -->
		<section class="comments-grid">
			<div class="comment-box">
				<h3>Teacher's Comment</h3>
				<p><?php echo rcValue($card['teacher_remark'] ?: 'No remark recorded for this term yet.'); ?></p>
			</div>
			<div class="comment-box">
				<h3>Principal's Comment</h3>
				<p><?php echo rcValue($card['principal_remark'] ?: 'No remark recorded for this term yet.'); ?></p>
			</div>
		</section>

		<!-- Signature area: blank lines are reserved for manual or generated signatures. -->
		<footer class="signature-section">
			<div class="signature-box"><span></span><strong>Class Teacher Signature</strong></div>
			<div class="signature-box"><span></span><strong>Principal Signature</strong></div>
		</footer>
	</main>
	<?php endif; ?>
</body>
</html>
