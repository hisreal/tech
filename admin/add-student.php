<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\StudentService;

$studentService = new StudentService();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
        header('Location: add-student.php');
        exit;
    }

    $result = $studentService->create($_POST, $_FILES, sms_current_user());

    if (!$result['success']) {
        sms_flash_set('error', $result['message']);
        Session::flashInput($_POST);
        Session::flashErrors($result['errors'] ?? []);
        header('Location: add-student.php');
        exit;
    }

    $credentials = $result['credentials'] ?? [];
    sms_flash_set('success', $result['message'] . ' Username: ' . ($credentials['username'] ?? '') . ' | Temporary Password: ' . ($credentials['temporary_password'] ?? '') . ' (share this with the student; they will be required to change it on first login).');

    if ((string) ($_POST['submit_mode'] ?? 'save') === 'save-add') {
        header('Location: add-student.php');
    } else {
        header('Location: student-list.php');
    }
    exit;
}

$flashMessages = sms_flash();
$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');

$sessions = $studentService->sessionsForSelect();
$classes = $studentService->classesForSelect();
$sections = $studentService->sectionsForSelect();
$currentSessionId = $studentService->currentSessionId();
$bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$statuses = ['Active', 'Inactive', 'Transferred'];

function sms_old(array $old, string $key, string $default = ''): string
{
    return sms_e($old[$key] ?? $default);
}
?>

