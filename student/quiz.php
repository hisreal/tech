<?php require_once('includes/header.php'); ?>

<div class="student-exam-module">
	<!-- Quiz dashboard hero: summarizes the student's examination area. -->
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

	<section class="row g-3 mb-4" aria-label="Quiz overview">
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-clipboard-list"></i></span>
				<div><span class="text-muted d-block">Available</span><h4 class="mb-0">6</h4></div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-clock"></i></span>
				<div><span class="text-muted d-block">Average Time</span><h4 class="mb-0">25m</h4></div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-circle-check"></i></span>
				<div><span class="text-muted d-block">Completed</span><h4 class="mb-0">3</h4></div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3">
			<div class="exam-stat-card d-flex align-items-center gap-3">
				<span class="exam-stat-icon"><i class="fa-solid fa-award"></i></span>
				<div><span class="text-muted d-block">Best Score</span><h4 class="mb-0">92%</h4></div>
			</div>
		</div>
	</section>

	<!-- Quiz cards: clear metadata and a direct start action for each assessment. -->
	<section class="row g-4" aria-label="Available quizzes">
		<?php
			$quizzes = [
				['title' => 'Information About UI/UX Design Degree', 'subject' => 'Design', 'description' => 'Test your understanding of design process, usability, and interface fundamentals.', 'duration' => '20 Minutes', 'questions' => 5, 'level' => 'Beginner'],
				['title' => 'JavaScript and Express Essentials', 'subject' => 'Computer Studies', 'description' => 'Practice core JavaScript concepts, server basics, and Express routing.', 'duration' => '35 Minutes', 'questions' => 10, 'level' => 'Intermediate'],
				['title' => 'Introduction to Python Programming', 'subject' => 'Programming', 'description' => 'Review variables, conditions, functions, and foundational Python syntax.', 'duration' => '30 Minutes', 'questions' => 8, 'level' => 'Beginner'],
				['title' => 'Responsive Websites with HTML5 and CSS3', 'subject' => 'Web Design', 'description' => 'Assess your knowledge of semantic markup, layout, and responsive styling.', 'duration' => '25 Minutes', 'questions' => 5, 'level' => 'Practical'],
				['title' => 'Photoshop Design Fundamentals', 'subject' => 'Creative Arts', 'description' => 'Answer questions on image editing, layers, selection tools, and export settings.', 'duration' => '30 Minutes', 'questions' => 10, 'level' => 'Intermediate'],
				['title' => 'C# Development with Visual Studio', 'subject' => 'Software Development', 'description' => 'Evaluate your understanding of C# syntax, debugging, and project workflow.', 'duration' => '25 Minutes', 'questions' => 7, 'level' => 'Advanced'],
			];
		?>
		<?php foreach ($quizzes as $index => $quiz): ?>
			<div class="col-md-6 col-xl-4">
				<article class="exam-card">
					<div class="d-flex align-items-start justify-content-between gap-3 mb-3">
						<span class="exam-card-icon"><i class="fa-solid fa-book-open-reader"></i></span>
						<span class="exam-chip"><i class="fa-solid fa-layer-group"></i><?php echo $quiz['level']; ?></span>
					</div>
					<h5 class="mb-2"><?php echo $quiz['title']; ?></h5>
					<p class="text-muted mb-0"><?php echo $quiz['description']; ?></p>
					<div class="exam-card-meta">
						<span class="exam-chip"><i class="fa-solid fa-book"></i><?php echo $quiz['subject']; ?></span>
						<span class="exam-chip"><i class="fa-solid fa-clock"></i><?php echo $quiz['duration']; ?></span>
						<span class="exam-chip"><i class="fa-solid fa-circle-question"></i><?php echo $quiz['questions']; ?> Questions</span>
					</div>
					<a href="quiz-question.php?quiz=<?php echo $index + 1; ?>" class="btn exam-start-btn rounded-pill d-inline-flex align-items-center">
						Start Quiz <i class="fa-solid fa-arrow-right ms-2"></i>
					</a>
				</article>
			</div>
		<?php endforeach; ?>
	</section>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>