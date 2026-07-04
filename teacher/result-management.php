<?php require_once('includes/header.php'); ?>

<?php
// Result management placeholder data. Replace with teacher-scoped database queries during backend integration.
$schoolName = 'Brighter Future Standard School, Katsina';
$teacherName = 'Mr. Adewale Olumide Johnson';
$assignedClasses = ['JSS 1A', 'JSS 2B', 'SS 1 Science', 'SS 2 Science'];
$subjectsHandled = ['Mathematics', 'Physics', 'Computer Science'];
$academicSessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$studentsByClass = [
	'JSS 1A' => [
		['reg_no' => 'BFSS/JSS1A/001', 'name' => 'Aisha Bello'],
		['reg_no' => 'BFSS/JSS1A/002', 'name' => 'Daniel Okafor'],
		['reg_no' => 'BFSS/JSS1A/003', 'name' => 'Maryam Musa'],
		['reg_no' => 'BFSS/JSS1A/004', 'name' => 'Samuel Adeyemi']
	],
	'JSS 2B' => [
		['reg_no' => 'BFSS/JSS2B/011', 'name' => 'Ibrahim Sani'],
		['reg_no' => 'BFSS/JSS2B/012', 'name' => 'Grace Emmanuel'],
		['reg_no' => 'BFSS/JSS2B/013', 'name' => 'Chinedu Nwosu']
	],
	'SS 1 Science' => [
		['reg_no' => 'BFSS/SS1SCI/021', 'name' => 'Fatima Abdullahi'],
		['reg_no' => 'BFSS/SS1SCI/022', 'name' => 'Joshua Martins'],
		['reg_no' => 'BFSS/SS1SCI/023', 'name' => 'Hauwa Ibrahim']
	],
	'SS 2 Science' => [
		['reg_no' => 'BFSS/SS2SCI/031', 'name' => 'Ruth James'],
		['reg_no' => 'BFSS/SS2SCI/032', 'name' => 'Kelvin Udo'],
		['reg_no' => 'BFSS/SS2SCI/033', 'name' => 'Zainab Aliyu']
	]
];
$previousResults = [
	['session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 1 Science', 'subject' => 'Mathematics', 'status' => 'Submitted'],
	['session' => '2025/2026', 'term' => 'First Term', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'status' => 'Draft']
];
function resultValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>

