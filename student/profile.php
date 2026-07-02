<?php require_once('includes/header.php'); ?>

<?php
// Student profile data: replace these values with database records when backend integration is ready.
$student = [
	'profile_picture' => '../assets/img/students/student-01.jpg',
	'full_name' => 'Ajiboye Isreal Oluwaseun',
	'student_id' => 'REG/STD/2026/0142',
	'gender' => 'Male',
	'date_of_birth' => '14 March 2009',
	'class' => 'SS 2',
	'department' => 'Science / Section A',
	'academic_session' => '2025/2026',
	'guardian_name' => 'Mrs. Ajiboye Grace',
	'guardian_phone' => '+234 801 234 5678',
	'email' => 'ajiboye.isreal@example.com',
	'home_address' => '24 Unity Avenue, Ikeja, Lagos State',
	'admission_date' => '12 September 2022',
	'status' => 'Active'
];

$profileFields = [
	['label' => 'Full Name', 'value' => $student['full_name'], 'icon' => 'fa-user'],
	['label' => 'Student ID / Registration Number', 'value' => $student['student_id'], 'icon' => 'fa-id-card'],
	['label' => 'Gender', 'value' => $student['gender'], 'icon' => 'fa-venus-mars'],
	['label' => 'Date of Birth', 'value' => $student['date_of_birth'], 'icon' => 'fa-cake-candles'],
	['label' => 'Class', 'value' => $student['class'], 'icon' => 'fa-graduation-cap'],
	['label' => 'Department', 'value' => $student['department'], 'icon' => 'fa-layer-group'],
	['label' => 'Academic Session', 'value' => $student['academic_session'], 'icon' => 'fa-calendar-days'],
	['label' => 'Admission Date', 'value' => $student['admission_date'], 'icon' => 'fa-calendar-check']
];

$contactFields = [
	['label' => 'Parent / Guardian Name', 'value' => $student['guardian_name'], 'icon' => 'fa-user-shield'],
	['label' => 'Parent Phone Number', 'value' => $student['guardian_phone'], 'icon' => 'fa-phone'],
	['label' => 'Email Address', 'value' => $student['email'], 'icon' => 'fa-envelope'],
	['label' => 'Home Address', 'value' => $student['home_address'], 'icon' => 'fa-location-dot']
];
?>

