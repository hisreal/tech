<?php require_once('includes/header.php'); ?>

<?php

use App\Core\Session;
use App\Services\ResultService;
use App\Services\TeacherService;

$resultService = new ResultService();
$teacherService = new TeacherService();
$currentUser = sms_current_user();
$teacherId = $teacherService->teacherIdForUser((int) $currentUser['id']);

$errors = Session::errors();

$sessionId = (int) ($_GET['session_id'] ?? $resultService->currentSessionId() ?? 0);
$termId = (int) ($_GET['term_id'] ?? $resultService->currentTermId() ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);
$sectionId = (int) ($_GET['section_id'] ?? 0);
$subjectId = (int) ($_GET['subject_id'] ?? 0);

$sessions = $resultService->sessionsForSelect();
$terms = $resultService->termsForSelect($sessionId ?: null);
$myClasses = $teacherId ? $resultService->classesForTeacher($teacherId) : [];
$mySections = $teacherId ? $resultService->sectionsForTeacher($teacherId, $classId ?: null) : [];
$mySubjects = $teacherId ? $resultService->subjectsForTeacher($teacherId) : [];

$batch = null;
$roster = [];
$scores = [];
$ownsSelection = $teacherId && $classId && $subjectId && $resultService->teacherOwnsClassSection($teacherId, $classId, $sectionId ?: null) && $resultService->teacherOwnsSubject($teacherId, $subjectId);

if ($ownsSelection && $sessionId && $termId) {
	$batch = $resultService->findOrCreateBatch($sessionId, $termId, $classId, $sectionId ?: null, $subjectId, $teacherId);
	$roster = $resultService->rosterForBatch($classId, $sectionId ?: null, $sessionId);
	$scores = $resultService->existingScores((int) $batch['id']);
}

$myBatches = $teacherId ? $resultService->listBatches(['teacher_id' => $teacherId], 1, 50)['data'] : [];

$totals = array_map(static fn (array $s): float => (float) ($s['total'] ?? 0), $scores);
$highestScore = $totals ? max($totals) : 0;
$lowestScore = $totals ? min($totals) : 0;
$classAverage = $totals ? round(array_sum($totals) / count($totals), 1) : 0;

function resultValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

function sms_rm_query(array $overrides = []): string
{
	return 'result-management.php?' . http_build_query(array_merge($_GET, $overrides));
}
?>

