<?php require_once('includes/header.php'); ?>
<?php require_once('includes/cbt-data.php'); ?>
<?php require_once('includes/cbt-page-helper.php'); ?>
<?php require_once('includes/cbt-module-styles.php'); ?>
<?php
$totalAttempts = array_sum(array_column($cbtExams, 'attempts'));
$cards = [
    ['title' => 'Total Exams', 'value' => count($cbtExams), 'description' => 'CBT exams created', 'icon' => 'fa-laptop-file', 'color' => 'success'],
    ['title' => 'Active Exams', 'value' => sms_cbt_count($cbtExams, 'status', 'Active'), 'description' => 'Currently available', 'icon' => 'fa-toggle-on', 'color' => 'success'],
    ['title' => 'Completed Exams', 'value' => sms_cbt_count($cbtExams, 'status', 'Completed'), 'description' => 'Closed CBT exams', 'icon' => 'fa-circle-check', 'color' => 'blue'],
    ['title' => 'Total Questions', 'value' => sms_cbt_total_questions($cbtExams), 'description' => 'Across all exams', 'icon' => 'fa-circle-question', 'color' => 'warning'],
    ['title' => 'Student Attempts', 'value' => $totalAttempts, 'description' => 'Submitted attempts', 'icon' => 'fa-users', 'color' => 'success'],
    ['title' => 'Average Score', 'value' => '72%', 'description' => 'All CBT attempts', 'icon' => 'fa-chart-line', 'color' => 'blue'],
];
?>
<div class="admin-cbt-module">
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> Dashboard</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-gauge-high"></i> CBT Dashboard</span><h3 class="mt-3 mb-2">CBT Dashboard</h3><p class="text-muted mb-0">Overview of CBT exams, questions, attempts, performance, and recent activity.</p></div><a class="module-btn btn-primary-soft" href="cbt-exams.php"><i class="fa-solid fa-plus"></i> Manage Exams</a></div></section>
    <?php sms_cbt_render_cards($cards); ?>
    <div class="two-grid"><section class="module-card"><h4>Exam Participation</h4><div class="chart-bars"><div class="chart-bar" style="height:68%"><span>JSS1</span></div><div class="chart-bar" style="height:74%"><span>JSS2</span></div><div class="chart-bar" style="height:62%"><span>JSS3</span></div><div class="chart-bar" style="height:83%"><span>SS1</span></div><div class="chart-bar" style="height:91%"><span>SS2</span></div><div class="chart-bar" style="height:70%"><span>SS3</span></div></div></section><section class="module-card"><h4>Monthly Exams</h4><div class="chart-bars"><div class="chart-bar" style="height:45%"><span>Jan</span></div><div class="chart-bar" style="height:58%"><span>Feb</span></div><div class="chart-bar" style="height:64%"><span>Mar</span></div><div class="chart-bar" style="height:72%"><span>Apr</span></div><div class="chart-bar" style="height:80%"><span>May</span></div><div class="chart-bar" style="height:76%"><span>Jun</span></div></div></section></div>
    <div class="two-grid"><section class="module-card"><h4>Pass vs Fail</h4><div class="metric-row"><span>Passed</span><span>86%</span></div><div class="metric-row"><span>Failed</span><span>14%</span></div><div class="metric-row"><span>Pending Review</span><span>3%</span></div></section><section class="module-card"><h4>Average Scores</h4><div class="metric-row"><span>Best Subject</span><span>Computer Science - 81%</span></div><div class="metric-row"><span>Lowest Subject</span><span>English Language - 68%</span></div><div class="metric-row"><span>Overall Average</span><span>72%</span></div></section></div>
    <section class="module-card"><h4>Recent Activities</h4><div class="row g-3 mt-1"><?php foreach ($cbtActivities as $activity): ?><div class="col-md-6 col-xl-3"><div class="metric-row"><span><?php echo sms_e($activity['type']); ?><br><small class="text-muted"><?php echo sms_e($activity['title']); ?></small></span><span><?php echo sms_e($activity['meta']); ?></span></div></div><?php endforeach; ?></div></section>
</div></div></div>
<?php sms_cbt_render_common_script(); ?>
<?php require_once('includes/footer.php'); ?>