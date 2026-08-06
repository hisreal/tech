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
require_once('includes/teacher-module-styles.php');

$subjects = $teacherService->subjectsForSelect();
$assignedSubjectIds = array_column($teacher['subjects'], 'id');
$fullName = trim($teacher['first_name'] . ' ' . $teacher['last_name']);
?>
<div class="admin-teacher-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> Assign Subjects</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-book-open"></i> Assign Subjects</span><h3 class="mt-3 mb-2"><?php echo sms_e($fullName); ?></h3><p class="text-muted mb-0">Manage which subjects this teacher is assigned to teach.</p></div><a class="module-btn btn-outline-soft" href="teacher-profile.php?teacher_id=<?php echo (int) $teacherId; ?>">Back to Profile</a></div></section>
    <form id="assignmentForm" method="post" action="teacher-assign-subjects.php">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="teacher_id" value="<?php echo (int) $teacherId; ?>">
        <section class="module-card">
            <h4>Current Subjects</h4>
            <div class="chip-list mb-3"><?php foreach ($teacher['subjects'] as $subject): ?><span class="chip"><?php echo sms_e($subject['name']); ?></span><?php endforeach; ?><?php if (!$teacher['subjects']): ?><span class="text-muted">No subjects assigned yet.</span><?php endif; ?></div>
            <label for="subjectSearch">Search Subjects</label>
            <input class="form-control mb-3" id="subjectSearch" placeholder="Search subject list">
            <div class="multi-select-box"><div class="multi-select-options" id="subjectOptions"><?php foreach ($subjects as $subject): ?><label class="multi-option"><input class="form-check-input" type="checkbox" name="subjects[]" value="<?php echo (int) $subject['id']; ?>" <?php echo in_array((int) $subject['id'], $assignedSubjectIds, true) ? 'checked' : ''; ?>> <?php echo sms_e($subject['name']); ?></label><?php endforeach; ?></div></div>
        </section>
        <section class="module-card"><div class="d-flex justify-content-end flex-wrap gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Subjects</button><a class="module-btn btn-muted-soft" href="teachers.php">Cancel</a></div></section>
    </form>
</div></div></div>
<script>
var s = document.getElementById('subjectSearch');
s.addEventListener('input', function(){ var q = this.value.toLowerCase(); document.querySelectorAll('#subjectOptions .multi-option').forEach(function(o){ o.style.display = o.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none'; }); });
</script>
<?php require_once('includes/footer.php'); ?>
