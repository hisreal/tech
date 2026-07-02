<?php require_once('includes/header.php'); ?>

<?php
// Accountant profile placeholder data. Replace these arrays with database values during backend integration.
$accountant = [
	'profile_picture' => '../assets/img/avatar/avatar-21.jpg',
	'full_name' => 'John Ibrahim',
	'staff_id' => 'BFSS/ACC/2026/001',
	'gender' => 'Male',
	'dob' => '15 March 1988',
	'phone' => '+234 803 456 7890',
	'email' => 'john.ibrahim@bfss.edu.ng',
	'address' => 'No. 18 GRA Road, Katsina',
	'state' => 'Katsina',
	'lga' => 'Katsina LGA',
	'nationality' => 'Nigerian',
	'department' => 'Accounts Department',
	'designation' => 'Accountant',
	'employment_status' => 'Active Full-Time Staff',
	'employment_date' => '02 January 2020',
	'qualification' => 'B.Sc. Accounting, ICAN Associate',
	'username' => 'john.accountant',
	'last_login' => '02 July 2026, 09:42 AM',
	'role' => 'Finance Officer',
	'branch' => 'Main Campus'
];
$profileFields = [
	['Profile Picture', 'Photo uploaded', 'fa-image'],
	['Full Name', $accountant['full_name'], 'fa-user-tie'],
	['Staff ID', $accountant['staff_id'], 'fa-id-card'],
	['Gender', $accountant['gender'], 'fa-venus-mars'],
	['Date of Birth', $accountant['dob'], 'fa-cake-candles'],
	['Phone Number', $accountant['phone'], 'fa-phone'],
	['Email Address', $accountant['email'], 'fa-envelope'],
	['Residential Address', $accountant['address'], 'fa-location-dot'],
	['State', $accountant['state'], 'fa-map'],
	['Local Government', $accountant['lga'], 'fa-map-location-dot'],
	['Nationality', $accountant['nationality'], 'fa-flag'],
	['Department', $accountant['department'], 'fa-building-columns'],
	['Designation', $accountant['designation'], 'fa-briefcase'],
	['Employment Status', $accountant['employment_status'], 'fa-circle-check'],
	['Date of Employment', $accountant['employment_date'], 'fa-calendar-check'],
	['Qualification', $accountant['qualification'], 'fa-graduation-cap'],
	['Username', $accountant['username'], 'fa-user-lock'],
	['Last Login', $accountant['last_login'], 'fa-clock-rotate-left']
];
$professionalFields = [
	['Staff ID', $accountant['staff_id'], 'fa-id-card'],
	['Department', $accountant['department'], 'fa-building-columns'],
	['Designation', $accountant['designation'], 'fa-briefcase'],
	['Employment Date', $accountant['employment_date'], 'fa-calendar-check'],
	['Employment Status', $accountant['employment_status'], 'fa-circle-check'],
	['Qualification', $accountant['qualification'], 'fa-graduation-cap'],
	['Role', $accountant['role'], 'fa-user-shield'],
	['Assigned Branch/Campus', $accountant['branch'], 'fa-school']
];
$loginActivity = [
	['02/07/2026', '09:42 AM', '192.168.1.24', 'Windows Desktop', 'Chrome', 'Successful'],
	['01/07/2026', '04:18 PM', '192.168.1.24', 'Windows Desktop', 'Edge', 'Successful'],
	['30/06/2026', '08:05 AM', '10.0.0.12', 'Android Phone', 'Chrome Mobile', 'Successful'],
	['29/06/2026', '02:10 PM', '172.16.0.8', 'Windows Desktop', 'Firefox', 'Failed']
];
$stats = [
	['Years of Service', '6 Years', 'fa-calendar-days'],
	['Receipts Generated', '1,850', 'fa-receipt'],
	['Payments Processed', '4,320', 'fa-money-bill-transfer'],
	['Financial Reports Generated', '285', 'fa-chart-pie']
];
function apValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>

