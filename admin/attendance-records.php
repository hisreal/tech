<?php require_once('includes/header.php'); ?>
<?php require_once('includes/attendance-data.php'); ?>
<?php require_once('includes/attendance-page-helper.php'); ?>
<?php require_once('includes/attendance-module-styles.php'); ?>
<?php
$studentPresent = sms_attendance_count_status($studentAttendanceRecords, 'Present');
$studentAbsent = sms_attendance_count_status($studentAttendanceRecords, 'Absent');
$teacherPresent = sms_attendance_count_status($teacherAttendanceRecords, 'Present');
$teacherAbsent = sms_attendance_count_status($teacherAttendanceRecords, 'Absent');
$cards = [
    ['title' => 'Students Present Today', 'value' => $studentPresent, 'description' => 'Students marked present', 'icon' => 'fa-user-check', 'color' => 'success'],
    ['title' => 'Students Absent Today', 'value' => $studentAbsent, 'description' => 'Students marked absent', 'icon' => 'fa-user-xmark', 'color' => 'danger'],
    ['title' => 'Teachers Present Today', 'value' => $teacherPresent, 'description' => 'Teachers checked in', 'icon' => 'fa-chalkboard-user', 'color' => 'blue'],
    ['title' => 'Teachers Absent Today', 'value' => $teacherAbsent, 'description' => 'Teachers absent today', 'icon' => 'fa-user-slash', 'color' => 'warning'],
];
?>
<div class="admin-attendance-module">
<?php sms_attendance_render_hero('Attendance Records', 'View, search, filter, edit, print, and export student and teacher attendance records from one central page.', 'fa-solid fa-clipboard-list', 'Attendance Records'); ?>
<?php sms_attendance_render_cards($cards); ?>
<section class="module-card">
    <h4>Search & Filter Attendance</h4>
    <form class="attendance-filter-form" id="attendanceRecordsFilter">
        <div class="filter-grid">
            <div><label>Academic Session</label><select class="form-select"><option value="">All Sessions</option><?php foreach ($attendanceSessions as $session): ?><option><?php echo sms_e($session); ?></option><?php endforeach; ?></select></div>
            <div><label>Term</label><select class="form-select"><option value="">All Terms</option><?php foreach ($attendanceTerms as $term): ?><option><?php echo sms_e($term); ?></option><?php endforeach; ?></select></div>
            <div><label>Attendance Type</label><select class="form-select" id="attendanceType"><option value="student">Student Attendance</option><option value="teacher">Teacher Attendance</option><option value="all">All Records</option></select></div>
            <div><label>Date</label><input class="form-control" type="date" value="2026-07-03"></div>
            <div><label>Date From</label><input class="form-control" type="date"></div>
            <div><label>Date To</label><input class="form-control" type="date"></div>
            <div><label>Class</label><select class="form-select"><option value="">All Classes</option><?php foreach ($attendanceClasses as $class): ?><option><?php echo sms_e($class); ?></option><?php endforeach; ?></select></div>
            <div><label>Section</label><select class="form-select"><option value="">All Sections</option><?php foreach ($attendanceSections as $section): ?><option><?php echo sms_e($section); ?></option><?php endforeach; ?></select></div>
            <div><label>Department</label><select class="form-select"><option value="">All Departments</option><?php foreach ($attendanceDepartments as $department): ?><option><?php echo sms_e($department); ?></option><?php endforeach; ?></select></div>
            <div><label>Student Name</label><input class="form-control" data-filter="search" placeholder="Search student or reg no"></div>
            <div><label>Teacher Name</label><input class="form-control" placeholder="Search teacher or staff ID"></div>
            <div><label>Attendance Status</label><select class="form-select" data-filter="status"><option value="">All Statuses</option><?php foreach ($attendanceStatuses as $status): ?><option><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
            <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div>
        </div>
    </form>
