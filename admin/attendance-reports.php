<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

$attendanceService = new AttendanceService();
require_once('includes/header.php');
require_once('includes/attendance-module-styles.php');

$reportTypes = ['Daily Attendance Report', 'Weekly Attendance Report', 'Monthly Attendance Report', 'Student Attendance Report', 'Teacher Attendance Report', 'Class Attendance Report', 'Department Attendance Report'];

$reportType = (string) ($_GET['report_type'] ?? $reportTypes[0]);
$scope = (string) ($_GET['scope'] ?? 'all');
$sessionId = (string) ($_GET['session_id'] ?? '');
$termId = (string) ($_GET['term_id'] ?? '');
$dateFrom = trim((string) ($_GET['date_from'] ?? date('Y-m-01')));
$dateTo = trim((string) ($_GET['date_to'] ?? date('Y-m-d')));
$classId = (string) ($_GET['class_id'] ?? '');
$sectionId = (string) ($_GET['section_id'] ?? '');
$departmentId = (string) ($_GET['department_id'] ?? '');
$search = trim((string) ($_GET['search'] ?? ''));

$report = $attendanceService->generateReport([
    'report_type' => $reportType, 'scope' => $scope, 'session_id' => $sessionId, 'term_id' => $termId,
    'date_from' => $dateFrom, 'date_to' => $dateTo, 'class_id' => $classId, 'section_id' => $sectionId,
    'department_id' => $departmentId, 'search' => $search,
]);

$sessions = $attendanceService->sessionsForSelect();
$terms = $attendanceService->termsForSelect();
$classes = $attendanceService->classesForSelect();
$sections = $attendanceService->sectionsForSelect();
$departments = $attendanceService->departmentsForSelect();

$cards = [
    ['title' => 'Report Types', 'value' => count($reportTypes), 'description' => 'Available report formats', 'icon' => 'fa-file-lines', 'color' => 'success'],
    ['title' => 'Total Present', 'value' => $report['totals']['present'], 'description' => 'Present in selected range', 'icon' => 'fa-user-check', 'color' => 'success'],
    ['title' => 'Total Absent', 'value' => $report['totals']['absent'], 'description' => 'Absent in selected range', 'icon' => 'fa-user-xmark', 'color' => 'danger'],
    ['title' => 'Attendance Percentage', 'value' => $report['totals']['rate'], 'description' => 'Present rate in range', 'icon' => 'fa-chart-line', 'color' => 'blue'],
];
?>
<div class="admin-attendance-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Attendance Management <i class="fa-solid fa-angle-right mx-1"></i> Attendance Reports</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-file-signature"></i> Attendance Reports</span>
                <h3 class="mt-3 mb-2">Attendance Reports</h3>
                <p class="text-muted mb-0">Generate daily, weekly, monthly, student, teacher, class, and department attendance reports from real records.</p>
            </div>
            <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </section>

    <section class="row g-3 mb-4"><?php foreach ($cards as $card): ?><div class="col-sm-6 col-xl-3"><?php sms_render_component('statistics-card', $card); ?></div><?php endforeach; ?></section>

    <section class="module-card">
        <h4>Report Generator</h4>
        <form method="get">
            <div class="filter-grid">
                <div><label>Report Type</label><select class="form-select" name="report_type"><?php foreach ($reportTypes as $type): ?><option <?php echo $reportType === $type ? 'selected' : ''; ?>><?php echo sms_e($type); ?></option><?php endforeach; ?></select></div>
                <div><label>Attendance Scope</label><select class="form-select" name="scope"><option value="all" <?php echo $scope === 'all' ? 'selected' : ''; ?>>All</option><option value="student" <?php echo $scope === 'student' ? 'selected' : ''; ?>>Student Attendance</option><option value="teacher" <?php echo $scope === 'teacher' ? 'selected' : ''; ?>>Teacher Attendance</option></select></div>
                <div><label>Academic Session</label><select class="form-select" name="session_id"><option value="">All Sessions</option><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo $sessionId === (string) $session['id'] ? 'selected' : ''; ?>><?php echo sms_e($session['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="term_id"><option value="">All Terms</option><?php foreach ($terms as $term): ?><option value="<?php echo (int) $term['id']; ?>" <?php echo $termId === (string) $term['id'] ? 'selected' : ''; ?>><?php echo sms_e($term['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Date From</label><input class="form-control" type="date" name="date_from" value="<?php echo sms_e($dateFrom); ?>"></div>
                <div><label>Date To</label><input class="form-control" type="date" name="date_to" value="<?php echo sms_e($dateTo); ?>"></div>
                <div><label>Class</label><select class="form-select" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo $classId === (string) $class['id'] ? 'selected' : ''; ?>><?php echo sms_e($class['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select" name="section_id"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option value="<?php echo (int) $section['id']; ?>" <?php echo $sectionId === (string) $section['id'] ? 'selected' : ''; ?>><?php echo sms_e($section['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Department</label><select class="form-select" name="department_id"><option value="">All Departments</option><?php foreach ($departments as $department): ?><option value="<?php echo (int) $department['id']; ?>" <?php echo $departmentId === (string) $department['id'] ? 'selected' : ''; ?>><?php echo sms_e($department['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Search</label><input class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Student/teacher name or ID"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-file-export"></i> Generate Report</button><a class="module-btn btn-muted-soft" href="attendance-reports.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1"><?php echo sms_e($reportType); ?></h4><p class="text-muted mb-0"><?php echo sms_e($dateFrom); ?> to <?php echo sms_e($dateTo); ?> &middot; <?php echo count($report['rows']); ?> row(s)</p></div>
            <div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="attendance-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'pdf']))); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a class="module-btn btn-outline-soft" href="attendance-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'excel']))); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a class="module-btn btn-outline-soft" href="attendance-report-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="metric-row"><span>Total Present</span><span><?php echo (int) $report['totals']['present']; ?></span></div></div>
            <div class="col-md-4"><div class="metric-row"><span>Total Absent</span><span><?php echo (int) $report['totals']['absent']; ?></span></div></div>
            <div class="col-md-4"><div class="metric-row"><span>Attendance Percentage</span><span><?php echo sms_e($report['totals']['rate']); ?></span></div></div>
        </div>
        <div class="table-shell">
            <table class="table attendance-table">
                <thead><tr><th><?php echo str_contains($reportType, 'Weekly') || str_contains($reportType, 'Monthly') || $reportType === 'Daily Attendance Report' ? 'Date/Period' : 'Group'; ?></th><th>Category</th><th>Present</th><th>Absent</th><th>Late / Excused / Leave</th><th>Total</th><th>Rate</th></tr></thead>
                <tbody>
                    <?php foreach ($report['rows'] as $row): ?>
                        <tr>
                            <td><?php echo sms_e((string) $row['label']); ?></td>
                            <td><?php echo sms_e($row['category']); ?></td>
                            <td><?php echo (int) $row['present']; ?></td>
                            <td><?php echo (int) $row['absent']; ?></td>
                            <td><?php echo (int) $row['other']; ?></td>
                            <td><?php echo (int) $row['total']; ?></td>
                            <td><span class="status-badge status-present"><?php echo sms_e($row['rate']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$report['rows']): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No attendance records found for the selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
