<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\TeacherService;

$teacherService = new TeacherService();
require_once('includes/header.php');

$search = trim((string) ($_GET['search'] ?? ''));
$departmentFilter = trim((string) ($_GET['department'] ?? ''));
$subjectFilter = trim((string) ($_GET['subject'] ?? ''));
$classFilter = trim((string) ($_GET['class'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $teacherService->list([
    'search' => $search,
    'department_id' => $departmentFilter,
    'subject_id' => $subjectFilter,
    'class_id' => $classFilter,
    'status' => $statusFilter,
], $page, 10);

$teachers = $result['data'];
$meta = $result['meta'];

$departmentOptions = $teacherService->departmentsForSelect();
$subjectOptions = $teacherService->subjectsForSelect();
$classOptions = $teacherService->classesForSelect();
$statusOptions = ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave', 'suspended' => 'Suspended', 'deleted' => 'Deleted'];

$allTeachersForStats = $teacherService->list([], 1, 500)['data'];
$totalTeachers = (int) $result['meta']['total'];
$activeTeachers = count(array_filter($allTeachersForStats, static fn (array $row): bool => $row['employment_status'] === 'active'));
$departmentCount = count($departmentOptions);
$assignedSubjectNames = array_merge([], ...array_map(
    static fn (array $row): array => $row['subject_names'] ? explode(', ', $row['subject_names']) : [],
    $allTeachersForStats
));
$subjectsAssignedCount = count(array_unique($assignedSubjectNames));

$summaryCards = [
    ['title' => 'Total Teachers', 'value' => number_format($totalTeachers), 'icon' => 'fa-chalkboard-user', 'color' => 'success'],
    ['title' => 'Active Teachers', 'value' => number_format($activeTeachers), 'icon' => 'fa-user-check', 'color' => 'blue'],
    ['title' => 'Departments', 'value' => number_format($departmentCount), 'icon' => 'fa-building-columns', 'color' => 'warning'],
    ['title' => 'Subjects Assigned', 'value' => number_format($subjectsAssignedCount), 'icon' => 'fa-book-open', 'color' => 'success'],
];

function sms_teacher_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    unset($query['page']);
    return 'teachers.php?' . http_build_query($query);
}
?>

<style>
    /* Teacher list module styles: scoped to preserve shared admin layout assets. */
    .admin-teacher-module { --atm-primary:#0f766e; --atm-primary-dark:#115e59; --atm-soft:rgba(15,118,110,.1); --atm-border:rgba(15,118,110,.16); --atm-ink:#10201d; --atm-muted:#64748b; --atm-danger:#dc2626; --atm-warning:#d97706; --atm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-teacher-module .module-hero,.admin-teacher-module .module-card,.admin-teacher-module .summary-card { background:rgba(255,255,255,.98); border:1px solid var(--atm-border); box-shadow:var(--atm-shadow); }
    .admin-teacher-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-teacher-module .breadcrumb-line { color:var(--atm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-teacher-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-teacher-module h3,.admin-teacher-module h4,.admin-teacher-module h5 { color:var(--atm-ink); font-weight:900; }
    .admin-teacher-module .summary-card { height:100%; padding:18px; border-radius:20px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-teacher-module .summary-card:hover,.admin-teacher-module .module-card:hover { transform:translateY(-2px); box-shadow:0 20px 42px rgba(15,23,42,.11); }
    .admin-teacher-module .summary-icon { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:var(--atm-soft); color:var(--atm-primary); }
    .admin-teacher-module .summary-icon.blue { background:rgba(37,99,235,.1); color:#2563eb; }
    .admin-teacher-module .summary-icon.warning { background:rgba(245,158,11,.13); color:var(--atm-warning); }
    .admin-teacher-module .summary-card h4 { margin:12px 0 2px; font-size:25px; }
    .admin-teacher-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; }
    .admin-teacher-module .filter-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    .admin-teacher-module label { color:var(--atm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-teacher-module .form-control,.admin-teacher-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-teacher-module .form-control:focus,.admin-teacher-module .form-select:focus { border-color:var(--atm-primary); box-shadow:0 0 0 .18rem rgba(15,118,110,.14); }
    .admin-teacher-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-teacher-module .module-btn:hover { transform:translateY(-2px); color:#fff; }
    .admin-teacher-module .btn-primary-soft { background:var(--atm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-teacher-module .btn-muted-soft { background:#f1f5f9; color:var(--atm-ink); }
    .admin-teacher-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--atm-danger); }
    .admin-teacher-module .btn-outline-soft { background:#fff; color:var(--atm-primary-dark); border:1px solid var(--atm-border); }
    .admin-teacher-module .table-shell { overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:18px; }
    .admin-teacher-module table { min-width:1120px; margin-bottom:0; }
    .admin-teacher-module thead th { position:sticky; top:0; z-index:2; background:#f0fdf4; color:var(--atm-primary-dark); font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid var(--atm-border); }
    .admin-teacher-module tbody td { vertical-align:middle; color:#1f2937; font-weight:700; }
    .admin-teacher-module tbody tr:hover { background:rgba(15,118,110,.045); }
    .admin-teacher-module .teacher-passport { width:44px; height:44px; border-radius:50%; object-fit:cover; border:3px solid #dcfce7; }
    .admin-teacher-module .chip-list { display:flex; flex-wrap:wrap; gap:6px; max-width:220px; }
    .admin-teacher-module .chip { display:inline-flex; padding:5px 8px; border-radius:999px; background:var(--atm-soft); color:var(--atm-primary-dark); font-size:12px; font-weight:900; }
    .admin-teacher-module .status-badge { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; }
    .admin-teacher-module .status-active { background:rgba(22,163,74,.12); color:#15803d; }
    .admin-teacher-module .status-inactive { background:#f1f5f9; color:#475569; }
    .admin-teacher-module .status-on_leave { background:rgba(245,158,11,.13); color:var(--atm-warning); }
    .admin-teacher-module .status-suspended { background:rgba(220,38,38,.1); color:var(--atm-danger); }
    .admin-teacher-module .status-deleted { background:rgba(100,116,139,.15); color:#475569; }
    .admin-teacher-module .teacher-action-dropdown { position:relative; display:inline-flex; justify-content:center; }
    .admin-teacher-module .action-menu-btn { width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:var(--atm-primary-dark); background:var(--atm-soft); border:1px solid var(--atm-border); }
    .admin-teacher-module .action-menu-btn:hover,.admin-teacher-module .action-menu-btn:focus { background:var(--atm-primary); color:#fff; box-shadow:0 10px 22px rgba(15,118,110,.18); }
    .admin-teacher-module .dropdown-menu.teacher-actions-menu { min-width:240px; padding:8px; border:1px solid var(--atm-border); border-radius:16px; box-shadow:0 20px 45px rgba(15,23,42,.14); }
    .admin-teacher-module .teacher-actions-menu .dropdown-item { display:flex; align-items:center; gap:10px; border-radius:10px; padding:9px 10px; color:var(--atm-ink); font-size:13px; font-weight:800; }
    .admin-teacher-module .teacher-actions-menu .dropdown-item i { width:18px; color:var(--atm-primary); }
    .admin-teacher-module .teacher-actions-menu .dropdown-item.text-danger i { color:var(--atm-danger); }
    .admin-teacher-module .teacher-actions-menu .dropdown-item:hover { background:var(--atm-soft); color:var(--atm-primary-dark); }
    .admin-teacher-module .pagination-strip { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding-top:16px; }
    .admin-teacher-module .page-link-soft { min-width:38px; height:38px; padding:0 10px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:#fff; border:1px solid var(--atm-border); color:var(--atm-primary-dark); font-weight:900; text-decoration:none; }
    .admin-teacher-module .page-link-soft.active { background:var(--atm-primary); color:#fff; }
    .admin-teacher-module .page-link-soft.disabled { opacity:.4; pointer-events:none; }
    @media(max-width:991.98px){ .admin-teacher-module .filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-teacher-module .module-hero,.admin-teacher-module .module-card{padding:18px;border-radius:18px}.admin-teacher-module .filter-grid{grid-template-columns:1fr}.admin-teacher-module .module-btn{width:100%}.admin-teacher-module .summary-card h4{font-size:21px} }
</style>

<div class="admin-teacher-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Page header and breadcrumb. -->
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Teacher Management <i class="fa-solid fa-angle-right mx-1"></i> All Teachers</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-chalkboard-user"></i> All Teachers</span>
                <h3 class="mt-3 mb-2">Teacher Management</h3>
                <p class="text-muted mb-0">View, search, filter, and manage teacher records, assignments, and academic activity.</p>
            </div>
            <a href="add-teacher.php" class="module-btn btn-primary-soft"><i class="fa-solid fa-user-plus"></i> Add Teacher</a>
        </div>
    </section>

    <!-- Summary cards reused through the shared dashboard-card component. -->
    <section class="row g-3 mb-4" aria-label="Teacher summary cards">
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
                <p class="text-muted mb-0">Filter teachers by name/staff ID, department, subject, class assignment, or status.</p>
            </div>
        </div>
        <form method="get">
            <div class="filter-grid">
                <div><label for="search">Search</label><input class="form-control" id="search" name="search" placeholder="Staff ID or name" value="<?php echo sms_e($search); ?>"></div>
                <div><label for="departmentFilter">Department</label><select class="form-select" id="departmentFilter" name="department"><option value="">All Departments</option><?php foreach ($departmentOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $departmentFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="subjectFilter">Subject</label><select class="form-select" id="subjectFilter" name="subject"><option value="">All Subjects</option><?php foreach ($subjectOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $subjectFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="classFilter">Assigned Class</label><select class="form-select" id="classFilter" name="class"><option value="">All Classes</option><?php foreach ($classOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $classFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="statusFilter">Employment Status</label><select class="form-select" id="statusFilter" name="status"><option value="">All Statuses</option><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button><a class="module-btn btn-muted-soft" href="teachers.php"><i class="fa-solid fa-rotate-left"></i> Reset</a></div>
            </div>
        </form>
    </section>

    <!-- Teachers table with compact row action dropdown. -->
    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Teacher Records</h4>
                <p class="text-muted mb-0"><?php echo (int) $meta['total']; ?> record(s) found.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Teacher List</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table align-middle" id="teacherTable">
                <thead>
                    <tr>
                        <th>Passport</th><th>Staff ID</th><th>Full Name</th><th>Department</th><th>Subjects</th><th>Classes</th><th>Phone</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                        <?php
                        $fullName = trim($teacher['first_name'] . ' ' . $teacher['last_name']);
                        $photo = !empty($teacher['passport_path']) ? '../' . ltrim((string) $teacher['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
                        $statusClass = 'status-' . strtolower((string) $teacher['employment_status']);
                        $subjectChips = $teacher['subject_names'] ? explode(', ', $teacher['subject_names']) : [];
                        $classChips = $teacher['class_names'] ? explode(', ', $teacher['class_names']) : [];
                        ?>
                        <tr>
                            <td><img class="teacher-passport" src="<?php echo sms_e($photo); ?>" alt="<?php echo sms_e($fullName); ?> passport"></td>
                            <td><?php echo sms_e($teacher['staff_no']); ?></td>
                            <td><?php echo sms_e($fullName); ?></td>
                            <td><?php echo sms_e($teacher['department_name'] ?? 'Unassigned'); ?></td>
                            <td><div class="chip-list"><?php foreach ($subjectChips as $subject): ?><span class="chip"><?php echo sms_e($subject); ?></span><?php endforeach; ?></div></td>
                            <td><div class="chip-list"><?php foreach ($classChips as $class): ?><span class="chip"><?php echo sms_e($class); ?></span><?php endforeach; ?></div></td>
                            <td><?php echo sms_e($teacher['phone'] ?? ''); ?></td>
                            <td><span class="status-badge <?php echo sms_e($statusClass); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst(str_replace('_', ' ', (string) $teacher['employment_status']))); ?></span></td>
                            <td>
                                <div class="dropdown teacher-action-dropdown">
                                    <button class="action-menu-btn" type="button" id="teacherAction<?php echo (int) $teacher['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Teacher actions"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end teacher-actions-menu" aria-labelledby="teacherAction<?php echo (int) $teacher['id']; ?>">
                                        <li><a class="dropdown-item" href="teacher-profile.php?teacher_id=<?php echo (int) $teacher['id']; ?>"><i class="fa-solid fa-eye"></i> View Profile</a></li>
                                        <li><a class="dropdown-item" href="edit-teacher.php?teacher_id=<?php echo (int) $teacher['id']; ?>"><i class="fa-solid fa-pen"></i> Edit Teacher</a></li>
                                        <li><a class="dropdown-item" href="assign-subjects.php?teacher_id=<?php echo (int) $teacher['id']; ?>"><i class="fa-solid fa-book-open"></i> Assign Subjects</a></li>
                                        <li><a class="dropdown-item" href="assign-classes.php?teacher_id=<?php echo (int) $teacher['id']; ?>"><i class="fa-solid fa-school"></i> Assign Classes</a></li>
                                        <li><button class="dropdown-item text-danger teacher-action delete-teacher-btn" type="button" data-teacher-id="<?php echo (int) $teacher['id']; ?>" data-teacher="<?php echo sms_e($fullName); ?>"><i class="fa-solid fa-trash"></i> Delete Teacher</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$teachers): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No teachers match your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-strip">
            <span class="text-muted fw-bold"><?php echo (int) $meta['total']; ?> record(s) &middot; page <?php echo (int) $meta['page']; ?> of <?php echo (int) $meta['last_page']; ?></span>
            <?php if ($meta['last_page'] > 1): ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="page-link-soft <?php echo $meta['page'] <= 1 ? 'disabled' : ''; ?>" href="<?php echo sms_e(sms_teacher_query(['page' => max(1, $meta['page'] - 1)])); ?>">Previous</a>
                    <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
                        <a class="page-link-soft <?php echo $p === (int) $meta['page'] ? 'active' : ''; ?>" href="<?php echo sms_e(sms_teacher_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                    <a class="page-link-soft <?php echo $meta['page'] >= $meta['last_page'] ? 'disabled' : ''; ?>" href="<?php echo sms_e(sms_teacher_query(['page' => min($meta['last_page'], $meta['page'] + 1)])); ?>">Next</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Delete confirmation modal for teacher records. -->
<div class="modal fade" id="deleteTeacherModal" tabindex="-1" aria-labelledby="deleteTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="teacher-delete.php">
            <div class="modal-header"><h5 class="modal-title" id="deleteTeacherModalLabel">Delete Teacher</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                <input type="hidden" name="teacher_id" id="deleteTeacherId">
                <p class="mb-0">Are you sure you want to delete this teacher?</p>
                <p class="text-muted fw-bold mb-0">This marks the teacher as deleted and disables their login. Records are preserved and this can be reversed by editing the teacher's status.</p>
                <p class="mt-2 mb-0"><strong id="deleteTeacherName">Teacher record</strong></p>
            </div>
            <div class="modal-footer"><button type="button" class="module-btn btn-muted-soft" data-bs-dismiss="modal">Cancel</button><button type="submit" class="module-btn btn-danger-soft">Delete Teacher</button></div>
        </form>
    </div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    function getDeleteModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteTeacherModal')) : null; }
    Array.prototype.forEach.call(document.querySelectorAll('.delete-teacher-btn'), function(button){
        button.addEventListener('click', function(){
            document.getElementById('deleteTeacherId').value = button.dataset.teacherId || '';
            document.getElementById('deleteTeacherName').textContent = button.dataset.teacher || 'Teacher record';
            var deleteModal = getDeleteModal(); if (deleteModal) { deleteModal.show(); }
        });
    });
    ['departmentFilter','subjectFilter','classFilter','statusFilter'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) { el.addEventListener('change', function(){ el.form.submit(); }); }
    });
})();
</script>

<?php require_once('includes/footer.php'); ?>
