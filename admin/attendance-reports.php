<?php require_once('includes/header.php'); ?>
<?php require_once('includes/attendance-data.php'); ?>
<?php require_once('includes/attendance-page-helper.php'); ?>
<?php require_once('includes/attendance-module-styles.php'); ?>
<?php
$reportTypes = ['Daily Attendance Report', 'Weekly Attendance Report', 'Monthly Attendance Report', 'Student Attendance Report', 'Teacher Attendance Report', 'Class Attendance Report', 'Department Attendance Report'];
$totalPresent = sms_attendance_count_status($studentAttendanceRecords, 'Present') + sms_attendance_count_status($teacherAttendanceRecords, 'Present');
$totalRecords = count($studentAttendanceRecords) + count($teacherAttendanceRecords);
$totalAbsent = sms_attendance_count_status($studentAttendanceRecords, 'Absent') + sms_attendance_count_status($teacherAttendanceRecords, 'Absent');
$cards = [
    ['title' => 'Report Types', 'value' => count($reportTypes), 'description' => 'Available report formats', 'icon' => 'fa-file-lines', 'color' => 'success'],
    ['title' => 'Total Present', 'value' => $totalPresent, 'description' => 'Preview present count', 'icon' => 'fa-user-check', 'color' => 'success'],
    ['title' => 'Total Absent', 'value' => $totalAbsent, 'description' => 'Preview absent count', 'icon' => 'fa-user-xmark', 'color' => 'danger'],
    ['title' => 'Attendance Percentage', 'value' => sms_attendance_rate($totalPresent, $totalRecords), 'description' => 'Current preview rate', 'icon' => 'fa-chart-line', 'color' => 'blue'],
];
?>
<div class="admin-attendance-module">
<?php sms_attendance_render_hero('Attendance Reports', 'Generate daily, weekly, monthly, student, teacher, class, and department attendance reports.', 'fa-solid fa-file-signature', 'Attendance Reports'); ?>
<?php sms_attendance_render_cards($cards); ?>
<section class="module-card">
    <h4>Report Generator</h4>
    <form class="attendance-filter-form" id="attendanceReportForm">
        <div class="filter-grid">
            <div><label>Report Type</label><select class="form-select" required><?php foreach ($reportTypes as $type): ?><option><?php echo sms_e($type); ?></option><?php endforeach; ?></select></div>
            <div><label>Academic Session</label><select class="form-select"><option value="">All Sessions</option><?php foreach ($attendanceSessions as $session): ?><option><?php echo sms_e($session); ?></option><?php endforeach; ?></select></div>
            <div><label>Term</label><select class="form-select"><option value="">All Terms</option><?php foreach ($attendanceTerms as $term): ?><option><?php echo sms_e($term); ?></option><?php endforeach; ?></select></div>
            <div><label>Date From</label><input class="form-control" type="date" value="2026-07-01"></div>
            <div><label>Date To</label><input class="form-control" type="date" value="2026-07-03"></div>
            <div><label>Attendance Type</label><select class="form-select"><option>All</option><option>Student Attendance</option><option>Teacher Attendance</option></select></div>
            <div><label>Class</label><select class="form-select"><option value="">All Classes</option><?php foreach ($attendanceClasses as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
            <div><label>Section</label><select class="form-select"><option value="">All Sections</option><?php foreach ($attendanceSections as $section): ?><option><?php echo sms_e($section); ?></option><?php endforeach; ?></select></div>
            <div><label>Department</label><select class="form-select"><option value="">All Departments</option><?php foreach ($attendanceDepartments as $department): ?><option><?php echo sms_e($department); ?></option><?php endforeach; ?></select></div>
            <div><label>Student</label><input class="form-control" data-filter="search" placeholder="Student name or reg no"></div>
            <div><label>Teacher</label><input class="form-control" placeholder="Teacher name or staff ID"></div>
            <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-file-export"></i> Generate Report</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div>
        </div>
    </form>
</section>
<section class="module-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Printable Report Preview</h4><p class="text-muted mb-0">Preview combines student and teacher attendance summaries and is ready for future report queries.</p></div><?php sms_attendance_render_exports(); ?></div>
    <div class="row g-3 mb-3"><div class="col-md-4"><div class="metric-row"><span>Total Present</span><span><?php echo sms_e($totalPresent); ?></span></div></div><div class="col-md-4"><div class="metric-row"><span>Total Absent</span><span><?php echo sms_e($totalAbsent); ?></span></div></div><div class="col-md-4"><div class="metric-row"><span>Attendance Percentage</span><span><?php echo sms_e(sms_attendance_rate($totalPresent, $totalRecords)); ?></span></div></div></div>
    <div class="table-shell"><table class="table attendance-table"><thead><tr><th>Date</th><th>Category</th><th>Group</th><th>Present</th><th>Absent</th><th>Late / Excused</th><th>Rate</th><th>Generated By</th></tr></thead><tbody><tr><td>2026-07-03</td><td>Student Attendance</td><td>SS 2 Science</td><td>35</td><td>3</td><td>2</td><td><span class="status-badge status-present">87.5%</span></td><td>Administrator</td></tr><tr><td>2026-07-03</td><td>Teacher Attendance</td><td>Science Department</td><td>8</td><td>1</td><td>1</td><td><span class="status-badge status-present">80%</span></td><td>Administrator</td></tr><tr><td>2026-07-01 - 2026-07-03</td><td>Department Attendance</td><td>ICT Department</td><td>21</td><td>2</td><td>1</td><td><span class="status-badge status-present">87.5%</span></td><td>Administrator</td></tr></tbody></table></div>
</section>
</div></div></div>
<?php sms_attendance_render_common_script(); ?>
<script>document.getElementById('attendanceReportForm').addEventListener('submit',function(e){e.preventDefault();alert('Attendance report generated in preview mode. Future backend should query attendance records and audit report generation.');});</script>
<?php require_once('includes/footer.php'); ?>