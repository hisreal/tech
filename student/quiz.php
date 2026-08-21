<?php require_once('includes/header.php'); ?>
<?php

use App\Services\CBTService;

$cbtService = new CBTService();
$currentUser = sms_current_user();
$studentId = $cbtService->studentIdForUser((int) $currentUser['id']);

$exams = $studentId ? $cbtService->availableExamsForStudent($studentId) : [];
$stats = $studentId ? $cbtService->studentAttemptStats($studentId) : ['completed' => 0, 'best_score' => 0];
$avgDuration = $exams ? round(array_sum(array_column($exams, 'duration_minutes')) / count($exams)) : 0;
?>

<div class="student-exam-module">
	<section class="exam-hero">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div>
				<span class="exam-kicker mb-3"><i class="fa-solid fa-laptop-file"></i> Online Examination Center</span>
				<h3 class="mb-2">Available Quizzes</h3>
				<p class="text-muted mb-0">Choose an assessment, review the instructions, and start when you are ready.</p>
			</div>
			<a href="dashboard.php" class="btn btn-light rounded-pill d-inline-flex align-items-center"><i class="fa-solid fa-arrow-left me-2"></i>Dashboard</a>
		</div>
	</section>

	<?php foreach (sms_flash() as $type => $messages): ?>
		<?php foreach ($messages as $message): ?>
			<div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>" role="alert"><?php echo sms_e($message); ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>

	<section class="row g-3 mb-4" aria-label="Quiz overview">
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-clipboard-list"></i></span>
				<div><span class="text-muted d-block">Available</span><h4 class="mb-0"><?php echo count($exams); ?></h4></div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-clock"></i></span>
				<div><span class="text-muted d-block">Average Duration</span><h4 class="mb-0"><?php echo $avgDuration; ?>m</h4></div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-circle-check"></i></span>
				<div><span class="text-muted d-block">Completed</span><h4 class="mb-0"><?php echo $stats['completed']; ?></h4></div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-award"></i></span>
				<div><span class="text-muted d-block">Best Score</span><h4 class="mb-0"><?php echo $stats['best_score']; ?>%</h4></div>
			</div>
		</div>
	</section>

	<section class="row g-4" aria-label="Available quizzes">
		<?php foreach ($exams as $exam): ?>
			<?php
			$isUnlimited = (int) $exam['maximum_attempts'] === 0;
			$remaining = $isUnlimited ? null : max(0, (int) $exam['maximum_attempts'] - (int) $exam['attempts_used']);
			$canStart = $isUnlimited || $remaining > 0 || $exam['in_progress_attempt_id'];
			?>
			<div class="col-md-6 col-xl-4">
				<article class="exam-card">
					<div class="d-flex align-items-start justify-content-between gap-3 mb-3">
						<span class="exam-card-icon"><i class="fa-solid fa-book-open-reader"></i></span>
						<span class="exam-chip"><i class="fa-solid fa-layer-group"></i><?php echo (int) $exam['question_count']; ?> Questions</span>
					</div>
					<h5 class="mb-2"><?php echo sms_e($exam['title']); ?> <?php echo $exam['exam_type'] === 'practice' ? '<span class="exam-chip" style="background:rgba(245,158,11,.14);color:#b45309;">Practice</span>' : ''; ?></h5>
					<p class="text-muted mb-0"><?php echo sms_e($exam['description'] ?: 'No description provided for this exam.'); ?></p>
					<div class="exam-card-meta">
						<span class="exam-chip"><i class="fa-solid fa-book"></i><?php echo sms_e($exam['subject_name']); ?></span>
						<span class="exam-chip"><i class="fa-solid fa-clock"></i><?php echo (int) $exam['duration_minutes']; ?> Minutes</span>
						<span class="exam-chip"><i class="fa-solid fa-rotate"></i><?php echo $isUnlimited ? 'Unlimited attempts' : $remaining . ' attempt(s) left'; ?></span>
					</div>
					<?php if ($exam['in_progress_attempt_id']): ?>
						<a href="quiz-question.php?exam_id=<?php echo (int) $exam['id']; ?>" class="btn exam-start-btn rounded-pill d-inline-flex align-items-center">Resume Quiz <i class="fa-solid fa-arrow-right ms-2"></i></a>
					<?php elseif ($canStart): ?>
						<a href="quiz-question.php?exam_id=<?php echo (int) $exam['id']; ?>" class="btn exam-start-btn rounded-pill d-inline-flex align-items-center">Start Quiz <i class="fa-solid fa-arrow-right ms-2"></i></a>
					<?php else: ?>
						<button class="btn exam-start-btn rounded-pill d-inline-flex align-items-center" disabled>No Attempts Left</button>
					<?php endif; ?>
				</article>
			</div>
		<?php endforeach; ?>
		<?php if (!$exams): ?>
			<div class="col-12"><p class="text-muted fw-bold text-center py-4">No CBT exams are available for your class right now. Check back later.</p></div>
		<?php endif; ?>
	</section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