<style>
	/* Accountant profile module: scoped premium profile management interface. */
	.accountant-profile-page{--ap-primary:#0f766e;--ap-primary-dark:#115e59;--ap-primary-soft:rgba(15,118,110,.1);--ap-success:#16a34a;--ap-success-soft:rgba(22,163,74,.12);--ap-warning:#f59e0b;--ap-warning-soft:rgba(245,158,11,.14);--ap-danger:#dc2626;--ap-danger-soft:rgba(220,38,38,.1);--ap-blue:#2563eb;--ap-blue-soft:rgba(37,99,235,.1);--ap-ink:#10201d;--ap-muted:#64748b;--ap-border:rgba(15,118,110,.18);--ap-shadow:0 22px 60px rgba(15,23,42,.09);padding-bottom:34px}.accountant-profile-page .profile-cover,.accountant-profile-page .profile-card,.accountant-profile-page .info-card,.accountant-profile-page .stat-card,.accountant-profile-page .profile-toast{background:rgba(255,255,255,.98);border:1px solid var(--ap-border);box-shadow:var(--ap-shadow)}.accountant-profile-page .profile-cover{position:relative;overflow:hidden;padding:30px;border-radius:28px;margin-bottom:22px;background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98))}.accountant-profile-page .profile-cover:after{content:"";position:absolute;inset:0;background:radial-gradient(circle at top right,rgba(20,184,166,.18),transparent 36%),radial-gradient(circle at bottom left,rgba(37,99,235,.1),transparent 32%);pointer-events:none}.accountant-profile-page .profile-cover>*{position:relative;z-index:1}.accountant-profile-page .breadcrumb-line{color:var(--ap-muted);font-size:13px;font-weight:800;margin-bottom:10px}.accountant-profile-page .breadcrumb-line a{color:var(--ap-primary-dark);text-decoration:none}.accountant-profile-page h3,.accountant-profile-page h4,.accountant-profile-page h5,.accountant-profile-page h6{color:var(--ap-ink);font-weight:900}.accountant-profile-page .profile-photo-wrap{width:148px;text-align:center}.accountant-profile-page .profile-photo{width:136px;height:136px;border-radius:50%;padding:7px;background:#fff;border:4px solid rgba(15,118,110,.2);box-shadow:0 18px 40px rgba(15,23,42,.16)}.accountant-profile-page .profile-photo img{width:100%;height:100%;border-radius:50%;object-fit:cover}.accountant-profile-page .upload-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;margin-top:10px;width:100%;min-height:38px;border-radius:999px;border:1px solid var(--ap-border);background:#fff;color:var(--ap-primary-dark);font-size:12px;font-weight:900}.accountant-profile-page .status-badge,.accountant-profile-page .role-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:900}.accountant-profile-page .status-badge{background:var(--ap-success-soft);color:var(--ap-success)}.accountant-profile-page .role-chip{background:var(--ap-primary-soft);color:var(--ap-primary-dark)}.accountant-profile-page .profile-card{border-radius:24px;padding:24px;margin-bottom:22px}.accountant-profile-page .card-title-row{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:18px;padding-bottom:15px;border-bottom:1px solid var(--ap-border)}.accountant-profile-page .title-icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:16px;background:var(--ap-primary-soft);color:var(--ap-primary)}.accountant-profile-page .info-card,.accountant-profile-page .stat-card{height:100%;padding:17px;border-radius:18px;transition:transform .18s ease,box-shadow .18s ease}.accountant-profile-page .info-card:hover,.accountant-profile-page .stat-card:hover{transform:translateY(-3px);box-shadow:0 18px 42px rgba(15,23,42,.11)}.accountant-profile-page .field-icon,.accountant-profile-page .stat-icon{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:var(--ap-primary-soft);color:var(--ap-primary);flex:0 0 auto}.accountant-profile-page .field-label{color:var(--ap-muted);font-size:12px;font-weight:900;text-transform:uppercase}.accountant-profile-page .field-value{color:var(--ap-ink);font-weight:900;word-break:break-word}.accountant-profile-page .form-label{color:var(--ap-ink);font-size:13px;font-weight:900}.accountant-profile-page .form-control{min-height:48px;border:1px solid rgba(148,163,184,.34);border-radius:15px;font-weight:800;box-shadow:none}.accountant-profile-page textarea.form-control{min-height:102px}.accountant-profile-page .form-control:focus{border-color:rgba(15,118,110,.72);box-shadow:0 0 0 4px rgba(15,118,110,.12)}.accountant-profile-page .form-control[readonly]{background:#f8fafc;color:#475569}.accountant-profile-page .action-row{display:flex;gap:10px;flex-wrap:wrap}.accountant-profile-page .ap-btn{min-height:44px;border:0;border-radius:15px;background:linear-gradient(135deg,var(--ap-primary),var(--ap-primary-dark));color:#fff;font-weight:900;box-shadow:0 14px 30px rgba(15,118,110,.22)}.accountant-profile-page .ap-btn:hover{color:#fff;transform:translateY(-2px)}.accountant-profile-page .security-input{position:relative}.accountant-profile-page .password-toggle{position:absolute;right:12px;top:38px;border:0;background:transparent;color:var(--ap-primary);font-weight:900}.accountant-profile-page .strength-track{height:9px;border-radius:999px;background:#e2e8f0;overflow:hidden}.accountant-profile-page .strength-fill{height:100%;width:0;background:var(--ap-danger);transition:width .2s ease,background .2s ease}.accountant-profile-page .switch-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 15px;border:1px solid rgba(148,163,184,.24);background:#f8fafc;border-radius:16px;margin-bottom:10px}.accountant-profile-page .switch-row label{font-weight:900;color:var(--ap-ink);margin:0}.accountant-profile-page .form-check-input:checked{background-color:var(--ap-primary);border-color:var(--ap-primary)}.accountant-profile-page .table-scroll{overflow:auto}.accountant-profile-page .profile-table{min-width:760px}.accountant-profile-page .profile-table thead th{position:sticky;top:0;background:linear-gradient(135deg,var(--ap-primary),var(--ap-primary-dark));color:#fff;border:0;font-size:12px;text-transform:uppercase}.accountant-profile-page .profile-table td{font-weight:750;vertical-align:middle}.accountant-profile-page .login-success{color:var(--ap-success);background:var(--ap-success-soft);padding:6px 10px;border-radius:999px;font-weight:900}.accountant-profile-page .login-failed{color:var(--ap-danger);background:var(--ap-danger-soft);padding:6px 10px;border-radius:999px;font-weight:900}.accountant-profile-page .completion-ring{width:128px;height:128px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--ap-primary) 90%,#e2e8f0 0);margin:auto}.accountant-profile-page .completion-ring span{display:grid;place-items:center;width:96px;height:96px;border-radius:50%;background:#fff;color:var(--ap-primary-dark);font-size:26px;font-weight:900}.accountant-profile-page .profile-toast{position:fixed;right:24px;bottom:24px;z-index:9999;display:none;align-items:center;gap:12px;padding:14px 18px;border-radius:18px;color:var(--ap-primary-dark);font-weight:900}.accountant-profile-page .profile-toast.show{display:flex}@media(max-width:767.98px){.accountant-profile-page .profile-cover,.accountant-profile-page .profile-card{padding:20px;border-radius:20px}.accountant-profile-page .profile-photo-wrap{width:100%}.accountant-profile-page .profile-photo{margin:auto}.accountant-profile-page .upload-btn{max-width:220px}.accountant-profile-page .action-row .btn,.accountant-profile-page .ap-btn{width:100%}}
</style>

<div class="accountant-profile-page">
	<!-- Profile cover: accountant identity, photo upload placeholder, and top summary. -->
	<section class="profile-cover">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> My Profile</div>
		<div class="row align-items-center row-gap-4">
			<div class="col-xl-8">
				<div class="d-flex align-items-center flex-wrap flex-md-nowrap gap-4">
					<div class="profile-photo-wrap"><div class="profile-photo"><img src="<?php echo apValue($accountant['profile_picture']); ?>" alt="Accountant profile picture"></div><button class="upload-btn" type="button"><i class="fa-solid fa-camera"></i>Upload New Photo</button></div>
					<div><span class="role-chip"><i class="fa-solid fa-wallet"></i>Accountant Profile</span><h3 class="mt-3 mb-1"><?php echo apValue($accountant['full_name']); ?></h3><p class="text-muted fw-bold mb-2"><i class="fa-solid fa-id-card me-2"></i><?php echo apValue($accountant['staff_id']); ?> | <?php echo apValue($accountant['department']); ?></p><div class="d-flex flex-wrap gap-2"><span class="status-badge"><i class="fa-solid fa-circle-check"></i><?php echo apValue($accountant['employment_status']); ?></span><span class="role-chip"><i class="fa-solid fa-clock"></i>Last Login: <?php echo apValue($accountant['last_login']); ?></span></div></div>
				</div>
			</div>
			<div class="col-xl-4"><div class="row g-3"><?php foreach($stats as $stat): ?><div class="col-sm-6"><div class="stat-card"><span class="stat-icon"><i class="fa-solid <?php echo $stat[2]; ?>"></i></span><h5 class="mt-3 mb-0"><?php echo apValue($stat[1]); ?></h5><p class="text-muted fw-bold mb-0"><?php echo apValue($stat[0]); ?></p></div></div><?php endforeach; ?></div></div>
		</div>
	</section>

	<!-- Accountant information card: complete profile fields in professional cards. -->
	<section class="profile-card">
		<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-address-card"></i></span><div><h4 class="mb-1">Accountant Information</h4><p class="text-muted mb-0">Complete staff profile and account details.</p></div></div></div>
		<div class="row g-3"><?php foreach($profileFields as $field): ?><div class="col-md-6 col-xl-4"><div class="info-card"><div class="d-flex gap-3"><span class="field-icon"><i class="fa-solid <?php echo $field[2]; ?>"></i></span><div><div class="field-label"><?php echo apValue($field[0]); ?></div><div class="field-value"><?php echo apValue($field[1]); ?></div></div></div></div></div><?php endforeach; ?></div>
	</section>

	<div class="row g-4">
		<div class="col-xl-8">
			<!-- Personal information form: read-only by default and editable on request. -->
			<section class="profile-card">
				<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-user-pen"></i></span><div><h4 class="mb-1">Personal Information</h4><p class="text-muted mb-0">Click Edit Profile to update editable personal fields.</p></div></div></div>
				<form id="personalForm" class="row g-3" novalidate>
					<div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control editable-field" value="<?php echo apValue($accountant['full_name']); ?>" readonly></div>
					<div class="col-md-6"><label class="form-label">Email Address</label><input type="email" class="form-control editable-field" value="<?php echo apValue($accountant['email']); ?>" readonly></div>
					<div class="col-md-6"><label class="form-label">Phone Number</label><input class="form-control editable-field" value="<?php echo apValue($accountant['phone']); ?>" readonly></div>
					<div class="col-md-6"><label class="form-label">State</label><input class="form-control editable-field" value="<?php echo apValue($accountant['state']); ?>" readonly></div>
					<div class="col-md-6"><label class="form-label">Local Government</label><input class="form-control editable-field" value="<?php echo apValue($accountant['lga']); ?>" readonly></div>
					<div class="col-md-6"><label class="form-label">Nationality</label><input class="form-control editable-field" value="<?php echo apValue($accountant['nationality']); ?>" readonly></div>
					<div class="col-12"><label class="form-label">Address</label><textarea class="form-control editable-field" readonly><?php echo apValue($accountant['address']); ?></textarea></div>
					<div class="col-12"><div class="action-row"><button type="button" class="btn ap-btn" id="editProfile"><i class="fa-solid fa-pen me-2"></i>Edit Profile</button><button type="button" class="btn btn-outline-success" id="saveProfile" disabled><i class="fa-solid fa-floppy-disk me-2"></i>Save Changes</button><button type="button" class="btn btn-outline-secondary" id="cancelProfile" disabled><i class="fa-solid fa-xmark me-2"></i>Cancel</button></div></div>
				</form>
			</section>
		</div>

		<div class="col-xl-4">
			<!-- Profile completion progress and missing data notice. -->
			<section class="profile-card h-100">
				<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-chart-simple"></i></span><div><h4 class="mb-1">Profile Completion</h4><p class="text-muted mb-0">Current profile quality score.</p></div></div></div>
				<div class="completion-ring"><span>90%</span></div><div class="mt-4 p-3 rounded-3" style="background:#f8fafc;border:1px solid rgba(148,163,184,.24);"><strong class="d-block mb-1">Missing Information</strong><span class="text-muted fw-bold">Emergency contact and digital signature are not yet attached.</span></div>
			</section>
		</div>
	</div>

	<!-- Professional information card. -->
	<section class="profile-card">
		<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-briefcase"></i></span><div><h4 class="mb-1">Professional Information</h4><p class="text-muted mb-0">Employment, department, branch, and responsibility details.</p></div></div></div>
		<div class="row g-3"><?php foreach($professionalFields as $field): ?><div class="col-md-6 col-xl-3"><div class="info-card"><div class="d-flex gap-3"><span class="field-icon"><i class="fa-solid <?php echo $field[2]; ?>"></i></span><div><div class="field-label"><?php echo apValue($field[0]); ?></div><div class="field-value"><?php echo apValue($field[1]); ?></div></div></div></div></div><?php endforeach; ?></div>
	</section>

	<div class="row g-4">
		<div class="col-xl-6">
			<!-- Account security and change password controls. -->
			<section class="profile-card h-100">
				<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-shield-halved"></i></span><div><h4 class="mb-1">Account Security</h4><p class="text-muted mb-0">Update password and review password strength.</p></div></div></div>
				<div class="row g-3">
					<?php foreach(['Current Password','New Password','Confirm New Password'] as $i => $label): ?><div class="col-12 security-input"><label class="form-label"><?php echo apValue($label); ?></label><input type="password" class="form-control password-field" id="password<?php echo $i; ?>"><button type="button" class="password-toggle" data-target="password<?php echo $i; ?>"><i class="fa-solid fa-eye"></i></button></div><?php endforeach; ?>
					<div class="col-12"><div class="d-flex justify-content-between fw-bold mb-2"><span>Password Strength</span><span id="strengthText" class="text-muted">Waiting</span></div><div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div></div>
					<div class="col-12"><div class="action-row"><button type="button" class="btn ap-btn" id="updatePassword"><i class="fa-solid fa-key me-2"></i>Update Password</button><button type="button" class="btn btn-outline-secondary" id="clearPassword"><i class="fa-solid fa-eraser me-2"></i>Clear</button></div></div>
				</div>
			</section>
		</div>
		<div class="col-xl-6">
			<!-- Notification preference toggles. -->
			<section class="profile-card h-100">
				<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-bell"></i></span><div><h4 class="mb-1">Notification Preferences</h4><p class="text-muted mb-0">Choose account and finance alerts.</p></div></div></div>
				<?php $preferences = ['Email Notifications','SMS Notifications','Payment Alerts','Financial Report Notifications','System Announcements']; foreach($preferences as $i => $preference): ?><div class="switch-row"><label for="pref<?php echo $i; ?>"><?php echo apValue($preference); ?></label><div class="form-check form-switch m-0"><input class="form-check-input" id="pref<?php echo $i; ?>" type="checkbox" <?php echo $i !== 1 ? 'checked' : ''; ?>></div></div><?php endforeach; ?>
			</section>
		</div>
	</div>

	<!-- Login activity table. -->
	<section class="profile-card">
		<div class="card-title-row"><div class="d-flex align-items-center gap-3"><span class="title-icon"><i class="fa-solid fa-clock-rotate-left"></i></span><div><h4 class="mb-1">Login Activity</h4><p class="text-muted mb-0">Recent account access history.</p></div></div></div>
		<div class="table-scroll"><table class="table profile-table align-middle"><thead><tr><th>Date</th><th>Time</th><th>IP Address</th><th>Device</th><th>Browser</th><th>Status</th></tr></thead><tbody><?php foreach($loginActivity as $login): ?><tr><td><?php echo apValue($login[0]); ?></td><td><?php echo apValue($login[1]); ?></td><td><?php echo apValue($login[2]); ?></td><td><?php echo apValue($login[3]); ?></td><td><?php echo apValue($login[4]); ?></td><td><span class="<?php echo $login[5] === 'Successful' ? 'login-success' : 'login-failed'; ?>"><?php echo apValue($login[5]); ?></span></td></tr><?php endforeach; ?></tbody></table></div>
	</section>

	<div class="profile-toast" id="profileToast"><i class="fa-solid fa-circle-check text-success"></i><span>Profile updated successfully.</span></div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Accountant profile behavior: editable fields, password toggles, strength meter, and placeholder notifications.
(function(){
	function qs(selector){ return document.querySelector(selector); }
	function qsa(selector){ return Array.prototype.slice.call(document.querySelectorAll(selector)); }
	var originalValues = qsa('.editable-field').map(function(field){ return field.value; });
	function toast(message){ var box = qs('#profileToast'); box.querySelector('span').textContent = message; box.classList.add('show'); window.clearTimeout(toast.timer); toast.timer = window.setTimeout(function(){ box.classList.remove('show'); }, 2400); }
	function setEditing(active){ qsa('.editable-field').forEach(function(field){ field.readOnly = !active; }); qs('#saveProfile').disabled = !active; qs('#cancelProfile').disabled = !active; qs('#editProfile').disabled = active; }
	qs('#editProfile').addEventListener('click', function(){ setEditing(true); toast('Profile fields are now editable.'); });
	qs('#saveProfile').addEventListener('click', function(){ originalValues = qsa('.editable-field').map(function(field){ return field.value; }); setEditing(false); toast('Profile changes saved successfully.'); });
	qs('#cancelProfile').addEventListener('click', function(){ qsa('.editable-field').forEach(function(field, index){ field.value = originalValues[index]; }); setEditing(false); toast('Profile editing cancelled.'); });
	qsa('.password-toggle').forEach(function(button){ button.addEventListener('click', function(){ var input = qs('#' + button.dataset.target); input.type = input.type === 'password' ? 'text' : 'password'; button.innerHTML = input.type === 'password' ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>'; }); });
	qs('#password1').addEventListener('input', function(){ var value = this.value, score = 0; if(value.length >= 8) score++; if(/[A-Z]/.test(value)) score++; if(/[0-9]/.test(value)) score++; if(/[^A-Za-z0-9]/.test(value)) score++; var labels = ['Weak','Fair','Good','Strong'], colors = ['#dc2626','#f59e0b','#2563eb','#16a34a']; qs('#strengthFill').style.width = (score * 25) + '%'; qs('#strengthFill').style.background = colors[Math.max(0, score - 1)] || '#dc2626'; qs('#strengthText').textContent = score ? labels[score - 1] : 'Waiting'; });
	qs('#updatePassword').addEventListener('click', function(){ var next = qs('#password1').value, confirm = qs('#password2').value; if(!next || next.length < 8){ toast('New password must be at least 8 characters.'); return; } if(next !== confirm){ toast('New password and confirmation do not match.'); return; } toast('Password updated successfully.'); qsa('.password-field').forEach(function(input){ input.value = ''; }); qs('#strengthFill').style.width = '0'; qs('#strengthText').textContent = 'Waiting'; });
	qs('#clearPassword').addEventListener('click', function(){ qsa('.password-field').forEach(function(input){ input.value = ''; }); qs('#strengthFill').style.width = '0'; qs('#strengthText').textContent = 'Waiting'; toast('Password fields cleared.'); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
