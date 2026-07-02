<?php require_once('includes/header.php'); ?>

<?php
// Teacher profile data: replace these placeholders with database values when backend integration is ready.
$teacher = [
	'profile_picture' => '../assets/img/avatar/avatar-21.jpg',
	'full_name' => 'Mr. Adewale Olumide Johnson',
	'staff_id' => 'BFSS/TCH/2026/018',
	'gender' => 'Male',
	'phone' => '+234 803 456 7890',
	'email' => 'adewale.johnson@bfss.edu.ng',
	'qualification' => 'B.Sc. Mathematics Education, TRCN Certified',
	'department' => 'Science Department',
	'employment_status' => 'Active Full-Time Staff',
	'assigned_classes' => ['JSS 1A', 'JSS 2B', 'SS 1 Science'],
	'subjects' => ['Mathematics', 'Physics', 'Computer Science']
];

$personalFields = [
	['label' => 'Full Name', 'value' => $teacher['full_name'], 'icon' => 'fa-user-tie'],
	['label' => 'Staff ID', 'value' => $teacher['staff_id'], 'icon' => 'fa-id-card'],
	['label' => 'Gender', 'value' => $teacher['gender'], 'icon' => 'fa-venus-mars'],
	['label' => 'Phone Number', 'value' => $teacher['phone'], 'icon' => 'fa-phone'],
	['label' => 'Email Address', 'value' => $teacher['email'], 'icon' => 'fa-envelope']
];

$professionalFields = [
	['label' => 'Qualification', 'value' => $teacher['qualification'], 'icon' => 'fa-graduation-cap'],
	['label' => 'Department', 'value' => $teacher['department'], 'icon' => 'fa-building-columns'],
	['label' => 'Employment Status', 'value' => $teacher['employment_status'], 'icon' => 'fa-circle-check']
];
?>