<!-- Student profile page styles: scoped to avoid affecting other dashboard pages. -->
<style>
	.student-profile-page {
		--profile-primary: #2563eb;
		--profile-primary-soft: rgba(37, 99, 235, 0.1);
		--profile-success: #16a34a;
		--profile-success-soft: rgba(22, 163, 74, 0.12);
		--profile-ink: #111827;
		--profile-muted: #64748b;
		--profile-border: rgba(148, 163, 184, 0.24);
	}

	.student-profile-page .profile-hero,
	.student-profile-page .profile-section,
	.student-profile-page .profile-field-card {
		background: rgba(255, 255, 255, 0.96);
		border: 1px solid var(--profile-border);
		border-radius: 22px;
		box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
		backdrop-filter: blur(14px);
	}

	.student-profile-page .profile-hero {
		position: relative;
		overflow: hidden;
		padding: 28px;
		animation: profileFadeUp .5s ease both;
	}

	.student-profile-page .profile-hero::before {
		content: "";
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 34%), radial-gradient(circle at bottom left, rgba(22, 163, 74, 0.12), transparent 30%);
		pointer-events: none;
	}

	.student-profile-page .profile-photo {
		width: 116px;
		height: 116px;
		border-radius: 28px;
		object-fit: cover;
		border: 5px solid #fff;
		box-shadow: 0 16px 36px rgba(15, 23, 42, 0.16);
	}

	.student-profile-page .profile-status-pill,
	.student-profile-page .profile-meta-pill {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		border-radius: 999px;
		font-size: 13px;
		font-weight: 600;
	}

	.student-profile-page .profile-status-pill {
		padding: 9px 14px;
		background: var(--profile-success-soft);
		color: var(--profile-success);
	}

	.student-profile-page .profile-meta-pill {
		padding: 8px 12px;
		background: rgba(248, 250, 252, 0.9);
		border: 1px solid var(--profile-border);
		color: var(--profile-muted);
	}

	.student-profile-page .profile-section {
		padding: 24px;
		animation: profileFadeUp .55s ease both;
	}

	.student-profile-page .profile-section-title {
		border-bottom: 1px solid var(--profile-border);
		padding-bottom: 16px;
		margin-bottom: 18px;
	}

	.student-profile-page .profile-field-card {
		height: 100%;
		padding: 18px;
		box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
		transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
	}

	.student-profile-page .profile-field-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1);
		border-color: rgba(37, 99, 235, 0.28);
	}

	.student-profile-page .profile-field-icon {
		width: 42px;
		height: 42px;
		border-radius: 14px;
		background: var(--profile-primary-soft);
		color: var(--profile-primary);
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.student-profile-page .profile-field-label {
		color: var(--profile-muted);
		font-size: 13px;
		font-weight: 600;
		margin-bottom: 4px;
	}

	.student-profile-page .profile-field-value {
		color: var(--profile-ink);
		font-weight: 700;
		line-height: 1.45;
		word-break: break-word;
	}

	@keyframes profileFadeUp {
		from { opacity: 0; transform: translateY(12px); }
		to { opacity: 1; transform: translateY(0); }
	}

	@media (max-width: 767.98px) {
		.student-profile-page .profile-hero,
		.student-profile-page .profile-section { padding: 20px; }
		.student-profile-page .profile-photo { width: 92px; height: 92px; border-radius: 22px; }
	}
</style>

<div class="row student-profile-page">
	<div class="col-lg-12 mx-auto">
		<!-- Page title: identifies the student profile module. -->
		<div class="page-title d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
			<div>
				<span class="badge bg-primary-transparent text-primary mb-2">Student Profile</span>
				<h5 class="mb-1">Profile Information</h5>
				<p class="text-muted mb-0">Student biodata and guardian contact details.</p>
			</div>
			<a href="edit-profile.php" class="btn btn-success rounded-pill d-inline-flex align-items-center"><i class="fa-solid fa-user-pen me-2"></i>Edit Profile</a>
		</div>

		<!-- Profile hero: displays the student photo and primary identity information. -->
		<section class="profile-hero mb-4">
			<div class="position-relative">
				<div class="row align-items-center row-gap-4">
					<div class="col-xl-8">
						<div class="d-flex align-items-center flex-wrap flex-sm-nowrap gap-3">
							<img src="<?php echo $student['profile_picture']; ?>" class="profile-photo" alt="Student profile picture">
							<div>
								<div class="d-flex align-items-center flex-wrap gap-2 mb-2">
									<h4 class="mb-0"><?php echo $student['full_name']; ?></h4>
									<span class="profile-status-pill"><i class="fa-solid fa-circle-check"></i><?php echo $student['status']; ?></span>
								</div>
								<p class="text-muted mb-3"><?php echo $student['student_id']; ?></p>
								<div class="d-flex flex-wrap gap-2">
									<span class="profile-meta-pill"><i class="fa-solid fa-graduation-cap"></i><?php echo $student['class']; ?></span>
									<span class="profile-meta-pill"><i class="fa-solid fa-layer-group"></i><?php echo $student['department']; ?></span>
									<span class="profile-meta-pill"><i class="fa-solid fa-calendar-days"></i><?php echo $student['academic_session']; ?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-4">
						<div class="text-xl-end">
							<span class="profile-meta-pill mb-2"><i class="fa-solid fa-user-shield"></i>Guardian</span>
							<h6 class="mb-1"><?php echo $student['guardian_name']; ?></h6>
							<p class="text-muted mb-0"><?php echo $student['guardian_phone']; ?></p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- Academic information: reusable cards render database-ready profile fields. -->
		<section class="profile-section mb-4">
			<div class="profile-section-title d-flex align-items-center justify-content-between flex-wrap row-gap-2">
				<div>
					<h5 class="fs-18 mb-1">Academic Details</h5>
					<p class="text-muted mb-0">Core school records for this student.</p>
				</div>
			</div>
			<div class="row g-3">
				<?php foreach ($profileFields as $field): ?>
					<div class="col-md-6 col-xl-4">
						<div class="profile-field-card">
							<div class="d-flex align-items-start gap-3">
								<span class="profile-field-icon"><i class="fa-solid <?php echo $field['icon']; ?>"></i></span>
								<div>
									<div class="profile-field-label"><?php echo $field['label']; ?></div>
									<div class="profile-field-value"><?php echo $field['value']; ?></div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Contact information: guardian and address fields kept separate for clarity. -->
		<section class="profile-section mb-4">
			<div class="profile-section-title d-flex align-items-center justify-content-between flex-wrap row-gap-2">
				<div>
					<h5 class="fs-18 mb-1">Contact & Guardian Details</h5>
					<p class="text-muted mb-0">Family contact and student communication information.</p>
				</div>
			</div>
			<div class="row g-3">
				<?php foreach ($contactFields as $field): ?>
					<div class="col-md-6">
						<div class="profile-field-card">
							<div class="d-flex align-items-start gap-3">
								<span class="profile-field-icon"><i class="fa-solid <?php echo $field['icon']; ?>"></i></span>
								<div>
									<div class="profile-field-label"><?php echo $field['label']; ?></div>
									<div class="profile-field-value"><?php echo $field['value']; ?></div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
</div>
</div>
</div>

<?php require_once('includes/footer.php'); ?>
