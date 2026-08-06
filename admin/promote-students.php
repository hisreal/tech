<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\StudentService;

$studentService = new StudentService();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
    } else {
        $studentIds = array_map('intval', (array) ($_POST['student_ids'] ?? []));
        $result = $studentService->promote(
            $studentIds,
            (int) ($_POST['from_session'] ?? 0),
            (int) ($_POST['to_session'] ?? 0),
            (int) ($_POST['to_class'] ?? 0),
            (int) ($_POST['to_section'] ?? 0) ?: null,
            sms_current_user()
        );
        sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
    }

    header('Location: promote-students.php?' . http_build_query([
        'from_session' => $_POST['from_session'] ?? '',
        'from_class' => $_POST['from_class'] ?? '',
        'from_section' => $_POST['from_section'] ?? '',
        'to_session' => $_POST['to_session'] ?? '',
        'to_class' => $_POST['to_class'] ?? '',
        'to_section' => $_POST['to_section'] ?? '',
    ]));
    exit;
}

$flashMessages = sms_flash();

require_once('includes/header.php');

$fromSession = (int) ($_GET['from_session'] ?? 0);
$fromClass = (int) ($_GET['from_class'] ?? 0);
$fromSection = (int) ($_GET['from_section'] ?? 0);
$toSession = (int) ($_GET['to_session'] ?? 0);
$toClass = (int) ($_GET['to_class'] ?? 0);
$toSection = (int) ($_GET['to_section'] ?? 0);

$sessions = $studentService->sessionsForSelect();
$classes = $studentService->classesForSelect();
$sections = $studentService->sectionsForSelect();
$currentSessionId = $studentService->currentSessionId();

$loaded = false;
$students = [];
if ($fromSession > 0 && $fromClass > 0) {
    $loaded = true;
    $result = $studentService->list([
        'session_id' => $fromSession,
        'class_id' => $fromClass,
        'section_id' => $fromSection ?: '',
        'status' => 'active',
    ], 1, 500);
    $students = $result['data'];
}

$promotionHistory = $studentService->promotionHistory(15);
?>

