<?php require_once('includes/header.php'); ?>

<?php
// Placeholder student profile values. Replace with database records when backend integration is ready.
$student = [
	'profile_picture' => '../assets/img/students/student-01.jpg',
	'full_name' => 'Ajiboye Isreal Oluwaseun',
	'student_id' => 'REG/STD/2026/0142',
	'class' => 'SS 2',
	'section' => 'Science / Section A',
	'term' => 'Second Term',
	'gender' => 'Male',
	'date_of_birth' => '2009-03-14',
	'email' => 'ajiboye.isreal@example.com',
	'phone' => '+2348012345678',
	'home_address' => '24 Unity Avenue, Ikeja, Lagos State',
	'guardian_name' => 'Mrs. Ajiboye Grace',
	'guardian_phone' => '+2348012345678'
];
?>

<style>
	/* Edit profile module: scoped styles keep the form independent from other student pages. */
	.edit-profile-page {
		--edit-primary: #15803d;
		--edit-primary-dark: #065f46;
		--edit-primary-soft: rgba(21, 128, 61, 0.1);
		--edit-danger: #dc2626;
		--edit-success: #16a34a;
		--edit-ink: #102018;
		--edit-muted: #64748b;
		--edit-border: rgba(21, 128, 61, 0.16);
		--edit-shadow: 0 22px 60px rgba(15, 23, 42, 0.09);
		padding-bottom: 34px;
	}

	.edit-profile-page .edit-profile-hero,
	.edit-profile-page .edit-profile-card {
		background: rgba(255, 255, 255, 0.97);
		border: 1px solid var(--edit-border);
		box-shadow: var(--edit-shadow);
	}

	.edit-profile-page .edit-profile-hero {
		position: relative;
		overflow: hidden;
		padding: 26px;
		border-radius: 24px;
		margin-bottom: 24px;
	}

	.edit-profile-page .edit-profile-hero::after {
		content: "";
		position: absolute;
		inset: 0;
		background: radial-gradient(circle at top right, rgba(34, 197, 94, 0.16), transparent 34%), radial-gradient(circle at bottom left, rgba(20, 83, 45, 0.1), transparent 34%);
		pointer-events: none;
	}

	.edit-profile-page .edit-profile-hero > * {
		position: relative;
		z-index: 1;
	}

	.edit-profile-page .edit-kicker,
	.edit-profile-page .profile-photo-wrap,
	.edit-profile-page .field-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.edit-profile-page .edit-kicker {
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--edit-primary-soft);
		color: var(--edit-primary-dark);
		font-size: 13px;
		font-weight: 800;
	}

	.edit-profile-page .edit-profile-hero h3 {
		margin: 12px 0 6px;
		color: var(--edit-ink);
		font-size: 26px;
		font-weight: 800;
	}

	.edit-profile-page .edit-profile-hero p {
		max-width: 720px;
		margin: 0;
		color: var(--edit-muted);
	}

	.edit-profile-page .edit-profile-card {
		max-width: 980px;
		margin: 0 auto;
		padding: 28px;
		border-radius: 26px;
	}

	.edit-profile-page .profile-photo-panel {
		padding: 18px;
		border: 1px solid var(--edit-border);
		border-radius: 22px;
		background: linear-gradient(180deg, #f8fafc, #fff);
	}

	.edit-profile-page .profile-photo-wrap {
		width: 126px;
		height: 126px;
		border-radius: 30px;
		background: #fff;
		box-shadow: 0 16px 36px rgba(15, 23, 42, 0.14);
	}

	.edit-profile-page .profile-photo-wrap img {
		width: 112px;
		height: 112px;
		border-radius: 24px;
		object-fit: cover;
	}

	.edit-profile-page .section-heading {
		padding-bottom: 14px;
		margin-bottom: 20px;
		border-bottom: 1px solid var(--edit-border);
	}

	.edit-profile-page .section-heading h5 {
		margin: 0;
		color: var(--edit-ink);
		font-size: 18px;
		font-weight: 800;
	}

	.edit-profile-page .section-heading p {
		margin: 4px 0 0;
		color: var(--edit-muted);
		font-size: 13px;
	}

	.edit-profile-page .form-label {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 8px;
		color: var(--edit-ink);
		font-size: 13px;
		font-weight: 800;
	}

	.edit-profile-page .field-icon {
		width: 28px;
		height: 28px;
		border-radius: 10px;
		background: var(--edit-primary-soft);
		color: var(--edit-primary);
		font-size: 12px;
		flex: 0 0 auto;
	}

	.edit-profile-page .form-control,
	.edit-profile-page .form-select {
		min-height: 50px;
		border: 1px solid rgba(148, 163, 184, 0.32);
		border-radius: 16px;
		color: var(--edit-ink);
		font-weight: 700;
		box-shadow: none;
		transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
	}

	.edit-profile-page textarea.form-control {
		min-height: 104px;
		resize: vertical;
	}

	.edit-profile-page .form-control:focus,
	.edit-profile-page .form-select:focus {
		border-color: rgba(21, 128, 61, 0.7);
		box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.12);
	}

	.edit-profile-page .form-control:hover,
	.edit-profile-page .form-select:hover {
		transform: translateY(-1px);
		border-color: rgba(21, 128, 61, 0.44);
	}

	.edit-profile-page .form-control[readonly] {
		background: #f8fafc;
		color: #475569;
		cursor: not-allowed;
	}

	.edit-profile-page .portal-alert {
		border-radius: 16px;
		margin-bottom: 18px;
	}

	.edit-profile-page .is-invalid {
		border-color: var(--edit-danger) !important;
	}

	.edit-profile-page .invalid-feedback {
		font-size: 12px;
		font-weight: 700;
	}

	.edit-profile-page .update-profile-btn {
		min-height: 52px;
		border: 0;
		border-radius: 16px;
		background: linear-gradient(135deg, var(--edit-primary), var(--edit-primary-dark));
		color: #fff;
		font-size: 15px;
		font-weight: 800;
		box-shadow: 0 16px 34px rgba(21, 128, 61, 0.26);
		transition: transform .2s ease, box-shadow .2s ease;
	}

	.edit-profile-page .update-profile-btn:hover,
	.edit-profile-page .update-profile-btn:focus {
		transform: translateY(-2px);
		box-shadow: 0 20px 42px rgba(21, 128, 61, 0.32);
		color: #fff;
	}

	@media (max-width: 767.98px) {
		.edit-profile-page .edit-profile-hero,
		.edit-profile-page .edit-profile-card { padding: 20px; border-radius: 20px; }
		.edit-profile-page .edit-profile-hero h3 { font-size: 22px; }
		.edit-profile-page .profile-photo-wrap { width: 104px; height: 104px; border-radius: 24px; }
		.edit-profile-page .profile-photo-wrap img { width: 92px; height: 92px; border-radius: 20px; }
	}