<!-- Teacher profile module styles: scoped so the teacher dashboard layout remains untouched. -->
<style>
	.teacher-profile-page {
		--teacher-primary: #0f766e;
		--teacher-primary-dark: #115e59;
		--teacher-primary-soft: rgba(15, 118, 110, 0.11);
		--teacher-accent: #2563eb;
		--teacher-accent-soft: rgba(37, 99, 235, 0.1);
		--teacher-success: #16a34a;
		--teacher-ink: #10201d;
		--teacher-muted: #64748b;
		--teacher-border: rgba(15, 118, 110, 0.18);
		--teacher-shadow: 0 22px 60px rgba(15, 23, 42, 0.09);
		padding-bottom: 34px;
	}

	.teacher-profile-page .teacher-hero,
	.teacher-profile-page .teacher-section,
	.teacher-profile-page .teacher-info-card,
	.teacher-profile-page .assignment-panel {
		background: rgba(255, 255, 255, 0.97);
		border: 1px solid var(--teacher-border);
		box-shadow: var(--teacher-shadow);
	}

	.teacher-profile-page .teacher-hero {
		position: relative;
		overflow: hidden;
		padding: 28px;
		border-radius: 26px;
		margin-bottom: 24px;
	}

	.teacher-profile-page .teacher-hero::after {
		content: "";
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top right, rgba(20, 184, 166, .16), transparent 35%), radial-gradient(circle at bottom left, rgba(37, 99, 235, .11), transparent 32%);
		pointer-events: none;
	}

	.teacher-profile-page .teacher-hero > * {
		position: relative;
		z-index: 1;
	}

	.teacher-profile-page .teacher-photo-frame,
	.teacher-profile-page .teacher-status,
	.teacher-profile-page .teacher-meta-chip,
	.teacher-profile-page .teacher-field-icon,
	.teacher-profile-page .assignment-chip,
	.teacher-profile-page .teacher-action-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.teacher-profile-page .teacher-photo-frame {
		width: 132px;
		height: 132px;
		border-radius: 32px;
		background: #fff;
		box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
		flex: 0 0 auto;
	}

	.teacher-profile-page .teacher-photo-frame img {
		width: 118px;
		height: 118px;
		border-radius: 26px;
		object-fit: cover;
	}

	.teacher-profile-page .teacher-status {
		gap: 7px;
		padding: 8px 12px;
		border-radius: 999px;
		background: rgba(22, 163, 74, 0.12);
		color: var(--teacher-success);
		font-size: 12px;
		font-weight: 800;
	}

	.teacher-profile-page .teacher-hero h3 {
		margin: 0;
		color: var(--teacher-ink);
		font-size: 28px;
		font-weight: 900;
	}

	.teacher-profile-page .teacher-staff-id {
		margin: 6px 0 14px;
		color: var(--teacher-muted);
		font-weight: 700;
	}

	.teacher-profile-page .teacher-meta-chip {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: rgba(248, 250, 252, 0.9);
		border: 1px solid rgba(148, 163, 184, .26);
		color: var(--teacher-muted);
		font-size: 12px;
		font-weight: 800;
	}

	.teacher-profile-page .teacher-actions {
		gap: 10px;
	}

	.teacher-profile-page .teacher-action-btn {
		gap: 8px;
		min-height: 44px;
		padding: 10px 16px;
		border-radius: 999px;
		font-weight: 800;
		transition: transform .2s ease, box-shadow .2s ease;
	}

	.teacher-profile-page .teacher-action-btn:hover {
		transform: translateY(-2px);
	}

	.teacher-profile-page .teacher-action-btn.primary {
		background: linear-gradient(135deg, var(--teacher-primary), var(--teacher-primary-dark));
		color: #fff;
		box-shadow: 0 14px 30px rgba(15, 118, 110, .24);
	}

	.teacher-profile-page .teacher-action-btn.secondary {
		background: #fff;
		color: var(--teacher-primary-dark);
		border: 1px solid var(--teacher-border);
	}

	.teacher-profile-page .teacher-section,
	.teacher-profile-page .assignment-panel {
		border-radius: 24px;
		padding: 24px;
	}

	.teacher-profile-page .teacher-section-title {
		margin-bottom: 18px;
		padding-bottom: 15px;
		border-bottom: 1px solid var(--teacher-border);
	}

	.teacher-profile-page .teacher-section-title h5 {
		margin: 0;
		color: var(--teacher-ink);
		font-size: 18px;
		font-weight: 900;
	}

	.teacher-profile-page .teacher-section-title p {
		margin: 4px 0 0;
		color: var(--teacher-muted);
		font-size: 13px;
	}

	.teacher-profile-page .teacher-info-card {
		height: 100%;
		padding: 18px;
		border-radius: 18px;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
		transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
	}

	.teacher-profile-page .teacher-info-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 18px 42px rgba(15, 23, 42, .1);
		border-color: rgba(15, 118, 110, .34);
	}

	.teacher-profile-page .teacher-field-icon {
		width: 42px;
		height: 42px;
		border-radius: 14px;
		background: var(--teacher-primary-soft);
		color: var(--teacher-primary);
		flex: 0 0 auto;
	}

	.teacher-profile-page .teacher-field-label {
		margin-bottom: 4px;
		color: var(--teacher-muted);
		font-size: 13px;
		font-weight: 800;
	}

	.teacher-profile-page .teacher-field-value {
		color: var(--teacher-ink);
		font-weight: 800;
		line-height: 1.45;
		word-break: break-word;
	}

	.teacher-profile-page .assignment-panel {
		height: 100%;
		box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
	}

	.teacher-profile-page .assignment-panel h6 {
		margin: 0 0 12px;
		color: var(--teacher-ink);
		font-weight: 900;
	}

	.teacher-profile-page .assignment-chip-list {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}

	.teacher-profile-page .assignment-chip {
		gap: 7px;
		padding: 9px 12px;
		border-radius: 999px;
		background: var(--teacher-primary-soft);
		color: var(--teacher-primary-dark);
		font-size: 13px;
		font-weight: 800;
	}

	.teacher-profile-page .assignment-chip.subject {
		background: var(--teacher-accent-soft);
		color: var(--teacher-accent);
	}

	@media (max-width: 767.98px) {
		.teacher-profile-page .teacher-hero,
		.teacher-profile-page .teacher-section,
		.teacher-profile-page .assignment-panel {
			padding: 20px;
			border-radius: 20px;
		}

		.teacher-profile-page .teacher-photo-frame {
			width: 104px;
			height: 104px;
			border-radius: 24px;
		}

		.teacher-profile-page .teacher-photo-frame img {
			width: 92px;
			height: 92px;
			border-radius: 20px;
		}

		.teacher-profile-page .teacher-hero h3 {
			font-size: 23px;
		}

		.teacher-profile-page .teacher-action-btn {
			width: 100%;
		}
	}
</style>

