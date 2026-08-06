<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

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

require_once('includes/header.php');

$fullName = trim($teacher['first_name'] . ' ' . $teacher['last_name']);
$photoUrl = !empty($teacher['passport_path']) ? '../' . ltrim((string) $teacher['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';

$profileSummaryCards = [
    ['title' => 'Subjects Taught', 'value' => count($teacher['subjects']), 'icon' => 'fa-book-open', 'color' => 'warning'],
    ['title' => 'Classes Assigned', 'value' => count($teacher['classes']), 'icon' => 'fa-school', 'color' => 'blue'],
    ['title' => 'Years of Experience', 'value' => (float) $teacher['years_experience'], 'icon' => 'fa-briefcase', 'color' => 'success'],
    ['title' => 'Documents on File', 'value' => count($teacher['documents']), 'icon' => 'fa-folder-open', 'color' => 'success'],
];
?>
<style>
.admin-teacher-module { --atm-primary:#0f766e; --atm-primary-dark:#115e59; --atm-soft:rgba(15,118,110,.1); --atm-border:rgba(15,118,110,.16); --atm-ink:#10201d; --atm-muted:#64748b; --atm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
.admin-teacher-module .module-hero,.admin-teacher-module .module-card { background:rgba(255,255,255,.98); border:1px solid var(--atm-border); box-shadow:var(--atm-shadow); }
.admin-teacher-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
.admin-teacher-module .breadcrumb-line { color:var(--atm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
.admin-teacher-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
.admin-teacher-module h3,.admin-teacher-module h4 { color:var(--atm-ink); font-weight:900; }
.admin-teacher-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; }
.admin-teacher-module .profile-photo { width:82px; height:82px; border-radius:50%; object-fit:cover; border:4px solid #dcfce7; }
.admin-teacher-module .info-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
.admin-teacher-module .info-grid .full { grid-column:1/-1; }
.admin-teacher-module .info-grid label { display:block; color:var(--atm-muted); font-size:12px; font-weight:900; text-transform:uppercase; margin-bottom:4px; }
.admin-teacher-module .info-grid p { margin:0; font-weight:800; color:var(--atm-ink); }
.admin-teacher-module .chip-list { display:flex; flex-wrap:wrap; gap:6px; }
.admin-teacher-module .chip { display:inline-flex; padding:6px 10px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; }
.admin-teacher-module .status-badge { display:inline-flex; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; background:rgba(22,163,74,.12); color:#15803d; }
.admin-teacher-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; }
.admin-teacher-module .module-btn:hover { color:#fff; }
.admin-teacher-module .btn-primary-soft { background:var(--atm-primary); color:#fff; }
.admin-teacher-module .btn-outline-soft { background:#fff; color:var(--atm-primary-dark); border:1px solid var(--atm-border); }
@media(max-width:767.98px){ .admin-teacher-module .info-grid{grid-template-columns:1fr} }
</style>

<div class="admin-teacher-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Teacher profile header. -->
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> Teacher Profile</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img class="profile-photo" src="<?php echo sms_e($photoUrl); ?>" alt="Teacher passport">
                <div>
                    <span class="module-kicker"><i class="fa-solid fa-id-card"></i> Teacher Profile</span>
                    <h3 class="mt-3 mb-1"><?php echo sms_e($fullName); ?></h3>
                    <p class="text-muted fw-bold mb-0"><?php echo sms_e((string) $teacher['staff_no']); ?> | <?php echo sms_e($teacher['department_name'] ?? 'Unassigned'); ?></p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="module-btn btn-primary-soft" href="edit-teacher.php?teacher_id=<?php echo (int) $teacherId; ?>"><i class="fa-solid fa-pen"></i> Edit Profile</a>
                <a class="module-btn btn-outline-soft" href="assign-subjects.php?teacher_id=<?php echo (int) $teacherId; ?>"><i class="fa-solid fa-book-open"></i> Assign Subjects</a>
                <a class="module-btn btn-outline-soft" href="assign-classes.php?teacher_id=<?php echo (int) $teacherId; ?>"><i class="fa-solid fa-school"></i> Assign Classes</a>
                <a class="module-btn btn-outline-soft" href="teacher-timetable.php?teacher_id=<?php echo (int) $teacherId; ?>"><i class="fa-solid fa-calendar-days"></i> Timetable</a>
            </div>
        </div>
    </section>

    <!-- Quick teacher statistics rendered with the shared statistics-card component. -->
    <section class="row g-3 mb-4" aria-label="Teacher profile summary cards">
        <?php foreach ($profileSummaryCards as $card): ?>
            <div class="col-sm-6 col-xl-3">
                <?php sms_render_component('statistics-card', $card); ?>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Personal information. -->
    <section class="module-card">
        <h4>Personal Information</h4>
        <div class="info-grid">
            <div><label>Full Name</label><p><?php echo sms_e($fullName); ?></p></div>
            <div><label>Staff ID</label><p><?php echo sms_e((string) $teacher['staff_no']); ?></p></div>
            <div><label>Gender</label><p><?php echo sms_e(ucfirst((string) ($teacher['gender'] ?? 'Not set'))); ?></p></div>
            <div><label>Date of Birth</label><p><?php echo sms_e((string) ($teacher['date_of_birth'] ?? 'Not set')); ?></p></div>
            <div><label>Phone</label><p><?php echo sms_e((string) ($teacher['phone'] ?? 'Not set')); ?></p></div>
            <div><label>Email</label><p><?php echo sms_e((string) ($teacher['email'] ?? 'Not set')); ?></p></div>
            <div class="full"><label>Address</label><p><?php echo sms_e((string) ($teacher['address'] ?? 'Not set')); ?></p></div>
        </div>
    </section>

    <!-- Professional information. -->
    <section class="module-card">
        <h4>Professional Information</h4>
        <div class="info-grid">
            <div><label>Department</label><p><?php echo sms_e($teacher['department_name'] ?? 'Unassigned'); ?></p></div>
            <div><label>Designation</label><p><?php echo sms_e((string) ($teacher['designation'] ?? 'Not set')); ?></p></div>
            <div><label>Qualification</label><p><?php echo sms_e((string) ($teacher['qualification'] ?? 'Not set')); ?></p></div>
            <div><label>Employment Date</label><p><?php echo sms_e((string) ($teacher['employment_date'] ?? 'Not set')); ?></p></div>
            <div><label>Employment Status</label><p><span class="status-badge"><?php echo sms_e(ucfirst(str_replace('_', ' ', (string) $teacher['employment_status']))); ?></span></p></div>
            <div><label>Experience</label><p><?php echo sms_e((string) $teacher['years_experience']); ?> Years</p></div>
            <div><label>Salary Grade</label><p><?php echo sms_e((string) ($teacher['salary_grade'] ?? 'Not set')); ?></p></div>
            <div><label>Contract Type</label><p><?php echo sms_e((string) ($teacher['contract_type'] ?? 'Not set')); ?></p></div>
        </div>
    </section>

    <!-- Assigned subjects and classes. -->
    <section class="row g-3">
        <div class="col-lg-6">
            <div class="module-card h-100">
                <h4>Assigned Subjects</h4>
                <p class="text-muted fw-bold">Subjects Taught: <?php echo count($teacher['subjects']); ?></p>
                <div class="chip-list"><?php foreach ($teacher['subjects'] as $subject): ?><span class="chip"><?php echo sms_e($subject['name']); ?></span><?php endforeach; ?><?php if (!$teacher['subjects']): ?><span class="text-muted">No subjects assigned yet.</span><?php endif; ?></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="module-card h-100">
                <h4>Assigned Classes</h4>
                <p class="text-muted fw-bold">Classes Assigned: <?php echo count($teacher['classes']); ?></p>
                <div class="chip-list"><?php foreach ($teacher['classes'] as $class): ?><span class="chip"><?php echo sms_e($class['name']); ?></span><?php endforeach; ?><?php if (!$teacher['classes']): ?><span class="text-muted">No classes assigned yet.</span><?php endif; ?></div>
            </div>
        </div>
    </section>

    <?php if (!empty($teacher['documents'])): ?>
    <section class="module-card">
        <h4>Uploaded Documents</h4>
        <?php foreach ($teacher['documents'] as $doc): ?><a class="module-btn btn-outline-soft me-2 mb-2" href="../<?php echo sms_e(ltrim((string) $doc['file_path'], '/')); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file"></i> <?php echo sms_e($doc['document_type']); ?></a><?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>

</div>
</div>
<?php require_once('includes/footer.php'); ?>
