<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('student');

use App\Services\CBTService;

$cbtService = new CBTService();
$currentUser = sms_current_user();
$studentId = $cbtService->studentIdForUser((int) $currentUser['id']);
$examId = (int) ($_GET['exam_id'] ?? 0);

if (!$studentId || !$examId) {
    header('Location: quiz.php');
    exit;
}

$latest = $cbtService->latestAttempt($examId, $studentId);
$attempt = null;

if ($latest && $latest['status'] !== 'in_progress') {
    // Already submitted (or auto-submitted) - show the result instead of trying to start a new attempt.
    $attempt = $cbtService->attemptWithExam((int) $latest['id']);
} else {
    $start = $cbtService->findOrStartAttempt($examId, $studentId);
    if ($start['success']) {
        $attempt = $cbtService->attemptWithExam((int) $start['attempt']['id']);
    } elseif (($start['reason'] ?? null) === 'max_attempts' && $latest) {
        // No attempts left - fall back to showing their last submitted result rather than bouncing them away.
        $attempt = $cbtService->attemptWithExam((int) $latest['id']);
    } else {
        sms_flash_set('error', $start['message']);
        header('Location: quiz.php');
        exit;
    }
}

if ($attempt['status'] === 'in_progress' && $cbtService->isAttemptExpired($attempt)) {
    $cbtService->submitAttempt((int) $attempt['id'], true);
    $attempt = $cbtService->attemptWithExam((int) $attempt['id']);
}

$result = $attempt['status'] !== 'in_progress' ? $cbtService->attemptResult((int) $attempt['id']) : null;
$passed = $result && (float) $result['percentage'] >= (float) $result['pass_mark'];

$questions = [];
$currentQuestion = null;
$qIndex = 0;
$answeredCount = 0;
if ($result === null) {
    $questions = $cbtService->questionsForAttempt((int) $attempt['id'], $attempt);
    $qIndex = max(0, min((int) ($_GET['q'] ?? 0), count($questions) - 1));
    $currentQuestion = $questions[$qIndex] ?? null;
    $answeredCount = count(array_filter($questions, static fn ($q) => $q['selected_option'] !== null));
}
$deadlineMs = $cbtService->attemptDeadline($attempt) * 1000;

require_once('includes/header.php');
?>

