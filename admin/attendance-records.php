<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AttendanceService;

$attendanceService = new AttendanceService();
require_once('includes/header.php');
require_once('includes/attendance-module-styles.php');

$today = date('Y-m-d');

// --- Mark Student Attendance: GET-driven roster load ---
$markSession = (int) ($_GET['mark_session'] ?? $attendanceService->currentSessionId() ?? 0);
$markTerm = (int) ($_GET['mark_term'] ?? $attendanceService->currentTermId() ?? 0);
$markClass = (int) ($_GET['mark_class'] ?? 0);
$markSection = (int) ($_GET['mark_section'] ?? 0);
$markDate = trim((string) ($_GET['mark_date'] ?? $today));

$studentRoster = [];
$existingStudentMarks = [];
if ($markClass > 0 && $markSession > 0) {
    $studentRoster = $attendanceService->studentRoster($markSession, $markClass, $markSection ?: null);
    $existingStudentMarks = $attendanceService->existingStudentMarksForDate($markClass, $markSection ?: null, $markDate);
}

// --- Mark Teacher Attendance: GET-driven roster load ---
$markTDate = trim((string) ($_GET['mark_tdate'] ?? ''));
$teacherRoster = [];
$existingTeacherMarks = [];
if ($markTDate !== '') {
    $teacherRoster = $attendanceService->teacherRoster();
    $existingTeacherMarks = $attendanceService->existingTeacherMarksForDate($markTDate);
}

// --- Student Attendance Records: filters + pagination ---
$sSearch = trim((string) ($_GET['s_search'] ?? ''));
$sClass = trim((string) ($_GET['s_class'] ?? ''));
$sSection = trim((string) ($_GET['s_section'] ?? ''));
$sStatus = trim((string) ($_GET['s_status'] ?? ''));
$sDate = trim((string) ($_GET['s_date'] ?? ''));
$sDateFrom = trim((string) ($_GET['s_date_from'] ?? ''));
$sDateTo = trim((string) ($_GET['s_date_to'] ?? ''));
$sPage = max(1, (int) ($_GET['student_page'] ?? 1));

$studentResult = $attendanceService->listStudentAttendance([
    'search' => $sSearch, 'class_id' => $sClass, 'section_id' => $sSection,
    'status' => $sStatus, 'date' => $sDate, 'date_from' => $sDateFrom, 'date_to' => $sDateTo,
], $sPage, 10);

// --- Teacher Attendance Records: filters + pagination ---
$tSearch = trim((string) ($_GET['t_search'] ?? ''));
$tDept = trim((string) ($_GET['t_department'] ?? ''));
$tStatus = trim((string) ($_GET['t_status'] ?? ''));
$tDate = trim((string) ($_GET['t_date'] ?? ''));
$tDateFrom = trim((string) ($_GET['t_date_from'] ?? ''));
$tDateTo = trim((string) ($_GET['t_date_to'] ?? ''));
$tPage = max(1, (int) ($_GET['teacher_page'] ?? 1));

$teacherResult = $attendanceService->listTeacherAttendance([
    'search' => $tSearch, 'department_id' => $tDept, 'status' => $tStatus,
    'date' => $tDate, 'date_from' => $tDateFrom, 'date_to' => $tDateTo,
], $tPage, 10);

$sessions = $attendanceService->sessionsForSelect();
$terms = $attendanceService->termsForSelect();
$classes = $attendanceService->classesForSelect();
$sections = $attendanceService->sectionsForSelect();
$departments = $attendanceService->departmentsForSelect();
$statusOptions = ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused', 'leave' => 'Leave'];

$todayStudentTotals = $attendanceService->listStudentAttendance(['date' => $today], 1, 1000)['data'];
$todayTeacherTotals = $attendanceService->listTeacherAttendance(['date' => $today], 1, 1000)['data'];
$studentPresentToday = count(array_filter($todayStudentTotals, static fn ($r) => $r['status'] === 'present'));
$studentAbsentToday = count(array_filter($todayStudentTotals, static fn ($r) => $r['status'] === 'absent'));
$teacherPresentToday = count(array_filter($todayTeacherTotals, static fn ($r) => $r['status'] === 'present'));
$teacherAbsentToday = count(array_filter($todayTeacherTotals, static fn ($r) => $r['status'] === 'absent'));

