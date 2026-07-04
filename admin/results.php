<?php require_once('includes/header.php'); ?>
<?php require_once('includes/result-data.php'); ?>
<?php require_once('includes/result-page-helper.php'); ?>
<?php require_once('includes/result-module-styles.php'); ?>
<?php
$cards = [
    ['title' => 'Result Batches', 'value' => count($resultBatches), 'description' => 'Classes and subjects processed', 'icon' => 'fa-layer-group', 'color' => 'success'],
    ['title' => 'Awaiting Approval', 'value' => sms_result_count($resultBatches, 'status', 'Submitted'), 'description' => 'Submitted by teachers', 'icon' => 'fa-paper-plane', 'color' => 'warning'],
    ['title' => 'Published', 'value' => sms_result_count($resultBatches, 'status', 'Published'), 'description' => 'Visible to students', 'icon' => 'fa-bullhorn', 'color' => 'blue'],
    ['title' => 'Locked', 'value' => sms_result_count($resultBatches, 'status', 'Locked'), 'description' => 'Protected result records', 'icon' => 'fa-lock', 'color' => 'success'],
];
?>
<div class="admin-result-module">
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Results</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-square-poll-vertical"></i> Results</span><h3 class="mt-3 mb-2">Results</h3><p class="text-muted mb-0">Central hub for approving, publishing, locking, unlocking, exporting, and reviewing class broadsheets.</p></div><button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button></div></section>
    <?php sms_result_render_cards($cards); ?>
    <section class="module-card">
        <h4>Search & Filter Results</h4>
        <form class="result-filter-form">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select"><option>2025/2026</option><option>2026/2027</option></select></div>
                <div><label>Term</label><select class="form-select"><option>First Term</option><option>Second Term</option><option>Third Term</option></select></div>
                <div><label>Class</label><select class="form-select"><option value="">All Classes</option><?php foreach ($resultClasses as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select"><option value="">All Sections</option><option>A</option><option>B</option><option>Science</option><option>Arts</option></select></div>
                <div><label>Subject</label><select class="form-select"><option value="">All Subjects</option><?php foreach ($resultSubjects as $subject): ?><option><?php echo sms_e($subject); ?></option><?php endforeach; ?></select></div>
                <div><label>Teacher</label><input class="form-control" data-filter="search" placeholder="Search teacher, class, subject"></div>
                <div><label>Result Status</label><select class="form-select" data-filter="status"><option value="">All Statuses</option><?php foreach ($resultStatuses as $status): ?><option><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div>
            </div>
        </form>
    </section>
    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Results Table</h4><p class="text-muted mb-0">Workflow actions are placeholders for future permission checks, status updates, calculations, and audit logs.</p></div><?php sms_result_render_exports(); ?></div>
        <div class="table-shell"><table class="table result-table"><thead><tr><th>Class</th><th>Subject</th><th>Teacher</th><th>Session</th><th>Term</th><th>Status</th><th>Last Updated</th><th>Actions</th></tr></thead><tbody><?php foreach ($resultBatches as $item): ?><tr data-status="<?php echo sms_e($item['status']); ?>"><td><?php echo sms_e($item['class']); ?></td><td><?php echo sms_e($item['subject']); ?></td><td><?php echo sms_e($item['teacher']); ?></td><td><?php echo sms_e($item['session']); ?></td><td><?php echo sms_e($item['term']); ?></td><td><?php echo sms_result_render_badge($item['status']); ?></td><td><?php echo sms_e($item['submitted_at']); ?></td><td><div class="dropdown"><button class="module-btn btn-muted-soft dropdown-toggle" data-bs-toggle="dropdown" type="button">Actions</button><div class="dropdown-menu dropdown-menu-end"><button class="dropdown-item result-action" data-action="View Results" type="button"><i class="fa-solid fa-eye me-2"></i>View Results</button><button class="dropdown-item result-action" data-action="Approve Results" type="button"><i class="fa-solid fa-check-double me-2"></i>Approve Results</button><button class="dropdown-item result-action" data-action="Publish Results" type="button"><i class="fa-solid fa-bullhorn me-2"></i>Publish Results</button><button class="dropdown-item result-action" data-action="Lock Results" type="button"><i class="fa-solid fa-lock me-2"></i>Lock Results</button><button class="dropdown-item result-action" data-action="Unlock Results" type="button"><i class="fa-solid fa-lock-open me-2"></i>Unlock Results</button><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#broadsheetModal" type="button"><i class="fa-solid fa-table-list me-2"></i>View Broadsheet</button><button class="dropdown-item result-export" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf me-2"></i>Export PDF</button><button class="dropdown-item result-export" data-format="Excel" type="button"><i class="fa-solid fa-file-excel me-2"></i>Export Excel</button></div></div></td></tr><?php endforeach; ?></tbody></table></div>
        <?php sms_result_render_pagination(); ?>
    </section>
    <section class="module-card">
        <h4>Automatic Calculations</h4>
        <div class="row g-3 mt-1"><div class="col-md-4"><div class="metric-row"><span>Total Score</span><span>CA + Exam + Practical</span></div></div><div class="col-md-4"><div class="metric-row"><span>Average & Grade</span><span>Uses Grade Settings</span></div></div><div class="col-md-4"><div class="metric-row"><span>Remark & Position</span><span>After approval/publish</span></div></div></div>
    </section>
    <div class="modal fade" id="broadsheetModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Class Broadsheet Preview</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="text-muted fw-bold">This printable broadsheet is embedded inside Results and will later load by class, session, term, and subject selection.</p><div class="table-shell"><table class="table result-table"><thead><tr><th>Registration Number</th><th>Student Name</th><th>Mathematics</th><th>English</th><th>Physics</th><th>Computer</th><th>Total</th><th>Average</th><th>Grade</th><th>Position</th></tr></thead><tbody><?php foreach ($broadsheetRows as $row): ?><tr><td><?php echo sms_e($row['reg_no']); ?></td><td><?php echo sms_e($row['student']); ?></td><td>96</td><td>86</td><td>80</td><td>95</td><td><?php echo sms_e($row['total']); ?></td><td><?php echo sms_e($row['average']); ?></td><td><?php echo sms_e($row['grade']); ?></td><td><?php echo sms_e($row['position']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Close</button><button class="module-btn btn-primary-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Broadsheet</button></div></div></div></div>
</div></div></div>
<?php sms_result_render_common_script(); ?>
<?php require_once('includes/footer.php'); ?>