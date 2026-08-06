<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\StudentService;

$studentService = new StudentService();
$id = (int) ($_GET['id'] ?? 0);
$student = $id > 0 ? $studentService->find($id) : null;

if ($student === null) {
    sms_flash_set('error', 'Student not found.');
    header('Location: student-list.php');
    exit;
}

$flashMessages = sms_flash();

require_once('includes/header.php');

$fullName = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
$photoUrl = !empty($student['passport_path']) ? '../' . ltrim((string) $student['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
$enrollment = $student['enrollment'] ?? [];
$guardian = $student['guardian'] ?? [];

$personalFields = [
    ['Registration Number', $student['registration_no'], 'fa-id-card'],
    ['Admission Number', $student['admission_no'] ?? 'Not set', 'fa-address-card'],
    ['Gender', ucfirst((string) ($student['gender'] ?? '')), 'fa-venus-mars'],
    ['Date of Birth', $student['date_of_birth'] ?? 'Not set', 'fa-cake-candles'],
    ['Blood Group', $student['blood_group'] ?? 'Not set', 'fa-droplet'],
    ['Genotype', $student['genotype'] ?? 'Not set', 'fa-dna'],
    ['Religion', $student['religion'] ?? 'Not set', 'fa-hands-praying'],
    ['Nationality', $student['nationality'] ?? 'Not set', 'fa-flag'],
];
$contactFields = [
    ['Phone', $student['phone'] ?? 'Not set', 'fa-phone'],
    ['Email', $student['email'] ?? 'Not set', 'fa-envelope'],
    ['Address', $student['address'] ?? 'Not set', 'fa-location-dot'],
    ['State', $student['state'] ?? 'Not set', 'fa-map'],
    ['Local Government', $student['local_government'] ?? 'Not set', 'fa-map-location-dot'],
    ['Emergency Contact', $student['emergency_contact'] ?? 'Not set', 'fa-truck-medical'],
];
$academicFields = [
    ['Academic Session', $enrollment['session_name'] ?? 'Not enrolled', 'fa-calendar-days'],
    ['Class', $enrollment['class_name'] ?? 'Not assigned', 'fa-school'],
    ['Section', $enrollment['section_name'] ?? 'Not assigned', 'fa-layer-group'],
    ['Roll Number', $enrollment['roll_number'] ?? 'Not set', 'fa-hashtag'],
    ['Enrolled Since', $enrollment['enrolled_at'] ?? 'Not set', 'fa-calendar-check'],
];
$guardianFields = [
    ['Guardian Name', $guardian['full_name'] ?? 'Not set', 'fa-user-shield'],
    ['Relationship', $guardian['relationship'] ?? 'Not set', 'fa-people-arrows'],
    ['Phone', $guardian['phone'] ?? 'Not set', 'fa-phone'],
    ['Email', $guardian['email'] ?? 'Not set', 'fa-envelope'],
    ['Occupation', $guardian['occupation'] ?? 'Not set', 'fa-briefcase'],
    ['Address', $guardian['address'] ?? 'Not set', 'fa-location-dot'],
];
$medicalFields = [
    ['Medical Conditions', $student['medical_conditions'] ?? 'None recorded', 'fa-notes-medical'],
    ['Allergies', $student['allergies'] ?? 'None recorded', 'fa-triangle-exclamation'],
];
?>
<style>
.profile-db-page{--p-primary:#0f766e;--p-dark:#115e59;--p-soft:rgba(15,118,110,.1);--p-border:rgba(15,118,110,.18);--p-muted:#64748b;--p-ink:#10201d;--p-shadow:0 22px 60px rgba(15,23,42,.09);padding-bottom:34px}.profile-db-page .profile-hero,.profile-db-page .profile-card,.profile-db-page .info-card{background:rgba(255,255,255,.98);border:1px solid var(--p-border);box-shadow:var(--p-shadow)}.profile-db-page .profile-hero{padding:28px;border-radius:26px;margin-bottom:22px;background:linear-gradient(135deg,rgba(240,253,244,.98),#fff)}.profile-db-page .profile-photo{width:132px;height:132px;border-radius:30px;object-fit:cover;border:6px solid #fff;box-shadow:0 18px 38px rgba(15,23,42,.16)}.profile-db-page h3,.profile-db-page h4{color:var(--p-ink);font-weight:900}.profile-db-page .role-chip,.profile-db-page .status-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;font-weight:900;font-size:12px}.profile-db-page .role-chip{background:var(--p-soft);color:var(--p-dark)}.profile-db-page .status-chip{background:rgba(22,163,74,.12);color:#16a34a}.profile-db-page .profile-card{border-radius:24px;padding:24px;margin-bottom:22px}.profile-db-page .info-card{height:100%;padding:17px;border-radius:18px}.profile-db-page .field-icon{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:var(--p-soft);color:var(--p-primary);flex:0 0 auto}.profile-db-page .field-label{color:var(--p-muted);font-size:12px;font-weight:900;text-transform:uppercase}.profile-db-page .field-value{color:var(--p-ink);font-weight:850;word-break:break-word}.profile-db-page .main-btn{min-height:46px;border:0;border-radius:15px;background:linear-gradient(135deg,var(--p-primary),var(--p-dark));color:#fff;font-weight:900;padding:0 22px;display:inline-flex;align-items:center;gap:8px;text-decoration:none}.profile-db-page .main-btn:hover{color:#fff}
</style>
<div class="profile-db-page">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="profile-hero">
        <div class="row align-items-center row-gap-4">
            <div class="col-xl-8">
                <div class="d-flex align-items-center flex-wrap gap-4">
                    <img src="<?php echo sms_e($photoUrl); ?>" class="profile-photo" alt="Profile photo">
                    <div>
                        <span class="role-chip"><i class="fa-solid fa-user-graduate"></i> Student</span>
                        <h3 class="mt-3 mb-1"><?php echo sms_e($fullName); ?></h3>
                        <p class="text-muted fw-bold mb-2"><?php echo sms_e($student['registration_no']); ?></p>
                        <span class="status-chip"><i class="fa-solid fa-circle-check"></i><?php echo sms_e(ucfirst((string) $student['status'])); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 text-xl-end d-flex gap-2 justify-content-xl-end flex-wrap">
                <a href="edit-student.php?id=<?php echo (int) $student['id']; ?>" class="main-btn"><i class="fa-solid fa-user-pen"></i>Edit Student</a>
                <a href="student-list.php" class="main-btn" style="background:#f1f5f9;color:var(--p-ink)"><i class="fa-solid fa-arrow-left"></i>All Students</a>
            </div>
        </div>
    </section>

    <?php $sections = [['Personal Information', $personalFields], ['Contact Information', $contactFields], ['Academic Information', $academicFields], ['Guardian Information', $guardianFields], ['Medical Information', $medicalFields]]; ?>
    <?php foreach ($sections as $section): ?>
        <section class="profile-card">
            <h4 class="mb-3"><?php echo sms_e($section[0]); ?></h4>
            <div class="row g-3">
                <?php foreach ($section[1] as $field): ?>
                    <div class="col-md-6 col-xl-4"><div class="info-card"><div class="d-flex gap-3"><span class="field-icon"><i class="fa-solid <?php echo sms_e($field[2]); ?>"></i></span><div><div class="field-label"><?php echo sms_e($field[0]); ?></div><div class="field-value"><?php echo sms_e((string) $field[1] ?: 'Not set'); ?></div></div></div></div></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php if (!empty($student['documents'])): ?>
        <section class="profile-card">
            <h4 class="mb-3">Uploaded Documents</h4>
            <?php foreach ($student['documents'] as $doc): ?>
                <a class="main-btn me-2 mb-2" style="background:#f1f5f9;color:var(--p-ink)" href="../<?php echo sms_e(ltrim((string) $doc['file_path'], '/')); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file"></i> <?php echo sms_e($doc['document_type']); ?></a>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</div>
</div></div>
<?php require_once('includes/footer.php'); ?>