$cards = [
    ['title' => 'Students Present Today', 'value' => $studentPresentToday, 'description' => 'Students marked present', 'icon' => 'fa-user-check', 'color' => 'success'],
    ['title' => 'Students Absent Today', 'value' => $studentAbsentToday, 'description' => 'Students marked absent', 'icon' => 'fa-user-xmark', 'color' => 'danger'],
    ['title' => 'Teachers Present Today', 'value' => $teacherPresentToday, 'description' => 'Teachers checked in', 'icon' => 'fa-chalkboard-user', 'color' => 'blue'],
    ['title' => 'Teachers Absent Today', 'value' => $teacherAbsentToday, 'description' => 'Teachers absent today', 'icon' => 'fa-user-slash', 'color' => 'warning'],
];

function sms_attendance_status_badge(string $status): string
{
    $icon = match ($status) {
        'present' => 'fa-check', 'absent' => 'fa-times', 'late' => 'fa-clock',
        'excused' => 'fa-file-shield', 'leave' => 'fa-calendar-minus', default => 'fa-circle-info',
    };

    return '<span class="status-badge status-' . sms_e($status) . '"><i class="fa-solid ' . $icon . '"></i> ' . sms_e(ucfirst($status)) . '</span>';
}

function sms_attendance_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    return 'attendance-records.php?' . http_build_query($query);
}
?>
<div class="admin-attendance-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Attendance Management <i class="fa-solid fa-angle-right mx-1"></i> Attendance Records</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-clipboard-list"></i> Attendance Records</span>
                <h3 class="mt-3 mb-2">Attendance Records</h3>
                <p class="text-muted mb-0">Mark daily student and teacher attendance, then search, filter, edit, and export the records.</p>
            </div>
            <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <?php foreach ($cards as $card): ?>
            <div class="col-sm-6 col-xl-3"><?php sms_render_component('statistics-card', $card); ?></div>
        <?php endforeach; ?>
    </section>

    <!-- Mark Student Attendance -->
    <section class="module-card" id="markStudent">
        <h4>Mark Student Attendance</h4>
        <p class="text-muted">Select a session, class, section, and date to load the class roster.</p>
        <form method="get">
            <input type="hidden" name="mark_tdate" value="<?php echo sms_e($markTDate); ?>">
            <div class="filter-grid">
                <div><label>Session</label><select class="form-select" name="mark_session"><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo $markSession === (int) $session['id'] ? 'selected' : ''; ?>><?php echo sms_e($session['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="mark_term"><?php foreach ($terms as $term): ?><option value="<?php echo (int) $term['id']; ?>" <?php echo $markTerm === (int) $term['id'] ? 'selected' : ''; ?>><?php echo sms_e($term['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" id="markClassSel" name="mark_class" required><option value="">Select Class</option><?php foreach ($classes as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo $markClass === (int) $class['id'] ? 'selected' : ''; ?>><?php echo sms_e($class['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select" id="markSectionSel" name="mark_section"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option value="<?php echo (int) $section['id']; ?>" data-class="<?php echo (int) $section['class_id']; ?>" <?php echo $markSection === (int) $section['id'] ? 'selected' : ''; ?>><?php echo sms_e($section['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Date</label><input class="form-control" type="date" name="mark_date" max="<?php echo sms_e($today); ?>" value="<?php echo sms_e($markDate); ?>"></div>
                <div class="d-flex align-items-end"><button class="module-btn btn-primary-soft w-100" type="submit"><i class="fa-solid fa-users-viewfinder"></i> Load Roster</button></div>
            </div>
        </form>

        <?php if ($markClass > 0): ?>
            <?php if (!$studentRoster): ?>
                <p class="text-muted mt-3 mb-0">No active students are enrolled in this class/section for the selected session.</p>
            <?php else: ?>
                <form method="post" action="attendance-mark-students.php" class="mt-3">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="session_id" value="<?php echo (int) $markSession; ?>">
                    <input type="hidden" name="term_id" value="<?php echo (int) $markTerm; ?>">
                    <input type="hidden" name="class_id" value="<?php echo (int) $markClass; ?>">
                    <input type="hidden" name="section_id" value="<?php echo (int) $markSection; ?>">
                    <input type="hidden" name="attendance_date" value="<?php echo sms_e($markDate); ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <p class="text-muted mb-0"><?php echo count($studentRoster); ?> student(s) &middot; <?php echo sms_e($markDate); ?><?php echo $existingStudentMarks ? ' &middot; already marked - saving will update existing records' : ''; ?></p>
                        <button type="button" class="module-btn btn-outline-soft" id="markAllPresent"><i class="fa-solid fa-check-double"></i> Mark All Present</button>
                    </div>
                    <div class="table-shell">
                        <table class="table align-middle">
                            <thead><tr><th>Registration No.</th><th>Student Name</th><th>Status</th><th>Notes</th></tr></thead>
                            <tbody>
                                <?php foreach ($studentRoster as $student): ?>
                                    <?php $existing = $existingStudentMarks[(int) $student['id']] ?? null; ?>
                                    <tr>
                                        <td><?php echo sms_e($student['registration_no']); ?></td>
                                        <td><?php echo sms_e(trim($student['first_name'] . ' ' . $student['last_name'])); ?></td>
                                        <td>
                                            <select class="form-select mark-status" name="status[<?php echo (int) $student['id']; ?>]">
                                                <?php foreach ($statusOptions as $value => $label): ?>
                                                    <option value="<?php echo sms_e($value); ?>" <?php echo ($existing['status'] ?? 'present') === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input class="form-control" name="notes[<?php echo (int) $student['id']; ?>]" value="<?php echo sms_e($existing['notes'] ?? ''); ?>" placeholder="Optional"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Attendance</button></div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Mark Teacher Attendance -->
    <section class="module-card" id="markTeacher">
        <h4>Mark Teacher Attendance</h4>
        <p class="text-muted">Select a date to load all active teachers.</p>
        <form method="get">
            <input type="hidden" name="mark_session" value="<?php echo (int) $markSession; ?>">
            <input type="hidden" name="mark_term" value="<?php echo (int) $markTerm; ?>">
            <input type="hidden" name="mark_class" value="<?php echo (int) $markClass; ?>">
            <input type="hidden" name="mark_section" value="<?php echo (int) $markSection; ?>">
            <input type="hidden" name="mark_date" value="<?php echo sms_e($markDate); ?>">
            <div class="filter-grid">
                <div><label>Date</label><input class="form-control" type="date" name="mark_tdate" max="<?php echo sms_e($today); ?>" value="<?php echo sms_e($markTDate); ?>"></div>
                <div class="d-flex align-items-end"><button class="module-btn btn-primary-soft w-100" type="submit"><i class="fa-solid fa-users-viewfinder"></i> Load Teachers</button></div>
            </div>
        </form>

        <?php if ($markTDate !== ''): ?>
            <?php if (!$teacherRoster): ?>
                <p class="text-muted mt-3 mb-0">No active teachers found.</p>
            <?php else: ?>
                <form method="post" action="attendance-mark-teachers.php" class="mt-3">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="attendance_date" value="<?php echo sms_e($markTDate); ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <p class="text-muted mb-0"><?php echo count($teacherRoster); ?> teacher(s) &middot; <?php echo sms_e($markTDate); ?><?php echo $existingTeacherMarks ? ' &middot; already marked - saving will update existing records' : ''; ?></p>
                        <button type="button" class="module-btn btn-outline-soft" id="markAllTeachersPresent"><i class="fa-solid fa-check-double"></i> Mark All Present</button>
                    </div>
                    <div class="table-shell">
                        <table class="table align-middle">
                            <thead><tr><th>Staff ID</th><th>Teacher Name</th><th>Status</th><th>Check-in</th><th>Check-out</th><th>Notes</th></tr></thead>
                            <tbody>
                                <?php foreach ($teacherRoster as $teacher): ?>
                                    <?php $existing = $existingTeacherMarks[(int) $teacher['id']] ?? null; ?>
                                    <tr>
                                        <td><?php echo sms_e($teacher['staff_no']); ?></td>
                                        <td><?php echo sms_e(trim($teacher['first_name'] . ' ' . $teacher['last_name'])); ?></td>
                                        <td>
                                            <select class="form-select mark-status" name="entries[<?php echo (int) $teacher['id']; ?>][status]">
                                                <?php foreach ($statusOptions as $value => $label): ?>
                                                    <option value="<?php echo sms_e($value); ?>" <?php echo ($existing['status'] ?? 'present') === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input class="form-control" type="time" name="entries[<?php echo (int) $teacher['id']; ?>][check_in]" value="<?php echo sms_e($existing['check_in'] ?? ''); ?>"></td>
                                        <td><input class="form-control" type="time" name="entries[<?php echo (int) $teacher['id']; ?>][check_out]" value="<?php echo sms_e($existing['check_out'] ?? ''); ?>"></td>
                                        <td><input class="form-control" name="entries[<?php echo (int) $teacher['id']; ?>][notes]" value="<?php echo sms_e($existing['notes'] ?? ''); ?>" placeholder="Optional"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Attendance</button></div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Student Attendance Records -->
    <section class="module-card">
        <h4>Student Attendance Records: Search &amp; Filter</h4>
        <form method="get">
            <?php foreach (['mark_session' => $markSession, 'mark_term' => $markTerm, 'mark_class' => $markClass, 'mark_section' => $markSection, 'mark_date' => $markDate, 'mark_tdate' => $markTDate] as $k => $v): ?><input type="hidden" name="<?php echo sms_e($k); ?>" value="<?php echo sms_e((string) $v); ?>"><?php endforeach; ?>
            <div class="filter-grid">
                <div><label>Student Name / Reg No.</label><input class="form-control" name="s_search" value="<?php echo sms_e($sSearch); ?>" placeholder="Search student"></div>
                <div><label>Class</label><select class="form-select" id="sClassSel" name="s_class"><option value="">All Classes</option><?php foreach ($classes as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo $sClass === (string) $class['id'] ? 'selected' : ''; ?>><?php echo sms_e($class['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select" id="sSectionSel" name="s_section"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option value="<?php echo (int) $section['id']; ?>" data-class="<?php echo (int) $section['class_id']; ?>" <?php echo $sSection === (string) $section['id'] ? 'selected' : ''; ?>><?php echo sms_e($section['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Status</label><select class="form-select" name="s_status"><option value="">All Statuses</option><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $sStatus === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div><label>Date</label><input class="form-control" type="date" name="s_date" value="<?php echo sms_e($sDate); ?>"></div>
                <div><label>Date From</label><input class="form-control" type="date" name="s_date_from" value="<?php echo sms_e($sDateFrom); ?>"></div>
                <div><label>Date To</label><input class="form-control" type="date" name="s_date_to" value="<?php echo sms_e($sDateTo); ?>"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="attendance-records.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Student Attendance Records</h4><p class="text-muted mb-0"><?php echo (int) $studentResult['meta']['total']; ?> record(s) found.</p></div>
            <div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="attendance-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['type' => 'student', 'format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table">
                <thead><tr><th>Date</th><th>Reg No.</th><th>Student Name</th><th>Class</th><th>Section</th><th>Status</th><th>Marked By</th><th>Notes</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($studentResult['data'] as $record): ?>
                        <tr>
                            <td><?php echo sms_e($record['attendance_date']); ?></td>
                            <td><?php echo sms_e($record['registration_no']); ?></td>
                            <td><?php echo sms_e(trim($record['first_name'] . ' ' . $record['last_name'])); ?></td>
                            <td><?php echo sms_e($record['class_name']); ?></td>
                            <td><?php echo sms_e($record['section_name'] ?? '-'); ?></td>
                            <td><?php echo sms_attendance_status_badge($record['status']); ?></td>
                            <td><?php echo sms_e($record['marked_by_name'] ?? 'System'); ?></td>
                            <td><?php echo sms_e($record['notes'] ?? ''); ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn edit-attendance-btn" title="Edit" type="button" data-bs-toggle="modal" data-bs-target="#editAttendanceModal" data-id="<?php echo (int) $record['id']; ?>" data-type="student" data-status="<?php echo sms_e($record['status']); ?>" data-notes="<?php echo sms_e($record['notes'] ?? ''); ?>"><i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="attendance-delete.php" class="delete-attendance-form" onsubmit="return confirm('Delete this student attendance record?');">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="attendance_id" value="<?php echo (int) $record['id']; ?>">
                                        <input type="hidden" name="attendance_type" value="student">
                                        <button class="action-btn" title="Delete" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$studentResult['data']): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No student attendance records match your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <span class="text-muted fw-bold"><?php echo (int) $studentResult['meta']['total']; ?> record(s) &middot; page <?php echo (int) $studentResult['meta']['page']; ?> of <?php echo (int) $studentResult['meta']['last_page']; ?></span>
            <?php if ($studentResult['meta']['last_page'] > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php for ($p = 1; $p <= $studentResult['meta']['last_page']; $p++): ?>
                        <a class="module-btn <?php echo $p === (int) $studentResult['meta']['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_attendance_query(['student_page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Teacher Attendance Records -->
    <section class="module-card">
        <h4>Teacher Attendance Records: Search &amp; Filter</h4>
        <form method="get">
            <?php foreach (['mark_session' => $markSession, 'mark_term' => $markTerm, 'mark_class' => $markClass, 'mark_section' => $markSection, 'mark_date' => $markDate, 'mark_tdate' => $markTDate] as $k => $v): ?><input type="hidden" name="<?php echo sms_e($k); ?>" value="<?php echo sms_e((string) $v); ?>"><?php endforeach; ?>
            <div class="filter-grid">
                <div><label>Teacher Name / Staff ID</label><input class="form-control" name="t_search" value="<?php echo sms_e($tSearch); ?>" placeholder="Search teacher"></div>
                <div><label>Department</label><select class="form-select" name="t_department"><option value="">All Departments</option><?php foreach ($departments as $department): ?><option value="<?php echo (int) $department['id']; ?>" <?php echo $tDept === (string) $department['id'] ? 'selected' : ''; ?>><?php echo sms_e($department['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Status</label><select class="form-select" name="t_status"><option value="">All Statuses</option><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $tStatus === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div><label>Date</label><input class="form-control" type="date" name="t_date" value="<?php echo sms_e($tDate); ?>"></div>
                <div><label>Date From</label><input class="form-control" type="date" name="t_date_from" value="<?php echo sms_e($tDateFrom); ?>"></div>
                <div><label>Date To</label><input class="form-control" type="date" name="t_date_to" value="<?php echo sms_e($tDateTo); ?>"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="attendance-records.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Teacher Attendance Records</h4><p class="text-muted mb-0"><?php echo (int) $teacherResult['meta']['total']; ?> record(s) found.</p></div>
            <div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="attendance-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['type' => 'teacher', 'format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table">
                <thead><tr><th>Date</th><th>Staff ID</th><th>Teacher Name</th><th>Department</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($teacherResult['data'] as $record): ?>
                        <tr>
                            <td><?php echo sms_e($record['attendance_date']); ?></td>
                            <td><?php echo sms_e($record['staff_no']); ?></td>
                            <td><?php echo sms_e(trim($record['first_name'] . ' ' . $record['last_name'])); ?></td>
                            <td><?php echo sms_e($record['department_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo sms_e($record['check_in'] ?? '-'); ?></td>
                            <td><?php echo sms_e($record['check_out'] ?? '-'); ?></td>
                            <td><?php echo sms_attendance_status_badge($record['status']); ?></td>
                            <td><?php echo sms_e($record['notes'] ?? ''); ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn edit-attendance-btn" title="Edit" type="button" data-bs-toggle="modal" data-bs-target="#editAttendanceModal" data-id="<?php echo (int) $record['id']; ?>" data-type="teacher" data-status="<?php echo sms_e($record['status']); ?>" data-notes="<?php echo sms_e($record['notes'] ?? ''); ?>"><i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="attendance-delete.php" class="delete-attendance-form" onsubmit="return confirm('Delete this teacher attendance record?');">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="attendance_id" value="<?php echo (int) $record['id']; ?>">
                                        <input type="hidden" name="attendance_type" value="teacher">
                                        <button class="action-btn" title="Delete" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$teacherResult['data']): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No teacher attendance records match your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <span class="text-muted fw-bold"><?php echo (int) $teacherResult['meta']['total']; ?> record(s) &middot; page <?php echo (int) $teacherResult['meta']['page']; ?> of <?php echo (int) $teacherResult['meta']['last_page']; ?></span>
            <?php if ($teacherResult['meta']['last_page'] > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php for ($p = 1; $p <= $teacherResult['meta']['last_page']; $p++): ?>
                        <a class="module-btn <?php echo $p === (int) $teacherResult['meta']['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_attendance_query(['teacher_page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="attendance-update.php">
            <div class="modal-header"><h5 class="modal-title">Edit Attendance Record</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                <input type="hidden" name="attendance_id" id="attendanceRecordId">
                <input type="hidden" name="attendance_type" id="attendanceRecordType">
                <label>Status</label>
                <select class="form-select mb-3" name="status" id="attendanceRecordStatus" required>
                    <?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo sms_e($value); ?>"><?php echo sms_e($label); ?></option><?php endforeach; ?>
                </select>
                <label>Notes</label>
                <textarea class="form-control" name="notes" id="attendanceRecordNotes" placeholder="Enter update notes"></textarea>
            </div>
            <div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Update Attendance</button></div>
        </form>
    </div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    document.querySelectorAll('.edit-attendance-btn').forEach(function(button){
        button.addEventListener('click', function(){
            document.getElementById('attendanceRecordId').value = button.dataset.id || '';
            document.getElementById('attendanceRecordType').value = button.dataset.type || '';
            document.getElementById('attendanceRecordStatus').value = button.dataset.status || 'present';
            document.getElementById('attendanceRecordNotes').value = button.dataset.notes || '';
        });
    });
    var markAllPresent = document.getElementById('markAllPresent');
    if (markAllPresent) {
        markAllPresent.addEventListener('click', function(){
            document.querySelectorAll('#markStudent .mark-status').forEach(function(select){ select.value = 'present'; });
        });
    }
    var markAllTeachersPresent = document.getElementById('markAllTeachersPresent');
    if (markAllTeachersPresent) {
        markAllTeachersPresent.addEventListener('click', function(){
            document.querySelectorAll('#markTeacher .mark-status').forEach(function(select){ select.value = 'present'; });
        });
    }
    function bindSectionFilter(classSelectId, sectionSelectId){
        var classSelect = document.getElementById(classSelectId);
        var sectionSelect = document.getElementById(sectionSelectId);
        if (!classSelect || !sectionSelect) { return; }
        function filter(){
            var selected = classSelect.value;
            Array.prototype.forEach.call(sectionSelect.options, function(option){
                if (!option.value) { return; }
                option.hidden = selected !== '' && option.dataset.class !== selected;
            });
        }
        classSelect.addEventListener('change', filter);
        filter();
    }
    bindSectionFilter('markClassSel', 'markSectionSel');
    bindSectionFilter('sClassSel', 'sSectionSel');
})();
</script>

<?php require_once('includes/footer.php'); ?>
