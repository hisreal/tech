<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\TeacherService;

$teacherService = new TeacherService();
$teacherId = (int) ($_GET['teacher_id'] ?? 0);
$teacher = $teacherId > 0 ? $teacherService->find($teacherId) : null;

if ($teacher === null) {
    sms_flash_set('error', 'Teacher not found.');
    header('Location: teachers.php');
    exit;
}

$flashMessages = sms_flash();
$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');

$departments = $teacherService->departmentsForSelect();
$subjects = $teacherService->subjectsForSelect();
$classSections = $teacherService->classSectionsForSelect();
$designations = ['Teacher', 'Senior Teacher', 'Class Teacher', 'Head of Department'];
$statuses = ['Active', 'Inactive', 'On Leave', 'Suspended'];
$contractTypes = ['Permanent', 'Contract', 'Part Time'];
$assignedSubjectIds = array_column($teacher['subjects'], 'id');
$assignedSectionIds = array_column($teacher['classes'], 'id');
$photoUrl = !empty($teacher['passport_path']) ? '../' . ltrim((string) $teacher['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
$currentEmploymentStatus = ucfirst(str_replace('_', ' ', (string) $teacher['employment_status']));

function sms_teacher_field(array $old, array $teacher, string $oldKey, string $teacherKey, string $default = ''): string
{
    if (array_key_exists($oldKey, $old)) {
        return sms_e($old[$oldKey]);
    }
    return sms_e((string) ($teacher[$teacherKey] ?? $default));
}
?>
<style>
    .admin-teacher-module { --atm-primary:#0f766e; --atm-primary-dark:#115e59; --atm-soft:rgba(15,118,110,.1); --atm-border:rgba(15,118,110,.16); --atm-ink:#10201d; --atm-muted:#64748b; --atm-danger:#dc2626; --atm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-teacher-module .module-hero,.admin-teacher-module .module-card { background:rgba(255,255,255,.98); border:1px solid var(--atm-border); box-shadow:var(--atm-shadow); }
    .admin-teacher-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-teacher-module .breadcrumb-line { color:var(--atm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-teacher-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-teacher-module h3,.admin-teacher-module h4 { color:var(--atm-ink); font-weight:900; }
    .admin-teacher-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; }
    .admin-teacher-module .form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:15px; }
    .admin-teacher-module .form-grid.two { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .admin-teacher-module .form-grid .full { grid-column:1/-1; }
    .admin-teacher-module label { color:var(--atm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-teacher-module .form-control,.admin-teacher-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-teacher-module textarea.form-control { min-height:98px; }
    .admin-teacher-module .multi-select-box { border:1px solid rgba(148,163,184,.35); border-radius:16px; padding:12px; background:#fff; }
    .admin-teacher-module .multi-select-options { max-height:190px; overflow:auto; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
    .admin-teacher-module .multi-option { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:12px; background:#f8fafc; font-weight:800; color:var(--atm-ink); }
    .admin-teacher-module .upload-box { min-height:94px; display:flex; align-items:center; gap:14px; padding:16px; border:1px dashed rgba(15,118,110,.35); border-radius:18px; background:rgba(240,253,244,.5); }
    .admin-teacher-module .upload-box img { width:60px; height:60px; border-radius:14px; object-fit:cover; border:2px solid #fff; box-shadow:0 8px 18px rgba(15,23,42,.14); }
    .admin-teacher-module .field-error { color:var(--atm-danger); font-size:12px; font-weight:800; margin-top:4px; display:block; }
    .admin-teacher-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; }
    .admin-teacher-module .module-btn:hover { color:#fff; }
    .admin-teacher-module .btn-primary-soft { background:var(--atm-primary); color:#fff; }
    .admin-teacher-module .btn-muted-soft { background:#f1f5f9; color:var(--atm-ink); }
    .admin-teacher-module .btn-outline-soft { background:#fff; color:var(--atm-primary-dark); border:1px solid var(--atm-border); }
    @media(max-width:991.98px){ .admin-teacher-module .form-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-teacher-module .form-grid{grid-template-columns:1fr} }
</style>
<div class="admin-teacher-module">
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> Edit Teacher</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-pen"></i> Edit Teacher</span><h3 class="mt-3 mb-2"><?php echo sms_e(trim($teacher['first_name'] . ' ' . $teacher['last_name'])); ?></h3><p class="text-muted mb-0">Update personal, contact, employment, qualification, assignment, and profile photo details.</p></div><a href="teacher-profile.php?teacher_id=<?php echo (int) $teacherId; ?>" class="module-btn btn-outline-soft"><i class="fa-solid fa-id-card"></i> View Profile</a></div></section>

    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <form id="teacherEditForm" method="post" action="teacher-update.php" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="teacher_id" value="<?php echo (int) $teacherId; ?>">
        <section class="module-card">
            <h4 class="mb-3">Personal & Contact Information</h4>
            <div class="form-grid">
                <div class="full upload-box"><img src="<?php echo sms_e($photoUrl); ?>" alt="Current passport photo" id="photoPreview"><div class="flex-grow-1"><label for="profilePhoto">Replace Profile Photo</label><input class="form-control" type="file" id="profilePhoto" name="passport" accept="image/png,image/jpeg,image/webp"></div></div>
                <div><label for="firstName">First Name</label><input class="form-control" id="firstName" name="first_name" value="<?php echo sms_teacher_field($old, $teacher, 'first_name', 'first_name'); ?>" required><?php if (isset($errors['first_name'])): ?><span class="field-error"><?php echo sms_e($errors['first_name']); ?></span><?php endif; ?></div>
                <div><label for="middleName">Middle Name</label><input class="form-control" id="middleName" name="middle_name" value="<?php echo sms_teacher_field($old, $teacher, 'middle_name', 'middle_name'); ?>"></div>
                <div><label for="lastName">Last Name</label><input class="form-control" id="lastName" name="last_name" value="<?php echo sms_teacher_field($old, $teacher, 'last_name', 'last_name'); ?>" required><?php if (isset($errors['last_name'])): ?><span class="field-error"><?php echo sms_e($errors['last_name']); ?></span><?php endif; ?></div>
                <div><label for="gender">Gender</label><select class="form-select" id="gender" name="gender"><option value="">Select</option><option value="male" <?php echo strtolower($old['gender'] ?? (string) $teacher['gender']) === 'male' ? 'selected' : ''; ?>>Male</option><option value="female" <?php echo strtolower($old['gender'] ?? (string) $teacher['gender']) === 'female' ? 'selected' : ''; ?>>Female</option></select></div>
                <div><label for="dob">Date of Birth</label><input class="form-control" type="date" id="dob" name="date_of_birth" value="<?php echo sms_teacher_field($old, $teacher, 'date_of_birth', 'date_of_birth'); ?>"></div>
                <div><label for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="<?php echo sms_teacher_field($old, $teacher, 'phone', 'phone'); ?>" required><?php if (isset($errors['phone'])): ?><span class="field-error"><?php echo sms_e($errors['phone']); ?></span><?php endif; ?></div>
                <div><label for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?php echo sms_teacher_field($old, $teacher, 'email', 'email'); ?>" required><?php if (isset($errors['email'])): ?><span class="field-error"><?php echo sms_e($errors['email']); ?></span><?php endif; ?></div>
                <div class="full"><label for="address">Address</label><textarea class="form-control" id="address" name="address"><?php echo sms_teacher_field($old, $teacher, 'address', 'address'); ?></textarea></div>
            </div>
        </section>
        <section class="module-card">
            <h4 class="mb-3">Employment Information</h4>
            <div class="form-grid">
                <div><label for="staffId">Staff ID</label><input class="form-control" id="staffId" value="<?php echo sms_e((string) $teacher['staff_no']); ?>" readonly></div>
                <div><label for="department">Department</label><select class="form-select" id="department" name="department"><?php foreach ($departments as $department): ?><option value="<?php echo (int) $department['id']; ?>" <?php echo (string) ($old['department'] ?? (string) $teacher['department_id']) === (string) $department['id'] ? 'selected' : ''; ?>><?php echo sms_e($department['name']); ?></option><?php endforeach; ?></select><?php if (isset($errors['department_id'])): ?><span class="field-error"><?php echo sms_e($errors['department_id']); ?></span><?php endif; ?></div>
                <div><label for="designation">Designation</label><select class="form-select" id="designation" name="designation"><?php foreach ($designations as $designation): ?><option <?php echo ($old['designation'] ?? (string) $teacher['designation']) === $designation ? 'selected' : ''; ?>><?php echo sms_e($designation); ?></option><?php endforeach; ?></select></div>
                <div><label for="employmentDate">Employment Date</label><input class="form-control" type="date" id="employmentDate" name="employment_date" value="<?php echo sms_teacher_field($old, $teacher, 'employment_date', 'employment_date'); ?>"></div>
                <div><label for="employmentStatus">Employment Status</label><select class="form-select" id="employmentStatus" name="employment_status"><?php foreach ($statuses as $status): ?><option <?php echo ($old['employment_status'] ?? $currentEmploymentStatus) === $status ? 'selected' : ''; ?>><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div><label for="qualification">Qualification</label><input class="form-control" id="qualification" name="qualification" value="<?php echo sms_teacher_field($old, $teacher, 'qualification', 'qualification'); ?>"></div>
                <div><label for="experience">Years of Experience</label><input class="form-control" type="number" min="0" id="experience" name="experience" value="<?php echo sms_teacher_field($old, $teacher, 'experience', 'years_experience'); ?>"></div>
                <div><label for="salaryGrade">Salary Grade</label><input class="form-control" id="salaryGrade" name="salary_grade" value="<?php echo sms_teacher_field($old, $teacher, 'salary_grade', 'salary_grade'); ?>"></div>
                <div><label for="contractType">Contract Type</label><select class="form-select" id="contractType" name="contract_type"><?php foreach ($contractTypes as $type): ?><option <?php echo ($old['contract_type'] ?? (string) $teacher['contract_type']) === $type ? 'selected' : ''; ?>><?php echo sms_e($type); ?></option><?php endforeach; ?></select></div>
            </div>
        </section>
        <section class="module-card">
            <h4 class="mb-3">Assigned Subjects & Classes</h4>
            <div class="form-grid two">
                <div class="multi-select-box"><label>Subjects</label><div class="multi-select-options"><?php foreach ($subjects as $subject): ?><label class="multi-option"><input class="form-check-input" type="checkbox" name="subjects[]" value="<?php echo (int) $subject['id']; ?>" <?php echo in_array((int) $subject['id'], $assignedSubjectIds, true) ? 'checked' : ''; ?>> <?php echo sms_e($subject['name']); ?></label><?php endforeach; ?></div></div>
                <div class="multi-select-box"><label>Classes</label><div class="multi-select-options"><?php foreach ($classSections as $section): ?><label class="multi-option"><input class="form-check-input" type="checkbox" name="classes[]" value="<?php echo (int) $section['id']; ?>" <?php echo in_array((int) $section['id'], $assignedSectionIds, true) ? 'checked' : ''; ?>> <?php echo sms_e($section['label']); ?></label><?php endforeach; ?></div></div>
            </div>
        </section>
        <?php if (!empty($teacher['documents'])): ?>
        <section class="module-card">
            <h4 class="mb-3">Uploaded Documents</h4>
            <?php foreach ($teacher['documents'] as $doc): ?><a class="module-btn btn-outline-soft me-2 mb-2" href="../<?php echo sms_e(ltrim((string) $doc['file_path'], '/')); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file"></i> <?php echo sms_e($doc['document_type']); ?></a><?php endforeach; ?>
        </section>
        <?php endif; ?>
        <section class="module-card"><div class="d-flex justify-content-end flex-wrap gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Update Teacher</button><a class="module-btn btn-muted-soft" href="teachers.php">Cancel</a></div></section>
    </form>
</div></div></div>
<script>
document.getElementById('teacherEditForm').addEventListener('submit', function(e){ if (!this.checkValidity()) { this.reportValidity(); e.preventDefault(); } });
var photoInput = document.getElementById('profilePhoto');
if (photoInput) { photoInput.addEventListener('change', function(){ var file = this.files && this.files[0]; if (file) { document.getElementById('photoPreview').src = URL.createObjectURL(file); } }); }
</script>
<?php require_once('includes/footer.php'); ?>