<div class="student-exam-module">
	<section class="exam-hero">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div>
				<span class="exam-kicker mb-3"><i class="fa-solid fa-shield-halved"></i> Secure Quiz Workplace</span>
				<h3 class="mb-2" id="quizTitle"><?php echo sms_e($attempt['title']); ?></h3>
				<p class="text-muted mb-0">Read carefully, choose the best answer, and submit before the timer ends.</p>
			</div>
			<a href="quiz.php" class="btn btn-light rounded-pill d-inline-flex align-items-center"><i class="fa-solid fa-arrow-left me-2"></i>Quiz Dashboard</a>
		</div>
	</section>

	<?php foreach (sms_flash() as $type => $messages): ?>
		<?php foreach ($messages as $message): ?>
			<div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>" role="alert"><?php echo sms_e($message); ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>

	<?php if ($result === null && $currentQuestion): ?>
		<div class="exam-workplace-wrap">
			<div class="row g-4 w-100 justify-content-center" id="quizWorkspace">
				<div class="col-xl-8 col-lg-10">
					<section class="exam-workplace">
						<form method="post" action="quiz-answer.php" id="answerForm">
							<input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
							<input type="hidden" name="attempt_id" value="<?php echo (int) $attempt['id']; ?>">
							<input type="hidden" name="exam_id" value="<?php echo (int) $examId; ?>">
							<input type="hidden" name="question_id" value="<?php echo (int) $currentQuestion['id']; ?>">
							<input type="hidden" name="current_q" value="<?php echo (int) $qIndex; ?>">

							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div>
									<span class="exam-kicker mb-2">Question <?php echo $qIndex + 1; ?> of <?php echo count($questions); ?></span>
									<h4 class="mb-0"><?php echo sms_e($currentQuestion['question_text']); ?></h4>
								</div>
								<div class="exam-timer" id="timerCard"><i class="fa-solid fa-clock me-2"></i><strong id="quizTimer">--:--</strong></div>
							</div>

							<div class="mb-4">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<span class="fw-semibold">Progress</span>
									<span class="fw-semibold text-success"><?php echo $answeredCount; ?>/<?php echo count($questions); ?> answered</span>
								</div>
								<div class="exam-progress-track" aria-label="Quiz completion progress">
									<div class="exam-progress-bar" style="width:<?php echo round((($qIndex + 1) / max(1, count($questions))) * 100); ?>%"></div>
								</div>
							</div>

							<div class="d-grid gap-3">
								<?php foreach ($currentQuestion['display_options'] as $option): ?>
									<label class="exam-answer-option <?php echo $currentQuestion['selected_option'] === $option['letter'] ? 'selected' : ''; ?>">
										<input type="radio" name="selected_option" value="<?php echo sms_e($option['letter']); ?>" <?php echo $currentQuestion['selected_option'] === $option['letter'] ? 'checked' : ''; ?> style="display:none" class="answer-radio">
										<span class="exam-option-letter d-inline-flex align-items-center justify-content-center"><?php echo sms_e($option['letter']); ?></span>
										<span><?php echo sms_e($option['text']); ?></span>
									</label>
								<?php endforeach; ?>
							</div>

							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 exam-action-row">
								<button type="submit" name="nav_target" value="prev" formnovalidate class="btn btn-light rounded-pill d-inline-flex align-items-center" <?php echo $qIndex === 0 ? 'disabled' : ''; ?>><i class="fa-solid fa-arrow-left me-2"></i>Previous</button>
								<div class="d-flex flex-wrap gap-2 exam-action-row">
									<?php if ($qIndex < count($questions) - 1): ?>
										<button type="submit" name="nav_target" value="next" formnovalidate class="btn btn-secondary rounded-pill d-inline-flex align-items-center">Next <i class="fa-solid fa-arrow-right ms-2"></i></button>
									<?php endif; ?>
									<button type="submit" name="nav_target" value="save" formnovalidate class="btn btn-outline-success rounded-pill d-inline-flex align-items-center"><i class="fa-solid fa-floppy-disk me-2"></i>Save Answer</button>
								</div>
							</div>
						</form>

						<form method="post" action="quiz-submit.php" id="submitForm" onsubmit="return confirm('Submit your exam now? You cannot change your answers after submitting.');" class="mt-2">
							<input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
							<input type="hidden" name="attempt_id" value="<?php echo (int) $attempt['id']; ?>">
							<input type="hidden" name="exam_id" value="<?php echo (int) $examId; ?>">
							<button type="submit" class="btn btn-success rounded-pill d-inline-flex align-items-center w-100"><i class="fa-solid fa-paper-plane me-2"></i>Submit Quiz</button>
						</form>
					</section>
				</div>

				<div class="col-xl-3 col-lg-10">
					<aside class="exam-side-panel">
						<h6 class="mb-3">Attempt Summary</h6>
						<div class="d-flex align-items-center justify-content-between mb-2"><span class="text-muted">Student</span><strong><?php echo sms_e($currentUser['full_name'] ?? $currentUser['username']); ?></strong></div>
						<div class="d-flex align-items-center justify-content-between mb-2"><span class="text-muted">Answered</span><strong><?php echo $answeredCount; ?>/<?php echo count($questions); ?></strong></div>
						<div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted">Remaining</span><strong><?php echo count($questions) - $answeredCount; ?></strong></div>
						<div class="d-flex flex-wrap gap-2">
							<?php foreach ($questions as $i => $q): ?>
								<button type="submit" name="nav_target" value="<?php echo $i; ?>" form="answerForm" formnovalidate class="exam-question-dot <?php echo $i === $qIndex ? 'active' : ''; ?> <?php echo $q['selected_option'] !== null ? 'answered' : ''; ?>"><?php echo $i + 1; ?></button>
							<?php endforeach; ?>
						</div>
					</aside>
				</div>
			</div>
		</div>

		<script data-cfasync="false" type="text/javascript">
		(function () {
			document.querySelectorAll('.answer-radio').forEach(function (radio) {
				radio.addEventListener('change', function () {
					document.querySelectorAll('.exam-answer-option').forEach(function (opt) { opt.classList.remove('selected'); });
					radio.closest('.exam-answer-option').classList.add('selected');
				});
			});

			var deadline = <?php echo (int) $deadlineMs; ?>;
			var timerEl = document.getElementById('quizTimer');
			var timerCard = document.getElementById('timerCard');
			var submitForm = document.getElementById('submitForm');
			var autoSubmitted = false;

			function tick() {
				var remaining = Math.max(0, Math.round((deadline - Date.now()) / 1000));
				var minutes = Math.floor(remaining / 60).toString().padStart(2, '0');
				var seconds = (remaining % 60).toString().padStart(2, '0');
				timerEl.textContent = minutes + ':' + seconds;
				timerCard.classList.toggle('warning', remaining <= 60 && remaining > 20);
				timerCard.classList.toggle('danger', remaining <= 20);
				if (remaining <= 0 && !autoSubmitted) {
					autoSubmitted = true;
					submitForm.submit();
				}
			}
			tick();
			setInterval(tick, 1000);
		})();
		</script>
	<?php elseif ($result): ?>
		<section class="exam-result-card mt-4">
			<div class="exam-result-ring" style="--score: <?php echo (float) $result['percentage']; ?>;"><span><?php echo sms_e($result['percentage']); ?>%</span></div>
			<h4 class="mb-1"><?php echo $passed ? 'Excellent Work, You Passed' : 'Quiz Submitted'; ?></h4>
			<p class="text-muted mb-4"><?php echo $attempt['status'] === 'auto_submitted' ? 'Time was up. Your quiz was submitted automatically.' : 'Your answers have been submitted successfully.'; ?></p>

			<div class="row g-3 text-start justify-content-center mb-4">
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Student Name</span><strong><?php echo sms_e($result['first_name'] . ' ' . $result['last_name']); ?></strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Quiz Title</span><strong><?php echo sms_e($result['exam_title']); ?></strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Total Questions</span><strong><?php echo count($result['review']); ?></strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Correct Answers</span><strong><?php echo count(array_filter($result['review'], fn ($r) => $r['is_correct'])); ?></strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Wrong Answers</span><strong><?php echo count(array_filter($result['review'], fn ($r) => !$r['is_correct'])); ?></strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Final Score</span><strong><?php echo sms_e($result['score']); ?></strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Percentage</span><strong><?php echo sms_e($result['percentage']); ?>%</strong></div></div>
				<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Status</span><strong class="<?php echo $passed ? 'text-success' : 'text-danger'; ?>"><?php echo $passed ? 'Pass' : 'Fail'; ?></strong></div></div>
			</div>

			<div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
				<?php if ($result['allow_review']): ?><button type="button" class="btn btn-light rounded-pill" id="reviewAnswers"><i class="fa-solid fa-eye me-2"></i>Review Answers</button><?php endif; ?>
				<a href="quiz.php" class="btn btn-success rounded-pill"><i class="fa-solid fa-arrow-left me-2"></i>Return to Quiz Dashboard</a>
			</div>

			<?php if ($result['allow_review']): ?>
			<div class="exam-review-panel" id="reviewPanel">
				<h6 class="mb-3">Answer Review</h6>
				<div class="d-grid gap-3">
					<?php foreach ($result['review'] as $i => $row): ?>
						<?php
						$optionColumns = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
						$selectedText = $row['selected_option'] ? $row['selected_option'] . '. ' . $row[$optionColumns[$row['selected_option']]] : null;
						?>
						<div class="exam-review-item">
							<strong>Question <?php echo $i + 1; ?>:</strong> <?php echo sms_e($row['question_text']); ?><br>
							<span class="text-muted">Your answer: <?php echo sms_e($selectedText ?? 'Not answered'); ?></span><br>
							<span class="<?php echo $row['is_correct'] ? 'text-success' : 'text-danger'; ?>">Correct answer: <?php echo sms_e($row['correct_option']); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<script data-cfasync="false" type="text/javascript">
			document.getElementById('reviewAnswers').addEventListener('click', function () {
				var panel = document.getElementById('reviewPanel');
				panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
			});
			</script>
			<?php endif; ?>
		</section>
	<?php else: ?>
		<section class="module-card"><p class="text-muted fw-bold mb-0">This exam has no questions available.</p></section>
	<?php endif; ?>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
