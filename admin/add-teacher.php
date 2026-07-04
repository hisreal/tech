<?php require_once('includes/header.php'); ?>

<?php
// Teacher registration option placeholders for future database-driven setup.
$departments = ['Science', 'Languages', 'Commercial', 'Arts', 'ICT'];
$designations = ['Teacher', 'Senior Teacher', 'Class Teacher', 'Head of Department'];
$statuses = ['Active', 'Inactive', 'On Leave', 'Suspended'];
$subjects = ['Mathematics', 'English Language', 'Physics', 'Computer Science', 'Biology', 'Economics', 'Chemistry'];
$classes = ['JSS1A', 'JSS2B', 'JSS3A', 'SS1 Science', 'SS2 Science', 'SS3 Arts'];
$generatedStaffId = 'TCH' . date('Y') . '-005';
?>

<style>
    /* Add Teacher page styles: responsive professional registration form. */
    .admin-teacher-module { --atm-primary:#0f766e; --atm-primary-dark:#115e59; --atm-soft:rgba(15,118,110,.1); --atm-border:rgba(15,118,110,.16); --atm-ink:#10201d; --atm-muted:#64748b; --atm-danger:#dc2626; --atm-warning:#d97706; --atm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-teacher-module .module-hero,.admin-teacher-module .module-card { background:rgba(255,255,255,.98); border:1px solid var(--atm-border); box-shadow:var(--atm-shadow); }
    .admin-teacher-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-teacher-module .breadcrumb-line { color:var(--atm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-teacher-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-teacher-module h3,.admin-teacher-module h4,.admin-teacher-module h5 { color:var(--atm-ink); font-weight:900; }
    .admin-teacher-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-teacher-module .module-card:hover { transform:translateY(-2px); box-shadow:0 20px 42px rgba(15,23,42,.11); }
    .admin-teacher-module .section-title { display:flex; align-items:center; gap:10px; padding-bottom:14px; margin-bottom:18px; border-bottom:1px solid rgba(148,163,184,.2); }
    .admin-teacher-module .section-icon { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:13px; background:var(--atm-soft); color:var(--atm-primary); }
    .admin-teacher-module .form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:15px; }
    .admin-teacher-module .form-grid.two { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .admin-teacher-module .form-grid .full { grid-column:1/-1; }
    .admin-teacher-module label { color:var(--atm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-teacher-module .form-control,.admin-teacher-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-teacher-module textarea.form-control { min-height:98px; }
    .admin-teacher-module .form-control:focus,.admin-teacher-module .form-select:focus { border-color:var(--atm-primary); box-shadow:0 0 0 .18rem rgba(15,118,110,.14); }
    .admin-teacher-module .upload-box { min-height:94px; display:flex; align-items:center; gap:14px; padding:16px; border:1px dashed rgba(15,118,110,.35); border-radius:18px; background:rgba(240,253,244,.5); }
    .admin-teacher-module .upload-box i { width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:#fff; color:var(--atm-primary); }
    .admin-teacher-module .multi-select-box { border:1px solid rgba(148,163,184,.35); border-radius:16px; padding:12px; background:#fff; }
    .admin-teacher-module .multi-select-options { max-height:190px; overflow:auto; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:10px; }
    .admin-teacher-module .multi-option { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:12px; background:#f8fafc; font-weight:800; color:var(--atm-ink); }
    .admin-teacher-module .multi-option:hover { background:var(--atm-soft); }
    .admin-teacher-module .password-meter { height:8px; border-radius:999px; background:#e2e8f0; overflow:hidden; margin-top:8px; }
    .admin-teacher-module .password-meter span { display:block; width:0; height:100%; background:var(--atm-danger); transition:width .2s ease, background .2s ease; }
    .admin-teacher-module .file-list { margin-top:8px; color:var(--atm-muted); font-size:13px; font-weight:800; }
    .admin-teacher-module .alert-success-soft { display:none; padding:14px 16px; border-radius:16px; background:rgba(22,163,74,.12); color:#166534; font-weight:900; margin-bottom:18px; }
    .admin-teacher-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-teacher-module .module-btn:hover { transform:translateY(-2px); }
    .admin-teacher-module .btn-primary-soft { background:var(--atm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-teacher-module .btn-muted-soft { background:#f1f5f9; color:var(--atm-ink); }
    .admin-teacher-module .btn-outline-soft { background:#fff; color:var(--atm-primary-dark); border:1px solid var(--atm-border); }
    .admin-teacher-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--atm-danger); }
    @media(max-width:991.98px){ .admin-teacher-module .form-grid,.admin-teacher-module .form-grid.two{grid-template-columns:repeat(2,minmax(0,1fr));}.admin-teacher-module .multi-select-options{grid-template-columns:1fr} }
    @media(max-width:575.98px){ .admin-teacher-module .module-hero,.admin-teacher-module .module-card{padding:18px;border-radius:18px}.admin-teacher-module .form-grid,.admin-teacher-module .form-grid.two{grid-template-columns:1fr}.admin-teacher-module .module-btn{width:100%} }
</style>

<div class="admin-teacher-module">
    <!-- Page header and breadcrumb. -->
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> Add Teacher</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-user-plus"></i> Teacher Registration</span>
                <h3 class="mt-3 mb-2">Add Teacher</h3>
                <p class="text-muted mb-0">Register a teacher, assign subjects/classes, create login details, and attach documents.</p>
            </div>
            <a href="teachers.php" class="module-btn btn-outline-soft"><i class="fa-solid fa-arrow-left"></i> All Teachers</a>
        </div>
    </section>

    <div id="teacherSaveAlert" class="alert-success-soft"><i class="fa-solid fa-circle-check me-2"></i>Teacher record saved successfully. This placeholder form is ready for profile redirection and database integration.</div>

    <form id="addTeacherForm" enctype="multipart/form-data">
        <!-- Personal information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-id-card"></i></span><div><h4 class="mb-1">Personal Information</h4><p class="text-muted mb-0">Teacher identity and contact information.</p></div></div>
            <div class="form-grid">
                <div class="full upload-box"><i class="fa-solid fa-camera"></i><div class="flex-grow-1"><label for="passportUpload">Passport Upload</label><input class="form-control tracked-file" type="file" id="passportUpload" name="passport" accept="image/*"><div class="file-list" data-file-list-for="passportUpload">No file selected</div></div></div>
                <div><label for="staffId">Staff ID</label><input class="form-control" id="staffId" name="staff_id" value="<?php echo sms_e($generatedStaffId); ?>" readonly></div>
                <div><label for="firstName">First Name</label><input class="form-control" id="firstName" name="first_name" required></div>
                <div><label for="middleName">Middle Name</label><input class="form-control" id="middleName" name="middle_name"></div>
                <div><label for="lastName">Last Name</label><input class="form-control" id="lastName" name="last_name" required></div>
                <div><label for="gender">Gender</label><select class="form-select" id="gender" name="gender" required><option value="">Select Gender</option><option>Male</option><option>Female</option></select></div>
                <div><label for="dob">Date of Birth</label><input class="form-control" type="date" id="dob" name="date_of_birth"></div>
                <div><label for="nationality">Nationality</label><input class="form-control" id="nationality" name="nationality" value="Nigerian"></div>
                <div><label for="state">State</label><input class="form-control" id="state" name="state"></div>
                <div><label for="lga">Local Government</label><input class="form-control" id="lga" name="local_government"></div>
                <div class="full"><label for="address">Residential Address</label><textarea class="form-control" id="address" name="address"></textarea></div>
                <div><label for="phone">Phone Number</label><input class="form-control" id="phone" name="phone" type="tel" required></div>
                <div><label for="email">Email Address</label><input class="form-control" id="email" name="email" type="email" required></div>
            </div>
        </section>

        <!-- Employment information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-briefcase"></i></span><div><h4 class="mb-1">Employment Information</h4><p class="text-muted mb-0">Departmental placement and employment status.</p></div></div>
            <div class="form-grid">
                <div><label for="department">Department</label><select class="form-select" id="department" name="department" required><option value="">Select Department</option><?php foreach ($departments as $department): ?><option><?php echo sms_e($department); ?></option><?php endforeach; ?></select></div>
                <div><label for="designation">Designation</label><select class="form-select" id="designation" name="designation" required><option value="">Select Designation</option><?php foreach ($designations as $designation): ?><option><?php echo sms_e($designation); ?></option><?php endforeach; ?></select></div>
                <div><label for="employmentDate">Employment Date</label><input class="form-control" type="date" id="employmentDate" name="employment_date" required></div>
                <div><label for="employmentStatus">Employment Status</label><select class="form-select" id="employmentStatus" name="employment_status" required><?php foreach ($statuses as $status): ?><option><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
            </div>
        </section>

        <!-- Academic and assignment information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-book-open-reader"></i></span><div><h4 class="mb-1">Academic Information & Assignments</h4><p class="text-muted mb-0">Qualifications plus multiple subject and class assignments.</p></div></div>
            <div class="form-grid">
                <div><label for="qualification">Qualification</label><input class="form-control" id="qualification" name="qualification" placeholder="B.Sc, NCE, M.Ed"></div>
                <div><label for="specialization">Specialization</label><input class="form-control" id="specialization" name="specialization" placeholder="Mathematics Education"></div>
                <div><label for="experience">Years of Experience</label><input class="form-control" type="number" min="0" id="experience" name="experience" placeholder="6"></div>
                <div class="full multi-select-box"><label for="subjectSearch">Subject Assignment</label><input class="form-control multi-search" id="subjectSearch" data-target="subjectOptions" placeholder="Search subjects"><div class="multi-select-options" id="subjectOptions"><?php foreach ($subjects as $subject): ?><label class="multi-option"><input class="form-check-input" type="checkbox" name="subjects[]" value="<?php echo sms_e($subject); ?>"> <?php echo sms_e($subject); ?></label><?php endforeach; ?></div></div>
                <div class="full multi-select-box"><label for="classSearch">Class Assignment</label><input class="form-control multi-search" id="classSearch" data-target="classOptions" placeholder="Search classes"><div class="multi-select-options" id="classOptions"><?php foreach ($classes as $class): ?><label class="multi-option"><input class="form-check-input" type="checkbox" name="classes[]" value="<?php echo sms_e($class); ?>"> <?php echo sms_e($class); ?></label><?php endforeach; ?></div></div>
            </div>
        </section>

        <!-- Account information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-user-lock"></i></span><div><h4 class="mb-1">Account Information</h4><p class="text-muted mb-0">Create teacher portal login details.</p></div></div>
            <div class="form-grid">
                <div><label for="username">Username</label><input class="form-control" id="username" name="username" required></div>
                <div><label for="password">Password</label><input class="form-control" type="password" id="password" name="password" required><div class="password-meter"><span id="passwordMeter"></span></div><small class="text-muted fw-bold" id="passwordStrengthText">Password strength: Not started</small></div>
                <div><label for="confirmPassword">Confirm Password</label><input class="form-control" type="password" id="confirmPassword" name="confirm_password" required></div>
            </div>
        </section>

        <!-- Document upload section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-folder-open"></i></span><div><h4 class="mb-1">Documents</h4><p class="text-muted mb-0">Upload PDF, JPG, or PNG files for teacher records.</p></div></div>
            <div class="form-grid">
                <div><label for="passportPhoto">Passport Photograph</label><input class="form-control tracked-file" type="file" id="passportPhoto" accept=".pdf,.jpg,.jpeg,.png"><div class="file-list" data-file-list-for="passportPhoto">No file selected</div></div>
                <div><label for="cvUpload">CV / Resume</label><input class="form-control tracked-file" type="file" id="cvUpload" accept=".pdf,.jpg,.jpeg,.png"><div class="file-list" data-file-list-for="cvUpload">No file selected</div></div>
                <div><label for="certificates">Certificate(s)</label><input class="form-control tracked-file" type="file" id="certificates" accept=".pdf,.jpg,.jpeg,.png" multiple><div class="file-list" data-file-list-for="certificates">No file selected</div></div>
                <div><label for="appointmentLetter">Appointment Letter</label><input class="form-control tracked-file" type="file" id="appointmentLetter" accept=".pdf,.jpg,.jpeg,.png"><div class="file-list" data-file-list-for="appointmentLetter">No file selected</div></div>
                <div><label for="idDocument">Identification Document</label><input class="form-control tracked-file" type="file" id="idDocument" accept=".pdf,.jpg,.jpeg,.png"><div class="file-list" data-file-list-for="idDocument">No file selected</div></div>
            </div>
        </section>

        <!-- Form actions. -->
        <section class="module-card">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <button class="module-btn btn-primary-soft" type="submit" data-mode="save"><i class="fa-solid fa-floppy-disk"></i> Save Teacher</button>
                <button class="module-btn btn-outline-soft" type="submit" data-mode="save-add"><i class="fa-solid fa-plus"></i> Save & Add Another</button>
                <button class="module-btn btn-muted-soft" type="reset"><i class="fa-solid fa-rotate-left"></i> Reset Form</button>
                <a class="module-btn btn-danger-soft" href="teachers.php"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </section>
    </form>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
/* Teacher registration interactions: searchable multi-selects, password strength, files, and save feedback. */
(function(){
    var form = document.getElementById('addTeacherForm');
    var alertBox = document.getElementById('teacherSaveAlert');
    var submitMode = 'save';
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirmPassword');
    var meter = document.getElementById('passwordMeter');
    var strengthText = document.getElementById('passwordStrengthText');

    function updatePasswordStrength() {
        var value = password.value;
        var score = 0;
        if (value.length >= 8) { score++; }
        if (/[A-Z]/.test(value)) { score++; }
        if (/[0-9]/.test(value)) { score++; }
        if (/[^A-Za-z0-9]/.test(value)) { score++; }
        var labels = ['Not started', 'Weak', 'Fair', 'Good', 'Strong'];
        var colors = ['#dc2626', '#dc2626', '#d97706', '#2563eb', '#16a34a'];
        meter.style.width = (score * 25) + '%';
        meter.style.background = colors[score];
        strengthText.textContent = 'Password strength: ' + labels[score];
    }
    function updateFileName(input) {
        var list = document.querySelector('[data-file-list-for="' + input.id + '"]');
        var names = Array.prototype.map.call(input.files || [], function(file){ return file.name; });
        if (list) { list.textContent = names.length ? names.join(', ') : 'No file selected'; }
    }

    Array.prototype.forEach.call(form.querySelectorAll('button[type="submit"]'), function(button){ button.addEventListener('click', function(){ submitMode = button.dataset.mode || 'save'; }); });
    Array.prototype.forEach.call(document.querySelectorAll('.multi-search'), function(search){
        search.addEventListener('input', function(){
            var target = document.getElementById(search.dataset.target);
            var query = search.value.toLowerCase();
            Array.prototype.forEach.call(target.querySelectorAll('.multi-option'), function(option){ option.style.display = option.textContent.toLowerCase().indexOf(query) > -1 ? '' : 'none'; });
        });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.tracked-file'), function(input){ input.addEventListener('change', function(){ updateFileName(input); }); });
    password.addEventListener('input', updatePasswordStrength);
    form.addEventListener('submit', function(event){
        event.preventDefault();
        if (password.value !== confirmPassword.value) { confirmPassword.setCustomValidity('Passwords do not match.'); } else { confirmPassword.setCustomValidity(''); }
        if (!form.checkValidity()) { form.reportValidity(); return; }
        alertBox.style.display = 'block';
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (submitMode === 'save-add') { setTimeout(function(){ form.reset(); meter.style.width = '0'; strengthText.textContent = 'Password strength: Not started'; Array.prototype.forEach.call(document.querySelectorAll('.file-list'), function(list){ list.textContent = 'No file selected'; }); }, 500); }
    });
})();
</script>

<?php require_once('includes/footer.php'); ?>