<style>
	/* Result management module: scoped green dashboard styling for score entry, summaries, and reports. */
	.result-page { --res-primary:#0f766e; --res-primary-dark:#115e59; --res-primary-soft:rgba(15,118,110,.1); --res-success:#16a34a; --res-success-soft:rgba(22,163,74,.12); --res-danger:#dc2626; --res-danger-soft:rgba(220,38,38,.1); --res-warning:#f59e0b; --res-warning-soft:rgba(245,158,11,.14); --res-blue:#2563eb; --res-blue-soft:rgba(37,99,235,.1); --res-ink:#10201d; --res-muted:#64748b; --res-border:rgba(15,118,110,.18); --res-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.result-page .result-hero,.result-page .result-card,.result-page .summary-card,.result-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--res-border); box-shadow:var(--res-shadow); }
	.result-page .result-hero { position:relative; overflow:hidden; padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.result-page .result-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--res-primary-soft); color:var(--res-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.result-page h3,.result-page h4,.result-page h5 { color:var(--res-ink); font-weight:900; }
	.result-page .result-card,.result-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.result-page .result-card { padding:24px; }
	.result-page .form-label { color:var(--res-ink); font-size:13px; font-weight:900; }
	.result-page .form-select,.result-page .form-control { min-height:48px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.result-page .form-select:focus,.result-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.result-page .score-input { min-width:72px; padding-left:10px; text-align:center; }
	.result-page .load-btn,.result-page .save-btn,.result-page .submit-btn { min-height:48px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--res-primary),var(--res-primary-dark)); color:#fff; font-weight:900; box-shadow:0 15px 32px rgba(15,118,110,.24); }
	.result-page .load-btn:hover,.result-page .save-btn:hover,.result-page .submit-btn:hover { color:#fff; transform:translateY(-2px); }
	.result-page .summary-card { height:100%; padding:18px; border-radius:20px; }
	.result-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--res-primary-soft); color:var(--res-primary); display:inline-flex; align-items:center; justify-content:center; }
	.result-page .summary-icon.success{background:var(--res-success-soft);color:var(--res-success)} .result-page .summary-icon.danger{background:var(--res-danger-soft);color:var(--res-danger)} .result-page .summary-icon.blue{background:var(--res-blue-soft);color:var(--res-blue)}
	.result-page .summary-card h4 { margin:10px 0 2px; font-weight:900; }
	.result-page .table-toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.result-page .table-scroll { max-height:560px; overflow:auto; }
	.result-page .result-table { min-width:900px; margin-bottom:0; }
	.result-page .result-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--res-primary),var(--res-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.result-page .result-table td { padding:11px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.result-page .status-badge { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; }
	.result-page .status-draft { background:var(--res-warning-soft); color:#b45309; }
	.result-page .status-submitted,.result-page .status-approved,.result-page .status-published { background:var(--res-success-soft); color:var(--res-success); }
	.result-page .status-locked { background:var(--res-danger-soft); color:var(--res-danger); }
	.result-page .action-row { display:flex; gap:10px; flex-wrap:wrap; }
	.result-page .history-table { min-width:880px; }
	@media (max-width:767.98px){ .result-page .result-hero,.result-page .result-card{padding:20px;border-radius:20px}.result-page .action-row,.result-page .action-row .btn{width:100%} }
</style>

<div class="result-page">
	<?php foreach (sms_flash() as $type => $messages): ?>
		<?php foreach ($messages as $message): ?>
			<div class="alert alert-<?php echo $type === 'error' ? 'danger' : resultValue($type); ?>" role="alert"><?php echo resultValue($message); ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>

	<section class="result-hero">
		<span class="result-kicker"><i class="fa-solid fa-chart-line"></i> Result Management</span>
		<h3 class="mt-3 mb-2">Enter, Calculate, and Submit Student Results</h3>
		<p class="text-muted mb-0">Load your assigned class and subject, enter CA and exam scores, review totals, and submit results for approval.</p>
	</section>

	<section class="result-card">
		<form method="get" class="row g-3 align-items-end">
			<div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="class_id" required><option value="">Select class</option><?php foreach ($myClasses as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo $classId === (int) $class['id'] ? 'selected' : ''; ?>><?php echo resultValue($class['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label">Section</label><select class="form-select" name="section_id"><option value="">All Sections</option><?php foreach ($mySections as $section): ?><option value="<?php echo (int) $section['id']; ?>" <?php echo $sectionId === (int) $section['id'] ? 'selected' : ''; ?>><?php echo resultValue($section['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-3"><label class="form-label">Subject</label><select class="form-select" name="subject_id" required><option value="">Select subject</option><?php foreach ($mySubjects as $subject): ?><option value="<?php echo (int) $subject['id']; ?>" <?php echo $subjectId === (int) $subject['id'] ? 'selected' : ''; ?>><?php echo resultValue($subject['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label">Session</label><select class="form-select" name="session_id"><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo resultValue($s['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-2"><label class="form-label">Term</label><select class="form-select" name="term_id"><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo resultValue($t['name']); ?></option><?php endforeach; ?></select></div>
			<div class="col-md-12"><button type="submit" class="btn load-btn"><i class="fa-solid fa-users-viewfinder me-2"></i>Load Students</button></div>
		</form>
		<?php if (!$myClasses): ?><p class="text-muted mt-3 mb-0">You have no assigned classes yet. Contact the administrator.</p><?php endif; ?>
	</section>

	<section class="row g-3 mb-4" aria-label="Result summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4><?php echo count($roster); ?></h4><p class="text-muted mb-0">Total Students</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-arrow-trend-up"></i></span><h4><?php echo resultValue($highestScore); ?></h4><p class="text-muted mb-0">Highest Score</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-arrow-trend-down"></i></span><h4><?php echo resultValue($lowestScore); ?></h4><p class="text-muted mb-0">Lowest Score</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-percent"></i></span><h4><?php echo resultValue($classAverage); ?>%</h4><p class="text-muted mb-0">Class Average</p></div></div>
	</section>

	<?php if ($classId && $subjectId && !$ownsSelection): ?>
		<div class="alert alert-danger">You are not assigned to that class and subject.</div>
	<?php elseif ($batch && $roster): ?>
		<section class="table-card mb-4">
			<div class="table-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3">
				<div><h5 class="mb-1">Result Entry</h5><p class="text-muted mb-0">CA1 + CA2 + CA3 + Exam + Practical = Total. Grade is applied automatically on save.</p></div>
				<span class="status-badge status-<?php echo resultValue($batch['status']); ?>"><i class="fa-solid fa-circle"></i> <?php echo resultValue(ucfirst($batch['status'])); ?></span>
			</div>
			<?php if ($batch['status'] === 'locked'): ?>
				<div class="alert alert-warning m-3">This result batch is locked and cannot be edited.</div>
			<?php endif; ?>
			<form method="post" action="score-save.php" id="scoreForm">
				<input type="hidden" name="_token" value="<?php echo resultValue(sms_csrf_token()); ?>">
				<input type="hidden" name="batch_id" value="<?php echo (int) $batch['id']; ?>">
				<input type="hidden" name="redirect_query" value="<?php echo resultValue(http_build_query($_GET)); ?>">
				<div class="table-scroll"><table class="table result-table align-middle" id="resultTable"><thead><tr><th>Reg. No.</th><th>Student</th><th>CA1 (20)</th><th>CA2 (20)</th><th>CA3 (20)</th><th>Exam (40)</th><th>Practical</th><th>Total</th></tr></thead><tbody>
					<?php foreach ($roster as $student): ?>
						<?php $existing = $scores[$student['id']] ?? null; ?>
						<tr data-row>
							<td><?php echo resultValue($student['registration_no']); ?></td>
							<td><?php echo resultValue($student['first_name'] . ' ' . $student['last_name']); ?></td>
							<td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][ca1]" value="<?php echo resultValue((string) ($existing['ca1'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
							<td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][ca2]" value="<?php echo resultValue((string) ($existing['ca2'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
							<td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][ca3]" value="<?php echo resultValue((string) ($existing['ca3'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
							<td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][exam]" value="<?php echo resultValue((string) ($existing['exam'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
							<td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][practical]" value="<?php echo resultValue((string) ($existing['practical'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
							<td class="row-total fw-bold"><?php echo resultValue((string) ($existing['total'] ?? '0.00')); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody></table></div>
				<?php if ($batch['status'] !== 'locked'): ?>
					<div class="d-flex justify-content-end gap-2 mt-3 p-3"><button class="btn save-btn" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Save Scores</button></div>
				<?php endif; ?>
			</form>
			<?php if ($batch['status'] === 'draft'): ?>
				<form method="post" action="score-submit.php" class="d-flex justify-content-end p-3 pt-0">
					<input type="hidden" name="_token" value="<?php echo resultValue(sms_csrf_token()); ?>">
					<input type="hidden" name="batch_id" value="<?php echo (int) $batch['id']; ?>">
					<input type="hidden" name="redirect_query" value="<?php echo resultValue(http_build_query($_GET)); ?>">
					<button class="btn submit-btn" type="submit"><i class="fa-solid fa-paper-plane me-2"></i>Submit for Approval</button>
				</form>
			<?php endif; ?>
		</section>
	<?php elseif ($classId && $subjectId): ?>
		<section class="result-card"><p class="text-muted fw-bold mb-0">No active students found for this class/section in the selected session.</p></section>
	<?php endif; ?>

	<section class="result-card">
		<h4 class="mb-1">Previous Results</h4>
		<p class="text-muted mb-3">All result batches you have created or been assigned to.</p>
		<div class="table-scroll"><table class="table result-table history-table align-middle"><thead><tr><th>Session</th><th>Term</th><th>Class</th><th>Subject</th><th>Students</th><th>Average</th><th>Status</th><th>Action</th></tr></thead><tbody>
			<?php foreach ($myBatches as $item): ?>
				<tr>
					<td><?php echo resultValue($item['session_name']); ?></td>
					<td><?php echo resultValue($item['term_name']); ?></td>
					<td><?php echo resultValue($item['class_name'] . ($item['section_name'] ? ' - ' . $item['section_name'] : '')); ?></td>
					<td><?php echo resultValue($item['subject_name']); ?></td>
					<td><?php echo (int) $item['student_count']; ?></td>
					<td><?php echo $item['average_score'] !== null ? resultValue(number_format((float) $item['average_score'], 1)) : '-'; ?></td>
					<td><span class="status-badge status-<?php echo resultValue($item['status']); ?>"><?php echo resultValue(ucfirst($item['status'])); ?></span></td>
					<td><a class="btn btn-sm btn-outline-success" href="<?php echo resultValue(sms_rm_query(['session_id' => $item['session_id'], 'term_id' => $item['term_id'], 'class_id' => $item['class_id'], 'section_id' => $item['section_id'] ?? '', 'subject_id' => $item['subject_id']])); ?>"><i class="fa-solid fa-eye"></i> View / Edit</a></td>
				</tr>
			<?php endforeach; ?>
			<?php if (!$myBatches): ?><tr><td colspan="8" class="text-center text-muted py-4">No result batches yet.</td></tr><?php endif; ?>
		</tbody></table></div>
	</section>
</div>

</div>
</div>

<script>
(function () {
	document.querySelectorAll('#resultTable tbody tr[data-row]').forEach(function (row) {
		var inputs = row.querySelectorAll('.score-input');
		var totalCell = row.querySelector('.row-total');
		function recalc() {
			var sum = 0;
			inputs.forEach(function (input) { sum += parseFloat(input.value || '0') || 0; });
			totalCell.textContent = sum.toFixed(2);
		}
		inputs.forEach(function (input) { input.addEventListener('input', recalc); });
	});
})();
</script>

<?php require_once('includes/footer.php'); ?>