</style>

<div class="edit-profile-page">
	<!-- Page intro: explains that academic fields are protected and contact fields can be updated. -->
	<section class="edit-profile-hero">
		<span class="edit-kicker"><i class="fa-solid fa-user-pen"></i> Student Profile</span>
		<h3>Edit Profile</h3>
		<p>View your official student details and update your personal contact information for school communication.</p>
	</section>

	<!-- Edit profile form: client-side validation only until backend persistence is connected. -->
	<section class="edit-profile-card">
		<div class="row g-4 align-items-start mb-4">
			<div class="col-md-4 col-lg-3">
				<div class="profile-photo-panel text-center">
					<span class="profile-photo-wrap mb-3">
						<img src="<?php echo $student['profile_picture']; ?>" alt="Student profile picture">
					</span>
					<h6 class="mb-1"><?php echo $student['full_name']; ?></h6>
					<p class="text-muted mb-0 small">Profile picture is managed by the school.</p>
				</div>
			</div>
			<div class="col-md-8 col-lg-9">
				<div class="section-heading">
					<h5>Profile Information</h5>
					<p>Fields marked as official records are read-only and can only be changed by the school administrator.</p>
				</div>
				<div id="profileNotice" class="portal-alert" role="alert"></div>
			</div>
		</div>

		<form id="editProfileForm" novalidate>
			<div class="row g-3">
				<div class="col-md-6">
					<label class="form-label" for="fullName"><span class="field-icon"><i class="fa-solid fa-user"></i></span>Full Name</label>
					<input type="text" class="form-control" id="fullName" value="<?php echo $student['full_name']; ?>" readonly>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="studentId"><span class="field-icon"><i class="fa-solid fa-id-card"></i></span>Student ID / Registration Number</label>
					<input type="text" class="form-control" id="studentId" value="<?php echo $student['student_id']; ?>" readonly>
				</div>
				<div class="col-md-4">
					<label class="form-label" for="studentClass"><span class="field-icon"><i class="fa-solid fa-school"></i></span>Class</label>
					<input type="text" class="form-control" id="studentClass" value="<?php echo $student['class']; ?>" readonly>
				</div>
				<div class="col-md-4">
					<label class="form-label" for="section"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span>Section</label>
					<input type="text" class="form-control" id="section" value="<?php echo $student['section']; ?>" readonly>
				</div>
				<div class="col-md-4">
					<label class="form-label" for="term"><span class="field-icon"><i class="fa-solid fa-book-open"></i></span>Term</label>
					<input type="text" class="form-control" id="term" value="<?php echo $student['term']; ?>" readonly>
				</div>

				<div class="col-md-6">
					<label class="form-label" for="gender"><span class="field-icon"><i class="fa-solid fa-venus-mars"></i></span>Gender</label>
					<select class="form-select" id="gender" name="gender" required>
						<option value="">Select gender</option>
						<option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
						<option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
					</select>
					<div class="invalid-feedback">Please select your gender.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="dateOfBirth"><span class="field-icon"><i class="fa-solid fa-cake-candles"></i></span>Date of Birth</label>
					<input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" value="<?php echo $student['date_of_birth']; ?>" required>
					<div class="invalid-feedback">Please enter a valid date of birth.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="email"><span class="field-icon"><i class="fa-solid fa-envelope"></i></span>Email Address</label>
					<input type="email" class="form-control" id="email" name="email" value="<?php echo $student['email']; ?>" placeholder="student@example.com" required>
					<div class="invalid-feedback">Please enter a valid email address.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="phone"><span class="field-icon"><i class="fa-solid fa-phone"></i></span>Phone Number</label>
					<input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $student['phone']; ?>" placeholder="+2348012345678" required>
					<div class="invalid-feedback">Please enter a valid phone number.</div>
				</div>
				<div class="col-12">
					<label class="form-label" for="homeAddress"><span class="field-icon"><i class="fa-solid fa-location-dot"></i></span>Home Address</label>
					<textarea class="form-control" id="homeAddress" name="home_address" required><?php echo $student['home_address']; ?></textarea>
					<div class="invalid-feedback">Please enter your home address.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="guardianName"><span class="field-icon"><i class="fa-solid fa-user-shield"></i></span>Parent / Guardian Name</label>
					<input type="text" class="form-control" id="guardianName" name="guardian_name" value="<?php echo $student['guardian_name']; ?>" required>
					<div class="invalid-feedback">Please enter parent or guardian name.</div>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="guardianPhone"><span class="field-icon"><i class="fa-solid fa-phone-volume"></i></span>Parent / Guardian Phone Number</label>
					<input type="tel" class="form-control" id="guardianPhone" name="guardian_phone" value="<?php echo $student['guardian_phone']; ?>" required>
					<div class="invalid-feedback">Please enter a valid parent or guardian phone number.</div>
				</div>
			</div>

			<div class="d-flex justify-content-end mt-4">
				<button type="submit" class="btn update-profile-btn px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Save Profile Changes</button>
			</div>
		</form>
	</section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Edit profile behavior: validates editable fields and provides database-ready success feedback.