<style>
    /* Add Student page styles: professional admission form with responsive sections. */
    .admin-student-module { --asm-primary:#0f766e; --asm-primary-dark:#115e59; --asm-soft:rgba(15,118,110,.1); --asm-border:rgba(15,118,110,.16); --asm-ink:#10201d; --asm-muted:#64748b; --asm-danger:#dc2626; --asm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-student-module .module-hero,.admin-student-module .module-card { background:rgba(255,255,255,.98); border:1px solid var(--asm-border); box-shadow:var(--asm-shadow); }
    .admin-student-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-student-module .breadcrumb-line { color:var(--asm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-student-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--asm-soft); color:var(--asm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-student-module h3,.admin-student-module h4,.admin-student-module h5 { color:var(--asm-ink); font-weight:900; }
    .admin-student-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .module-card:hover { transform:translateY(-2px); box-shadow:0 20px 42px rgba(15,23,42,.11); }
    .admin-student-module .section-title { display:flex; align-items:center; gap:10px; padding-bottom:14px; margin-bottom:18px; border-bottom:1px solid rgba(148,163,184,.2); }
    .admin-student-module .section-icon { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:13px; background:var(--asm-soft); color:var(--asm-primary); }
    .admin-student-module .form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:15px; }
    .admin-student-module .form-grid.two { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .admin-student-module .form-grid .full { grid-column:1/-1; }
    .admin-student-module label { color:var(--asm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-student-module .form-control,.admin-student-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-student-module textarea.form-control { min-height:98px; }
    .admin-student-module .form-control:focus,.admin-student-module .form-select:focus { border-color:var(--asm-primary); box-shadow:0 0 0 .18rem rgba(15,118,110,.14); }
    .admin-student-module .upload-box { min-height:96px; display:flex; align-items:center; gap:14px; padding:16px; border:1px dashed rgba(15,118,110,.35); border-radius:18px; background:rgba(240,253,244,.5); }
    .admin-student-module .upload-box i { width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:#fff; color:var(--asm-primary); }
    .admin-student-module .field-error { color:var(--asm-danger); font-size:12px; font-weight:800; margin-top:4px; display:block; }
    .admin-student-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .module-btn:hover { transform:translateY(-2px); color:#fff; }
    .admin-student-module .btn-primary-soft { background:var(--asm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-student-module .btn-muted-soft { background:#f1f5f9; color:var(--asm-ink); }
    .admin-student-module .btn-outline-soft { background:#fff; color:var(--asm-primary-dark); border:1px solid var(--asm-border); }
    .admin-student-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--asm-danger); }
    @media(max-width:991.98px){ .admin-student-module .form-grid,.admin-student-module .form-grid.two{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-student-module .module-hero,.admin-student-module .module-card{padding:18px;border-radius:18px}.admin-student-module .form-grid,.admin-student-module .form-grid.two{grid-template-columns:1fr}.admin-student-module .module-btn{width:100%} }
</style>

<div class="admin-student-module">
    <!-- Page header and breadcrumb. -->
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Student Management <i class="fa-solid fa-angle-right mx-1"></i> Add Student</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-user-plus"></i> Admission Form</span>
                <h3 class="mt-3 mb-2">Add Student</h3>
                <p class="text-muted mb-0">Capture personal, contact, academic, guardian, medical, and document information for a new student.</p>
            </div>
            <a href="student-list.php" class="module-btn btn-outline-soft"><i class="fa-solid fa-arrow-left"></i> All Students</a>
        </div>
    </section>

    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <form id="addStudentForm" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="submit_mode" id="submitMode" value="save">

        <!-- Personal information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-id-card"></i></span><div><h4 class="mb-1">Personal Information</h4><p class="text-muted mb-0">Basic identity and demographic details.</p></div></div>
            <div class="form-grid">
                <div class="full upload-box"><i class="fa-solid fa-camera"></i><div class="flex-grow-1"><label for="passportUpload">Passport Upload</label><input class="form-control" type="file" id="passportUpload" name="passport" accept="image/png,image/jpeg,image/webp"></div></div>
                <div><label for="admissionNo">Admission Number</label><input class="form-control" id="admissionNo" name="admission_no" placeholder="ADM-001" value="<?php echo sms_old($old, 'admission_no'); ?>"><?php if (isset($errors['admission_no'])): ?><span class="field-error"><?php echo sms_e($errors['admission_no']); ?></span><?php endif; ?></div>
                <div><label for="registrationNo">Registration Number</label><input class="form-control" id="registrationNo" name="registration_no" placeholder="REG-2026-001" required value="<?php echo sms_old($old, 'registration_no'); ?>"><?php if (isset($errors['registration_no'])): ?><span class="field-error"><?php echo sms_e($errors['registration_no']); ?></span><?php endif; ?></div>
                <div><label for="firstName">First Name</label><input class="form-control" id="firstName" name="first_name" required value="<?php echo sms_old($old, 'first_name'); ?>"><?php if (isset($errors['first_name'])): ?><span class="field-error"><?php echo sms_e($errors['first_name']); ?></span><?php endif; ?></div>
                <div><label for="middleName">Middle Name</label><input class="form-control" id="middleName" name="middle_name" value="<?php echo sms_old($old, 'middle_name'); ?>"></div>
                <div><label for="lastName">Last Name</label><input class="form-control" id="lastName" name="last_name" required value="<?php echo sms_old($old, 'last_name'); ?>"><?php if (isset($errors['last_name'])): ?><span class="field-error"><?php echo sms_e($errors['last_name']); ?></span><?php endif; ?></div>
                <div><label for="gender">Gender</label><select class="form-select" id="gender" name="gender"><option value="">Select Gender</option><option value="male" <?php echo ($old['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option><option value="female" <?php echo ($old['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option></select><?php if (isset($errors['gender'])): ?><span class="field-error"><?php echo sms_e($errors['gender']); ?></span><?php endif; ?></div>
                <div><label for="dob">Date of Birth</label><input class="form-control" type="date" id="dob" name="date_of_birth" value="<?php echo sms_old($old, 'date_of_birth'); ?>"><?php if (isset($errors['date_of_birth'])): ?><span class="field-error"><?php echo sms_e($errors['date_of_birth']); ?></span><?php endif; ?></div>
                <div><label for="bloodGroup">Blood Group</label><select class="form-select" id="bloodGroup" name="blood_group"><option value="">Select Blood Group</option><?php foreach ($bloodGroups as $group): ?><option <?php echo ($old['blood_group'] ?? '') === $group ? 'selected' : ''; ?>><?php echo sms_e($group); ?></option><?php endforeach; ?></select></div>
                <div><label for="religion">Religion</label><input class="form-control" id="religion" name="religion" value="<?php echo sms_old($old, 'religion'); ?>"></div>
                <div><label for="nationality">Nationality</label><input class="form-control" id="nationality" name="nationality" value="<?php echo sms_old($old, 'nationality', 'Nigerian'); ?>"></div>
            </div>
        </section>

        <!-- Contact information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-address-book"></i></span><div><h4 class="mb-1">Contact Information</h4><p class="text-muted mb-0">Student communication and residential details.</p></div></div>
            <div class="form-grid">
                <div class="full"><label for="address">Address</label><textarea class="form-control" id="address" name="address"><?php echo sms_old($old, 'address'); ?></textarea></div>
                <div><label for="state">State</label><input class="form-control" id="state" name="state" value="<?php echo sms_old($old, 'state'); ?>"></div>
                <div><label for="lga">Local Government</label><input class="form-control" id="lga" name="local_government" value="<?php echo sms_old($old, 'local_government'); ?>"></div>
                <div><label for="phone">Phone Number</label><input class="form-control" id="phone" name="phone" type="tel" value="<?php echo sms_old($old, 'phone'); ?>"></div>
                <div><label for="email">Email Address</label><input class="form-control" id="email" name="email" type="email" value="<?php echo sms_old($old, 'email'); ?>"><?php if (isset($errors['email'])): ?><span class="field-error"><?php echo sms_e($errors['email']); ?></span><?php endif; ?></div>
            </div>
        </section>

        <!-- Academic information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-school"></i></span><div><h4 class="mb-1">Academic Information</h4><p class="text-muted mb-0">Class placement and admission status.</p></div></div>
            <div class="form-grid">
                <div><label for="session">Academic Session</label><select class="form-select" id="session" name="academic_session"><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo (int) ($old['academic_session'] ?? $currentSessionId) === (int) $session['id'] ? 'selected' : ''; ?>><?php echo sms_e($session['name']); ?></option><?php endforeach; ?></select><?php if (isset($errors['session_id'])): ?><span class="field-error"><?php echo sms_e($errors['session_id']); ?></span><?php endif; ?></div>
                <div><label for="className">Class</label><select class="form-select" id="className" name="class" required><option value="">Select Class</option><?php foreach ($classes as $class): ?><option value="<?php echo (int) $class['id']; ?>" data-class="<?php echo (int) $class['id']; ?>" <?php echo (string) ($old['class'] ?? '') === (string) $class['id'] ? 'selected' : ''; ?>><?php echo sms_e($class['name']); ?></option><?php endforeach; ?></select><?php if (isset($errors['class_id'])): ?><span class="field-error"><?php echo sms_e($errors['class_id']); ?></span><?php endif; ?></div>
                <div><label for="section">Section</label><select class="form-select" id="section" name="section"><option value="">Select Section</option><?php foreach ($sections as $section): ?><option value="<?php echo (int) $section['id']; ?>" data-class="<?php echo (int) $section['class_id']; ?>" <?php echo (string) ($old['section'] ?? '') === (string) $section['id'] ? 'selected' : ''; ?>><?php echo sms_e($section['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="admissionDate">Admission Date</label><input class="form-control" type="date" id="admissionDate" name="admission_date" value="<?php echo sms_old($old, 'admission_date', date('Y-m-d')); ?>"></div>
                <div><label for="studentStatus">Student Status</label><select class="form-select" id="studentStatus" name="student_status"><?php foreach ($statuses as $status): ?><option <?php echo ($old['student_status'] ?? 'Active') === $status ? 'selected' : ''; ?>><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
            </div>
        </section>

        <!-- Parent and guardian information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-people-roof"></i></span><div><h4 class="mb-1">Parent / Guardian Information</h4><p class="text-muted mb-0">Guardian contacts for communication and emergencies.</p></div></div>
            <div class="form-grid">
                <div><label for="fatherName">Father's Name</label><input class="form-control" id="fatherName" name="father_name" value="<?php echo sms_old($old, 'father_name'); ?>"></div>
                <div><label for="motherName">Mother's Name</label><input class="form-control" id="motherName" name="mother_name" value="<?php echo sms_old($old, 'mother_name'); ?>"></div>
                <div><label for="guardianName">Guardian Name</label><input class="form-control" id="guardianName" name="guardian_name" required value="<?php echo sms_old($old, 'guardian_name'); ?>"><?php if (isset($errors['guardian_name'])): ?><span class="field-error"><?php echo sms_e($errors['guardian_name']); ?></span><?php endif; ?></div>
                <div><label for="relationship">Relationship</label><input class="form-control" id="relationship" name="relationship" placeholder="Father, Mother, Uncle" value="<?php echo sms_old($old, 'relationship'); ?>"></div>
                <div><label for="parentPhone">Parent Phone</label><input class="form-control" id="parentPhone" name="parent_phone" type="tel" required value="<?php echo sms_old($old, 'parent_phone'); ?>"><?php if (isset($errors['parent_phone'])): ?><span class="field-error"><?php echo sms_e($errors['parent_phone']); ?></span><?php endif; ?></div>
                <div><label for="parentEmail">Parent Email</label><input class="form-control" id="parentEmail" name="parent_email" type="email" value="<?php echo sms_old($old, 'parent_email'); ?>"><?php if (isset($errors['parent_email'])): ?><span class="field-error"><?php echo sms_e($errors['parent_email']); ?></span><?php endif; ?></div>
                <div class="full"><label for="parentAddress">Parent Address</label><textarea class="form-control" id="parentAddress" name="parent_address"><?php echo sms_old($old, 'parent_address'); ?></textarea></div>
                <div><label for="occupation">Occupation</label><input class="form-control" id="occupation" name="occupation" value="<?php echo sms_old($old, 'occupation'); ?>"></div>
            </div>
        </section>

        <!-- Medical and document information section. -->
        <section class="module-card">
            <div class="section-title"><span class="section-icon"><i class="fa-solid fa-kit-medical"></i></span><div><h4 class="mb-1">Medical Information & Documents</h4><p class="text-muted mb-0">Health notes and supporting admission documents.</p></div></div>
            <div class="form-grid">
                <div><label for="genotype">Genotype</label><input class="form-control" id="genotype" name="genotype" placeholder="AA, AS, SS" value="<?php echo sms_old($old, 'genotype'); ?>"></div>
                <div><label for="allergies">Allergies</label><input class="form-control" id="allergies" name="allergies" value="<?php echo sms_old($old, 'allergies'); ?>"></div>
                <div><label for="medicalConditions">Medical Conditions</label><input class="form-control" id="medicalConditions" name="medical_conditions" value="<?php echo sms_old($old, 'medical_conditions'); ?>"></div>
                <div><label for="emergencyContact">Emergency Contact</label><input class="form-control" id="emergencyContact" name="emergency_contact" type="tel" value="<?php echo sms_old($old, 'emergency_contact'); ?>"></div>
                <div><label for="birthCertificate">Birth Certificate</label><input class="form-control" type="file" id="birthCertificate" name="birth_certificate" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>
                <div><label for="previousResult">Previous School Result</label><input class="form-control" type="file" id="previousResult" name="previous_result" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>
                <div><label for="passportPhoto">Passport Photograph</label><input class="form-control" type="file" id="passportPhoto" name="passport_photograph" accept="image/png,image/jpeg,image/webp"></div>
                <div><label for="transferLetter">Transfer Letter</label><input class="form-control" type="file" id="transferLetter" name="transfer_letter" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>
            </div>
        </section>

        <!-- Form actions. -->
        <section class="module-card">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <button class="module-btn btn-primary-soft" type="submit" data-mode="save"><i class="fa-solid fa-floppy-disk"></i> Save Student</button>
                <button class="module-btn btn-outline-soft" type="submit" data-mode="save-add"><i class="fa-solid fa-plus"></i> Save & Add Another</button>
                <button class="module-btn btn-muted-soft" type="reset"><i class="fa-solid fa-rotate-left"></i> Reset</button>
                <a class="module-btn btn-danger-soft" href="student-list.php"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </section>
    </form>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    var form = document.getElementById('addStudentForm');
    var submitMode = document.getElementById('submitMode');
    Array.prototype.forEach.call(form.querySelectorAll('button[type="submit"]'), function(button){
        button.addEventListener('click', function(){ submitMode.value = button.dataset.mode || 'save'; });
    });
    var classSelect = document.getElementById('className');
    var sectionSelect = document.getElementById('section');
    function filterSections(){
        var selected = classSelect.value;
        var firstVisible = null;
        Array.prototype.forEach.call(sectionSelect.options, function(option){
            if (!option.value) { return; }
            var show = option.dataset.class === selected;
            option.hidden = !show;
            if (show && !firstVisible) { firstVisible = option; }
        });
        if (sectionSelect.selectedOptions[0] && sectionSelect.selectedOptions[0].hidden) {
            sectionSelect.value = firstVisible ? firstVisible.value : '';
        }
    }
    if (classSelect && sectionSelect) {
        classSelect.addEventListener('change', filterSections);
        filterSections();
    }
})();
</script>

<?php require_once('includes/footer.php'); ?>
