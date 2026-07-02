<?php require_once('includes/header.php'); ?>

<?php
// CBT placeholder data. Replace these arrays with logged-in teacher database queries later.
$subjectsHandled = ['Mathematics', 'Physics', 'Computer Science'];
$assignedClasses = ['JSS 1A', 'JSS 2B', 'SS 1 Science', 'SS 2 Science'];
$academicSessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$questionBank = [
	['title' => 'First Term Mathematics Test', 'subject' => 'Mathematics', 'class' => 'SS 1 Science', 'questions' => 20, 'status' => 'Published'],
	['title' => 'Physics Motion Quiz', 'subject' => 'Physics', 'class' => 'SS 2 Science', 'questions' => 15, 'status' => 'Draft'],
	['title' => 'Computer Basics CBT', 'subject' => 'Computer Science', 'class' => 'JSS 2B', 'questions' => 10, 'status' => 'Published']
];

function cbtValue($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
	/* CBT management module: scoped premium styles for setup, question builder, preview, and question bank. */
	.cbt-page {
		--cbt-primary: #0f766e;
		--cbt-primary-dark: #115e59;
		--cbt-primary-soft: rgba(15, 118, 110, .1);
		--cbt-success: #16a34a;
		--cbt-success-soft: rgba(22, 163, 74, .12);
		--cbt-danger: #dc2626;
		--cbt-danger-soft: rgba(220, 38, 38, .1);
		--cbt-warning: #f59e0b;
		--cbt-warning-soft: rgba(245, 158, 11, .14);
		--cbt-blue: #2563eb;
		--cbt-blue-soft: rgba(37, 99, 235, .1);
		--cbt-ink: #10201d;
		--cbt-muted: #64748b;
		--cbt-border: rgba(15, 118, 110, .18);
		--cbt-shadow: 0 22px 60px rgba(15, 23, 42, .09);
		padding-bottom: 34px;
	}

	.cbt-page .cbt-hero,
	.cbt-page .cbt-card,
	.cbt-page .question-card,
	.cbt-page .preview-question {
		background: rgba(255, 255, 255, .98);
		border: 1px solid var(--cbt-border);
		box-shadow: var(--cbt-shadow);
	}

	.cbt-page .cbt-hero {
		position: relative;
		overflow: hidden;
		padding: 28px;
		border-radius: 26px;
		margin-bottom: 22px;
		background: linear-gradient(135deg, rgba(240, 253, 244, .98), rgba(255, 255, 255, .98));
	}

	.cbt-page .cbt-hero::after {
		content: "";
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top right, rgba(20, 184, 166, .15), transparent 35%), radial-gradient(circle at bottom left, rgba(37, 99, 235, .08), transparent 32%);
		pointer-events: none;
	}

	.cbt-page .cbt-hero > * {
		position: relative;
		z-index: 1;
	}

	.cbt-page .cbt-kicker,
	.cbt-page .field-icon,
	.cbt-page .status-badge,
	.cbt-page .question-count-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.cbt-page .cbt-kicker {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--cbt-primary-soft);
		color: var(--cbt-primary-dark);
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.cbt-page h3,
	.cbt-page h4,
	.cbt-page h5 {
		color: var(--cbt-ink);
		font-weight: 900;
	}

	.cbt-page .cbt-card {
		padding: 24px;
		border-radius: 24px;
		margin-bottom: 22px;
	}

	.cbt-page .form-label {
		font-size: 13px;
		font-weight: 900;
		color: var(--cbt-ink);
	}

	.cbt-page .field-wrap {
		position: relative;
	}

	.cbt-page .field-icon {
		position: absolute;
		left: 14px;
		top: 50%;
		transform: translateY(-50%);
		color: var(--cbt-primary);
		pointer-events: none;
	}

	.cbt-page .form-control,
	.cbt-page .form-select,
	.cbt-page textarea {
		border: 1px solid rgba(148, 163, 184, .32);
		border-radius: 15px;
		font-weight: 750;
		box-shadow: none;
	}

	.cbt-page .form-control,
	.cbt-page .form-select {
		min-height: 50px;
		padding-left: 42px;
	}

	.cbt-page textarea.form-control {
		padding: 14px;
		min-height: 104px;
	}

	.cbt-page .form-control:focus,
	.cbt-page .form-select:focus,
	.cbt-page textarea:focus {
		border-color: rgba(15, 118, 110, .72);
		box-shadow: 0 0 0 4px rgba(15, 118, 110, .12);
	}

	.cbt-page .notice {
		display: none;
		gap: 8px;
		align-items: center;
		padding: 12px 14px;
		border-radius: 14px;
		font-weight: 800;
		margin-bottom: 16px;
	}

	.cbt-page .notice.is-visible { display: flex; }
	.cbt-page .notice.success { color: var(--cbt-success); background: var(--cbt-success-soft); }
	.cbt-page .notice.error { color: var(--cbt-danger); background: var(--cbt-danger-soft); }

	.cbt-page .question-card {
		padding: 20px;
		border-radius: 22px;
		margin-bottom: 16px;
		transition: transform .18s ease, border-color .18s ease;
	}

	.cbt-page .question-card:hover {
		transform: translateY(-2px);
		border-color: rgba(15, 118, 110, .32);
	}

	.cbt-page .question-count-badge {
		width: 42px;
		height: 42px;
		border-radius: 14px;
		background: var(--cbt-primary-soft);
		color: var(--cbt-primary-dark);
		font-weight: 900;
	}

	.cbt-page .option-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 12px;
	}

	.cbt-page .option-box {
		padding: 12px;
		border: 1px solid rgba(148, 163, 184, .24);
		border-radius: 16px;
		background: #f8fafc;
	}

	.cbt-page .correct-row {
		display: flex;
		gap: 12px;
		flex-wrap: wrap;
		padding-top: 12px;
	}

	.cbt-page .correct-choice {
		display: inline-flex;
		gap: 7px;
		align-items: center;
		padding: 8px 11px;
		border-radius: 999px;
		background: var(--cbt-primary-soft);
		color: var(--cbt-primary-dark);
		font-weight: 900;
	}

	.cbt-page .action-bar {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
	}

	.cbt-page .btn-cbt-primary,
	.cbt-page .btn-cbt-secondary {
		min-height: 46px;
		border-radius: 14px;
		font-weight: 900;
		transition: transform .18s ease, box-shadow .18s ease;
	}

	.cbt-page .btn-cbt-primary {
		border: 0;
		background: linear-gradient(135deg, var(--cbt-primary), var(--cbt-primary-dark));
		color: #fff;
		box-shadow: 0 15px 32px rgba(15, 118, 110, .24);
	}

	.cbt-page .btn-cbt-primary:hover,
	.cbt-page .btn-cbt-secondary:hover {
		transform: translateY(-2px);
	}

	.cbt-page .btn-cbt-primary:hover { color: #fff; }

	.cbt-page .preview-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
		gap: 14px;
	}

	.cbt-page .preview-question {
		padding: 16px;
		border-radius: 18px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
	}

	.cbt-page .preview-question ol {
		margin-bottom: 8px;
		padding-left: 20px;
	}

	.cbt-page .bank-table-wrap {
		overflow-x: auto;
		border-radius: 18px;
		border: 1px solid rgba(148, 163, 184, .2);
	}

	.cbt-page .bank-table {
		min-width: 850px;
		margin-bottom: 0;
	}

	.cbt-page .bank-table thead th {
		padding: 14px 12px;
		background: linear-gradient(135deg, var(--cbt-primary), var(--cbt-primary-dark));
		color: #fff;
		border: 0;
		font-size: 12px;
		font-weight: 900;
		text-transform: uppercase;
	}

	.cbt-page .bank-table td {
		padding: 13px 12px;
		vertical-align: middle;
		border-color: rgba(148, 163, 184, .2);
		font-weight: 750;
	}

	.cbt-page .status-badge {
		gap: 6px;
		padding: 7px 10px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 900;
	}

	.cbt-page .status-published { background: var(--cbt-success-soft); color: var(--cbt-success); }
	.cbt-page .status-draft { background: var(--cbt-warning-soft); color: #b45309; }

	.cbt-page .bank-actions {
		display: flex;
		gap: 7px;
		flex-wrap: wrap;
	}

	@media (max-width: 767.98px) {
		.cbt-page .cbt-hero,
		.cbt-page .cbt-card,
		.cbt-page .question-card {
			padding: 20px;
			border-radius: 20px;
		}

		.cbt-page .option-grid {
			grid-template-columns: 1fr;
		}

		.cbt-page .action-bar,
		.cbt-page .action-bar .btn,
		.cbt-page .bank-actions .btn {
			width: 100%;
		}
	}
</style>

<div class="cbt-page">
	<!-- CBT hero: introduces the teacher question management workflow. -->
	<section class="cbt-hero">
		<span class="cbt-kicker"><i class="fa-solid fa-laptop-code"></i> CBT Question Management</span>
		<h3 class="mt-3 mb-2">Create, Preview, and Publish CBT Questions</h3>
		<p class="text-muted mb-0">Build computer-based tests for your assigned subjects and classes, manage drafts, and prepare assessments for students.</p>
	</section>

	<div id="cbtNotice" class="notice" role="alert"><i class="fa-solid fa-circle-info"></i><span></span></div>

	<!-- CBT setup form: stores exam context before questions are published. -->
	<section class="cbt-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div>
				<h4 class="mb-1">CBT Setup Information</h4>
				<p class="text-muted mb-0">Select the academic context and define the test details.</p>
			</div>
		</div>
		<form id="cbtSetupForm" class="row g-3" novalidate>
			<div class="col-md-4">
				<label class="form-label" for="subjectSelect">Subject Selection</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-book-open"></i></span><select class="form-select" id="subjectSelect" required><option value="">Select subject</option><?php foreach ($subjectsHandled as $subject): ?><option value="<?php echo cbtValue($subject); ?>"><?php echo cbtValue($subject); ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="classSelect">Class Selection</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classSelect" required><option value="">Select class</option><?php foreach ($assignedClasses as $class): ?><option value="<?php echo cbtValue($class); ?>"><?php echo cbtValue($class); ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="sessionSelect">Academic Session</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionSelect" required><option value="">Select session</option><?php foreach ($academicSessions as $session): ?><option value="<?php echo cbtValue($session); ?>"><?php echo cbtValue($session); ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="termSelect">Term</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termSelect" required><option value="">Select term</option><?php foreach ($terms as $term): ?><option value="<?php echo cbtValue($term); ?>"><?php echo cbtValue($term); ?></option><?php endforeach; ?></select></div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="cbtTitle">CBT Title</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-heading"></i></span><input type="text" class="form-control" id="cbtTitle" placeholder="First Term Mathematics Test" required></div>
			</div>
			<div class="col-md-2">
				<label class="form-label" for="durationInput">Duration</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-regular fa-clock"></i></span><input type="number" min="1" class="form-control" id="durationInput" placeholder="30" required></div>
			</div>
			<div class="col-md-2">
				<label class="form-label" for="questionLimit">No. of Questions</label>
				<div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-list-ol"></i></span><input type="number" min="1" class="form-control" id="questionLimit" placeholder="20"></div>
			</div>
			<div class="col-12">
				<label class="form-label" for="instructionsInput">Instructions / Description</label>
				<textarea class="form-control" id="instructionsInput" placeholder="Answer all questions. Select the best option for each question."></textarea>
			</div>
		</form>
	</section>

	<!-- Question builder: teachers add, edit, duplicate, delete, and reorder objective questions. -->
	<section class="cbt-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div>
				<h4 class="mb-1">Question Creation</h4>
				<p class="text-muted mb-0">Add multiple-choice questions and select the correct option.</p>
			</div>
			<button type="button" class="btn btn-cbt-primary" id="addQuestionBtn"><i class="fa-solid fa-plus me-2"></i>Add Question</button>
		</div>
		<div id="questionBuilder"></div>
		<div class="action-bar mt-3">
			<button type="button" class="btn btn-outline-success btn-cbt-secondary" id="previewBtn"><i class="fa-solid fa-eye me-2"></i>Preview Questions</button>
			<button type="button" class="btn btn-outline-secondary btn-cbt-secondary" id="saveQuestionBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Question</button>
			<button type="button" class="btn btn-cbt-primary" id="saveDraftBtn"><i class="fa-solid fa-file-circle-plus me-2"></i>Save as Draft</button>
			<button type="button" class="btn btn-cbt-primary" id="publishBtn"><i class="fa-solid fa-paper-plane me-2"></i>Publish CBT</button>
		</div>
	</section>

	<!-- Question preview: generated dynamically before publishing. -->
	<section class="cbt-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div>
				<h4 class="mb-1">Question Preview</h4>
				<p class="text-muted mb-0">Review questions and correct answers before submission.</p>
			</div>
		</div>
		<div class="preview-grid" id="previewArea"><div class="preview-question text-muted">No questions added yet.</div></div>
	</section>

	<!-- Question bank: placeholder records ready for database integration. -->
	<section class="cbt-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
			<div>
				<h4 class="mb-1">CBT Question Bank</h4>
				<p class="text-muted mb-0">Manage saved CBT tests and publishing status.</p>
			</div>
		</div>
		<div class="bank-table-wrap">
			<table class="table bank-table align-middle">
				<thead><tr><th>CBT Title</th><th>Subject</th><th>Class</th><th>Questions</th><th>Status</th><th>Action</th></tr></thead>
				<tbody id="questionBankBody"></tbody>
			</table>
		</div>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// CBT module behavior: dynamic question builder, validation, preview, draft/publish, and question bank controls.
(function () {
	var questions = [];
	var nextQuestionId = 1;
	var bank = <?php echo json_encode($questionBank); ?>;

	function byId(id) { return document.getElementById(id); }
	function escapeHtml(value) { return String(value || '').replace(/[&<>"]/g, function (char) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[char]; }); }
	function showNotice(type, message) { var notice = byId('cbtNotice'); notice.className = 'notice is-visible ' + type; notice.querySelector('span').textContent = message; window.scrollTo({ top: notice.offsetTop - 20, behavior: 'smooth' }); }

	function createQuestion(seed) {
		return Object.assign({ id: nextQuestionId++, text: '', options: { A: '', B: '', C: '', D: '' }, correct: '' }, seed || {});
	}

	function renderQuestions() {
		byId('questionBuilder').innerHTML = questions.map(function (question, index) {
			var number = index + 1;
			return '<article class="question-card" data-id="' + question.id + '">' +
				'<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"><div class="d-flex align-items-center gap-2"><span class="question-count-badge">' + number + '</span><h5 class="mb-0">Question ' + number + '</h5></div><div class="bank-actions"><button type="button" class="btn btn-sm btn-outline-secondary move-up"><i class="fa-solid fa-arrow-up"></i></button><button type="button" class="btn btn-sm btn-outline-secondary move-down"><i class="fa-solid fa-arrow-down"></i></button><button type="button" class="btn btn-sm btn-outline-success duplicate-question"><i class="fa-solid fa-copy"></i> Duplicate</button><button type="button" class="btn btn-sm btn-outline-danger delete-question"><i class="fa-solid fa-trash"></i> Delete</button></div></div>' +
				'<label class="form-label">Question Text</label><textarea class="form-control question-text mb-3" placeholder="What is the value of 5 x 5?">' + escapeHtml(question.text) + '</textarea>' +
				'<div class="option-grid">' + ['A', 'B', 'C', 'D'].map(function (letter) { return '<div class="option-box"><label class="form-label">Option ' + letter + '</label><input type="text" class="form-control option-input" data-option="' + letter + '" value="' + escapeHtml(question.options[letter]) + '" placeholder="Enter option ' + letter + '"></div>'; }).join('') + '</div>' +
				'<div class="correct-row"><strong class="me-1">Correct Answer:</strong>' + ['A', 'B', 'C', 'D'].map(function (letter) { return '<label class="correct-choice"><input type="radio" name="correct_' + question.id + '" value="' + letter + '" ' + (question.correct === letter ? 'checked' : '') + '> ' + letter + '</label>'; }).join('') + '</div>' +
			'</article>';
		}).join('');
		if (!questions.length) { byId('questionBuilder').innerHTML = '<div class="text-muted fw-bold">No questions yet. Click Add Question to begin.</div>'; }
	}

	function syncQuestionsFromDom() {
		document.querySelectorAll('.question-card').forEach(function (card) {
			var id = parseInt(card.getAttribute('data-id'), 10);
			var question = questions.find(function (item) { return item.id === id; });
			if (!question) { return; }
			question.text = card.querySelector('.question-text').value.trim();
			card.querySelectorAll('.option-input').forEach(function (input) { question.options[input.getAttribute('data-option')] = input.value.trim(); });
			var checked = card.querySelector('input[type="radio"]:checked');
			question.correct = checked ? checked.value : '';
		});
	}

	function validateSetup(requireQuestions) {
		syncQuestionsFromDom();
		var required = [
			['subjectSelect', 'Please select a subject.'], ['classSelect', 'Please select a class.'], ['sessionSelect', 'Please select an academic session.'], ['termSelect', 'Please select a term.'], ['cbtTitle', 'Please enter the CBT title.'], ['durationInput', 'Please enter the test duration.']
		];
		for (var i = 0; i < required.length; i += 1) { if (!byId(required[i][0]).value.trim()) { showNotice('error', required[i][1]); return false; } }
		if (parseInt(byId('durationInput').value, 10) <= 0) { showNotice('error', 'Test duration must be greater than zero.'); return false; }
		if (requireQuestions && !questions.length) { showNotice('error', 'Add at least one question before publishing.'); return false; }
		for (var q = 0; q < questions.length; q += 1) {
			var item = questions[q];
			if (!item.text || !item.options.A || !item.options.B || !item.options.C || !item.options.D) { showNotice('error', 'Question ' + (q + 1) + ' must have question text and all four options.'); return false; }
			if (!item.correct) { showNotice('error', 'Please select the correct answer for Question ' + (q + 1) + '.'); return false; }
		}
		return true;
	}

	function renderPreview() {
		syncQuestionsFromDom();
		byId('previewArea').innerHTML = questions.length ? questions.map(function (question, index) {
			return '<article class="preview-question"><h5>Question ' + (index + 1) + '</h5><p>' + escapeHtml(question.text || 'Untitled question') + '</p><ol type="A"><li>' + escapeHtml(question.options.A) + '</li><li>' + escapeHtml(question.options.B) + '</li><li>' + escapeHtml(question.options.C) + '</li><li>' + escapeHtml(question.options.D) + '</li></ol><strong>Correct Answer: ' + escapeHtml(question.correct || 'Not selected') + '</strong></article>';
		}).join('') : '<div class="preview-question text-muted">No questions added yet.</div>';
	}

	function statusBadge(status) {
		var published = status === 'Published';
		return '<span class="status-badge ' + (published ? 'status-published' : 'status-draft') + '"><i class="fa-solid ' + (published ? 'fa-circle-check' : 'fa-file-lines') + '"></i>' + status + '</span>';
	}

	function renderBank() {
		byId('questionBankBody').innerHTML = bank.map(function (item, index) {
			return '<tr data-index="' + index + '"><td>' + escapeHtml(item.title) + '</td><td>' + escapeHtml(item.subject) + '</td><td>' + escapeHtml(item.class) + '</td><td>' + item.questions + '</td><td>' + statusBadge(item.status) + '</td><td><div class="bank-actions"><button type="button" class="btn btn-sm btn-outline-primary view-bank"><i class="fa-solid fa-eye"></i> View</button><button type="button" class="btn btn-sm btn-outline-success edit-bank"><i class="fa-solid fa-pen"></i> Edit</button><button type="button" class="btn btn-sm btn-outline-warning toggle-bank"><i class="fa-solid fa-toggle-on"></i> ' + (item.status === 'Published' ? 'Unpublish' : 'Publish') + '</button><button type="button" class="btn btn-sm btn-outline-danger delete-bank"><i class="fa-solid fa-trash"></i> Delete</button></div></td></tr>';
		}).join('');
	}

	function addToBank(status) {
		if (!validateSetup(status === 'Published')) { return; }
		bank.unshift({ title: byId('cbtTitle').value.trim(), subject: byId('subjectSelect').value, class: byId('classSelect').value, questions: questions.length, status: status });
		renderBank();
		showNotice('success', status === 'Published' ? 'CBT published successfully.' : 'CBT saved as draft successfully.');
	}

	document.addEventListener('DOMContentLoaded', function () {
		questions.push(createQuestion());
		renderQuestions();
		renderPreview();
		renderBank();

		byId('addQuestionBtn').addEventListener('click', function () { syncQuestionsFromDom(); questions.push(createQuestion()); renderQuestions(); });
		byId('previewBtn').addEventListener('click', renderPreview);
		byId('saveQuestionBtn').addEventListener('click', function () { syncQuestionsFromDom(); renderPreview(); showNotice('success', 'Question changes saved in the builder preview.'); });
		byId('saveDraftBtn').addEventListener('click', function () { addToBank('Draft'); });
		byId('publishBtn').addEventListener('click', function () { addToBank('Published'); });

		document.addEventListener('click', function (event) {
			var card = event.target.closest('.question-card');
			if (card) {
				var id = parseInt(card.getAttribute('data-id'), 10);
				var index = questions.findIndex(function (question) { return question.id === id; });
				if (event.target.closest('.delete-question')) { questions.splice(index, 1); renderQuestions(); renderPreview(); }
				if (event.target.closest('.duplicate-question')) { syncQuestionsFromDom(); var copy = JSON.parse(JSON.stringify(questions[index])); copy.id = nextQuestionId++; questions.splice(index + 1, 0, copy); renderQuestions(); }
				if (event.target.closest('.move-up') && index > 0) { syncQuestionsFromDom(); var up = questions.splice(index, 1)[0]; questions.splice(index - 1, 0, up); renderQuestions(); }
				if (event.target.closest('.move-down') && index < questions.length - 1) { syncQuestionsFromDom(); var down = questions.splice(index, 1)[0]; questions.splice(index + 1, 0, down); renderQuestions(); }
			}

			var row = event.target.closest('#questionBankBody tr');
			if (row) {
				var bankIndex = parseInt(row.getAttribute('data-index'), 10);
				if (event.target.closest('.delete-bank')) { bank.splice(bankIndex, 1); renderBank(); showNotice('success', 'CBT record deleted from the question bank.'); }
				if (event.target.closest('.toggle-bank')) { bank[bankIndex].status = bank[bankIndex].status === 'Published' ? 'Draft' : 'Published'; renderBank(); showNotice('success', 'CBT publishing status updated.'); }
				if (event.target.closest('.view-bank')) { showNotice('success', 'Viewing: ' + bank[bankIndex].title + '. Database-backed question viewing can be connected later.'); }
				if (event.target.closest('.edit-bank')) { byId('cbtTitle').value = bank[bankIndex].title; byId('subjectSelect').value = bank[bankIndex].subject; byId('classSelect').value = bank[bankIndex].class; showNotice('success', 'CBT setup loaded for editing. Add or update questions below.'); }
			}
		});
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>
