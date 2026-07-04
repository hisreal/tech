<?php require_once('includes/header.php'); ?>

<?php
// Placeholder data for the Student Management list. Replace these arrays with database queries during backend integration.
$sessions = ['2025/2026', '2026/2027'];
$classes = ['JSS 1', 'JSS 2', 'JSS 3', 'SS 1 Science', 'SS 2 Science', 'SS 3 Arts'];
$sections = ['A', 'B', 'Science', 'Commercial', 'Arts'];
$statuses = ['Active', 'Graduated', 'Suspended', 'Transferred'];
$students = [
    ['passport' => '../assets/img/students/student-01.jpg', 'reg_no' => 'REG-2026-001', 'admission_no' => 'ADM-001', 'name' => 'Musa Ibrahim', 'gender' => 'Male', 'class' => 'SS 2 Science', 'section' => 'Science', 'parent_phone' => '08031234567', 'status' => 'Active'],
    ['passport' => '../assets/img/students/student-02.jpg', 'reg_no' => 'REG-2026-002', 'admission_no' => 'ADM-002', 'name' => 'Aisha Bello', 'gender' => 'Female', 'class' => 'JSS 1', 'section' => 'A', 'parent_phone' => '08029876543', 'status' => 'Active'],
    ['passport' => '../assets/img/students/student-03.jpg', 'reg_no' => 'REG-2026-003', 'admission_no' => 'ADM-003', 'name' => 'David Okafor', 'gender' => 'Male', 'class' => 'SS 1 Science', 'section' => 'Science', 'parent_phone' => '08155552222', 'status' => 'Active'],
    ['passport' => '../assets/img/students/student-04.jpg', 'reg_no' => 'REG-2026-004', 'admission_no' => 'ADM-004', 'name' => 'Fatima Sani', 'gender' => 'Female', 'class' => 'JSS 3', 'section' => 'B', 'parent_phone' => '07061112223', 'status' => 'Active'],
    ['passport' => '../assets/img/students/student-05.jpg', 'reg_no' => 'REG-2026-005', 'admission_no' => 'ADM-005', 'name' => 'Emeka John', 'gender' => 'Male', 'class' => 'SS 3 Arts', 'section' => 'Arts', 'parent_phone' => '09034445556', 'status' => 'Graduated'],
];
$totalStudents = count($students);
$maleStudents = count(array_filter($students, fn($student) => $student['gender'] === 'Male'));
$femaleStudents = count(array_filter($students, fn($student) => $student['gender'] === 'Female'));
$newAdmissions = 42;
$summaryCards = [
    ['title' => 'Total Students', 'value' => number_format($totalStudents), 'icon' => 'fa-user-graduate', 'color' => 'success'],
    ['title' => 'Male Students', 'value' => number_format($maleStudents), 'icon' => 'fa-person', 'color' => 'blue'],
    ['title' => 'Female Students', 'value' => number_format($femaleStudents), 'icon' => 'fa-person-dress', 'color' => 'warning'],
    ['title' => 'New Admissions', 'value' => number_format($newAdmissions), 'icon' => 'fa-user-plus', 'color' => 'success'],
];
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
    .admin-student-module .module-btn:hover { transform:translateY(-2px); }
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
    .admin-student-module .status-transferred { background:rgba(245,158,11,.13); color:var(--asm-warning); }
    .admin-student-module .student-action-dropdown { position:relative; display:inline-flex; justify-content:center; }
    .admin-student-module .action-menu-btn { width:38px; height:38px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:var(--asm-primary-dark); background:var(--asm-soft); border:1px solid var(--asm-border); }
    .admin-student-module .action-menu-btn:hover,.admin-student-module .action-menu-btn:focus { background:var(--asm-primary); color:#fff; box-shadow:0 10px 22px rgba(15,118,110,.18); }
    .admin-student-module .dropdown-menu.student-actions-menu { min-width:230px; padding:8px; border:1px solid var(--asm-border); border-radius:16px; box-shadow:0 20px 45px rgba(15,23,42,.14); }
    .admin-student-module .student-actions-menu .dropdown-item { display:flex; align-items:center; gap:10px; border-radius:10px; padding:9px 10px; color:var(--asm-ink); font-size:13px; font-weight:800; }
    .admin-student-module .student-actions-menu .dropdown-item i { width:18px; color:var(--asm-primary); }
    .admin-student-module .student-actions-menu .dropdown-item.text-danger i { color:var(--asm-danger); }
    .admin-student-module .student-actions-menu .dropdown-item:hover { background:var(--asm-soft); color:var(--asm-primary-dark); }
    .admin-student-module .pagination-strip { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding-top:16px; }
    .admin-student-module .page-link-soft { min-width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:#fff; border:1px solid var(--asm-border); color:var(--asm-primary-dark); font-weight:900; text-decoration:none; }
    .admin-student-module .page-link-soft.active { background:var(--asm-primary); color:#fff; }
    @media(max-width:991.98px){ .admin-student-module .filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-student-module .module-hero,.admin-student-module .module-card{padding:18px;border-radius:18px}.admin-student-module .filter-grid{grid-template-columns:1fr}.admin-student-module .module-btn{width:100%}.admin-student-module .summary-card h4{font-size:21px} }
</style>

<div class="admin-student-module">
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

    <!-- Search and filtering controls prepared for future database queries. -->
    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Search & Filter</h4>
                <p class="text-muted mb-0">Filter by registration details, class, session, or current student status.</p>
            </div>
        </div>
        <form id="studentFilterForm">
            <div class="filter-grid">
                <div><label for="regNo">Registration Number</label><input class="form-control" id="regNo" name="reg_no" placeholder="REG-2026-001"></div>
                <div><label for="admissionNo">Admission Number</label><input class="form-control" id="admissionNo" name="admission_no" placeholder="ADM-001"></div>
                <div><label for="studentName">Student Name</label><input class="form-control" id="studentName" name="student_name" placeholder="Search by name"></div>
                <div><label for="classFilter">Class</label><select class="form-select" id="classFilter" name="class"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
                <div><label for="sectionFilter">Section</label><select class="form-select" id="sectionFilter" name="section"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option><?php echo sms_e($section); ?></option><?php endforeach; ?></select></div>
                <div><label for="sessionFilter">Academic Session</label><select class="form-select" id="sessionFilter" name="session"><option value="">All Sessions</option><?php foreach ($sessions as $session): ?><option><?php echo sms_e($session); ?></option><?php endforeach; ?></select></div>
                <div><label for="statusFilter">Status</label><select class="form-select" id="statusFilter" name="status"><option value="">All Statuses</option><?php foreach ($statuses as $status): ?><option><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button><button class="module-btn btn-muted-soft" type="reset"><i class="fa-solid fa-rotate-left"></i> Reset</button></div>
            </div>
        </form>
    </section>

    <!-- Student table with bulk actions and page controls. -->
    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Student Records</h4>
                <p class="text-muted mb-0">Select rows for bulk exports, printing, or administrative actions.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="module-btn btn-danger-soft" type="button" id="deleteSelected"><i class="fa-solid fa-trash"></i> Delete Selected</button>
                <button class="module-btn btn-outline-soft" type="button" id="exportCsv"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
                <button class="module-btn btn-outline-soft" type="button" id="exportExcel"><i class="fa-solid fa-file-excel"></i> Export Excel</button>
                <button class="module-btn btn-outline-soft" type="button" id="printList"><i class="fa-solid fa-print"></i> Print List</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table align-middle" id="studentTable">
                <thead>
                    <tr>
                        <th><input class="form-check-input" type="checkbox" id="selectAllStudents" aria-label="Select all students"></th>
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
                        <?php $statusClass = 'status-' . strtolower(str_replace(' ', '-', $student['status'])); ?>
                        <tr data-student-name="<?php echo sms_e(strtolower($student['name'])); ?>" data-reg-no="<?php echo sms_e(strtolower($student['reg_no'])); ?>" data-admission-no="<?php echo sms_e(strtolower($student['admission_no'])); ?>" data-class="<?php echo sms_e($student['class']); ?>" data-section="<?php echo sms_e($student['section']); ?>" data-status="<?php echo sms_e($student['status']); ?>">
                            <td><input class="form-check-input student-select" type="checkbox" value="<?php echo sms_e($student['reg_no']); ?>" aria-label="Select <?php echo sms_e($student['name']); ?>"></td>
                            <td><img class="student-passport" src="<?php echo sms_e($student['passport']); ?>" alt="<?php echo sms_e($student['name']); ?> passport"></td>
                            <td><?php echo sms_e($student['reg_no']); ?></td>
                            <td><?php echo sms_e($student['admission_no']); ?></td>
                            <td><?php echo sms_e($student['name']); ?></td>
                            <td><?php echo sms_e($student['gender']); ?></td>
                            <td><?php echo sms_e($student['class']); ?></td>
                            <td><?php echo sms_e($student['section']); ?></td>
                            <td><?php echo sms_e($student['parent_phone']); ?></td>
                            <td><span class="status-badge <?php echo sms_e($statusClass); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e($student['status']); ?></span></td>
                            <td>
                                <div class="dropdown student-action-dropdown" aria-label="Student row actions">
                                    <button class="action-menu-btn" type="button" id="studentAction<?php echo sms_e($student['reg_no']); ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Student actions">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end student-actions-menu" aria-labelledby="studentAction<?php echo sms_e($student['reg_no']); ?>">
                                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-eye"></i> View Profile</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-pen"></i> Edit Student</a></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-trash"></i> Delete Student</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-calendar-check"></i> View Attendance</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-square-poll-vertical"></i> View Results</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-receipt"></i> Payment History</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-print"></i> Print Student Information</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-strip">
            <div class="d-flex align-items-center gap-2 flex-wrap"><span class="text-muted fw-bold">Records per page</span><select class="form-select" style="width:90px"><option>10</option><option>25</option><option>50</option><option>100</option></select></div>
            <div class="d-flex align-items-center gap-2 flex-wrap"><a href="#" class="page-link-soft">Previous</a><a href="#" class="page-link-soft active">1</a><a href="#" class="page-link-soft">2</a><a href="#" class="page-link-soft">3</a><a href="#" class="page-link-soft">Next</a></div>
        </div>
    </section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
/* Student list interactions: filtering, bulk selection, export, and print placeholders. */
(function(){
    var filterForm = document.getElementById('studentFilterForm');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#studentTable tbody tr'));
    var selectAll = document.getElementById('selectAllStudents');

    function normalize(value) { return String(value || '').trim().toLowerCase(); }
    function selectedRows() { return rows.filter(function(row){ return row.querySelector('.student-select').checked; }); }
    function notify(message) { alert(message); }

    filterForm.addEventListener('submit', function(event){
        event.preventDefault();
        var reg = normalize(document.getElementById('regNo').value);
        var admission = normalize(document.getElementById('admissionNo').value);
        var name = normalize(document.getElementById('studentName').value);
        var classValue = document.getElementById('classFilter').value;
        var sectionValue = document.getElementById('sectionFilter').value;
        var statusValue = document.getElementById('statusFilter').value;
        rows.forEach(function(row){
            var visible = (!reg || normalize(row.dataset.regNo).indexOf(reg) > -1)
                && (!admission || normalize(row.dataset.admissionNo).indexOf(admission) > -1)
                && (!name || normalize(row.dataset.studentName).indexOf(name) > -1)
                && (!classValue || row.dataset.class === classValue)
                && (!sectionValue || row.dataset.section === sectionValue)
                && (!statusValue || row.dataset.status === statusValue);
            row.style.display = visible ? '' : 'none';
        });
    });

    filterForm.addEventListener('reset', function(){ setTimeout(function(){ rows.forEach(function(row){ row.style.display = ''; }); }, 0); });
    selectAll.addEventListener('change', function(){ rows.forEach(function(row){ row.querySelector('.student-select').checked = selectAll.checked; }); });
    document.getElementById('deleteSelected').addEventListener('click', function(){
        var count = selectedRows().length;
        if (!count) { notify('Please select at least one student before deleting.'); return; }
        if (confirm('Delete ' + count + ' selected student record(s)? This is a placeholder action for future database integration.')) { notify('Selected student records marked for deletion.'); }
    });
    document.getElementById('exportCsv').addEventListener('click', function(){ notify('CSV export is ready for backend integration.'); });
    document.getElementById('exportExcel').addEventListener('click', function(){ notify('Excel export is ready for backend integration.'); });
    document.getElementById('printList').addEventListener('click', function(){ window.print(); });
})();
</script>

<?php require_once('includes/footer.php'); ?>