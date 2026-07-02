<?php require_once('includes/header.php'); ?>

<style>
	/* Check Result module: scoped styles keep this page independent from other dashboard modules. */
	.check-result-page {
		--result-primary: #15803d;
		--result-primary-dark: #166534;
		--result-primary-soft: rgba(22, 163, 74, 0.11);
		--result-ink: #12201a;
		--result-muted: #64748b;
		--result-border: rgba(148, 163, 184, 0.26);
		--result-danger: #dc2626;
		min-height: calc(100vh - 180px);
		padding: 26px 0 42px;
	}

	.check-result-page .result-shell {
		max-width: 980px;
		margin: 0 auto;
	}

	.check-result-page .result-hero {
		margin-bottom: 22px;
		padding: 24px;
		border: 1px solid var(--result-border);
		border-radius: 24px;
		background: linear-gradient(135deg, rgba(240, 253, 244, 0.96), rgba(255, 255, 255, 0.98));
		box-shadow: 0 20px 55px rgba(15, 23, 42, 0.08);
	}

	.check-result-page .result-kicker,
	.check-result-page .result-icon,
	.check-result-page .select-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.check-result-page .result-kicker {
		gap: 8px;
		margin-bottom: 12px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--result-primary-soft);
		color: var(--result-primary-dark);
		font-size: 12px;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .4px;
	}

	.check-result-page .result-hero h3 {
		margin: 0 0 8px;
		color: var(--result-ink);
		font-size: 26px;
		font-weight: 800;
	}

	.check-result-page .result-hero p {
		max-width: 680px;
		margin: 0;
		color: var(--result-muted);
		font-size: 15px;
	}

	.check-result-page .result-card {
		max-width: 620px;
		margin: 0 auto;
		padding: 28px;
		border: 1px solid var(--result-border);
		border-radius: 26px;
		background: rgba(255, 255, 255, 0.98);
		box-shadow: 0 24px 70px rgba(15, 23, 42, 0.1);
	}

	.check-result-page .result-card-head {
		display: flex;
		align-items: center;
		gap: 14px;
		margin-bottom: 24px;
	}

	.check-result-page .result-icon {
		width: 52px;
		height: 52px;
		border-radius: 18px;
		background: var(--result-primary-soft);
		color: var(--result-primary);
		font-size: 22px;
		flex: 0 0 auto;
	}

	.check-result-page .result-card-head h4 {
		margin: 0;
		color: var(--result-ink);
		font-size: 20px;
		font-weight: 800;
	}

	.check-result-page .result-card-head p {
		margin: 2px 0 0;
		color: var(--result-muted);
		font-size: 13px;
	}

	.check-result-page .form-label {
		margin-bottom: 8px;
		color: var(--result-ink);
		font-size: 13px;
		font-weight: 800;
	}

	.check-result-page .select-wrap {
		position: relative;
	}

	.check-result-page .select-icon {
		position: absolute;
		left: 14px;
		top: 50%;
		width: 22px;
		height: 22px;
		transform: translateY(-50%);
		color: var(--result-primary);
		pointer-events: none;
	}

	.check-result-page .form-select {
		min-height: 52px;
		padding-left: 46px;
		border: 1px solid var(--result-border);
		border-radius: 16px;
		color: var(--result-ink);
		font-weight: 700;
		box-shadow: none;
		transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
	}

	.check-result-page .form-select:focus {
		border-color: rgba(21, 128, 61, 0.72);
		box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.12);
	}

	.check-result-page .form-select:hover {
		transform: translateY(-1px);
		border-color: rgba(21, 128, 61, 0.45);
	}

	.check-result-page .validation-message {
		display: none;
		margin: 2px 0 18px;
		padding: 12px 14px;
		border-radius: 14px;
		background: rgba(220, 38, 38, 0.09);
		color: var(--result-danger);
		font-size: 13px;
		font-weight: 700;
	}

	.check-result-page .validation-message.is-visible {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.check-result-page .check-result-btn {
		width: 100%;
		min-height: 54px;
		border: 0;
		border-radius: 16px;
		background: linear-gradient(135deg, var(--result-primary), var(--result-primary-dark));
		color: #fff;
		font-size: 15px;
		font-weight: 800;
		box-shadow: 0 16px 34px rgba(21, 128, 61, 0.26);
		transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
	}

	.check-result-page .check-result-btn:hover,
	.check-result-page .check-result-btn:focus {
		transform: translateY(-2px);
		box-shadow: 0 20px 42px rgba(21, 128, 61, 0.32);
		filter: saturate(1.08);
		color: #fff;
	}

	.check-result-page .result-note {
		margin-top: 18px;
		color: var(--result-muted);
		font-size: 12px;
		text-align: center;
	}

	@media (max-width: 767.98px) {
		.check-result-page { padding-top: 16px; }
		.check-result-page .result-hero,
		.check-result-page .result-card { border-radius: 20px; padding: 20px; }
		.check-result-page .result-hero h3 { font-size: 22px; }
		.check-result-page .result-card-head { align-items: flex-start; }
	}
</style>

<div class="check-result-page">
	<div class="result-shell">
		<!-- Page intro: explains the result-checking workflow before the student opens the report card. -->
		<section class="result-hero">
			<span class="result-kicker"><i class="fa-solid fa-chart-line"></i> Student Results</span>
			<h3>Check Academic Result</h3>
			<p>Select your academic session, term, and class to open the matching report card. These values are passed forward for future database integration.</p>
		</section>

		<!-- Result checking form: validates selections and redirects to the printable report card. -->
		<section class="result-card" aria-label="Check result form">
			<div class="result-card-head">
				<span class="result-icon"><i class="fa-solid fa-file-circle-check"></i></span>
				<div>
					<h4>Result Details</h4>
					<p>Choose all fields before checking your result.</p>
				</div>
			</div>

			<form id="checkResultForm" novalidate>
				<div class="validation-message" id="resultValidation" role="alert">
					<i class="fa-solid fa-circle-exclamation"></i>
					<span>Please select Academic Session, Term, and Class before checking your result.</span>
				</div>

				<div class="mb-3">
					<label class="form-label" for="academicSession">Academic Session</label>
					<div class="select-wrap">
						<span class="select-icon"><i class="fa-solid fa-calendar-days"></i></span>
						<select class="form-select" id="academicSession" name="session" required>
							<option value="">Select academic session</option>
							<option value="2025/2026">2025/2026</option>
							<option value="2026/2027">2026/2027</option>
						</select>
					</div>
				</div>

				<div class="mb-3">
					<label class="form-label" for="term">Term</label>
					<div class="select-wrap">
						<span class="select-icon"><i class="fa-solid fa-book-open"></i></span>
						<select class="form-select" id="term" name="term" required>
							<option value="">Select term</option>
							<option value="First Term">First Term</option>
							<option value="Second Term">Second Term</option>
							<option value="Third Term">Third Term</option>
						</select>
					</div>
				</div>

				<div class="mb-4">
					<label class="form-label" for="studentClass">Class</label>
					<div class="select-wrap">
						<span class="select-icon"><i class="fa-solid fa-school"></i></span>
						<select class="form-select" id="studentClass" name="class" required>
							<option value="">Select class</option>
							<option value="JSS 1">JSS 1</option>
							<option value="JSS 2">JSS 2</option>
							<option value="JSS 3">JSS 3</option>
							<option value="SS 1">SS 1</option>
							<option value="SS 2">SS 2</option>
							<option value="SS 3">SS 3</option>
						</select>
					</div>
				</div>

				<button type="submit" class="btn check-result-btn">
					<i class="fa-solid fa-magnifying-glass-chart me-2"></i>Check Result
				</button>
			</form>

			<p class="result-note"><i class="fa-solid fa-lock me-1"></i> Result selection is prepared for secure PHP/database validation later.</p>
		</section>
	</div>
</div>
<div>
	<div>

<script data-cfasync="false" type="text/javascript">
	// Result form workflow: validate required dropdowns and pass values to the report-card page.
	(function () {
		var form = document.getElementById('checkResultForm');
		var validation = document.getElementById('resultValidation');
		var session = document.getElementById('academicSession');
		var term = document.getElementById('term');
		var studentClass = document.getElementById('studentClass');

		if (!form || !validation || !session || !term || !studentClass) {
			return;
		}

		function showValidation(message) {
			validation.querySelector('span').textContent = message;
			validation.classList.add('is-visible');
		}

		function hideValidation() {
			validation.classList.remove('is-visible');
		}

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			if (!session.value || !term.value || !studentClass.value) {
				showValidation('Please select Academic Session, Term, and Class before checking your result.');
				return;
			}

			hideValidation();

			var params = new URLSearchParams({
				session: session.value,
				term: term.value,
				class: studentClass.value
			});

			window.location.href = 'report-card.html?' + params.toString();
		});

		[session, term, studentClass].forEach(function (field) {
			field.addEventListener('change', hideValidation);
		});
	}());
</script>

<?php require_once('includes/footer.php'); ?>
