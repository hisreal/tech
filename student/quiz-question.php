<?php require_once('includes/header.php'); ?>

<div class="student-exam-module">
	<!-- Quiz workplace hero: gives students context before and during the attempt. -->
	<section class="exam-hero">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div>
				<span class="exam-kicker mb-3"><i class="fa-solid fa-shield-halved"></i> Secure Quiz Workplace</span>
				<h3 class="mb-2" id="quizTitle">Information About UI/UX Design Degree</h3>
				<p class="text-muted mb-0">Read carefully, choose the best answer, and submit before the timer ends.</p>
			</div>
			<a href="quiz.php" class="btn btn-light rounded-pill d-inline-flex align-items-center"><i class="fa-solid fa-arrow-left me-2"></i>Quiz Dashboard</a>
		</div>
	</section>

	<div class="exam-workplace-wrap">
		<div class="row g-4 w-100 justify-content-center" id="quizWorkspace">
			<div class="col-xl-8 col-lg-10">
				<!-- Main quiz workplace: centered card that renders the active question and answer options. -->
				<section class="exam-workplace">
					<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
						<div>
							<span class="exam-kicker mb-2" id="questionBadge">Question 1 of 5</span>
							<h4 class="mb-0" id="questionText">Question text appears here.</h4>
						</div>
						<div class="exam-timer" id="timerCard"><i class="fa-solid fa-clock me-2"></i><strong id="quizTimer">03:00</strong></div>
					</div>

					<div class="mb-4">
						<div class="d-flex align-items-center justify-content-between mb-2">
							<span class="fw-semibold">Progress</span>
							<span class="fw-semibold text-success" id="progressText">20%</span>
						</div>
						<div class="exam-progress-track" aria-label="Quiz completion progress">
							<div class="exam-progress-bar" id="quizProgressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="20"></div>
						</div>
					</div>

					<div class="d-grid gap-3" id="answerOptions"></div>

					<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 exam-action-row">
						<button type="button" class="btn btn-light rounded-pill d-inline-flex align-items-center" id="prevQuestion"><i class="fa-solid fa-arrow-left me-2"></i>Previous</button>
						<div class="d-flex flex-wrap gap-2 exam-action-row">
							<button type="button" class="btn btn-secondary rounded-pill d-inline-flex align-items-center" id="nextQuestion">Next <i class="fa-solid fa-arrow-right ms-2"></i></button>
							<button type="button" class="btn btn-success rounded-pill d-inline-flex align-items-center" id="submitQuiz"><i class="fa-solid fa-paper-plane me-2"></i>Submit Quiz</button>
						</div>
					</div>
				</section>
			</div>

			<div class="col-xl-3 col-lg-10">
				<!-- Side panel: progress summary and quick question navigator. -->
				<aside class="exam-side-panel">
					<h6 class="mb-3">Attempt Summary</h6>
					<div class="d-flex align-items-center justify-content-between mb-2"><span class="text-muted">Student</span><strong>Ajiboye Isreal</strong></div>
					<div class="d-flex align-items-center justify-content-between mb-2"><span class="text-muted">Answered</span><strong id="answeredCount">0/5</strong></div>
					<div class="d-flex align-items-center justify-content-between mb-3"><span class="text-muted">Remaining</span><strong id="remainingCount">5</strong></div>
					<div class="d-flex flex-wrap gap-2" id="questionNavigator"></div>
				</aside>
			</div>
		</div>
	</div>

	<!-- Result summary: shown after submission with score, pass status, and review option. -->
	<section class="exam-result-card mt-4" id="resultPanel">
		<div class="exam-result-ring" id="resultRing" style="--score: 0;"><span id="resultPercent">0%</span></div>
		<h4 class="mb-1" id="resultHeading">Quiz Submitted</h4>
		<p class="text-muted mb-4" id="resultMessage">Your quiz result is ready.</p>

		<div class="row g-3 text-start justify-content-center mb-4">
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Student Name</span><strong id="resultStudent">Ajiboye Isreal Oluwaseun</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Quiz Title</span><strong id="resultQuizTitle">UI/UX Design</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Total Questions</span><strong id="resultTotal">0</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Correct Answers</span><strong id="resultCorrect">0</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Wrong Answers</span><strong id="resultWrong">0</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Final Score</span><strong id="resultScore">0/0</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Percentage</span><strong id="resultPercentage">0%</strong></div></div>
			<div class="col-sm-6 col-xl-3"><div class="exam-stat-card"><span class="text-muted d-block">Status</span><strong id="resultStatus">Pending</strong></div></div>
		</div>

		<div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
			<button type="button" class="btn btn-light rounded-pill" id="reviewAnswers"><i class="fa-solid fa-eye me-2"></i>Review Answers</button>
			<a href="quiz.php" class="btn btn-success rounded-pill"><i class="fa-solid fa-arrow-left me-2"></i>Return to Quiz Dashboard</a>
		</div>

		<div class="exam-review-panel" id="reviewPanel">
			<h6 class="mb-3">Answer Review</h6>
			<div class="d-grid gap-3" id="reviewList"></div>
		</div>
	</section>