(function () {
	function byId(id) { return document.getElementById(id); }

	function showNotice(type, message) {
		var notice = byId('profileNotice');
		notice.className = 'portal-alert ' + type;
		notice.textContent = message;
	}

	function setInvalid(field, invalid) {
		field.classList.toggle('is-invalid', invalid);
	}

	function isValidPhone(value) {
		return /^[+]?[-0-9\s()]{7,20}$/.test(value.trim());
	}

	document.addEventListener('DOMContentLoaded', function () {
		var form = byId('editProfileForm');
		if (!form) { return; }

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			var fields = {
				gender: byId('gender'),
				dateOfBirth: byId('dateOfBirth'),
				email: byId('email'),
				phone: byId('phone'),
				homeAddress: byId('homeAddress'),
				guardianName: byId('guardianName'),
				guardianPhone: byId('guardianPhone')
			};
			var hasError = false;

			Object.keys(fields).forEach(function (key) {
				setInvalid(fields[key], false);
			});

			if (!fields.gender.value) { setInvalid(fields.gender, true); hasError = true; }
			if (!fields.dateOfBirth.value) { setInvalid(fields.dateOfBirth, true); hasError = true; }
			if (!fields.email.value || !fields.email.checkValidity()) { setInvalid(fields.email, true); hasError = true; }
			if (!isValidPhone(fields.phone.value)) { setInvalid(fields.phone, true); hasError = true; }
			if (!fields.homeAddress.value.trim()) { setInvalid(fields.homeAddress, true); hasError = true; }
			if (!fields.guardianName.value.trim()) { setInvalid(fields.guardianName, true); hasError = true; }
			if (!isValidPhone(fields.guardianPhone.value)) { setInvalid(fields.guardianPhone, true); hasError = true; }

			if (hasError) {
				showNotice('error', 'Please correct the highlighted fields before saving your profile.');
				return;
			}

			showNotice('success', 'Profile details validated successfully. Backend saving can be connected here.');
		});

		form.querySelectorAll('input, select, textarea').forEach(function (field) {
			field.addEventListener('input', function () { setInvalid(field, false); });
			field.addEventListener('change', function () { setInvalid(field, false); });
		});
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>
