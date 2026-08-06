<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\StudentService;

$studentService = new StudentService();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $formAction = (string) ($_POST['form_action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    $actor = sms_current_user();

    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
    } elseif ($formAction === 'delete') {
        $result = $studentService->delete($id, $actor);
        sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
    }

    header('Location: student-list.php?' . http_build_query($_GET));
    exit;
}

$flashMessages = sms_flash();

require_once('includes/header.php');

$search = trim((string) ($_GET['search'] ?? ''));
$classFilter = trim((string) ($_GET['class'] ?? ''));
$sectionFilter = trim((string) ($_GET['section'] ?? ''));
$sessionFilter = trim((string) ($_GET['session'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$genderFilter = trim((string) ($_GET['gender'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $studentService->list([
    'search' => $search,
    'class_id' => $classFilter,
    'section_id' => $sectionFilter,
    'session_id' => $sessionFilter,
    'status' => $statusFilter,
    'gender' => $genderFilter,
], $page, 10);

$students = $result['data'];
$meta = $result['meta'];

$classOptions = $studentService->classesForSelect();
$sectionOptions = $studentService->sectionsForSelect($classFilter !== '' ? (int) $classFilter : null);
$sessionOptions = $studentService->sessionsForSelect();
$statusOptions = ['active' => 'Active', 'graduated' => 'Graduated', 'withdrawn' => 'Withdrawn', 'suspended' => 'Suspended', 'deleted' => 'Deleted'];

$currentSessionId = $studentService->currentSessionId();
$totalStudents = (int) $studentService->list(['session_id' => $currentSessionId], 1, 1)['meta']['total'];
$maleStudents = (int) $studentService->list(['session_id' => $currentSessionId, 'gender' => 'male'], 1, 1)['meta']['total'];
$femaleStudents = (int) $studentService->list(['session_id' => $currentSessionId, 'gender' => 'female'], 1, 1)['meta']['total'];
$newAdmissions = (int) $studentService->list(['session_id' => $currentSessionId, 'status' => 'active'], 1, 1)['meta']['total'];

$summaryCards = [
    ['title' => 'Total Students', 'value' => number_format($totalStudents), 'icon' => 'fa-user-graduate', 'color' => 'success'],
    ['title' => 'Male Students', 'value' => number_format($maleStudents), 'icon' => 'fa-person', 'color' => 'blue'],
    ['title' => 'Female Students', 'value' => number_format($femaleStudents), 'icon' => 'fa-person-dress', 'color' => 'warning'],
    ['title' => 'Active This Session', 'value' => number_format($newAdmissions), 'icon' => 'fa-user-plus', 'color' => 'success'],
];

function sms_student_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    unset($query['page']);
    return 'student-list.php?' . http_build_query($query);
}
?>

<style>
    /* Student list module styles: scoped to preserve shared dashboard styling. */
    .admin-student-module { --asm-primary:#0f766e; --asm-primary-dark:#115e59; --asm-soft:rgba(15,118,110,.1); --asm-border:rgba(15,118,110,.16); --asm-ink:#10201d; --asm-muted:#64748b; --asm-danger:#dc2626; --asm-warning:#d97706; --asm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-student-module .module-hero,.admin-student-module .module-card,.admin-student-module .summary-card { background:rgba(255,255,255,.98); border:1px solid var(--asm-border); box-shadow:var(--asm-shadow); }
    .admin-student-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-student-module .breadcrumb-line { color:var(--asm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-student-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--asm-soft); color:var(--asm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-student-module h3,.admin-student-module h4,.admin-student-module h5 { color:var(--asm-ink); font-weight:900; }
    .admin-student-module .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .summary-card:hover,.admin-student-module .module-card:hover { transform:translateY(-2px); box-shadow:0 20px 42px rgba(15,23,42,.11); }
    .admin-student-module .summary-icon { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:var(--asm-soft); color:var(--asm-primary); }
    .admin-student-module .summary-icon.blue { background:rgba(37,99,235,.1); color:#2563eb; }
    .admin-student-module .summary-icon.warning { background:rgba(245,158,11,.13); color:var(--asm-warning); }
    .admin-student-module .summary-card h4 { margin:12px 0 2px; font-size:25px; }
    .admin-student-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; }
    .admin-student-module .filter-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .admin-student-module label { color:var(--asm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-student-module .form-control,.admin-student-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-student-module .form-control:focus,.admin-student-module .form-select:focus { border-color:var(--asm-primary); box-shadow:0 0 0 .18rem rgba(15,118,110,.14); }
    .admin-student-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .module-btn:hover { transform:translateY(-2px); color:#fff; }
    .admin-student-module .btn-primary-soft { background:var(--asm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-student-module .btn-muted-soft { background:#f1f5f9; color:var(--asm-ink); }
    .admin-student-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--asm-danger); }
    .admin-student-module .btn-outline-soft { background:#fff; color:var(--asm-primary-dark); border:1px solid var(--asm-border); }
    .admin-student-module .table-shell { overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:18px; }
    .admin-student-module table { min-width:1080px; margin-bottom:0; }
    .admin-student-module thead th { position:sticky; top:0; z-index:2; background:#f0fdf4; color:var(--asm-primary-dark); font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid var(--asm-border); }
    .admin-student-module tbody td { vertical-align:middle; color:#1f2937; font-weight:700; }
    .admin-student-module tbody tr:hover { background:rgba(15,118,110,.045); }
    .admin-student-module .student-passport { width:44px; height:44px; border-radius:50%; object-fit:cover; border:3px solid #dcfce7; }
    .admin-student-module .status-badge { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; }
    .admin-student-module .status-active { background:rgba(22,163,74,.12); color:#15803d; }
    .admin-student-module .status-graduated { background:rgba(37,99,235,.1); color:#1d4ed8; }
    .admin-student-module .status-suspended { background:rgba(220,38,38,.1); color:var(--asm-danger); }
    .admin-student-module .status-withdrawn { background:rgba(245,158,11,.13); color:var(--asm-warning); }
    .admin-student-module .status-deleted { background:rgba(100,116,139,.15); color:#475569; }
    .admin-student-module .student-action-dropdown { position:relative; display:inline-flex; justify-content:center; }
    .admin-student-module .action-menu-btn { width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:var(--asm-primary-dark); background:var(--asm-soft); border:1px solid var(--asm-border); }
    .admin-student-module .action-menu-btn:hover,.admin-student-module .action-menu-btn:focus { background:var(--asm-primary); color:#fff; box-shadow:0 10px 22px rgba(15,118,110,.18); }
    .admin-student-module .dropdown-menu.student-actions-menu { min-width:230px; padding:8px; border:1px solid var(--asm-border); border-radius:16px; box-shadow:0 20px 45px rgba(15,23,42,.14); }
    .admin-student-module .student-actions-menu .dropdown-item { display:flex; align-items:center; gap:10px; border-radius:10px; padding:9px 10px; color:var(--asm-ink); font-size:13px; font-weight:800; }
    .admin-student-module .student-actions-menu .dropdown-item i { width:18px; color:var(--asm-primary); }
    .admin-student-module .student-actions-menu .dropdown-item.text-danger i { color:var(--asm-danger); }
    .admin-student-module .student-actions-menu .dropdown-item:hover { background:var(--asm-soft); color:var(--asm-primary-dark); }
    .admin-student-module .pagination-strip { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding-top:16px; }
    .admin-student-module .page-link-soft { min-width:38px; height:38px; padding:0 10px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:#fff; border:1px solid var(--asm-border); color:var(--asm-primary-dark); font-weight:900; text-decoration:none; }
    .admin-student-module .page-link-soft.active { background:var(--asm-primary); color:#fff; }
    .admin-student-module .page-link-soft.disabled { opacity:.4; pointer-events:none; }
    @media(max-width:991.98px){ .admin-student-module .filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-student-module .module-hero,.admin-student-module .module-card{padding:18px;border-radius:18px}.admin-student-module .filter-grid{grid-template-columns:1fr}.admin-student-module .module-btn{width:100%}.admin-student-module .summary-card h4{font-size:21px} }
</style>

<div class="admin-student-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Page header and breadcrumb. -->
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Student Management <i class="fa-solid fa-angle-right mx-1"></i> All Students</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-users"></i> All Students</span>
                <h3 class="mt-3 mb-2">Student Management</h3>
                <p class="text-muted mb-0">View, search, filter, and manage every student record from one professional workspace.</p>
            </div>
            <a href="add-student.php" class="module-btn btn-primary-soft"><i class="fa-solid fa-user-plus"></i> Add Student</a>
        </div>
    </section>

    <!-- Summary cards reused through the shared dashboard-card component. -->
    <section class="row g-3 mb-4" aria-label="Student summary cards">
        <?php foreach ($summaryCards as $card): ?>
            <div class="col-sm-6 col-xl-3">
                <?php sms_render_component('dashboard-card', $card); ?>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Search and filtering controls. -->
    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Search & Filter</h4>
                <p class="text-muted mb-0">Filter by name, registration/admission number, class, session, gender, or status.</p>
            </div>
        </div>
        <form method="get">
            <div class="filter-grid">
                <div><label for="search">Search</label><input class="form-control" id="search" name="search" placeholder="Name, reg. no, or admission no" value="<?php echo sms_e($search); ?>"></div>
                <div><label for="classFilter">Class</label><select class="form-select" id="classFilter" name="class"><option value="">All Classes</option><?php foreach ($classOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $classFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="sectionFilter">Section</label><select class="form-select" id="sectionFilter" name="section"><option value="">All Sections</option><?php foreach ($sectionOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $sectionFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="sessionFilter">Academic Session</label><select class="form-select" id="sessionFilter" name="session"><option value="">Current Session</option><?php foreach ($sessionOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $sessionFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="genderFilter">Gender</label><select class="form-select" id="genderFilter" name="gender"><option value="">All Genders</option><option value="male" <?php echo $genderFilter === 'male' ? 'selected' : ''; ?>>Male</option><option value="female" <?php echo $genderFilter === 'female' ? 'selected' : ''; ?>>Female</option><option value="other" <?php echo $genderFilter === 'other' ? 'selected' : ''; ?>>Other</option></select></div>
                <div><label for="statusFilter">Status</label><select class="form-select" id="statusFilter" name="status"><option value="">Active, Graduated, etc.</option><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button><a class="module-btn btn-muted-soft" href="student-list.php"><i class="fa-solid fa-rotate-left"></i> Reset</a></div>
            </div>
        </form>
    </section>

    <!-- Student table. -->
    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Student Records</h4>
                <p class="text-muted mb-0"><?php echo (int) $meta['total']; ?> record(s) found.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print List</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table align-middle" id="studentTable">
                <thead>
                    <tr>
                        <th>Passport</th>
                        <th>Registration Number</th>
                        <th>Admission Number</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Parent Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php
                        $fullName = trim($student['first_name'] . ' ' . $student['last_name']);
                        $photo = !empty($student['passport_path']) ? '../' . ltrim((string) $student['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
                        $statusClass = 'status-' . strtolower((string) $student['status']);
                        ?>
                        <tr>
                            <td><img class="student-passport" src="<?php echo sms_e($photo); ?>" alt="<?php echo sms_e($fullName); ?> passport"></td>
                            <td><?php echo sms_e($student['registration_no']); ?></td>
                            <td><?php echo sms_e($student['admission_no'] ?? ''); ?></td>
                            <td><?php echo sms_e($fullName); ?></td>
                            <td><?php echo sms_e(ucfirst((string) ($student['gender'] ?? ''))); ?></td>
                            <td><?php echo sms_e($student['class_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo sms_e($student['section_name'] ?? ''); ?></td>
                            <td><?php echo sms_e($student['guardian_phone'] ?? ''); ?></td>
                            <td><span class="status-badge <?php echo sms_e($statusClass); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst((string) $student['status'])); ?></span></td>
                            <td>
                                <div class="dropdown student-action-dropdown">
                                    <button class="action-menu-btn" type="button" id="studentAction<?php echo (int) $student['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Student actions">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end student-actions-menu" aria-labelledby="studentAction<?php echo (int) $student['id']; ?>">
                                        <li><a class="dropdown-item" href="student-profile.php?id=<?php echo (int) $student['id']; ?>"><i class="fa-solid fa-eye"></i> View Profile</a></li>
                                        <li><a class="dropdown-item" href="edit-student.php?id=<?php echo (int) $student['id']; ?>"><i class="fa-solid fa-pen"></i> Edit Student</a></li>
                                        <li><a class="dropdown-item text-danger delete-student" href="#" data-id="<?php echo (int) $student['id']; ?>" data-name="<?php echo sms_e($fullName); ?>"><i class="fa-solid fa-trash"></i> Delete Student</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$students): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No students match your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-strip">
            <span class="text-muted fw-bold"><?php echo (int) $meta['total']; ?> record(s) &middot; page <?php echo (int) $meta['page']; ?> of <?php echo (int) $meta['last_page']; ?></span>
            <?php if ($meta['last_page'] > 1): ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="page-link-soft <?php echo $meta['page'] <= 1 ? 'disabled' : ''; ?>" href="<?php echo sms_e(sms_student_query(['page' => max(1, $meta['page'] - 1)])); ?>">Previous</a>
                    <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
                        <a class="page-link-soft <?php echo $p === (int) $meta['page'] ? 'active' : ''; ?>" href="<?php echo sms_e(sms_student_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                    <a class="page-link-soft <?php echo $meta['page'] >= $meta['last_page'] ? 'disabled' : ''; ?>" href="<?php echo sms_e(sms_student_query(['page' => min($meta['last_page'], $meta['page'] + 1)])); ?>">Next</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="modal fade" id="deleteStudentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="post">
    <div class="modal-header"><h5 class="modal-title">Delete Student</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="form_action" value="delete">
        <input type="hidden" name="id" id="deleteStudentId" value="">
        <p>Are you sure you want to delete <strong id="deleteStudentName">this student</strong>?</p>
        <p class="text-muted fw-bold">This marks the student as deleted and disables their login. Academic records are preserved and this can be reversed by editing the student's status.</p>
    </div>
    <div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-danger-soft" type="submit">Delete</button></div>
</form></div></div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    document.querySelectorAll('.delete-student').forEach(function(link){
        link.addEventListener('click', function(event){
            event.preventDefault();
            document.getElementById('deleteStudentId').value = link.dataset.id;
            document.getElementById('deleteStudentName').textContent = link.dataset.name;
            var modal = new bootstrap.Modal(document.getElementById('deleteStudentModal'));
            modal.show();
        });
    });
    var classFilter = document.getElementById('classFilter');
    if (classFilter) {
        classFilter.addEventListener('change', function(){ classFilter.form.submit(); });
    }
})();
</script>

<?php require_once('includes/footer.php'); ?>
