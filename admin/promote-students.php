<?php require_once('includes/header.php'); ?>

<?php
// Promotion placeholders. Replace with academic session, class, student, and audit-log queries later.
$sessions = ['2025/2026', '2026/2027'];
$classes = ['JSS 1', 'JSS 2', 'JSS 3', 'SS 1 Science', 'SS 2 Science', 'SS 3 Arts'];
$sections = ['A', 'B', 'Science', 'Commercial', 'Arts'];
$students = [
    ['reg_no' => 'REG-2026-001', 'name' => 'Musa Ibrahim', 'class' => 'SS 1 Science', 'section' => 'Science', 'status' => 'Eligible'],
    ['reg_no' => 'REG-2026-002', 'name' => 'Aisha Bello', 'class' => 'JSS 1', 'section' => 'A', 'status' => 'Eligible'],
    ['reg_no' => 'REG-2026-003', 'name' => 'David Okafor', 'class' => 'JSS 2', 'section' => 'B', 'status' => 'Pending Result'],
    ['reg_no' => 'REG-2026-004', 'name' => 'Fatima Sani', 'class' => 'JSS 3', 'section' => 'A', 'status' => 'Eligible'],
];
$promotionHistory = [
    ['student' => 'Halima Usman', 'previous' => 'JSS 1A', 'new' => 'JSS 2A', 'session' => '2024/2025 to 2025/2026', 'date' => '03/07/2026', 'by' => 'Administrator'],
    ['student' => 'Samuel Ade', 'previous' => 'SS 1 Science', 'new' => 'SS 2 Science', 'session' => '2024/2025 to 2025/2026', 'date' => '03/07/2026', 'by' => 'Administrator'],
];
?>