<div class="teacher-profile-page">
	<!-- Page title: identifies the teacher profile management module. -->
	<div class="page-title d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
		<div>
			<span class="badge bg-primary-transparent text-primary mb-2">Teacher Profile</span>
			<h5 class="mb-1">Profile Management</h5>
			<p class="text-muted mb-0">View professional records, teaching assignments, and account actions.</p>
		</div>
	</div>

	<!-- Teacher profile hero: photograph, identity, status, and primary actions. -->
	<section class="teacher-hero">
		<div class="row align-items-center row-gap-4">
			<div class="col-xl-8">
				<div class="d-flex align-items-center flex-wrap flex-sm-nowrap gap-3">
					<span class="teacher-photo-frame"><img src="<?php echo $teacher['profile_picture']; ?>" alt="Teacher profile picture"></span>
					<div>
						<div class="d-flex align-items-center flex-wrap gap-2 mb-2">
							<h3><?php echo $teacher['full_name']; ?></h3>
							<span class="teacher-status"><i class="fa-solid fa-circle-check"></i><?php echo $teacher['employment_status']; ?></span>
						</div>
						<p class="teacher-staff-id"><i class="fa-solid fa-id-card me-2"></i><?php echo $teacher['staff_id']; ?></p>
						<div class="d-flex flex-wrap gap-2">
							<span class="teacher-meta-chip"><i class="fa-solid fa-building-columns"></i><?php echo $teacher['department']; ?></span>
							<span class="teacher-meta-chip"><i class="fa-solid fa-graduation-cap"></i><?php echo $teacher['qualification']; ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4">
				<div class="teacher-actions d-flex flex-wrap justify-content-xl-end">
					<a href="edit-profile.php" class="teacher-action-btn primary"><i class="fa-solid fa-user-pen"></i>Edit Profile</a>
					<a href="change-password.php" class="teacher-action-btn secondary"><i class="fa-solid fa-key"></i>Change Password</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Personal information: teacher contact and identity details. -->
	<section class="teacher-section mt-4">
		<div class="teacher-section-title">
			<h5>Personal Details</h5>
			<p>Teacher identity and communication information.</p>
		</div>
		<div class="row g-3">
			<?php foreach ($personalFields as $field): ?>
				<div class="col-md-6 col-xl-4">
					<div class="teacher-info-card">
						<div class="d-flex align-items-start gap-3">
							<span class="teacher-field-icon"><i class="fa-solid <?php echo $field['icon']; ?>"></i></span>
							<div>
								<div class="teacher-field-label"><?php echo $field['label']; ?></div>
								<div class="teacher-field-value"><?php echo $field['value']; ?></div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Professional information: qualification, department, and employment state. -->
	<section class="teacher-section mt-4">
		<div class="teacher-section-title">
			<h5>Professional Details</h5>
			<p>Academic qualification and current employment placement.</p>
		</div>
		<div class="row g-3">
			<?php foreach ($professionalFields as $field): ?>
				<div class="col-md-6 col-xl-4">
					<div class="teacher-info-card">
						<div class="d-flex align-items-start gap-3">
							<span class="teacher-field-icon"><i class="fa-solid <?php echo $field['icon']; ?>"></i></span>
							<div>
								<div class="teacher-field-label"><?php echo $field['label']; ?></div>
								<div class="teacher-field-value"><?php echo $field['value']; ?></div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Teaching assignments: multiple classes and subjects are displayed as reusable chips. -->
	<section class="teacher-section mt-4">
		<div class="teacher-section-title">
			<h5>Teaching Assignments</h5>
			<p>Classes and subjects currently assigned to this teacher.</p>
		</div>
		<div class="row g-3">
			<div class="col-lg-6">
				<div class="assignment-panel">
					<h6><i class="fa-solid fa-chalkboard-user me-2 text-success"></i>Assigned Classes</h6>
					<div class="assignment-chip-list">
						<?php foreach ($teacher['assigned_classes'] as $class): ?>
							<span class="assignment-chip"><i class="fa-solid fa-school"></i><?php echo $class; ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="assignment-panel">
					<h6><i class="fa-solid fa-book-open-reader me-2 text-primary"></i>Subjects Handled</h6>
					<div class="assignment-chip-list">
						<?php foreach ($teacher['subjects'] as $subject): ?>
							<span class="assignment-chip subject"><i class="fa-solid fa-book"></i><?php echo $subject; ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

</div>
</div>

<?php require_once('includes/footer.php'); ?>
