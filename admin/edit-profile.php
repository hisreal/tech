<?php require_once('includes/header.php'); ?>

<?php
// Placeholder teacher values. Replace with database values when profile persistence is connected.
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

$classOptions = ['JSS 1A', 'JSS 1B', 'JSS 2A', 'JSS 2B', 'JSS 3A', 'SS 1 Science', 'SS 2 Science', 'SS 3 Science'];
$subjectOptions = ['Mathematics', 'Physics', 'Computer Science', 'Basic Science', 'Chemistry', 'Further Mathematics'];
?>

<style>
	/* Teacher edit profile: scoped form styling for future database integration. */
	.teacher-edit-page {
		--edit-primary: #0f766e;
		--edit-primary-dark: #115e59;
		--edit-primary-soft: rgba(15, 118, 110, .11);
		--edit-danger: #dc2626;
		--edit-ink: #10201d;
		--edit-muted: #64748b;
		--edit-border: rgba(15, 118, 110, .18);
		--edit-shadow: 0 22px 60px rgba(15, 23, 42, .09);
		padding-bottom: 34px;
	}

	.teacher-edit-page .teacher-edit-hero,
	.teacher-edit-page .teacher-edit-card {
		background: rgba(255, 255, 255, .97);
		border: 1px solid var(--edit-border);
		box-shadow: var(--edit-shadow);
	}

	.teacher-edit-page .teacher-edit-hero {
		padding: 26px;
		border-radius: 24px;
		margin-bottom: 24px;
	}

	.teacher-edit-page .teacher-edit-card {
		max-width: 1040px;
		margin: 0 auto;
		padding: 28px;
		border-radius: 26px;
	}

	.teacher-edit-page .edit-kicker,
	.teacher-edit-page .field-icon,
	.teacher-edit-page .teacher-photo-preview {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.teacher-edit-page .edit-kicker {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--edit-primary-soft);
		color: var(--edit-primary-dark);
		font-size: 13px;
		font-weight: 800;
	}

	.teacher-edit-page h3 {
		margin: 12px 0 6px;
		color: var(--edit-ink);
		font-size: 26px;
		font-weight: 900;
	}

	.teacher-edit-page .teacher-photo-preview {
		width: 126px;
		height: 126px;
		border-radius: 30px;
		background: #fff;
		box-shadow: 0 16px 36px rgba(15, 23, 42, .14);
	}

	.teacher-edit-page .teacher-photo-preview img {
		width: 112px;
		height: 112px;
		border-radius: 24px;
		object-fit: cover;
	}

	.teacher-edit-page .section-heading {
		padding-bottom: 14px;
		margin-bottom: 20px;
		border-bottom: 1px solid var(--edit-border);
	}

	.teacher-edit-page .section-heading h5 {
		margin: 0;
		font-size: 18px;
		font-weight: 900;
		color: var(--edit-ink);
	}

	.teacher-edit-page .section-heading p {
		margin: 4px 0 0;
		color: var(--edit-muted);
		font-size: 13px;
	}

	.teacher-edit-page .form-label {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 8px;
		color: var(--edit-ink);
		font-size: 13px;
		font-weight: 800;
	}

	.teacher-edit-page .field-icon {
		width: 28px;
		height: 28px;
		border-radius: 10px;
		background: var(--edit-primary-soft);
		color: var(--edit-primary);
		font-size: 12px;
	}

	.teacher-edit-page .form-control,
	.teacher-edit-page .form-select {
		min-height: 50px;
		border: 1px solid rgba(148, 163, 184, .32);
		border-radius: 16px;
		font-weight: 700;
		box-shadow: none;
		transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
	}

	.teacher-edit-page .form-control:focus,
	.teacher-edit-page .form-select:focus {
		border-color: rgba(15, 118, 110, .72);
		box-shadow: 0 0 0 4px rgba(15, 118, 110, .12);
	}

	.teacher-edit-page .form-control:hover,
	.teacher-edit-page .form-select:hover {
		transform: translateY(-1px);
	}

	.teacher-edit-page .is-invalid {
		border-color: var(--edit-danger) !important;
	}

	.teacher-edit-page .save-teacher-btn {
		min-height: 52px;
		border: 0;
		border-radius: 16px;
		background: linear-gradient(135deg, var(--edit-primary), var(--edit-primary-dark));
		color: #fff;
		font-weight: 900;
		box-shadow: 0 16px 34px rgba(15, 118, 110, .25);
	}

	.teacher-edit-page .save-teacher-btn:hover {
		color: #fff;
		transform: translateY(-2px);
	}

	@media (max-width: 767.98px) {
		.teacher-edit-page .teacher-edit-hero,
		.teacher-edit-page .teacher-edit-card { padding: 20px; border-radius: 20px; }
	}