<style>
    /* Promote Students page styles: scoped promotion workspace with responsive tables. */
    .admin-student-module { --asm-primary:#0f766e; --asm-primary-dark:#115e59; --asm-soft:rgba(15,118,110,.1); --asm-border:rgba(15,118,110,.16); --asm-ink:#10201d; --asm-muted:#64748b; --asm-danger:#dc2626; --asm-warning:#d97706; --asm-shadow:0 22px 56px rgba(15,23,42,.08); padding-bottom:34px; }
    .admin-student-module .module-hero,.admin-student-module .module-card,.admin-student-module .mini-card { background:rgba(255,255,255,.98); border:1px solid var(--asm-border); box-shadow:var(--asm-shadow); }
    .admin-student-module .module-hero { padding:26px; border-radius:24px; margin-bottom:22px; background:linear-gradient(135deg,rgba(240,253,244,.98),#fff); }
    .admin-student-module .breadcrumb-line { color:var(--asm-muted); font-size:13px; font-weight:800; margin-bottom:10px; }
    .admin-student-module .module-kicker { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:var(--asm-soft); color:var(--asm-primary-dark); font-size:12px; font-weight:900; text-transform:uppercase; }
    .admin-student-module h3,.admin-student-module h4,.admin-student-module h5 { color:var(--asm-ink); font-weight:900; }
    .admin-student-module .module-card { border-radius:22px; padding:22px; margin-bottom:22px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .module-card:hover,.admin-student-module .mini-card:hover { transform:translateY(-2px); box-shadow:0 20px 42px rgba(15,23,42,.11); }
    .admin-student-module .filter-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    .admin-student-module label { color:var(--asm-ink); font-size:13px; font-weight:900; margin-bottom:7px; }
    .admin-student-module .form-control,.admin-student-module .form-select { min-height:46px; border-radius:14px; border:1px solid rgba(148,163,184,.35); font-weight:700; }
    .admin-student-module .form-control:focus,.admin-student-module .form-select:focus { border-color:var(--asm-primary); box-shadow:0 0 0 .18rem rgba(15,118,110,.14); }
    .admin-student-module .module-btn { border:0; min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:14px; padding:10px 15px; font-weight:900; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .module-btn:hover { transform:translateY(-2px); }
    .admin-student-module .btn-primary-soft { background:var(--asm-primary); color:#fff; box-shadow:0 12px 24px rgba(15,118,110,.22); }
    .admin-student-module .btn-muted-soft { background:#f1f5f9; color:var(--asm-ink); }
    .admin-student-module .btn-outline-soft { background:#fff; color:var(--asm-primary-dark); border:1px solid var(--asm-border); }
    .admin-student-module .btn-danger-soft { background:rgba(220,38,38,.1); color:var(--asm-danger); }
    .admin-student-module .table-shell { overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:18px; }
    .admin-student-module table { min-width:920px; margin-bottom:0; }
    .admin-student-module thead th { position:sticky; top:0; z-index:2; background:#f0fdf4; color:var(--asm-primary-dark); font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1px solid var(--asm-border); }
    .admin-student-module tbody td { vertical-align:middle; color:#1f2937; font-weight:700; }
    .admin-student-module tbody tr:hover { background:rgba(15,118,110,.045); }
    .admin-student-module .status-badge { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:900; }
    .admin-student-module .status-eligible { background:rgba(22,163,74,.12); color:#15803d; }
    .admin-student-module .status-pending { background:rgba(245,158,11,.13); color:var(--asm-warning); }
    .admin-student-module .notice { display:none; padding:14px 16px; border-radius:16px; background:rgba(22,163,74,.12); color:#166534; font-weight:900; margin-bottom:18px; }
    .admin-student-module .mini-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
    .admin-student-module .mini-card { border-radius:18px; padding:18px; transition:transform .18s ease, box-shadow .18s ease; }
    .admin-student-module .mini-card span { color:var(--asm-muted); font-weight:800; font-size:13px; }
    .admin-student-module .mini-card strong { display:block; margin-top:8px; color:var(--asm-ink); font-size:22px; }
    @media(max-width:991.98px){ .admin-student-module .filter-grid,.admin-student-module .mini-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
    @media(max-width:575.98px){ .admin-student-module .module-hero,.admin-student-module .module-card{padding:18px;border-radius:18px}.admin-student-module .filter-grid,.admin-student-module .mini-grid{grid-template-columns:1fr}.admin-student-module .module-btn{width:100%} }
</style>

<div class="admin-student-module">
    <!-- Page header and breadcrumb. -->
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

    <div id="promotionNotice" class="notice"><i class="fa-solid fa-circle-check me-2"></i>Students promoted successfully. This action is ready for database/audit-log integration.</div>

    <!-- Promotion filters. -->
    <section class="module-card">
        <h4 class="mb-2">Promotion Filters</h4>
        <p class="text-muted mb-3">Select the current class and destination class before loading students for promotion.</p>
        <form id="promotionFilterForm">
            <div class="filter-grid">
                <div><label for="currentSession">Current Academic Session</label><select class="form-select" id="currentSession" required><option value="">Select Session</option><?php foreach ($sessions as $session): ?><option><?php echo sms_e($session); ?></option><?php endforeach; ?></select></div>
                <div><label for="currentClass">Current Class</label><select class="form-select" id="currentClass" required><option value="">Select Class</option><?php foreach ($classes as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
                <div><label for="currentSection">Current Section</label><select class="form-select" id="currentSection"><option value="">Select Section</option><?php foreach ($sections as $section): ?><option><?php echo sms_e($section); ?></option><?php endforeach; ?></select></div>
                <div><label for="nextSession">Next Academic Session</label><select class="form-select" id="nextSession" required><option value="">Select Next Session</option><?php foreach ($sessions as $session): ?><option><?php echo sms_e($session); ?></option><?php endforeach; ?></select></div>
                <div><label for="destinationClass">Destination Class</label><select class="form-select" id="destinationClass" required><option value="">Select Destination</option><?php foreach ($classes as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
                <div><label for="destinationSection">Destination Section</label><select class="form-select" id="destinationSection"><option value="">Select Section</option><?php foreach ($sections as $section): ?><option><?php echo sms_e($section); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-users-viewfinder"></i> Load Students</button><button class="module-btn btn-muted-soft" type="reset"><i class="fa-solid fa-rotate-left"></i> Reset</button></div>
            </div>
        </form>
    </section>

    <!-- Quick promotion summary cards. -->
    <section class="mini-grid" aria-label="Promotion summary">
        <div class="mini-card"><span>Loaded Students</span><strong id="loadedCount">0</strong></div>
        <div class="mini-card"><span>Selected Students</span><strong id="selectedCount">0</strong></div>
        <div class="mini-card"><span>Eligible Students</span><strong><?php echo count(array_filter($students, fn($student) => $student['status'] === 'Eligible')); ?></strong></div>
    </section>

    <!-- Promotion table. -->
    <section class="module-card" id="promotionTableCard" style="display:none">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Student Promotion Table</h4>
                <p class="text-muted mb-0">Select individual students or use select all before promoting.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="module-btn btn-primary-soft" type="button" id="promoteSelected"><i class="fa-solid fa-check-double"></i> Promote Selected Students</button>
                <button class="module-btn btn-outline-soft" type="button" id="promoteEntireClass"><i class="fa-solid fa-school-circle-check"></i> Promote Entire Class</button>
                <button class="module-btn btn-danger-soft" type="button" id="cancelPromotion"><i class="fa-solid fa-xmark"></i> Cancel</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table align-middle">
                <thead>
                    <tr><th><input class="form-check-input" type="checkbox" id="selectAllPromotion"></th><th>Registration Number</th><th>Student Name</th><th>Current Class</th><th>Current Section</th><th>Promotion Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php $statusClass = $student['status'] === 'Eligible' ? 'status-eligible' : 'status-pending'; ?>
                        <tr>
                            <td><input class="form-check-input promotion-select" type="checkbox" value="<?php echo sms_e($student['reg_no']); ?>"></td>
                            <td><?php echo sms_e($student['reg_no']); ?></td>
                            <td><?php echo sms_e($student['name']); ?></td>
                            <td><?php echo sms_e($student['class']); ?></td>
                            <td><?php echo sms_e($student['section']); ?></td>
                            <td><span class="status-badge <?php echo sms_e($statusClass); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e($student['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Promotion history prepared for audit log integration. -->
    <section class="module-card">
        <h4 class="mb-2">Promotion History</h4>
        <p class="text-muted mb-3">Recent promotion records. This table is structured for future audit log storage.</p>
        <div class="table-shell">
            <table class="table align-middle">
                <thead><tr><th>Student</th><th>Previous Class</th><th>New Class</th><th>Session</th><th>Date Promoted</th><th>Promoted By</th></tr></thead>
                <tbody>
                    <?php foreach ($promotionHistory as $history): ?>
                        <tr><td><?php echo sms_e($history['student']); ?></td><td><?php echo sms_e($history['previous']); ?></td><td><?php echo sms_e($history['new']); ?></td><td><?php echo sms_e($history['session']); ?></td><td><?php echo sms_e($history['date']); ?></td><td><?php echo sms_e($history['by']); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
/* Promotion workflow: load students, select rows, confirm, and show success feedback. */
(function(){
    var form = document.getElementById('promotionFilterForm');
    var tableCard = document.getElementById('promotionTableCard');
    var notice = document.getElementById('promotionNotice');
    var checks = Array.prototype.slice.call(document.querySelectorAll('.promotion-select'));
    var selectAll = document.getElementById('selectAllPromotion');
    var loadedCount = document.getElementById('loadedCount');
    var selectedCount = document.getElementById('selectedCount');

    function getDestinationLabel() {
        var destinationClass = document.getElementById('destinationClass').value || 'the destination class';
        var destinationSection = document.getElementById('destinationSection').value;
        return destinationSection ? destinationClass + ' ' + destinationSection : destinationClass;
    }
    function updateSelectedCount() { selectedCount.textContent = checks.filter(function(check){ return check.checked; }).length; }
    function showSuccess() { notice.style.display = 'block'; notice.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    function confirmPromotion(count) {
        if (!count) { alert('Please select at least one student before promoting.'); return false; }
        return confirm('Are you sure you want to promote ' + count + ' student(s) to ' + getDestinationLabel() + '? This action can be reversed only by an administrator.');
    }

    form.addEventListener('submit', function(event){
        event.preventDefault();
        if (!form.checkValidity()) { form.reportValidity(); return; }
        tableCard.style.display = 'block';
        loadedCount.textContent = checks.length;
        updateSelectedCount();
        tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    form.addEventListener('reset', function(){ setTimeout(function(){ tableCard.style.display = 'none'; loadedCount.textContent = '0'; checks.forEach(function(check){ check.checked = false; }); updateSelectedCount(); }, 0); });
    selectAll.addEventListener('change', function(){ checks.forEach(function(check){ check.checked = selectAll.checked; }); updateSelectedCount(); });
    checks.forEach(function(check){ check.addEventListener('change', updateSelectedCount); });
    document.getElementById('promoteSelected').addEventListener('click', function(){ var count = checks.filter(function(check){ return check.checked; }).length; if (confirmPromotion(count)) { showSuccess(); } });
    document.getElementById('promoteEntireClass').addEventListener('click', function(){ checks.forEach(function(check){ check.checked = true; }); updateSelectedCount(); if (confirmPromotion(checks.length)) { showSuccess(); } });
    document.getElementById('cancelPromotion').addEventListener('click', function(){ checks.forEach(function(check){ check.checked = false; }); selectAll.checked = false; updateSelectedCount(); });
})();
</script>

<?php require_once('includes/footer.php'); ?>