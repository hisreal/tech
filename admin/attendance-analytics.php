<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

$attendanceService = new AttendanceService();
require_once('includes/header.php');
require_once('includes/attendance-module-styles.php');

$sessions = $attendanceService->sessionsForSelect();
$sessionId = (int) ($_GET['session_id'] ?? $attendanceService->currentSessionId() ?? 0);
$termId = (int) ($_GET['term_id'] ?? $attendanceService->currentTermId() ?? 0);

$analytics = $attendanceService->analyticsOverview($sessionId ?: null, $termId ?: null);

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

$cards = [
    ['title' => 'Overall Attendance Rate', 'value' => $analytics['overallRate'], 'description' => 'School-wide attendance', 'icon' => 'fa-chart-pie', 'color' => 'success'],
    ['title' => 'Student Attendance Rate', 'value' => $analytics['studentRate'], 'description' => 'Student attendance in selected session', 'icon' => 'fa-user-graduate', 'color' => 'blue'],
    ['title' => 'Teacher Attendance Rate', 'value' => $analytics['teacherRate'], 'description' => 'Teacher attendance in selected term', 'icon' => 'fa-chalkboard-user', 'color' => 'success'],
    ['title' => 'Best Attendance Class', 'value' => $analytics['bestClass'] ?? 'N/A', 'description' => 'Highest attendance rate', 'icon' => 'fa-trophy', 'color' => 'warning'],
];
?>
<div class="admin-attendance-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Attendance Management <i class="fa-solid fa-angle-right mx-1"></i> Attendance Analytics</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-chart-line"></i> Attendance Analytics</span>
                <h3 class="mt-3 mb-2">Attendance Analytics</h3>
                <p class="text-muted mb-0">Real attendance percentages, trends, class comparisons, and insight panels computed from recorded data.</p>
            </div>
            <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </section>

    <section class="module-card">
        <form method="get" class="filter-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
            <div><label>Academic Session</label><select class="form-select" name="session_id" onchange="this.form.submit()"><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo $sessionId === (int) $session['id'] ? 'selected' : ''; ?>><?php echo sms_e($session['name']); ?></option><?php endforeach; ?></select></div>
            <div class="d-flex align-items-end"><p class="text-muted mb-0">Monthly trends and class rates are scoped to the selected session's date range.</p></div>
        </form>
    </section>

    <section class="row g-3 mb-4"><?php foreach ($cards as $card): ?><div class="col-sm-6 col-xl-3"><?php sms_render_component('statistics-card', $card); ?></div><?php endforeach; ?></section>

    <div class="two-grid">
        <section class="module-card"><h4>Student Attendance Trend</h4><p class="text-muted">Monthly present-rate within the selected session.</p><div class="chart-bars"><?php foreach ($analytics['studentTrend'] as $index => $value): ?><div class="chart-bar" style="height:<?php echo sms_e((string) max(2, $value)); ?>%" title="<?php echo sms_e((string) $value); ?>%"><span><?php echo sms_e($months[$index]); ?></span></div><?php endforeach; ?></div></section>
        <section class="module-card"><h4>Teacher Attendance Trend</h4><p class="text-muted">Monthly present-rate within the selected session's dates.</p><div class="chart-bars"><?php foreach ($analytics['teacherTrend'] as $index => $value): ?><div class="chart-bar" style="height:<?php echo sms_e((string) max(2, $value)); ?>%" title="<?php echo sms_e((string) $value); ?>%"><span><?php echo sms_e($months[$index]); ?></span></div><?php endforeach; ?></div></section>
    </div>

    <div class="two-grid">
        <section class="module-card">
            <h4>Attendance by Class</h4>
            <div class="chart-bars"><?php foreach ($analytics['classRates'] as $name => $rate): ?><div class="chart-bar" style="height:<?php echo sms_e((string) max(2, $rate)); ?>%" title="<?php echo sms_e((string) $rate); ?>%"><span><?php echo sms_e($name); ?></span></div><?php endforeach; ?></div>
            <?php if (!$analytics['classRates']): ?><p class="text-muted mt-3 mb-0">No student attendance recorded for this session yet.</p><?php endif; ?>
        </section>
        <section class="module-card">
            <h4>Attendance Distribution</h4>
            <p class="text-muted">Present vs. absent vs. other (late/excused/leave), combined student + teacher.</p>
            <div class="pie-chart" style="background:conic-gradient(#0f766e 0 <?php echo sms_e((string) $analytics['distribution']['present']); ?>%, #f59e0b <?php echo sms_e((string) $analytics['distribution']['present']); ?>% <?php echo sms_e((string) ($analytics['distribution']['present'] + $analytics['distribution']['other'])); ?>%, #dc2626 <?php echo sms_e((string) ($analytics['distribution']['present'] + $analytics['distribution']['other'])); ?>% 100%)"></div>
            <div class="metric-list mt-4">
                <div class="metric-row"><span>Present</span><span><?php echo sms_e((string) $analytics['distribution']['present']); ?>%</span></div>
                <div class="metric-row"><span>Late / Excused / Leave</span><span><?php echo sms_e((string) $analytics['distribution']['other']); ?>%</span></div>
                <div class="metric-row"><span>Absent</span><span><?php echo sms_e((string) $analytics['distribution']['absent']); ?>%</span></div>
            </div>
        </section>
    </div>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Attendance by Class</h4><p class="text-muted mb-0">Class attendance rates for the selected session.</p></div></div>
        <div class="table-shell">
            <table class="table attendance-table">
                <thead><tr><th>Class</th><th>Attendance Rate</th><th>Trend</th><th>Risk Level</th><th>Insight</th></tr></thead>
                <tbody>
                    <?php foreach ($analytics['classRates'] as $name => $rate): ?>
                        <tr>
                            <td><?php echo sms_e($name); ?></td>
                            <td><span class="status-badge status-present"><?php echo sms_e((string) $rate); ?>%</span></td>
                            <td><?php echo $rate >= 90 ? '<i class="fa-solid fa-arrow-trend-up text-success"></i> Strong' : '<i class="fa-solid fa-arrow-trend-down text-warning"></i> Watch'; ?></td>
                            <td><?php echo $rate >= 90 ? '<span class="status-badge status-present">Low</span>' : '<span class="status-badge status-late">Medium</span>'; ?></td>
                            <td><?php echo $rate >= 90 ? 'Maintain current monitoring.' : 'Notify class teacher and review absences.'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$analytics['classRates']): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No class attendance data for this session yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="module-card">
        <h4>Insights Panel</h4>
        <div class="row g-3 mt-1">
            <div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-trophy text-success mt-1"></i><span>Best Attendance Class: <?php echo sms_e($analytics['bestClass'] ?? 'No data yet'); ?></span></div></div>
            <div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-triangle-exclamation text-warning mt-1"></i><span>Lowest Attendance Class: <?php echo sms_e($analytics['worstClass'] ?? 'No data yet'); ?></span></div></div>
            <div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-medal text-primary mt-1"></i><span>Students with Perfect Attendance: <?php echo (int) $analytics['studentPerfectAttendance']; ?></span></div></div>
            <div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-user-clock text-danger mt-1"></i><span>Students Frequently Absent (3+): <?php echo (int) $analytics['studentFrequentlyAbsent']; ?></span></div></div>
            <div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-user-check text-success mt-1"></i><span>Teachers with Perfect Attendance: <?php echo (int) $analytics['teacherPerfectAttendance']; ?></span></div></div>
            <div class="col-md-4"><div class="insight-item"><i class="fa-solid fa-user-xmark text-warning mt-1"></i><span>Teachers Frequently Absent (3+): <?php echo (int) $analytics['teacherFrequentlyAbsent']; ?></span></div></div>
        </div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
