<?php require_once('includes/header.php'); ?>
<?php require_once('includes/result-data.php'); ?>
<?php require_once('includes/result-page-helper.php'); ?>
<?php require_once('includes/result-module-styles.php'); ?>
<?php
$totalScore = array_sum(array_column($resultReportCardSubjects, 'total'));
$averageScore = round($totalScore / max(count($resultReportCardSubjects), 1), 1);
$cards = [
    ['title' => 'Student Total', 'value' => $totalScore, 'description' => 'Total score across subjects', 'icon' => 'fa-calculator', 'color' => 'success'],
    ['title' => 'Average', 'value' => $averageScore . '%', 'description' => 'Computed automatically', 'icon' => 'fa-chart-line', 'color' => 'blue'],
    ['title' => 'Grade', 'value' => 'A', 'description' => 'From configured grade scale', 'icon' => 'fa-award', 'color' => 'warning'],
    ['title' => 'Position', 'value' => '1st', 'description' => 'Class position preview', 'icon' => 'fa-ranking-star', 'color' => 'success'],
];
?>
<div class="admin-result-module">
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Report Cards</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-file-lines"></i> Report Cards</span><h3 class="mt-3 mb-2">Report Cards</h3><p class="text-muted mb-0">Generate printable student report cards with scores, attendance, position, and official remarks.</p></div><button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button></div></section>
    <?php sms_result_render_cards($cards); ?>
    <section class="module-card"><h4>Report Card Filters</h4><form class="result-filter-form"><div class="filter-grid"><div><label>Academic Session</label><select class="form-select"><option>2025/2026</option><option>2026/2027</option></select></div><div><label>Term</label><select class="form-select"><option>First Term</option><option>Second Term</option><option>Third Term</option></select></div><div><label>Class</label><select class="form-select"><option>SS 2 Science</option><?php foreach ($resultClasses as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div><div><label>Student</label><input class="form-control" data-filter="search" value="Musa Ibrahim"></div><div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-eye"></i> Preview</button><button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button><button class="module-btn btn-outline-soft result-export" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf"></i> Download PDF</button></div></div></form></section>
    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Student Report Card Preview</h4><p class="text-muted mb-0">Reusable template ready for dynamic student and result data.</p></div><?php sms_result_render_exports(); ?></div>
        <div class="row g-3 mb-3"><div class="col-md-3"><div class="metric-row"><span>Student</span><span>Musa Ibrahim</span></div></div><div class="col-md-3"><div class="metric-row"><span>Reg No.</span><span>REG-2026-001</span></div></div><div class="col-md-3"><div class="metric-row"><span>Class</span><span>SS 2 Science</span></div></div><div class="col-md-3"><div class="metric-row"><span>Attendance</span><span>92%</span></div></div></div>
        <div class="table-shell"><table class="table result-table"><thead><tr><th>Subject</th><th>1st CA</th><th>2nd CA</th><th>3rd CA</th><th>Exam</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead><tbody><?php foreach ($resultReportCardSubjects as $subject): ?><tr><td><?php echo sms_e($subject['subject']); ?></td><td><?php echo sms_e($subject['ca1']); ?></td><td><?php echo sms_e($subject['ca2']); ?></td><td><?php echo sms_e($subject['ca3']); ?></td><td><?php echo sms_e($subject['exam']); ?></td><td><?php echo sms_e($subject['total']); ?></td><td><span class="status-badge status-approved"><?php echo sms_e($subject['grade']); ?></span></td><td><?php echo sms_e($subject['remark']); ?></td></tr><?php endforeach; ?></tbody></table></div>
        <div class="two-grid mt-3"><div class="module-card mb-0"><h5>Teacher's Remark</h5><p class="mb-0 text-muted">Excellent academic performance. Keep working hard.</p></div><div class="module-card mb-0"><h5>Principal's Remark</h5><p class="mb-0 text-muted">Promoted. Maintain this strong effort next term.</p></div></div>
    </section>
</div></div></div>
<?php sms_result_render_common_script(); ?>
<?php require_once('includes/footer.php'); ?>