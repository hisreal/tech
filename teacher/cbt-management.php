<?php require_once('includes/header.php'); ?>
<?php

use App\Core\Session;
use App\Services\CBTService;

$cbtService = new CBTService();
$currentUser = sms_current_user();
$teacherId = $cbtService->teacherIdForUser((int) $currentUser['id']);

$errors = Session::errors();
$old = Session::oldAll();

$sessions = $cbtService->sessionsForSelect();
$terms = $cbtService->termsForSelect();
$currentSessionId = $cbtService->currentSessionId();
$currentTermId = $cbtService->currentTermId();
$classes = $teacherId ? $cbtService->classesForTeacher($teacherId) : [];
$subjects = $teacherId ? $cbtService->subjectsForSelect(null, $teacherId) : [];

$exams = $teacherId ? $cbtService->listExams([], 1, 200, $teacherId)['data'] : [];

$examId = (int) ($_GET['exam_id'] ?? 0);
$exam = null;
$questions = [];
if ($examId && $teacherId) {
    $exam = $cbtService->findExam($examId);
    if ($exam && (int) $exam['teacher_id'] !== $teacherId) {
        $exam = null;
    }
    if ($exam) {
        $questions = $cbtService->listQuestions($examId);
    }
}
$locked = $exam && in_array($exam['status'], ['active', 'completed'], true);

