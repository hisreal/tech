<?php require_once('includes/header.php'); ?>

<div class="student-portal-module">
	<!-- Password page hero: gives security context for the account action. -->
	<section class="portal-hero">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
			<div>
				<span class="portal-kicker mb-3"><i class="fa-solid fa-lock"></i> Account Security</span>
				<h3 class="mb-2">Change Password</h3>
				<p class="text-muted mb-0">Use a strong password to keep your student portal account secure.</p>
			</div>
			<span class="portal-chip"><i class="fa-solid fa-shield-halved"></i> Secure Update</span>
		</div>
	</section>

	<!-- Password form card: client-side validation only until backend integration is added. -->
	<section class="portal-form-card">
		<div class="text-center mb-4">
			<span class="portal-icon mb-3"><i class="fa-solid fa-key"></i></span>
			<h5 class="mb-1">Update Your Password</h5>
			<p class="text-muted mb-0">Enter your current password and choose a new secure password.</p>
		</div>

		<div id="passwordNotice" class="portal-alert mb-3" role="alert"></div>

		<form id="changePasswordForm" novalidate>
			<div class="mb-3">
				<label for="currentPassword" class="form-label fw-semibold">Current Password</label>
				<div class="password-field">
					<input type="password" class="form-control" id="currentPassword" autocomplete="current-password" placeholder="Enter current password">
					<button type="button" class="password-toggle" data-toggle-password="currentPassword" aria-label="Show current password"><i class="fa-regular fa-eye"></i></button>
				</div>
			</div>

			<div class="mb-3">
				<label for="newPassword" class="form-label fw-semibold">New Password</label>
				<div class="password-field">
					<input type="password" class="form-control" id="newPassword" autocomplete="new-password" placeholder="Enter new password">
					<button type="button" class="password-toggle" data-toggle-password="newPassword" aria-label="Show new password"><i class="fa-regular fa-eye"></i></button>
				</div>
				<div class="password-strength-track mt-3"><div class="password-strength-bar" id="passwordStrengthBar"></div></div>
				<small class="text-muted d-block mt-2" id="passwordStrengthText">Password strength: Not started</small>
				<ul class="validation-list" aria-label="Password requirements">
					<li id="ruleRequired"><i class="fa-regular fa-circle"></i>Password required</li>
					<li id="ruleLength"><i class="fa-regular fa-circle"></i>Minimum 8 characters</li>
					<li id="ruleMatch"><i class="fa-regular fa-circle"></i>Password confirmation must match</li>
				</ul>
			</div>

			<div class="mb-4">
				<label for="confirmPassword" class="form-label fw-semibold">Confirm New Password</label>
				<div class="password-field">
					<input type="password" class="form-control" id="confirmPassword" autocomplete="new-password" placeholder="Confirm new password">
					<button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Show confirmation password"><i class="fa-regular fa-eye"></i></button>
				</div>
			</div>

			<button type="submit" class="btn btn-success rounded-pill w-100 d-inline-flex align-items-center justify-content-center">
				<i class="fa-solid fa-shield-halved me-2"></i>Update Password
			</button>
		</form>
	</section>
</div>

</div>
</div>

<!-- Change password behavior: visibility toggles, strength meter, and validation feedback. -->
<script data-cfasync="false" type="text/javascript">
(function () {
	function byId(id) { return document.getElementById(id); }

	function setRule(id, isValid) {
		var item = byId(id);
		item.classList.toggle('valid', isValid);
		item.querySelector('i').className = isValid ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle';
	}

	function showNotice(type, message) {
		var notice = byId('passwordNotice');
		notice.className = 'portal-alert mb-3 ' + type;
		notice.textContent = message;
	}

	function evaluatePassword() {
		var current = byId('currentPassword').value.trim();
		var password = byId('newPassword').value;
		var confirm = byId('confirmPassword').value;
		var hasRequired = password.length > 0;
		var hasLength = password.length >= 8;
		var hasMatch = password.length > 0 && password === confirm;
		var score = 0;

		if (hasRequired) { score += 25; }
		if (hasLength) { score += 35; }
		if (/[A-Z]/.test(password)) { score += 15; }
		if (/[0-9]/.test(password)) { score += 15; }
		if (/[^A-Za-z0-9]/.test(password)) { score += 10; }

		var bar = byId('passwordStrengthBar');
		var label = byId('passwordStrengthText');
		bar.style.width = Math.min(score, 100) + '%';
		bar.style.background = score >= 75 ? '#16a34a' : score >= 50 ? '#f59e0b' : '#dc2626';
		label.textContent = 'Password strength: ' + (score >= 75 ? 'Strong' : score >= 50 ? 'Medium' : hasRequired ? 'Weak' : 'Not started');

		setRule('ruleRequired', hasRequired);
		setRule('ruleLength', hasLength);
		setRule('ruleMatch', hasMatch);

		return { current: current, hasRequired: hasRequired, hasLength: hasLength, hasMatch: hasMatch };
	}

	document.addEventListener('DOMContentLoaded', function () {
		var form = byId('changePasswordForm');
		var fields = ['currentPassword', 'newPassword', 'confirmPassword'];

		document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
			button.addEventListener('click', function () {
				var input = byId(button.getAttribute('data-toggle-password'));
				var show = input.type === 'password';
				input.type = show ? 'text' : 'password';
				button.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
			});
		});

		fields.forEach(function (fieldId) {
			byId(fieldId).addEventListener('input', evaluatePassword);
		});

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			var result = evaluatePassword();

			if (!result.current) {
				showNotice('error', 'Current password is required.');
				return;
			}
			if (!result.hasRequired) {
				showNotice('error', 'New password is required.');
				return;
			}
			if (!result.hasLength) {
				showNotice('error', 'New password must be at least 8 characters long.');
				return;
			}
			if (!result.hasMatch) {
				showNotice('error', 'Confirm password must match the new password.');
				return;
			}

			showNotice('success', 'Password updated successfully. This demo uses client-side validation only.');
			form.reset();
			evaluatePassword();
		});

		evaluatePassword();
	});
}());
</script>

<?php require_once('includes/footer.php'); ?>