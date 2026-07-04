<?php require_once('includes/header.php'); ?>
<?php require_once('includes/attendance-data.php'); ?>
<?php require_once('includes/attendance-page-helper.php'); ?>
<?php require_once('includes/attendance-module-styles.php'); ?>
<?php
$cards = [
    ['title' => 'Overall Attendance Rate', 'value' => '93%', 'description' => 'School-wide attendance', 'icon' => 'fa-chart-pie', 'color' => 'success'],
    ['title' => 'Student Attendance Rate', 'value' => '92%', 'description' => 'Student attendance trend', 'icon' => 'fa-user-graduate', 'color' => 'blue'],
    ['title' => 'Teacher Attendance Rate', 'value' => '95%', 'description' => 'Teacher attendance trend', 'icon' => 'fa-chalkboard-user', 'color' => 'success'],
    ['title' => 'Average Monthly Attendance', 'value' => '94%', 'description' => 'Current monthly average', 'icon' => 'fa-calendar-days', 'color' => 'warning'],
];
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$studentTrend = [84, 88, 87, 91, 90, 92, 89, 93, 94, 92, 91, 95];
$teacherTrend = [90, 91, 93, 94, 92, 95, 96, 94, 95, 96, 94, 97];
?>
<div class="admin-attendance-module">
<?php sms_attendance_render_hero('Attendance Analytics', 'View attendance percentages, trends, comparisons, distribution, and insight panels for management review.', 'fa-solid fa-chart-line', 'Attendance Analytics'); ?>
<?php sms_attendance_render_cards($cards); ?>
<div class="two-grid">
    <section class="module-card"><h4>Student Attendance Trend</h4><p class="text-muted">Line chart placeholder using monthly trend values.</p><div class="chart-bars"><?php foreach ($studentTrend as $index => $value): ?><div class="chart-bar" style="height:<?php echo sms_e($value); ?>%"><span><?php echo sms_e($months[$index]); ?></span></div><?php endforeach; ?></div></section>
    <section class="module-card"><h4>Teacher Attendance Trend</h4><p class="text-muted">Line chart placeholder using monthly teacher attendance values.</p><div class="chart-bars"><?php foreach ($teacherTrend as $index => $value): ?><div class="chart-bar" style="height:<?php echo sms_e($value); ?>%"><span><?php echo sms_e($months[$index]); ?></span></div><?php endforeach; ?></div></section>
</div>
<div class="two-grid">
    <section class="module-card"><h4>Monthly Attendance Comparison</h4><div class="chart-bars"><div class="chart-bar" style="height:88%"><span>May</span></div><div class="chart-bar" style="height:92%"><span>Jun</span></div><div class="chart-bar" style="height:94%"><span>Jul</span></div><div class="chart-bar" style="height:91%"><span>Aug</span></div></div></section>
    <section class="module-card"><h4>Attendance Distribution</h4><p class="text-muted">Present vs absent distribution placeholder.</p><div class="pie-chart"></div><div class="metric-list mt-4"><div class="metric-row"><span>Present</span><span>78%</span></div><div class="metric-row"><span>Late / Excused</span><span>9%</span></div><div class="metric-row"><span>Absent</span><span>13%</span></div></div></section>
</div>
<section class="module-card"><div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Attendance by Class</h4><p class="text-muted mb-0">Bar chart data shown in a report-ready table.</p></div><?php sms_attendance_render_exports(); ?></div><div class="table-shell"><table class="table attendance-table"><thead><tr><th>Class</th><th>Attendance Rate</th><th>Trend</th><th>Risk Level</th><th>Insight</th></tr></thead><tbody><?php foreach ($attendanceClassBalances as $class => $rate): ?><tr><td><?php echo sms_e($class); ?></td><td><span class="status-badge status-present"><?php echo sms_e($rate); ?>%</span></td><td><?php echo $rate >= 90 ? '<i class="fa-solid fa-arrow-trend-up text-success"></i> Improving' : '<i class="fa-solid fa-arrow-trend-down text-warning"></i> Watch'; ?></td><td><?php echo $rate >= 90 ? '<span class="status-badge status-present">Low</span>' : '<span class="status-badge status-late">Medium</span>'; ?></td><td><?php echo $rate >= 90 ? 'Maintain current monitoring.' : 'Notify class teacher and review absences.'; ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="module-card"><h4>Insights Panel</h4><div class="row g-3 mt-1"><div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-trophy text-success mt-1"></i><span>Best Attendance Class: SS 3</span></div></div><div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-triangle-exclamation text-warning mt-1"></i><span>Lowest Attendance Class: JSS 2</span></div></div><div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-medal text-primary mt-1"></i><span>Students with Perfect Attendance: 48</span></div></div><div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-user-clock text-danger mt-1"></i><span>Students Frequently Absent: 9</span></div></div><div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-user-check text-success mt-1"></i><span>Teachers with Perfect Attendance: 18</span></div></div><div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-user-xmark text-warning mt-1"></i><span>Teachers Frequently Absent: 3</span></div></div></div></section>
</div></div></div>
<?php sms_attendance_render_common_script(); ?>
<?php require_once('includes/footer.php'); ?>