<style>
    .admin-student-module { --asm-primary:#0f766e; --asm-primary-dark:#115e59; --asm-soft:rgba(15,118,110,.1); --asm-border:rgba(15,118,110,.16); --asm-ink:#10201d; --asm-muted:#64748b; --asm-danger:#dc2626; --asm-warning:#d97706; --asm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-student-module .module-hero,.admin-student-module .module-card,.admin-student-module .mini-card { background:rgba(255,255,255,.98); border:1px solid var(--asm-border); box-shadow:var(--asm-shadow); }
    .admin-student-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-student-module .breadcrumb-line { color:var(--asm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-student-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--asm-soft); color:var(--asm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-student-module h3,.admin-student-module h4,.admin-student-module h5 { color:var(--asm-ink); font-weight:900; }
    .admin-student-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; }
    .admin-student-module .filter-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    .admin-student-module label { color:var(--asm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-student-module .form-control,.admin-student-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-student-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .module-btn:hover { transform:translateY(-2px); color:#fff; }
    .admin-student-module .btn-primary-soft { background:var(--asm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-student-module .btn-muted-soft { background:#f1f5f9; color:var(--asm-ink); }
    .admin-student-module .btn-outline-soft { background:#fff; color:var(--asm-primary-dark); border:1px solid var(--asm-border); }
    .admin-student-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--asm-danger); }
    .admin-student-module .table-shell { overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:18px; }
    .admin-student-module table { min-width:920px; margin-bottom:0; }
    .admin-student-module thead th { position:sticky; top:0; z-index:2; background:#f0fdf4; color:var(--asm-primary-dark); font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid var(--asm-border); }
    .admin-student-module tbody td { vertical-align:middle; color:#1f2937; font-weight:700; }
    .admin-student-module mini-grid,.admin-student-module .mini-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
    .admin-student-module .mini-card { border-radius:18px; padding:18px; }
    .admin-student-module .mini-card span { color:var(--asm-muted); font-weight:800; font-size:13px; }
    .admin-student-module .mini-card strong { display:block; margin-top:8px; color:var(--asm-ink); font-size:22px; }
    @media(max-width:991.98px){ .admin-student-module .filter-grid,.admin-student-module .mini-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-student-module .module-hero,.admin-student-module .module-card{padding:18px;border-radius:18px}.admin-student-module .filter-grid,.admin-student-module .mini-grid{grid-template-columns:1fr}.admin-student-module .module-btn{width:100%} }
</style>

<div class="admin-student-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Student Management <i class="fa-solid fa-angle-right mx-1"></i> Promote Students</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-arrow-up-right-dots"></i> Promotion Workspace</span>
                <h3 class="mt-3 mb-2">Promote Students</h3>
                <p class="text-muted mb-0">Move students from their current class/session to the next academic placement with a clear audit trail.</p>
            </div>
            <a href="student-list.php" class="module-btn btn-outline-soft"><i class="fa-solid fa-users"></i> All Students</a>
        </div>
    </section>

    <section class="module-card">
        <h4 class="mb-2">Promotion Filters</h4>
        <p class="text-muted mb-3">Select the current class and destination class, then load students for promotion.</p>
        <form method="get">
            <div class="filter-grid">
                <div><label for="fromSessionSel">Current Academic Session</label><select class="form-select" id="fromSessionSel" name="from_session" required><option value="">Select Session</option><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo $fromSession === (int) $session['id'] ? 'selected' : ''; ?>><?php echo sms_e($session['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="fromClassSel">Current Class</label><select class="form-select" id="fromClassSel" name="from_class" required><option value="">Select Class</option><?php foreach ($classes as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo $fromClass === (int) $class['id'] ? 'selected' : ''; ?>><?php echo sms_e($class['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="fromSectionSel">Current Section</label><select class="form-select" id="fromSectionSel" name="from_section"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option value="<?php echo (int) $section['id']; ?>" data-class="<?php echo (int) $section['class_id']; ?>" <?php echo $fromSection === (int) $section['id'] ? 'selected' : ''; ?>><?php echo sms_e($section['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="toSessionSel">Next Academic Session</label><select class="form-select" id="toSessionSel" name="to_session" required><option value="">Select Next Session</option><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo $toSession === (int) $session['id'] ? 'selected' : ''; ?>><?php echo sms_e($session['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="toClassSel">Destination Class</label><select class="form-select" id="toClassSel" name="to_class" required><option value="">Select Destination</option><?php foreach ($classes as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo $toClass === (int) $class['id'] ? 'selected' : ''; ?>><?php echo sms_e($class['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="toSectionSel">Destination Section</label><select class="form-select" id="toSectionSel" name="to_section"><option value="">Select Section</option><?php foreach ($sections as $section): ?><option value="<?php echo (int) $section['id']; ?>" data-class="<?php echo (int) $section['class_id']; ?>" <?php echo $toSection === (int) $section['id'] ? 'selected' : ''; ?>><?php echo sms_e($section['name']); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-users-viewfinder"></i> Load Students</button><a class="module-btn btn-muted-soft" href="promote-students.php"><i class="fa-solid fa-rotate-left"></i> Reset</a></div>
            </div>
        </form>
    </section>

    <?php if ($loaded): ?>
        <section class="mini-grid" aria-label="Promotion summary">
            <div class="mini-card"><span>Loaded Students</span><strong id="loadedCount"><?php echo count($students); ?></strong></div>
            <div class="mini-card"><span>Selected Students</span><strong id="selectedCount">0</strong></div>
            <div class="mini-card"><span>Destination</span><strong><?php $destClass = current(array_filter($classes, fn($c) => (int) $c['id'] === $toClass)); ?><?php echo sms_e($destClass['name'] ?? 'Not selected'); ?></strong></div>
        </section>

        <section class="module-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Student Promotion Table</h4>
                    <p class="text-muted mb-0">Select individual students or use select all before promoting.</p>
                </div>
            </div>
            <?php if (!$students): ?>
                <p class="text-muted">No active students found for the selected session/class/section.</p>
            <?php else: ?>
                <form method="post" id="promoteForm">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="from_session" value="<?php echo (int) $fromSession; ?>">
                    <input type="hidden" name="from_class" value="<?php echo (int) $fromClass; ?>">
                    <input type="hidden" name="from_section" value="<?php echo (int) $fromSection; ?>">
                    <input type="hidden" name="to_session" value="<?php echo (int) $toSession; ?>">
                    <input type="hidden" name="to_class" value="<?php echo (int) $toClass; ?>">
                    <input type="hidden" name="to_section" value="<?php echo (int) $toSection; ?>">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button class="module-btn btn-primary-soft" type="submit" id="promoteSelectedBtn"><i class="fa-solid fa-check-double"></i> Promote Selected Students</button>
                        <button class="module-btn btn-outline-soft" type="button" id="promoteEntireClass"><i class="fa-solid fa-school-circle-check"></i> Promote Entire Class</button>
                    </div>
                    <div class="table-shell">
                        <table class="table align-middle">
                            <thead><tr><th><input class="form-check-input" type="checkbox" id="selectAllPromotion"></th><th>Registration Number</th><th>Student Name</th><th>Current Class</th><th>Current Section</th></tr></thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><input class="form-check-input promotion-select" type="checkbox" name="student_ids[]" value="<?php echo (int) $student['id']; ?>"></td>
                                        <td><?php echo sms_e($student['registration_no']); ?></td>
                                        <td><?php echo sms_e(trim($student['first_name'] . ' ' . $student['last_name'])); ?></td>
                                        <td><?php echo sms_e($student['class_name'] ?? ''); ?></td>
                                        <td><?php echo sms_e($student['section_name'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="module-card">
        <h4 class="mb-2">Promotion History</h4>
        <p class="text-muted mb-3">Recent promotion records.</p>
        <div class="table-shell">
            <table class="table align-middle">
                <thead><tr><th>Student</th><th>Previous Class</th><th>New Class</th><th>Session</th><th>Date Promoted</th><th>Promoted By</th></tr></thead>
                <tbody>
                    <?php foreach ($promotionHistory as $history): ?>
                        <tr><td><?php echo sms_e($history['student_name']); ?></td><td><?php echo sms_e($history['from_class_name']); ?></td><td><?php echo sms_e($history['to_class_name']); ?></td><td><?php echo sms_e($history['from_session_name'] . ' to ' . $history['to_session_name']); ?></td><td><?php echo sms_e($history['promoted_at']); ?></td><td><?php echo sms_e($history['promoted_by_name'] ?? 'System'); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$promotionHistory): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No promotions recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    var checks = Array.prototype.slice.call(document.querySelectorAll('.promotion-select'));
    var selectAll = document.getElementById('selectAllPromotion');
    var selectedCount = document.getElementById('selectedCount');
    function updateSelectedCount(){ if (selectedCount) { selectedCount.textContent = checks.filter(function(c){ return c.checked; }).length; } }
    if (selectAll) { selectAll.addEventListener('change', function(){ checks.forEach(function(c){ c.checked = selectAll.checked; }); updateSelectedCount(); }); }
    checks.forEach(function(c){ c.addEventListener('change', updateSelectedCount); });

    var form = document.getElementById('promoteForm');
    if (form) {
        form.addEventListener('submit', function(event){
            var count = checks.filter(function(c){ return c.checked; }).length;
            if (!count) {
                event.preventDefault();
                alert('Please select at least one student before promoting.');
                return;
            }
            if (!confirm('Promote ' + count + ' student(s) to the selected destination? This creates a new enrollment and cannot be undone from this screen.')) {
                event.preventDefault();
            }
        });
    }
    var promoteEntireClass = document.getElementById('promoteEntireClass');
    if (promoteEntireClass) {
        promoteEntireClass.addEventListener('click', function(){
            checks.forEach(function(c){ c.checked = true; });
            updateSelectedCount();
            form.requestSubmit(document.getElementById('promoteSelectedBtn'));
        });
    }

    function bindSectionFilter(classSelectId, sectionSelectId){
        var classSelect = document.getElementById(classSelectId);
        var sectionSelect = document.getElementById(sectionSelectId);
        if (!classSelect || !sectionSelect) { return; }
        function filter(){
            var selected = classSelect.value;
            Array.prototype.forEach.call(sectionSelect.options, function(option){
                if (!option.value) { return; }
                option.hidden = selected !== '' && option.dataset.class !== selected;
            });
        }
        classSelect.addEventListener('change', filter);
        filter();
    }
    bindSectionFilter('fromClassSel', 'fromSectionSel');
    bindSectionFilter('toClassSel', 'toSectionSel');
})();
</script>

<?php require_once('includes/footer.php'); ?>