</style>

<div class="teacher-edit-page">
	<!-- Edit page intro: explains the profile fields available for update. -->
	<section class="teacher-edit-hero">
		<span class="edit-kicker"><i class="fa-solid fa-user-pen"></i> Teacher Profile</span>
		<h3>Edit Profile</h3>
		<p class="text-muted mb-0">Update teacher contact details, professional records, assigned classes, subjects, and profile picture.</p>
	</section>

	<!-- Teacher edit form: client-side validation only until backend saving is connected. -->
	<section class="teacher-edit-card">
		<div class="row g-4 align-items-start mb-4">
			<div class="col-md-3 text-center">
				<span class="teacher-photo-preview mb-3"><img id="teacherPhotoPreview" src="<?php echo $teacher['profile_picture']; ?>" alt="Teacher profile picture"></span>
				<label class="form-label justify-content-center" for="profilePicture"><span class="field-icon"><i class="fa-solid fa-camera"></i></span>Profile Picture</label>
				<input type="file" class="form-control" id="profilePicture" accept="image/*">
			</div>
			<div class="col-md-9">
				<div class="section-heading">
					<h5>Teacher Profile Details</h5>
					<p>Staff ID is read-only. Other profile fields can be connected to the teacher database later.</p>
				</div>
				<div id="teacherProfileNotice" class="portal-alert" role="alert"></div>
			</div>
		</div>

		<form id="teacherEditProfileForm" novalidate>
			<div class="row g-3">
				<div class="col-md-6">
					<label class="form-label" for="fullName"><span class="field-icon"><i class="fa-solid fa-user-tie"></i></span>Full Name</label>
					<input type="text" class="form-control" id="fullName" value="<?php echo $teacher['full_name']; ?>" required>
					<div class="invalid-feedback">Full name is required.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="staffId"><span class="field-icon"><i class="fa-solid fa-id-card"></i></span>Staff ID</label>
					<input type="text" class="form-control" id="staffId" value="<?php echo $teacher['staff_id']; ?>" readonly>
				</div>
				<div class="col-md-4">
					<label class="form-label" for="gender"><span class="field-icon"><i class="fa-solid fa-venus-mars"></i></span>Gender</label>
					<select class="form-select" id="gender" required>
						<option value="">Select gender</option>
						<option value="Male" <?php echo $teacher['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
						<option value="Female" <?php echo $teacher['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
					</select>
					<div class="invalid-feedback">Gender is required.</div>
				</div>
				<div class="col-md-4">
					<label class="form-label" for="phone"><span class="field-icon"><i class="fa-solid fa-phone"></i></span>Phone Number</label>
					<input type="tel" class="form-control" id="phone" value="<?php echo $teacher['phone']; ?>" required>
					<div class="invalid-feedback">Valid phone number is required.</div>
				</div>
				<div class="col-md-4">
					<label class="form-label" for="email"><span class="field-icon"><i class="fa-solid fa-envelope"></i></span>Email Address</label>
					<input type="email" class="form-control" id="email" value="<?php echo $teacher['email']; ?>" required>
					<div class="invalid-feedback">Valid email address is required.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="qualification"><span class="field-icon"><i class="fa-solid fa-graduation-cap"></i></span>Qualification</label>
					<input type="text" class="form-control" id="qualification" value="<?php echo $teacher['qualification']; ?>" required>
					<div class="invalid-feedback">Qualification is required.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="department"><span class="field-icon"><i class="fa-solid fa-building-columns"></i></span>Department</label>
					<input type="text" class="form-control" id="department" value="<?php echo $teacher['department']; ?>" required>
					<div class="invalid-feedback">Department is required.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="classes"><span class="field-icon"><i class="fa-solid fa-chalkboard-user"></i></span>Classes Assigned</label>
					<select class="form-select" id="classes" multiple required>
						<?php foreach ($classOptions as $class): ?>
							<option value="<?php echo $class; ?>" <?php echo in_array($class, $teacher['assigned_classes'], true) ? 'selected' : ''; ?>><?php echo $class; ?></option>
						<?php endforeach; ?>
					</select>
					<div class="invalid-feedback">Select at least one class.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="subjects"><span class="field-icon"><i class="fa-solid fa-book-open-reader"></i></span>Subjects Handled</label>
					<select class="form-select" id="subjects" multiple required>
						<?php foreach ($subjectOptions as $subject): ?>
							<option value="<?php echo $subject; ?>" <?php echo in_array($subject, $teacher['subjects'], true) ? 'selected' : ''; ?>><?php echo $subject; ?></option>
						<?php endforeach; ?>
					</select>
					<div class="invalid-feedback">Select at least one subject.</div>
				</div>
			</div>
			<div class="d-flex justify-content-end mt-4">
				<button type="submit" class="btn save-teacher-btn px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Save Teacher Profile</button>
			</div>
		</form>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Teacher edit profile behavior: preview image and validate editable fields before backend saving.
(function () {
	function byId(id) { return document.getElementById(id); }
	function mark(field, invalid) { field.classList.toggle('is-invalid', invalid); }
	function selectedCount(select) { return Array.prototype.filter.call(select.options, function (option) { return option.selected; }).length; }
	function phoneOk(value) { return /^[+]?[-0-9\s()]{7,20}$/.test(value.trim()); }
	function showNotice(type, message) {
		var notice = byId('teacherProfileNotice');
		notice.className = 'portal-alert ' + type;
		notice.textContent = message;
	}

	document.addEventListener('DOMContentLoaded', function () {
		var form = byId('teacherEditProfileForm');
		var picture = byId('profilePicture');
		var preview = byId('teacherPhotoPreview');
		if (!form) { return; }

		if (picture && preview) {
			picture.addEventListener('change', function () {
				var file = picture.files && picture.files[0];
				if (file) { preview.src = URL.createObjectURL(file); }
			});
		}

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			var fields = [byId('fullName'), byId('gender'), byId('phone'), byId('email'), byId('qualification'), byId('department'), byId('classes'), byId('subjects')];
			var hasError = false;
			fields.forEach(function (field) { mark(field, false); });

			if (!byId('fullName').value.trim()) { mark(byId('fullName'), true); hasError = true; }
			if (!byId('gender').value) { mark(byId('gender'), true); hasError = true; }
			if (!phoneOk(byId('phone').value)) { mark(byId('phone'), true); hasError = true; }
			if (!byId('email').value || !byId('email').checkValidity()) { mark(byId('email'), true); hasError = true; }
			if (!byId('qualification').value.trim()) { mark(byId('qualification'), true); hasError = true; }
			if (!byId('department').value.trim()) { mark(byId('department'), true); hasError = true; }
			if (selectedCount(byId('classes')) < 1) { mark(byId('classes'), true); hasError = true; }
			if (selectedCount(byId('subjects')) < 1) { mark(byId('subjects'), true); hasError = true; }

			if (hasError) {
				showNotice('error', 'Please correct the highlighted teacher profile fields.');
				return;
			}

			showNotice('success', 'Teacher profile validated successfully. Backend saving can be connected here.');
		});

		form.querySelectorAll('input, select').forEach(function (field) {
			field.addEventListener('input', function () { mark(field, false); });
			field.addEventListener('change', function () { mark(field, false); });
		});
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>