</section>
<section class="module-card attendance-record-panel" data-panel="student">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Student Attendance Records</h4><p class="text-muted mb-0">Student roll-call records with edit, print, and export placeholders for future audit-backed workflows.</p></div><?php sms_attendance_render_exports(); ?></div>
    <div class="table-shell"><table class="table attendance-table"><thead><tr><th><input class="form-check-input" type="checkbox"></th><th>Date</th><th>Registration Number</th><th>Student Name</th><th>Class</th><th>Section</th><th>Status</th><th>Time Marked</th><th>Marked By</th><th>Actions</th></tr></thead><tbody><?php foreach ($studentAttendanceRecords as $record): ?><tr data-status="<?php echo sms_e($record['status']); ?>"><td><input class="form-check-input" type="checkbox"></td><td><?php echo sms_e($record['date']); ?></td><td><?php echo sms_e($record['reg_no']); ?></td><td><?php echo sms_e($record['student_name']); ?></td><td><?php echo sms_e($record['class']); ?></td><td><?php echo sms_e($record['section']); ?></td><td><?php echo sms_attendance_badge($record['status']); ?></td><td><?php echo sms_e($record['time_marked']); ?></td><td><?php echo sms_e($record['marked_by']); ?></td><td><div class="d-flex gap-1"><button class="action-btn" title="View Details"><i class="fa-solid fa-eye"></i></button><button class="action-btn edit-attendance-btn" title="Edit Attendance" data-bs-toggle="modal" data-bs-target="#editAttendanceModal" data-id="<?php echo sms_e($record['id']); ?>" data-type="student" data-status="<?php echo sms_e($record['status']); ?>" data-remarks="<?php echo sms_e($record['remarks']); ?>"><i class="fa-solid fa-pen"></i></button><button class="action-btn" title="Print Record" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div></td></tr><?php endforeach; ?></tbody></table></div>
    <?php sms_attendance_render_pagination(); ?>
</section>
<section class="module-card attendance-record-panel" data-panel="teacher" style="display:none">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><div><h4 class="mb-1">Teacher Attendance Records</h4><p class="text-muted mb-0">Teacher check-in/check-out records with admin review and edit placeholders.</p></div><?php sms_attendance_render_exports(); ?></div>
    <div class="table-shell"><table class="table attendance-table"><thead><tr><th><input class="form-check-input" type="checkbox"></th><th>Date</th><th>Staff ID</th><th>Teacher Name</th><th>Department</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($teacherAttendanceRecords as $record): ?><tr data-status="<?php echo sms_e($record['status']); ?>"><td><input class="form-check-input" type="checkbox"></td><td><?php echo sms_e($record['date']); ?></td><td><?php echo sms_e($record['staff_id']); ?></td><td><?php echo sms_e($record['teacher_name']); ?></td><td><?php echo sms_e($record['department']); ?></td><td><?php echo sms_e($record['check_in']); ?></td><td><?php echo sms_e($record['check_out']); ?></td><td><?php echo sms_attendance_badge($record['status']); ?></td><td><div class="d-flex gap-1"><button class="action-btn" title="View Details"><i class="fa-solid fa-eye"></i></button><button class="action-btn edit-attendance-btn" title="Edit Attendance" data-bs-toggle="modal" data-bs-target="#editAttendanceModal" data-id="<?php echo sms_e($record['id']); ?>" data-type="teacher" data-status="<?php echo sms_e($record['status']); ?>" data-remarks="<?php echo sms_e($record['remarks']); ?>"><i class="fa-solid fa-pen"></i></button><button class="action-btn" title="Print Record" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div></td></tr><?php endforeach; ?></tbody></table></div>
    <?php sms_attendance_render_pagination(); ?>
</section>
<section class="module-card"><h4>Bulk Actions</h4><div class="d-flex flex-wrap gap-2"><button class="module-btn btn-outline-soft export-btn" data-format="PDF" type="button"><i class="fa-solid fa-file-pdf"></i> Export PDF</button><button class="module-btn btn-outline-soft export-btn" data-format="Excel" type="button"><i class="fa-solid fa-file-excel"></i> Export Excel</button><button class="module-btn btn-outline-soft export-btn" data-format="CSV" type="button"><i class="fa-solid fa-file-csv"></i> Export CSV</button><button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Selected Records</button></div></section>
<?php sms_attendance_render_edit_modal(); ?>
</div></div></div>
<?php sms_attendance_render_common_script(); ?>
<script>
/* Toggle student/teacher record tables from the central Attendance Records filter. */
(function(){
    var type = document.getElementById('attendanceType');
    function syncPanels(){
        var value = type.value;
        document.querySelectorAll('.attendance-record-panel').forEach(function(panel){
            panel.style.display = value === 'all' || panel.dataset.panel === value ? '' : 'none';
        });
    }
    if(type){ type.addEventListener('change', syncPanels); syncPanels(); }
})();
</script>
<?php require_once('includes/footer.php'); ?>