</div>

</div>
</div>

<!-- Quiz workplace behavior: question navigation, live progress, timer, scoring, and result rendering. -->
<script data-cfasync="false" type="text/javascript">
(function () {
	var questions = [
		{ text: 'What does UI stand for?', options: ['User Intention', 'User Interface', 'Universal Interaction', 'Usability Information'], answer: 1 },
		{ text: 'Which design principle focuses on building around real user needs?', options: ['User-Centered Design', 'Decorative First Design', 'Code-Centered Design', 'Color-Only Design'], answer: 0 },
		{ text: 'Which tool is commonly used for wireframing and interface prototyping?', options: ['Microsoft Excel', 'Figma', 'Command Prompt', 'Notepad'], answer: 1 },
		{ text: 'What is a wireframe in product design?', options: ['A database table', 'A low-fidelity layout of a screen', 'A final printed poster', 'A server configuration file'], answer: 1 },
		{ text: 'What is the primary goal of UX design?', options: ['To improve usability and satisfaction', 'To use more colors', 'To make files smaller', 'To remove all text'], answer: 0 }
	];

	var state = {
		current: 0,
		answers: [null, null, null, null, null],
		remainingSeconds: 180,
		submitted: false
	};

	function byId(id) { return document.getElementById(id); }

	function answeredCount() {
		return state.answers.filter(function (answer) { return answer !== null; }).length;
	}

	function formatTime(seconds) {
		var minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
		var secs = (seconds % 60).toString().padStart(2, '0');
		return minutes + ':' + secs;
	}

	function updateProgress() {
		var progress = Math.round(((state.current + 1) / questions.length) * 100);
		byId('progressText').textContent = progress + '%';
		byId('quizProgressBar').style.width = progress + '%';
		byId('quizProgressBar').setAttribute('aria-valuenow', progress);
		byId('answeredCount').textContent = answeredCount() + '/' + questions.length;
		byId('remainingCount').textContent = questions.length - answeredCount();
	}

	function updateTimer() {
		var timerCard = byId('timerCard');
		byId('quizTimer').textContent = formatTime(state.remainingSeconds);
		timerCard.classList.toggle('warning', state.remainingSeconds <= 60 && state.remainingSeconds > 20);
		timerCard.classList.toggle('danger', state.remainingSeconds <= 20);
	}

	function renderNavigator() {
		var navigator = byId('questionNavigator');
		navigator.innerHTML = '';
		questions.forEach(function (question, index) {
			var button = document.createElement('button');
			button.type = 'button';
			button.className = 'exam-question-dot';
			button.textContent = index + 1;
			button.classList.toggle('active', index === state.current);
			button.classList.toggle('answered', state.answers[index] !== null);
			button.addEventListener('click', function () {
				state.current = index;
				renderQuestion();
			});
			navigator.appendChild(button);
		});
	}

	function renderQuestion() {
		var question = questions[state.current];
		byId('questionBadge').textContent = 'Question ' + (state.current + 1) + ' of ' + questions.length;
		byId('questionText').textContent = question.text;
		byId('answerOptions').innerHTML = '';

		question.options.forEach(function (option, index) {
			var optionEl = document.createElement('button');
			optionEl.type = 'button';
			optionEl.className = 'exam-answer-option';
			optionEl.classList.toggle('selected', state.answers[state.current] === index);
			optionEl.innerHTML = '<span class="exam-option-letter">' + String.fromCharCode(65 + index) + '</span><span>' + option + '</span>';
			optionEl.addEventListener('click', function () {
				state.answers[state.current] = index;
				renderQuestion();
			});
			byId('answerOptions').appendChild(optionEl);
		});

		byId('prevQuestion').disabled = state.current === 0;
		byId('nextQuestion').innerHTML = state.current === questions.length - 1 ? 'Review <i class="fa-solid fa-arrow-right ms-2"></i>' : 'Next <i class="fa-solid fa-arrow-right ms-2"></i>';
		renderNavigator();
		updateProgress();
	}

	function showResult(reason) {
		if (state.submitted) { return; }
		state.submitted = true;

		var correct = questions.filter(function (question, index) { return state.answers[index] === question.answer; }).length;
		var wrong = questions.length - correct;
		var percent = Math.round((correct / questions.length) * 100);
		var passed = percent >= 70;

		byId('quizWorkspace').style.display = 'none';
		byId('resultPanel').style.display = 'block';
		byId('resultRing').style.setProperty('--score', percent);
		byId('resultPercent').textContent = percent + '%';
		byId('resultHeading').textContent = passed ? 'Excellent Work, You Passed' : 'Quiz Submitted';
		byId('resultMessage').textContent = reason === 'timeout' ? 'Time is up. Your quiz was submitted automatically.' : 'Your answers have been submitted successfully.';
		byId('resultQuizTitle').textContent = byId('quizTitle').textContent;
		byId('resultTotal').textContent = questions.length;
		byId('resultCorrect').textContent = correct;
		byId('resultWrong').textContent = wrong;
		byId('resultScore').textContent = correct + '/' + questions.length;
		byId('resultPercentage').textContent = percent + '%';
		byId('resultStatus').textContent = passed ? 'Pass' : 'Fail';
		byId('resultStatus').className = passed ? 'text-success' : 'text-danger';

		renderReview();
		window.scrollTo({ top: 0, behavior: 'smooth' });
	}

	function renderReview() {
		var reviewList = byId('reviewList');
		reviewList.innerHTML = '';
		questions.forEach(function (question, index) {
			var selected = state.answers[index];
			var item = document.createElement('div');
			item.className = 'exam-review-item';
			item.innerHTML = '<strong>Question ' + (index + 1) + ':</strong> ' + question.text + '<br><span class="text-muted">Your answer: ' + (selected === null ? 'Not answered' : question.options[selected]) + '</span><br><span class="text-success">Correct answer: ' + question.options[question.answer] + '</span>';
			reviewList.appendChild(item);
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		byId('prevQuestion').addEventListener('click', function () {
			if (state.current > 0) { state.current -= 1; renderQuestion(); }
		});

		byId('nextQuestion').addEventListener('click', function () {
			if (state.current < questions.length - 1) { state.current += 1; renderQuestion(); }
			else { byId('submitQuiz').focus(); }
		});

		byId('submitQuiz').addEventListener('click', function () { showResult('manual'); });
		byId('reviewAnswers').addEventListener('click', function () {
			var panel = byId('reviewPanel');
			panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
		});

		var timer = setInterval(function () {
			if (state.submitted) { clearInterval(timer); return; }
			state.remainingSeconds -= 1;
			updateTimer();
			if (state.remainingSeconds <= 0) { clearInterval(timer); showResult('timeout'); }
		}, 1000);

		updateTimer();
		renderQuestion();
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>