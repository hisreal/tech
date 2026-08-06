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

$classSections = $teacherService->classSectionsForSelect();
$assignedSectionIds = array_column($teacher['classes'], 'id');
$fullName = trim($teacher['first_name'] . ' ' . $teacher['last_name']);
?>
<div class="admin-teacher-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> Assign Classes</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-school"></i> Assign Classes</span><h3 class="mt-3 mb-2"><?php echo sms_e($fullName); ?></h3><p class="text-muted mb-0">Manage which classes and sections this teacher is assigned to teach.</p></div><a class="module-btn btn-outline-soft" href="teacher-profile.php?teacher_id=<?php echo (int) $teacherId; ?>">Back to Profile</a></div></section>
    <form id="assignmentForm" method="post" action="teacher-assign-classes.php">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="teacher_id" value="<?php echo (int) $teacherId; ?>">
        <section class="module-card">
            <h4>Current Classes</h4>
            <div class="chip-list mb-3"><?php foreach ($teacher['classes'] as $class): ?><span class="chip"><?php echo sms_e($class['name']); ?></span><?php endforeach; ?><?php if (!$teacher['classes']): ?><span class="text-muted">No classes assigned yet.</span><?php endif; ?></div>
            <label for="classSearch">Search Classes</label>
            <input class="form-control mb-3" id="classSearch" placeholder="Search class list">
            <div class="multi-select-box"><div class="multi-select-options" id="classOptions"><?php foreach ($classSections as $section): ?><label class="multi-option"><input class="form-check-input" type="checkbox" name="classes[]" value="<?php echo (int) $section['id']; ?>" <?php echo in_array((int) $section['id'], $assignedSectionIds, true) ? 'checked' : ''; ?>> <?php echo sms_e($section['label']); ?></label><?php endforeach; ?></div></div>
        </section>
        <section class="module-card"><div class="d-flex justify-content-end flex-wrap gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Classes</button><a class="module-btn btn-muted-soft" href="teachers.php">Cancel</a></div></section>
    </form>
</div></div></div>
<script>
var s = document.getElementById('classSearch');
s.addEventListener('input', function(){ var q = this.value.toLowerCase(); document.querySelectorAll('#classOptions .multi-option').forEach(function(o){ o.style.display = o.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none'; }); });
</script>
<?php require_once('includes/footer.php'); ?>