<style>
	/* Result management module: scoped green dashboard styling for score entry, summaries, and reports. */
	.result-page { --res-primary:#0f766e; --res-primary-dark:#115e59; --res-primary-soft:rgba(15,118,110,.1); --res-success:#16a34a; --res-success-soft:rgba(22,163,74,.12); --res-danger:#dc2626; --res-danger-soft:rgba(220,38,38,.1); --res-warning:#f59e0b; --res-warning-soft:rgba(245,158,11,.14); --res-blue:#2563eb; --res-blue-soft:rgba(37,99,235,.1); --res-ink:#10201d; --res-muted:#64748b; --res-border:rgba(15,118,110,.18); --res-shadow:0 22px 60px rgba(15,23,42,.09); padding-bottom:34px; }
	.result-page .result-hero,.result-page .result-card,.result-page .summary-card,.result-page .table-card { background:rgba(255,255,255,.98); border:1px solid var(--res-border); box-shadow:var(--res-shadow); }
	.result-page .result-hero { position:relative; overflow:hidden; padding:28px; border-radius:26px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98)); }
	.result-page .result-hero:after { content:""; position:absolute; inset:0; background:radial-gradient(circle at top right,rgba(20,184,166,.16),transparent 35%),radial-gradient(circle at bottom left,rgba(37,99,235,.08),transparent 32%); pointer-events:none; }
	.result-page .result-hero>* { position:relative; z-index:1; }
	.result-page .result-kicker,.result-page .field-icon,.result-page .summary-icon,.result-page .status-badge { display:inline-flex; align-items:center; justify-content:center; }
	.result-page .result-kicker { gap:8px; padding:8px 12px; border-radius:999px; background:var(--res-primary-soft); color:var(--res-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
	.result-page h3,.result-page h4,.result-page h5 { color:var(--res-ink); font-weight:900; }
	.result-page .result-card,.result-page .table-card { border-radius:24px; overflow:hidden; margin-bottom:22px; }
	.result-page .result-card { padding:24px; }
	.result-page .form-label { color:var(--res-ink); font-size:13px; font-weight:900; }
	.result-page .field-wrap { position:relative; }
	.result-page .field-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--res-primary); pointer-events:none; }
	.result-page .form-select,.result-page .form-control { min-height:48px; padding-left:42px; border:1px solid rgba(148,163,184,.32); border-radius:15px; font-weight:800; box-shadow:none; }
	.result-page .form-select:focus,.result-page .form-control:focus { border-color:rgba(15,118,110,.72); box-shadow:0 0 0 4px rgba(15,118,110,.12); }
	.result-page .score-input { min-width:72px; padding-left:10px; text-align:center; }
	.result-page .load-btn,.result-page .save-btn,.result-page .submit-btn,.result-page .update-btn { min-height:48px; border:0; border-radius:15px; background:linear-gradient(135deg,var(--res-primary),var(--res-primary-dark)); color:#fff; font-weight:900; box-shadow:0 15px 32px rgba(15,118,110,.24); }
	.result-page .load-btn:hover,.result-page .save-btn:hover,.result-page .submit-btn:hover,.result-page .update-btn:hover { color:#fff; transform:translateY(-2px); }
	.result-page .notice { display:none; gap:8px; align-items:center; padding:12px 14px; border-radius:14px; font-weight:800; margin-bottom:16px; }
	.result-page .notice.is-visible { display:flex; } .result-page .notice.success{color:var(--res-success);background:var(--res-success-soft)} .result-page .notice.error{color:var(--res-danger);background:var(--res-danger-soft)}
	.result-page .summary-card { height:100%; padding:18px; border-radius:20px; }
	.result-page .summary-icon { width:42px; height:42px; border-radius:14px; background:var(--res-primary-soft); color:var(--res-primary); }
	.result-page .summary-icon.success{background:var(--res-success-soft);color:var(--res-success)} .result-page .summary-icon.danger{background:var(--res-danger-soft);color:var(--res-danger)} .result-page .summary-icon.blue{background:var(--res-blue-soft);color:var(--res-blue)}
	.result-page .summary-card h4 { margin:10px 0 2px; font-weight:900; }
	.result-page .table-toolbar { padding:18px 20px; border-bottom:1px solid rgba(148,163,184,.2); background:linear-gradient(180deg,#f8fafc,#fff); }
	.result-page .table-scroll { max-height:560px; overflow:auto; }
	.result-page .result-table { min-width:1080px; margin-bottom:0; }
	.result-page .result-table thead th { position:sticky; top:0; z-index:2; padding:14px 10px; background:linear-gradient(135deg,var(--res-primary),var(--res-primary-dark)); color:#fff; border:0; font-size:12px; font-weight:900; text-transform:uppercase; }
	.result-page .result-table td { padding:11px 10px; vertical-align:middle; border-color:rgba(148,163,184,.2); font-weight:750; }
	.result-page .result-table tbody tr:hover { background:rgba(15,118,110,.04); }
	.result-page .result-table.is-locked input { pointer-events:none; background:#f1f5f9; color:#64748b; }
	.result-page .grade-pill,.result-page .status-badge { gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; white-space:nowrap; }
	.result-page .grade-a,.result-page .status-submitted { background:var(--res-success-soft); color:var(--res-success); }
	.result-page .grade-b,.result-page .grade-c { background:var(--res-blue-soft); color:var(--res-blue); }
	.result-page .grade-d,.result-page .status-draft { background:var(--res-warning-soft); color:#b45309; }
	.result-page .grade-f { background:var(--res-danger-soft); color:var(--res-danger); }
	.result-page .action-row { display:flex; gap:10px; flex-wrap:wrap; }
	.result-page .history-table { min-width:880px; }
	.result-page .history-actions { display:flex; gap:7px; flex-wrap:wrap; }
	@media (max-width:767.98px){ .result-page .result-hero,.result-page .result-card{padding:20px;border-radius:20px}.result-page .action-row,.result-page .action-row .btn,.result-page .history-actions .btn{width:100%} }
</style>

<div class="result-page">
	<!-- Page intro: describes the result entry and submission workflow. -->
	<section class="result-hero">
		<span class="result-kicker"><i class="fa-solid fa-chart-line"></i> Result Management</span>
		<h3 class="mt-3 mb-2">Enter, Calculate, and Submit Student Results</h3>
		<p class="text-muted mb-0">Load your assigned class, enter CA and exam scores, review totals, submit results, and generate printable class result sheets.</p>
	</section>

	<div id="resultNotice" class="notice" role="alert"><i class="fa-solid fa-circle-info"></i><span></span></div>

	<!-- Result entry controls: selected context determines which students are loaded. -->
	<section class="result-card">
		<form id="loadResultForm" class="row g-3 align-items-end" novalidate>
			<div class="col-md-3"><label class="form-label" for="classSelect">Class Selection</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classSelect" required><option value="">Select class</option><?php foreach ($assignedClasses as $class): ?><option value="<?php echo resultValue($class); ?>"><?php echo resultValue($class); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-3"><label class="form-label" for="subjectSelect">Subject Selection</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-book-open"></i></span><select class="form-select" id="subjectSelect" required><option value="">Select subject</option><?php foreach ($subjectsHandled as $subject): ?><option value="<?php echo resultValue($subject); ?>"><?php echo resultValue($subject); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-2"><label class="form-label" for="sessionSelect">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" id="sessionSelect" required><option value="">Session</option><?php foreach ($academicSessions as $session): ?><option value="<?php echo resultValue($session); ?>"><?php echo resultValue($session); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-2"><label class="form-label" for="termSelect">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" id="termSelect" required><option value="">Term</option><?php foreach ($terms as $term): ?><option value="<?php echo resultValue($term); ?>"><?php echo resultValue($term); ?></option><?php endforeach; ?></select></div></div>
			<div class="col-md-2"><button type="submit" class="btn load-btn w-100"><i class="fa-solid fa-users-viewfinder me-2"></i>Load Students</button></div>
		</form>
	</section>

	<!-- Result summary cards: recalculated automatically as scores change. -->
	<section class="row g-3 mb-4" aria-label="Result summary cards">
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4 id="totalStudents">0</h4><p class="text-muted mb-0">Total Students</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-arrow-trend-up"></i></span><h4 id="highestScore">0</h4><p class="text-muted mb-0">Highest Score</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon danger"><i class="fa-solid fa-arrow-trend-down"></i></span><h4 id="lowestScore">0</h4><p class="text-muted mb-0">Lowest Score</p></div></div>
		<div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-percent"></i></span><h4 id="classAverage">0%</h4><p class="text-muted mb-0">Class Average</p></div></div>
	</section>

	<!-- Student result table: score inputs calculate total, grade, and remark instantly. -->
	<section class="table-card mb-4" id="resultTableCard" style="display:none;">
		<div class="table-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h5 class="mb-1" id="resultTableTitle">Result Entry</h5><p class="text-muted mb-0">Enter CA, exam, and practical scores. Totals, grades, and remarks update automatically.</p></div><div class="action-row"><button type="button" class="btn save-btn" id="saveDraftBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Draft</button><button type="button" class="btn submit-btn" id="submitResultBtn"><i class="fa-solid fa-paper-plane me-2"></i>Submit Results</button><button type="button" class="btn update-btn" id="updateResultBtn" style="display:none;"><i class="fa-solid fa-pen-to-square me-2"></i>Update Result</button></div></div>
		<div class="table-scroll"><table class="table result-table align-middle" id="resultTable"><thead><tr><th>Registration Number</th><th>Student Name</th><th>1st CA</th><th>2nd CA</th><th>3rd CA</th><th>Exam</th><th>Practical</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead><tbody id="resultBody"></tbody></table></div>
	</section>

	<!-- Previous results and export controls: manage submitted/draft records and generate reports. -->
	<section class="result-card">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Previous Results</h4><p class="text-muted mb-0">View, edit, print, or export class result sheets.</p></div><div class="action-row"><button type="button" class="btn btn-outline-success" id="exportCsvBtn"><i class="fa-solid fa-file-csv me-2"></i>CSV</button><button type="button" class="btn btn-outline-danger" id="exportPdfBtn"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button><button type="button" class="btn btn-outline-secondary" id="printSheetBtn"><i class="fa-solid fa-print me-2"></i>Print</button></div></div>
		<div class="table-scroll"><table class="table result-table history-table align-middle"><thead><tr><th>Session</th><th>Term</th><th>Class</th><th>Subject</th><th>Status</th><th>Action</th></tr></thead><tbody id="historyBody"></tbody></table></div>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Result management behavior: load students, calculate totals/grades/remarks, save drafts, submit, edit, and export reports.
(function () {
	var schoolName = <?php echo json_encode($schoolName); ?>;
	var teacherName = <?php echo json_encode($teacherName); ?>;
	var studentsByClass = <?php echo json_encode($studentsByClass); ?>;
	var resultHistory = <?php echo json_encode($previousResults); ?>;
	var resultRecords = [];
	var activeContext = null;
	var editingIndex = -1;

	function byId(id) { return document.getElementById(id); }
	function showNotice(type, message) { var n = byId('resultNotice'); n.className = 'notice is-visible ' + type; n.querySelector('span').textContent = message; window.scrollTo({ top: n.offsetTop - 20, behavior: 'smooth' }); }
	function contextKey(ctx) { return [ctx.session, ctx.term, ctx.className, ctx.subject].join('|'); }
	function scoreValue(input) { var v = parseFloat(input.value); return isNaN(v) ? 0 : v; }
	function grade(total) { if (total >= 70) return 'A'; if (total >= 60) return 'B'; if (total >= 50) return 'C'; if (total >= 40) return 'D'; return 'F'; }
	function remark(g) { return { A:'Excellent', B:'Very Good', C:'Good', D:'Fair', F:'Needs Improvement' }[g] || 'Needs Improvement'; }
	function gradeClass(g) { return 'grade-pill grade-' + String(g).toLowerCase(); }
	function percent(value) { return value.toFixed(1).replace('.0', '') + '%'; }

	function readRows() {
		return Array.prototype.map.call(document.querySelectorAll('#resultBody tr'), function (row) {
			var scores = {}; row.querySelectorAll('.score-input').forEach(function (input) { scores[input.getAttribute('data-score')] = scoreValue(input); });
			var total = scores.ca1 + scores.ca2 + scores.ca3 + scores.exam + scores.practical;
			var g = grade(total);
			return { reg_no: row.getAttribute('data-reg'), name: row.getAttribute('data-name'), scores: scores, total: total, grade: g, remark: remark(g) };
		});
	}

	function updateSummary() {
		var rows = readRows();
		var totals = rows.map(function (row) { return row.total; });
		byId('totalStudents').textContent = rows.length;
		byId('highestScore').textContent = totals.length ? Math.max.apply(Math, totals) : 0;
		byId('lowestScore').textContent = totals.length ? Math.min.apply(Math, totals) : 0;
		byId('classAverage').textContent = totals.length ? percent(totals.reduce(function (a, b) { return a + b; }, 0) / totals.length) : '0%';
	}

	function calculateRow(row) {
		var total = 0; row.querySelectorAll('.score-input').forEach(function (input) { total += scoreValue(input); });
		var g = grade(total);
		row.querySelector('.total-cell').textContent = total;
		row.querySelector('.grade-cell').innerHTML = '<span class="' + gradeClass(g) + '">' + g + '</span>';
		row.querySelector('.remark-cell').textContent = remark(g);
		updateSummary();
	}

	function renderStudents(existing) {
		var students = studentsByClass[activeContext.className] || [];
		var existingRows = existing ? existing.rows : [];
		byId('resultBody').innerHTML = students.map(function (student) {
			var saved = existingRows.find(function (row) { return row.reg_no === student.reg_no; });
			var s = saved ? saved.scores : { ca1:'', ca2:'', ca3:'', exam:'', practical:'' };
			return '<tr data-reg="' + student.reg_no + '" data-name="' + student.name + '"><td>' + student.reg_no + '</td><td>' + student.name + '</td>' +
				['ca1','ca2','ca3','exam','practical'].map(function (key) { return '<td><input type="number" min="0" max="100" class="form-control score-input" data-score="' + key + '" value="' + (s[key] === undefined ? '' : s[key]) + '"></td>'; }).join('') +
				'<td class="total-cell">0</td><td class="grade-cell"><span class="grade-pill grade-f">F</span></td><td class="remark-cell">Needs Improvement</td></tr>';
		}).join('');
		document.querySelectorAll('#resultBody tr').forEach(calculateRow);
	}

	function setLocked(locked) {
		byId('resultTable').classList.toggle('is-locked', locked);
		byId('saveDraftBtn').style.display = locked ? 'none' : 'inline-flex';
		byId('submitResultBtn').style.display = locked ? 'none' : 'inline-flex';
		byId('updateResultBtn').style.display = locked ? 'inline-flex' : 'none';
	}

	function validateScores(requireComplete) {
		var ok = true;
		document.querySelectorAll('.score-input').forEach(function (input) {
			var raw = input.value.trim();
			input.classList.remove('is-invalid');
			if (!raw && requireComplete) { ok = false; input.classList.add('is-invalid'); return; }
			if (raw) { var val = parseFloat(raw); if (isNaN(val) || val < 0 || val > 100) { ok = false; input.classList.add('is-invalid'); } }
		});
		if (!ok) { showNotice('error', requireComplete ? 'Complete all scores with valid numeric values before submitting.' : 'Scores must be numeric values between 0 and 100.'); }
		return ok;
	}

	function buildRecord(status) { return { session: activeContext.session, term: activeContext.term, class: activeContext.className, subject: activeContext.subject, status: status, rows: readRows() }; }
	function findRecordIndex(ctx) { var key = contextKey(ctx); return resultRecords.findIndex(function (record) { return contextKey({ session: record.session, term: record.term, className: record.class, subject: record.subject }) === key; }); }

	function statusBadge(status) { var submitted = status === 'Submitted'; return '<span class="status-badge ' + (submitted ? 'status-submitted' : 'status-draft') + '"><i class="fa-solid ' + (submitted ? 'fa-circle-check' : 'fa-file-lines') + '"></i>' + status + '</span>'; }
	function renderHistory() {
		var combined = resultHistory.concat(resultRecords.map(function (r) { return { session:r.session, term:r.term, class:r.class, subject:r.subject, status:r.status }; }));
		byId('historyBody').innerHTML = combined.map(function (item, index) { return '<tr data-index="' + index + '"><td>' + item.session + '</td><td>' + item.term + '</td><td>' + item.class + '</td><td>' + item.subject + '</td><td>' + statusBadge(item.status) + '</td><td><div class="history-actions"><button type="button" class="btn btn-sm btn-outline-primary view-history"><i class="fa-solid fa-eye"></i> View Result</button><button type="button" class="btn btn-sm btn-outline-success edit-history"><i class="fa-solid fa-pen"></i> Edit Result</button><button type="button" class="btn btn-sm btn-outline-secondary print-history"><i class="fa-solid fa-print"></i> Print Result</button></div></td></tr>'; }).join('');
	}

	function csvEscape(v) { return '"' + String(v).replace(/"/g, '""') + '"'; }
	function exportCsv() {
		var rows = [['School Name', schoolName], ['Teacher', teacherName], ['Session', activeContext ? activeContext.session : ''], ['Term', activeContext ? activeContext.term : ''], ['Class', activeContext ? activeContext.className : ''], ['Subject', activeContext ? activeContext.subject : ''], [], ['Registration Number','Student Name','1st CA','2nd CA','3rd CA','Exam','Practical','Total','Grade','Remark']];
		readRows().forEach(function (r) { rows.push([r.reg_no,r.name,r.scores.ca1,r.scores.ca2,r.scores.ca3,r.scores.exam,r.scores.practical,r.total,r.grade,r.remark]); });
		var csv = rows.map(function (row) { return row.map(csvEscape).join(','); }).join('\n');
		var link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type:'text/csv;charset=utf-8;' })); link.download = (activeContext ? activeContext.className.replace(/\s+/g, '_') + '_' + activeContext.subject.replace(/\s+/g, '_') : 'Class') + '_Result_Sheet.csv'; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href);
	}
	function reportHtml() {
		var rows = readRows().map(function (r) { return '<tr><td>' + r.reg_no + '</td><td>' + r.name + '</td><td>' + r.scores.ca1 + '</td><td>' + r.scores.ca2 + '</td><td>' + r.scores.ca3 + '</td><td>' + r.scores.exam + '</td><td>' + r.scores.practical + '</td><td>' + r.total + '</td><td>' + r.grade + '</td><td>' + r.remark + '</td></tr>'; }).join('');
		return '<html><head><title>Class Result Sheet</title><style>@page{size:A4 landscape;margin:12mm}body{font-family:Arial,sans-serif;color:#10201d}h2{text-transform:uppercase;margin:0 0 4px}.meta{margin:3px 0 12px;font-weight:700}table{width:100%;border-collapse:collapse;font-size:11px}td,th{border:1px solid #777;padding:6px;text-align:left}th{background:#0f766e;color:#fff}</style></head><body><h2>' + schoolName + '</h2><div class="meta">Teacher: ' + teacherName + ' | Session: ' + (activeContext ? activeContext.session : '') + ' | Term: ' + (activeContext ? activeContext.term : '') + ' | Class: ' + (activeContext ? activeContext.className : '') + ' | Subject: ' + (activeContext ? activeContext.subject : '') + '</div><table><thead><tr><th>Reg No</th><th>Name</th><th>1st CA</th><th>2nd CA</th><th>3rd CA</th><th>Exam</th><th>Practical</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead><tbody>' + rows + '</tbody></table></body></html>';
	}
	function printReport() { var win = window.open('', '_blank'); win.document.write(reportHtml()); win.document.close(); win.focus(); win.print(); }

	document.addEventListener('DOMContentLoaded', function () {
		renderHistory();
		byId('loadResultForm').addEventListener('submit', function (event) {
			event.preventDefault();
			activeContext = { className: byId('classSelect').value, subject: byId('subjectSelect').value, session: byId('sessionSelect').value, term: byId('termSelect').value };
			if (!activeContext.className || !activeContext.subject || !activeContext.session || !activeContext.term) { showNotice('error', 'Please select class, subject, academic session, and term.'); return; }
			editingIndex = findRecordIndex(activeContext);
			var existing = editingIndex >= 0 ? resultRecords[editingIndex] : null;
			byId('resultTableTitle').textContent = activeContext.className + ' - ' + activeContext.subject + ' Result Entry';
			byId('resultTableCard').style.display = 'block'; renderStudents(existing); setLocked(existing && existing.status === 'Submitted'); showNotice('success', existing ? 'Saved result record loaded.' : 'Students loaded. Enter scores to calculate results.');
		});
		document.addEventListener('input', function (event) { if (event.target.classList.contains('score-input')) { if (parseFloat(event.target.value) > 100) { event.target.value = 100; } calculateRow(event.target.closest('tr')); } });
		byId('saveDraftBtn').addEventListener('click', function () { if (!activeContext || !validateScores(false)) return; var record = buildRecord('Draft'); var idx = findRecordIndex(activeContext); if (idx >= 0) resultRecords[idx] = record; else resultRecords.push(record); renderHistory(); showNotice('success', 'Result saved as draft successfully.'); });
		byId('submitResultBtn').addEventListener('click', function () { if (!activeContext || !validateScores(true)) return; var record = buildRecord('Submitted'); var idx = findRecordIndex(activeContext); if (idx >= 0) resultRecords[idx] = record; else resultRecords.push(record); setLocked(true); renderHistory(); showNotice('success', 'Results submitted successfully and locked from accidental editing.'); });
		byId('updateResultBtn').addEventListener('click', function () { setLocked(false); showNotice('success', 'Editing enabled. Update scores and submit again when approved.'); });
		byId('exportCsvBtn').addEventListener('click', function () { if (!activeContext) { showNotice('error', 'Load a result sheet before exporting CSV.'); return; } exportCsv(); });
		byId('exportPdfBtn').addEventListener('click', function () { if (!activeContext) { showNotice('error', 'Load a result sheet before exporting PDF.'); return; } printReport(); });
		byId('printSheetBtn').addEventListener('click', function () { if (!activeContext) { showNotice('error', 'Load a result sheet before printing.'); return; } printReport(); });
		document.addEventListener('click', function (event) { var row = event.target.closest('#historyBody tr'); if (!row) return; if (event.target.closest('.view-history')) showNotice('success', 'Result preview is ready after loading the matching class result sheet.'); if (event.target.closest('.edit-history')) { showNotice('success', 'Edit permission granted for this result record. Load the matching class, subject, session, and term to update scores.'); } if (event.target.closest('.print-history')) printReport(); });
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>