function cbtValue($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
	/* CBT management module: scoped premium styles for setup, question builder, preview, and question bank. */
	.cbt-page { --cbt-primary: #0f766e; --cbt-primary-dark: #115e59; --cbt-primary-soft: rgba(15, 118, 110, .1); --cbt-success: #16a34a; --cbt-success-soft: rgba(22, 163, 74, .12); --cbt-danger: #dc2626; --cbt-danger-soft: rgba(220, 38, 38, .1); --cbt-warning: #f59e0b; --cbt-warning-soft: rgba(245, 158, 11, .14); --cbt-blue: #2563eb; --cbt-blue-soft: rgba(37, 99, 235, .1); --cbt-ink: #10201d; --cbt-muted: #64748b; --cbt-border: rgba(15, 118, 110, .18); --cbt-shadow: 0 22px 60px rgba(15, 23, 42, .09); padding-bottom: 34px; }
	.cbt-page .cbt-hero, .cbt-page .cbt-card, .cbt-page .question-card, .cbt-page .preview-question { background: rgba(255, 255, 255, .98); border: 1px solid var(--cbt-border); box-shadow: var(--cbt-shadow); }
	.cbt-page .cbt-hero { position: relative; overflow: hidden; padding: 28px; border-radius: 26px; margin-bottom: 22px; background: linear-gradient(135deg, rgba(240, 253, 244, .98), rgba(255, 255, 255, .98)); }
	.cbt-page .cbt-kicker, .cbt-page .field-icon, .cbt-page .status-badge { display: inline-flex; align-items: center; justify-content: center; }
	.cbt-page .cbt-kicker { gap: 8px; padding: 8px 12px; border-radius: 999px; background: var(--cbt-primary-soft); color: var(--cbt-primary-dark); font-size: 12px; font-weight: 900; text-transform: uppercase; }
	.cbt-page h3, .cbt-page h4, .cbt-page h5 { color: var(--cbt-ink); font-weight: 900; }
	.cbt-page .cbt-card { padding: 24px; border-radius: 24px; margin-bottom: 22px; }
	.cbt-page .form-label { font-size: 13px; font-weight: 900; color: var(--cbt-ink); }
	.cbt-page .form-control, .cbt-page .form-select, .cbt-page textarea { border: 1px solid rgba(148, 163, 184, .32); border-radius: 15px; font-weight: 750; box-shadow: none; min-height: 48px; }
	.cbt-page textarea.form-control { padding: 14px; min-height: 104px; }
	.cbt-page .question-card { padding: 20px; border-radius: 22px; margin-bottom: 16px; }
	.cbt-page .option-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
	.cbt-page .action-bar { display: flex; gap: 10px; flex-wrap: wrap; }
	.cbt-page .btn-cbt-primary, .cbt-page .btn-cbt-secondary { min-height: 46px; border-radius: 14px; font-weight: 900; }
	.cbt-page .btn-cbt-primary { border: 0; background: linear-gradient(135deg, var(--cbt-primary), var(--cbt-primary-dark)); color: #fff; box-shadow: 0 15px 32px rgba(15, 118, 110, .24); }
	.cbt-page .btn-cbt-primary:hover { color: #fff; }
	.cbt-page .bank-table-wrap { overflow-x: auto; border-radius: 18px; border: 1px solid rgba(148, 163, 184, .2); }
	.cbt-page .bank-table { min-width: 900px; margin-bottom: 0; }
	.cbt-page .bank-table thead th { padding: 14px 12px; background: linear-gradient(135deg, var(--cbt-primary), var(--cbt-primary-dark)); color: #fff; border: 0; font-size: 12px; font-weight: 900; text-transform: uppercase; }
	.cbt-page .bank-table td { padding: 13px 12px; vertical-align: middle; border-color: rgba(148, 163, 184, .2); font-weight: 750; }
	.cbt-page .status-badge { gap: 6px; padding: 7px 10px; border-radius: 999px; font-size: 12px; font-weight: 900; }
	.cbt-page .status-published, .cbt-page .status-active { background: var(--cbt-success-soft); color: var(--cbt-success); }
	.cbt-page .status-draft, .cbt-page .status-inactive { background: var(--cbt-warning-soft); color: #b45309; }
	.cbt-page .status-completed, .cbt-page .status-archived { background: var(--cbt-blue-soft); color: var(--cbt-blue); }
	.cbt-page .bank-actions { display: flex; gap: 7px; flex-wrap: wrap; }
	@media (max-width: 767.98px) { .cbt-page .cbt-hero, .cbt-page .cbt-card, .cbt-page .question-card { padding: 20px; border-radius: 20px; } .cbt-page .option-grid { grid-template-columns: 1fr; } }
</style>

<div class="cbt-page">
	<section class="cbt-hero">
		<span class="cbt-kicker"><i class="fa-solid fa-laptop-code"></i> CBT Question Management</span>
		<h3 class="mt-3 mb-2">Create, Preview, and Publish CBT Questions</h3>
		<p class="text-muted mb-0">Build computer-based tests for your assigned subjects and classes, manage drafts, and prepare assessments for students.</p>
	</section>

	<?php foreach (sms_flash() as $type => $messages): ?>
		<?php foreach ($messages as $message): ?>
			<div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>" role="alert"><?php echo cbtValue($message); ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>

	<?php if (!$teacherId): ?>
		<section class="cbt-card"><p class="text-muted fw-bold mb-0">Your teacher profile is not linked to a staff record yet. Contact the administrator.</p></section>
	<?php else: ?>

	<!-- CBT setup form: creates a new exam scoped to this teacher's assigned subjects/classes. -->
	<section class="cbt-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div><h4 class="mb-1">CBT Setup Information</h4><p class="text-muted mb-0">Select the academic context and define the test details.</p></div>
		</div>
		<form method="post" action="cbt-exam-store.php">
			<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
			<div class="row g-3">
				<div class="col-md-4"><label class="form-label">Subject</label><select class="form-select" name="subject_id" required><option value="">Select subject</option><?php foreach ($subjects as $s): ?><option value="<?php echo (int) $s['id']; ?>"><?php echo cbtValue($s['name']); ?></option><?php endforeach; ?></select></div>
				<div class="col-md-4"><label class="form-label">Class</label><select class="form-select" name="class_id" required><option value="">Select class</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>"><?php echo cbtValue($c['name']); ?></option><?php endforeach; ?></select></div>
				<div class="col-md-4"><label class="form-label">Academic Session</label><select class="form-select" name="session_id" required><option value="">Select session</option><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $currentSessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo cbtValue($s['name']); ?></option><?php endforeach; ?></select></div>
				<div class="col-md-4"><label class="form-label">Term</label><select class="form-select" name="term_id" required><option value="">Select term</option><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $currentTermId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo cbtValue($t['name']); ?></option><?php endforeach; ?></select></div>
				<div class="col-md-4"><label class="form-label">CBT Title</label><input type="text" class="form-control" name="title" placeholder="First Term Mathematics Test" required></div>
				<div class="col-md-2"><label class="form-label">Duration (mins)</label><input type="number" min="1" class="form-control" name="duration_minutes" placeholder="30" required></div>
				<div class="col-md-2"><label class="form-label">Pass Mark (%)</label><input type="number" min="0" max="100" class="form-control" name="pass_mark" value="50"></div>
				<div class="col-12"><label class="form-label">Instructions / Description</label><textarea class="form-control" name="instructions" placeholder="Answer all questions. Select the best option for each question."></textarea></div>
				<div class="col-12"><button type="submit" class="btn btn-cbt-primary"><i class="fa-solid fa-plus me-2"></i>Create Exam</button></div>
			</div>
		</form>
	</section>

	<?php if ($exam): ?>
		<!-- Question builder: scoped to the exam selected from the question bank below. -->
		<section class="cbt-card">
			<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
				<div><h4 class="mb-1">Question Creation &mdash; <?php echo cbtValue($exam['title']); ?></h4><p class="text-muted mb-0"><?php echo cbtValue($exam['subject_name']); ?> &middot; <?php echo cbtValue($exam['class_name']); ?> &middot; <?php echo (int) $exam['question_count']; ?> question(s) added</p></div>
			</div>
			<?php if ($locked): ?>
				<div class="alert alert-warning">This exam is <?php echo cbtValue($exam['status']); ?> and its questions can no longer be changed.</div>
			<?php else: ?>
			<form method="post" action="cbt-question-store.php">
				<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
				<input type="hidden" name="exam_id" value="<?php echo (int) $examId; ?>">
				<div class="mb-3"><label class="form-label">Question Text</label><textarea class="form-control" name="question_text" required></textarea></div>
				<div class="option-grid mb-3">
					<div><label class="form-label">Option A</label><input class="form-control" name="option_a" required></div>
					<div><label class="form-label">Option B</label><input class="form-control" name="option_b" required></div>
					<div><label class="form-label">Option C</label><input class="form-control" name="option_c" required></div>
					<div><label class="form-label">Option D</label><input class="form-control" name="option_d" required></div>
				</div>
				<div class="row g-3 align-items-end">
					<div class="col-md-3"><label class="form-label">Correct Option</label><select class="form-select" name="correct_option" required><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option></select></div>
					<div class="col-md-3"><label class="form-label">Mark</label><input type="number" step="0.5" min="0.5" class="form-control" name="mark" value="1"></div>
					<div class="col-md-3"><button type="submit" class="btn btn-cbt-primary"><i class="fa-solid fa-plus me-2"></i>Add Question</button></div>
				</div>
			</form>
			<?php endif; ?>
		</section>

		<section class="cbt-card">
			<h4 class="mb-3">Questions Added</h4>
			<?php foreach ($questions as $i => $q): ?>
				<article class="question-card">
					<div class="d-flex justify-content-between align-items-start gap-2 mb-2">
						<h5 class="mb-0">Question <?php echo $i + 1; ?></h5>
						<?php if (!$locked): ?>
						<form method="post" action="cbt-question-delete.php" onsubmit="return confirm('Delete this question?');">
							<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
							<input type="hidden" name="id" value="<?php echo (int) $q['id']; ?>">
							<input type="hidden" name="exam_id" value="<?php echo (int) $examId; ?>">
							<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
						</form>
						<?php endif; ?>
					</div>
					<p><?php echo cbtValue($q['question_text']); ?></p>
					<div class="option-grid">
						<div class="option-box p-2 <?php echo $q['correct_option'] === 'A' ? 'text-success fw-bold' : ''; ?>">A. <?php echo cbtValue($q['option_a']); ?></div>
						<div class="option-box p-2 <?php echo $q['correct_option'] === 'B' ? 'text-success fw-bold' : ''; ?>">B. <?php echo cbtValue($q['option_b']); ?></div>
						<div class="option-box p-2 <?php echo $q['correct_option'] === 'C' ? 'text-success fw-bold' : ''; ?>">C. <?php echo cbtValue($q['option_c']); ?></div>
						<div class="option-box p-2 <?php echo $q['correct_option'] === 'D' ? 'text-success fw-bold' : ''; ?>">D. <?php echo cbtValue($q['option_d']); ?></div>
					</div>
					<p class="mb-0 mt-2 text-muted fw-bold">Correct Answer: <?php echo cbtValue($q['correct_option']); ?> &middot; Mark: <?php echo cbtValue($q['mark']); ?></p>
				</article>
			<?php endforeach; ?>
			<?php if (!$questions): ?><p class="text-muted fw-bold">No questions added yet.</p><?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- Question bank: this teacher's own CBT exams with real publish/activate/archive/delete actions. -->
	<section class="cbt-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div><h4 class="mb-1">CBT Question Bank</h4><p class="text-muted mb-0">Manage your saved CBT tests and publishing status.</p></div>
		</div>
		<div class="bank-table-wrap">
			<table class="table bank-table align-middle">
				<thead><tr><th>CBT Title</th><th>Subject</th><th>Class</th><th>Questions</th><th>Status</th><th>Action</th></tr></thead>
				<tbody>
				<?php foreach ($exams as $item): ?>
					<tr>
						<td><?php echo cbtValue($item['title']); ?></td>
						<td><?php echo cbtValue($item['subject_name']); ?></td>
						<td><?php echo cbtValue($item['class_name']); ?></td>
						<td><?php echo (int) $item['question_count']; ?></td>
						<td><span class="status-badge status-<?php echo cbtValue($item['status']); ?>"><?php echo cbtValue(ucfirst($item['status'])); ?></span></td>
						<td>
							<div class="bank-actions">
								<a class="btn btn-sm btn-outline-primary" href="cbt-management.php?exam_id=<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-pen"></i> Manage</a>
								<?php if ($item['status'] === 'draft'): ?>
								<form method="post" action="cbt-exam-status.php" style="display:inline">
									<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
									<input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
									<input type="hidden" name="status" value="published">
									<button class="btn btn-sm btn-outline-success" type="submit"><i class="fa-solid fa-bullhorn"></i> Publish</button>
								</form>
								<?php endif; ?>
								<?php if (in_array($item['status'], ['published', 'inactive'], true)): ?>
								<form method="post" action="cbt-exam-status.php" style="display:inline">
									<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
									<input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
									<input type="hidden" name="status" value="active">
									<button class="btn btn-sm btn-outline-success" type="submit"><i class="fa-solid fa-toggle-on"></i> Activate</button>
								</form>
								<?php endif; ?>
								<?php if ($item['status'] === 'active'): ?>
								<form method="post" action="cbt-exam-status.php" style="display:inline">
									<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
									<input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
									<input type="hidden" name="status" value="inactive">
									<button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fa-solid fa-toggle-off"></i> Deactivate</button>
								</form>
								<?php endif; ?>
								<?php if ((int) $item['attempt_count'] === 0): ?>
								<form method="post" action="cbt-exam-delete.php" onsubmit="return confirm('Delete this exam and its questions?');" style="display:inline">
									<input type="hidden" name="_token" value="<?php echo cbtValue(sms_csrf_token()); ?>">
									<input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
									<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
								</form>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if (!$exams): ?><tr><td colspan="6" class="text-center text-muted py-4">No CBT exams created yet.</td></tr><?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
	<?php endif; ?>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
