<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\TimetableService;

$timetableService = new TimetableService();
require_once('includes/header.php');
require_once('includes/timetable-module-styles.php');

$errors = Session::errors();
$old = Session::oldAll();

$sessionId = (string) ($_GET['session_id'] ?? '');
$termId = (string) ($_GET['term_id'] ?? '');
$classId = (string) ($_GET['class_id'] ?? '');
$sectionId = (string) ($_GET['section_id'] ?? '');
$teacherId = (string) ($_GET['teacher_id'] ?? '');
$departmentId = (string) ($_GET['department_id'] ?? '');
$day = (string) ($_GET['day'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$search = trim((string) ($_GET['search'] ?? ''));
$view = (string) ($_GET['view'] ?? 'class');
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = [
    'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'section_id' => $sectionId,
    'teacher_id' => $teacherId, 'department_id' => $departmentId, 'day' => $day, 'status' => $status, 'search' => $search,
];

$sessions = $timetableService->sessionsForSelect();
$terms = $timetableService->termsForSelect();
$classes = $timetableService->classesForSelect();
$sections = $timetableService->sectionsForSelect();
$subjects = $timetableService->subjectsForSelect();
$teachers = $timetableService->teachersForSelect();
$venues = $timetableService->venuesForSelect();
$departments = $timetableService->departmentsForSelect();
$workingDays = $timetableService->workingDays();
$statuses = ['draft' => 'Draft', 'published' => 'Published', 'unpublished' => 'Unpublished'];

$gridClassId = $classId !== '' ? (int) $classId : (int) ($classes[0]['id'] ?? 0);
$gridTeacherId = $teacherId !== '' ? (int) $teacherId : (int) ($teachers[0]['id'] ?? 0);

$classGridEntries = $gridClassId ? $timetableService->grid(array_merge($filters, ['class_id' => $gridClassId, 'teacher_id' => ''])) : [];
$teacherGridEntries = $gridTeacherId ? $timetableService->grid(array_merge($filters, ['teacher_id' => $gridTeacherId, 'class_id' => ''])) : [];

$tableResult = $timetableService->list($filters, $page, 10);

$totalToday = 0;
$todayName = date('l');
foreach ($tableResult['data'] as $row) {
    if ($row['day_name'] === $todayName) { $totalToday++; }
}
$publishedCount = (int) ($timetableService->list(array_merge($filters, ['status' => 'published']), 1, 1)['meta']['total'] ?? 0);

$cards = [
    ['title' => 'Total Timetable Entries', 'value' => number_format((int) $tableResult['meta']['total']), 'description' => 'Matching current filters', 'icon' => 'fa-calendar-days', 'color' => 'success'],
    ['title' => 'Published Entries', 'value' => number_format($publishedCount), 'description' => 'Visible to teachers/students', 'icon' => 'fa-circle-check', 'color' => 'blue'],
    ['title' => 'Classes', 'value' => number_format(count($classes)), 'description' => 'Active classes', 'icon' => 'fa-school', 'color' => 'success'],
    ['title' => 'Teachers', 'value' => number_format(count($teachers)), 'description' => 'Active teaching staff', 'icon' => 'fa-chalkboard-user', 'color' => 'warning'],
];

function sms_tt_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    return 'timetables.php?' . http_build_query($query);
}

function sms_tt_old(array $old, string $key, string $default = ''): string
{
    return sms_e($old[$key] ?? $default);
}
?>
<div class="admin-timetable-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Timetable Management <i class="fa-solid fa-angle-right mx-1"></i> Timetables</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-calendar-days"></i> Timetables</span>
                <h3 class="mt-3 mb-2">Timetables</h3>
                <p class="text-muted mb-0">Create, manage, publish, print, and export class and teacher timetables with real conflict detection.</p>
            </div>
            <button class="module-btn btn-primary-soft" id="addTimetableBtn" type="button"><i class="fa-solid fa-plus"></i> Add Timetable</button>
        </div>
    </section>

    <section class="row g-3 mb-4"><?php foreach ($cards as $card): ?><div class="col-sm-6 col-xl-3"><?php sms_render_component('dashboard-card', $card); ?></div><?php endforeach; ?></section>

    <section class="module-card">
        <h4>Search &amp; Filter</h4>
        <form method="get" id="filterForm">
            <input type="hidden" name="view" value="<?php echo sms_e($view); ?>">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id"><option value="">All Sessions</option><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $sessionId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="term_id"><option value="">All Terms</option><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $termId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" id="filterClass" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $classId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select" id="filterSection" name="section_id"><option value="">All Sections</option><?php foreach ($sections as $item): ?><option value="<?php echo (int) $item['id']; ?>" data-class="<?php echo (int) $item['class_id']; ?>" <?php echo $sectionId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Teacher</label><select class="form-select" name="teacher_id"><option value="">All Teachers</option><?php foreach ($teachers as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $teacherId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e(trim($item['first_name'] . ' ' . $item['last_name'])); ?></option><?php endforeach; ?></select></div>
                <div><label>Department</label><select class="form-select" name="department_id"><option value="">All Departments</option><?php foreach ($departments as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $departmentId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Day of the Week</label><select class="form-select" name="day"><option value="">All Days</option><?php foreach ($workingDays as $item): ?><option <?php echo $day === $item ? 'selected' : ''; ?>><?php echo sms_e($item); ?></option><?php endforeach; ?></select></div>
                <div><label>Status</label><select class="form-select" name="status"><option value="">All Statuses</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $status === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="full"><label>Search</label><input class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Subject, teacher, or class"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="timetables.php">Reset Filters</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Timetable Views</h4><p class="text-muted mb-0">Switch between class grid, teacher schedule, and full table layout.</p></div>
            <div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="timetable-export.php?<?php echo sms_e(http_build_query($_GET)); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
        <div class="view-tabs">
            <a class="view-tab <?php echo $view === 'class' ? 'active' : ''; ?>" href="<?php echo sms_e(sms_tt_query(['view' => 'class'])); ?>"><i class="fa-solid fa-table-cells-large"></i> Class Timetable</a>
            <a class="view-tab <?php echo $view === 'teacher' ? 'active' : ''; ?>" href="<?php echo sms_e(sms_tt_query(['view' => 'teacher'])); ?>"><i class="fa-solid fa-chalkboard-user"></i> Teacher Timetable</a>
            <a class="view-tab <?php echo $view === 'table' ? 'active' : ''; ?>" href="<?php echo sms_e(sms_tt_query(['view' => 'table'])); ?>"><i class="fa-solid fa-table-list"></i> Table View</a>
        </div>

        <?php if ($view === 'class'): ?>
            <?php if (!$gridClassId): ?>
                <p class="text-muted">Add a class first to view its timetable grid.</p>
            <?php else: ?>
                <p class="text-muted mb-2">Showing: <strong><?php echo sms_e(current(array_filter($classes, fn ($c) => (int) $c['id'] === $gridClassId))['name'] ?? ''); ?></strong><?php echo $sectionId !== '' ? ' &middot; ' . sms_e(current(array_filter($sections, fn ($s) => (int) $s['id'] === (int) $sectionId))['name'] ?? '') : ''; ?> &middot; use the Class/Section filters above to change.</p>
                <div class="timetable-grid">
                    <div class="tt-cell tt-head">Time</div>
                    <?php foreach ($workingDays as $wDay): ?><div class="tt-cell tt-head"><?php echo sms_e($wDay); ?></div><?php endforeach; ?>
                    <?php $periods = $timetableService->periodsForSelect(); foreach ($periods as $period): ?>
                        <div class="tt-cell tt-head"><?php echo sms_e($period['period_name']); ?><br><small><?php echo sms_e(substr($period['start_time'], 0, 5) . ' - ' . substr($period['end_time'], 0, 5)); ?></small></div>
                        <?php foreach ($workingDays as $wDay): ?>
                            <?php $entry = current(array_filter($classGridEntries, fn ($e) => $e['day_name'] === $wDay && $e['start_time'] === $period['start_time'] && $e['end_time'] === $period['end_time'])); ?>
                            <div class="tt-cell">
                                <?php if ($entry): ?>
                                    <div class="tt-entry"><strong><?php echo sms_e($entry['subject_name']); ?></strong><span><?php echo sms_e(trim($entry['teacher_first_name'] . ' ' . $entry['teacher_last_name'])); ?></span><br><small><?php echo sms_e($entry['venue_name'] ?? 'No venue'); ?></small></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($view === 'teacher'): ?>
            <?php if (!$gridTeacherId): ?>
                <p class="text-muted">No teachers available.</p>
            <?php else: ?>
                <p class="text-muted mb-2">Showing: <strong><?php echo sms_e(trim((current(array_filter($teachers, fn ($t) => (int) $t['id'] === $gridTeacherId)) ?: ['first_name' => '', 'last_name' => ''])['first_name'] . ' ' . (current(array_filter($teachers, fn ($t) => (int) $t['id'] === $gridTeacherId)) ?: ['last_name' => ''])['last_name'])); ?></strong> &middot; use the Teacher filter above to change.</p>
                <div class="table-shell">
                    <table class="table"><thead><tr><th>Day</th><th>Time</th><th>Class</th><th>Subject</th><th>Venue</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($teacherGridEntries as $entry): ?>
                                <tr><td><?php echo sms_e($entry['day_name']); ?></td><td><?php echo sms_e(substr($entry['start_time'], 0, 5) . ' - ' . substr($entry['end_time'], 0, 5)); ?></td><td><?php echo sms_e($entry['class_name'] . ($entry['section_name'] ? ' - ' . $entry['section_name'] : '')); ?></td><td><?php echo sms_e($entry['subject_name']); ?></td><td><?php echo sms_e($entry['venue_name'] ?? '-'); ?></td><td><span class="status-badge status-<?php echo sms_e($entry['status']); ?>"><?php echo sms_e(ucfirst($entry['status'])); ?></span></td></tr>
                            <?php endforeach; ?>
                            <?php if (!$teacherGridEntries): ?><tr><td colspan="6" class="text-center text-muted py-4">No timetable entries for this teacher yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="post" action="timetable-bulk.php" id="bulkForm">
                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="module-btn btn-primary-soft" type="submit" name="bulk_action" value="published"><i class="fa-solid fa-bullhorn"></i> Publish Selected</button>
                    <button class="module-btn btn-muted-soft" type="submit" name="bulk_action" value="unpublished"><i class="fa-solid fa-eye-slash"></i> Unpublish Selected</button>
                    <button class="module-btn btn-danger-soft" type="submit" name="bulk_action" value="delete" onclick="return confirm('Delete all selected timetable entries?');"><i class="fa-solid fa-trash"></i> Delete Selected</button>
                </div>
                <div class="table-shell">
                    <table class="table">
                        <thead><tr><th><input class="form-check-input" type="checkbox" id="selectAllEntries"></th><th>Day</th><th>Start</th><th>End</th><th>Subject</th><th>Teacher</th><th>Class</th><th>Section</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($tableResult['data'] as $entry): ?>
                                <tr>
                                    <td><input class="form-check-input entry-select" type="checkbox" name="ids[]" value="<?php echo (int) $entry['id']; ?>"></td>
                                    <td><?php echo sms_e($entry['day_name']); ?></td>
                                    <td><?php echo sms_e(substr($entry['start_time'], 0, 5)); ?></td>
                                    <td><?php echo sms_e(substr($entry['end_time'], 0, 5)); ?></td>
                                    <td><?php echo sms_e($entry['subject_name']); ?></td>
                                    <td><?php echo sms_e(trim($entry['teacher_first_name'] . ' ' . $entry['teacher_last_name'])); ?></td>
                                    <td><?php echo sms_e($entry['class_name']); ?></td>
                                    <td><?php echo sms_e($entry['section_name'] ?? '-'); ?></td>
                                    <td><?php echo sms_e($entry['venue_name'] ?? '-'); ?></td>
                                    <td><span class="status-badge status-<?php echo sms_e($entry['status']); ?>"><?php echo sms_e(ucfirst($entry['status'])); ?></span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="module-btn btn-muted-soft dropdown-toggle" data-bs-toggle="dropdown" type="button">Actions</button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button class="dropdown-item edit-entry-btn" type="button"
                                                    data-id="<?php echo (int) $entry['id']; ?>" data-session="<?php echo (int) $entry['session_id']; ?>" data-term="<?php echo (int) $entry['term_id']; ?>"
                                                    data-class="<?php echo (int) $entry['class_id']; ?>" data-section="<?php echo (int) ($entry['section_id'] ?? 0); ?>"
                                                    data-subject="<?php echo (int) $entry['subject_id']; ?>" data-teacher="<?php echo (int) $entry['teacher_id']; ?>"
                                                    data-venue="<?php echo (int) ($entry['venue_id'] ?? 0); ?>" data-day="<?php echo sms_e($entry['day_name']); ?>"
                                                    data-start="<?php echo sms_e(substr($entry['start_time'], 0, 5)); ?>" data-end="<?php echo sms_e(substr($entry['end_time'], 0, 5)); ?>" data-status="<?php echo sms_e($entry['status']); ?>">
                                                    <i class="fa-solid fa-pen me-2"></i>Edit</button>
                                                <?php if ($entry['status'] !== 'published'): ?><button class="dropdown-item publish-btn" type="button" data-id="<?php echo (int) $entry['id']; ?>" data-status="published"><i class="fa-solid fa-bullhorn me-2"></i>Publish</button><?php endif; ?>
                                                <?php if ($entry['status'] === 'published'): ?><button class="dropdown-item publish-btn" type="button" data-id="<?php echo (int) $entry['id']; ?>" data-status="unpublished"><i class="fa-solid fa-eye-slash me-2"></i>Unpublish</button><?php endif; ?>
                                                <button class="dropdown-item text-danger delete-btn" type="button" data-id="<?php echo (int) $entry['id']; ?>"><i class="fa-solid fa-trash me-2"></i>Delete</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$tableResult['data']): ?><tr><td colspan="11" class="text-center text-muted py-4">No timetable entries match your search.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
                <span class="text-muted fw-bold"><?php echo (int) $tableResult['meta']['total']; ?> record(s) &middot; page <?php echo (int) $tableResult['meta']['page']; ?> of <?php echo (int) $tableResult['meta']['last_page']; ?></span>
                <?php if ($tableResult['meta']['last_page'] > 1): ?>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php for ($p = 1; $p <= $tableResult['meta']['last_page']; $p++): ?>
                            <a class="module-btn <?php echo $p === (int) $tableResult['meta']['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_tt_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Reports -->
    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Reports</h4><p class="text-muted mb-0">Teacher workload, class schedule, and venue utilization for the selected session/term.</p></div>
            <a class="module-btn btn-outline-soft" href="timetable-report-export.php?<?php echo sms_e(http_build_query($_GET)); ?>"><i class="fa-solid fa-file-csv"></i> Export Report</a>
        </div>
        <?php
        $reportSessionId = $sessionId !== '' ? (int) $sessionId : $timetableService->currentSessionId();
        $reportTermId = $termId !== '' ? (int) $termId : $timetableService->currentTermId();
        $workload = $timetableService->teacherWorkloadReport($reportSessionId, $reportTermId);
        $classReport = $timetableService->classScheduleReport($reportSessionId, $reportTermId);
        $venueReport = $timetableService->venueUtilizationReport($reportSessionId, $reportTermId);
        ?>
        <div class="two-grid">
            <div>
                <h5 class="mb-2">Teacher Workload</h5>
                <div class="table-shell"><table class="table"><thead><tr><th>Teacher</th><th>Periods</th><th>Hours</th><th>Subjects</th><th>Classes</th></tr></thead><tbody>
                    <?php foreach ($workload as $row): ?><tr><td><?php echo sms_e($row['teacher_name']); ?></td><td><?php echo (int) $row['periods']; ?></td><td><?php echo sms_e(number_format((float) $row['hours'], 1)); ?></td><td><?php echo (int) $row['subject_count']; ?></td><td><?php echo (int) $row['class_count']; ?></td></tr><?php endforeach; ?>
                    <?php if (!$workload): ?><tr><td colspan="5" class="text-center text-muted py-3">No data.</td></tr><?php endif; ?>
                </tbody></table></div>
            </div>
            <div>
                <h5 class="mb-2">Class Schedule Summary</h5>
                <div class="table-shell"><table class="table"><thead><tr><th>Class</th><th>Periods</th><th>Hours</th><th>Subjects</th></tr></thead><tbody>
                    <?php foreach ($classReport as $row): ?><tr><td><?php echo sms_e($row['class_name']); ?></td><td><?php echo (int) $row['periods']; ?></td><td><?php echo sms_e(number_format((float) $row['hours'], 1)); ?></td><td><?php echo (int) $row['subject_count']; ?></td></tr><?php endforeach; ?>
                    <?php if (!$classReport): ?><tr><td colspan="4" class="text-center text-muted py-3">No data.</td></tr><?php endif; ?>
                </tbody></table></div>
            </div>
        </div>
        <div class="mt-3">
            <h5 class="mb-2">Venue Utilization</h5>
            <div class="table-shell"><table class="table"><thead><tr><th>Venue</th><th>Bookings</th><th>Hours</th></tr></thead><tbody>
                <?php foreach ($venueReport as $row): ?><tr><td><?php echo sms_e($row['venue_name']); ?></td><td><?php echo (int) $row['bookings']; ?></td><td><?php echo sms_e(number_format((float) $row['hours'], 1)); ?></td></tr><?php endforeach; ?>
                <?php if (!$venueReport): ?><tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr><?php endif; ?>
            </tbody></table></div>
        </div>
    </section>

    <!-- Add/Edit modal -->
    <div class="modal fade" id="timetableModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" action="<?php echo !empty($old['id']) ? 'timetable-update.php' : 'timetable-store.php'; ?>" id="timetableForm">
                <div class="modal-header"><h5 class="modal-title" id="timetableModalTitle"><?php echo !empty($old['id']) ? 'Edit Timetable Entry' : 'Add Timetable Entry'; ?></h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="ttId" value="<?php echo sms_e($old['id'] ?? ''); ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <?php if (isset($errors['conflict'])): ?><div class="alert alert-danger"><?php echo sms_e($errors['conflict']); ?></div><?php endif; ?>
                    <div class="form-grid">
                        <div><label>Academic Session</label><select class="form-select" name="session_id" id="ttSession" required><option value="">Select</option><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo ($old['session_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select><?php if (isset($errors['session_id'])): ?><span class="field-error text-danger"><?php echo sms_e($errors['session_id']); ?></span><?php endif; ?></div>
                        <div><label>Term</label><select class="form-select" name="term_id" id="ttTerm" required><option value="">Select</option><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" data-session="<?php echo (int) $item['session_id']; ?>" <?php echo ($old['term_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Class</label><select class="form-select" name="class_id" id="ttClass" required><option value="">Select</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo ($old['class_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Section</label><select class="form-select" name="section_id" id="ttSection"><option value="">Whole Class</option><?php foreach ($sections as $item): ?><option value="<?php echo (int) $item['id']; ?>" data-class="<?php echo (int) $item['class_id']; ?>" <?php echo ($old['section_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Subject</label><select class="form-select" name="subject_id" id="ttSubject" required><option value="">Select</option><?php foreach ($subjects as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo ($old['subject_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Teacher</label><select class="form-select" name="teacher_id" id="ttTeacher" required><option value="">Select</option><?php foreach ($teachers as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo ($old['teacher_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e(trim($item['first_name'] . ' ' . $item['last_name'])); ?></option><?php endforeach; ?></select></div>
                        <div><label>Venue/Classroom</label><select class="form-select" name="venue_id" id="ttVenue"><option value="">No venue</option><?php foreach ($venues as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo ($old['venue_id'] ?? '') === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Day</label><select class="form-select" name="day_name" id="ttDay" required><?php foreach ($workingDays as $item): ?><option <?php echo ($old['day_name'] ?? '') === $item ? 'selected' : ''; ?>><?php echo sms_e($item); ?></option><?php endforeach; ?></select></div>
                        <div><label>Start Time</label><input class="form-control" name="start_time" id="ttStart" type="time" value="<?php echo sms_tt_old($old, 'start_time'); ?>" required></div>
                        <div><label>End Time</label><input class="form-control" name="end_time" id="ttEnd" type="time" value="<?php echo sms_tt_old($old, 'end_time'); ?>" required></div>
                        <div><label>Status</label><select class="form-select" name="status" id="ttStatus"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo ($old['status'] ?? 'draft') === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="module-btn btn-muted-soft" data-bs-dismiss="modal" type="button">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Timetable</button></div>
            </form>
        </div>
    </div>

    <!-- Hidden delete/publish forms -->
    <form method="post" action="timetable-delete.php" id="deleteForm" style="display:none">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="id" id="deleteId">
        <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
    </form>
    <form method="post" action="timetable-bulk.php" id="publishForm" style="display:none">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="ids[]" id="publishId">
        <input type="hidden" name="bulk_action" id="publishAction">
        <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
    </form>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    var modalEl = document.getElementById('timetableModal');
    function getModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }
    var form = document.getElementById('timetableForm');
    var title = document.getElementById('timetableModalTitle');

    document.getElementById('addTimetableBtn').addEventListener('click', function(){
        form.action = 'timetable-store.php';
        title.textContent = 'Add Timetable Entry';
        document.getElementById('ttId').value = '';
        form.reset();
        var modal = getModal(); if (modal) { modal.show(); }
    });

    document.querySelectorAll('.edit-entry-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            form.action = 'timetable-update.php';
            title.textContent = 'Edit Timetable Entry';
            document.getElementById('ttId').value = btn.dataset.id;
            document.getElementById('ttSession').value = btn.dataset.session;
            document.getElementById('ttTerm').value = btn.dataset.term;
            document.getElementById('ttClass').value = btn.dataset.class;
            document.getElementById('ttSection').value = btn.dataset.section || '';
            document.getElementById('ttSubject').value = btn.dataset.subject;
            document.getElementById('ttTeacher').value = btn.dataset.teacher;
            document.getElementById('ttVenue').value = btn.dataset.venue || '';
            document.getElementById('ttDay').value = btn.dataset.day;
            document.getElementById('ttStart').value = btn.dataset.start;
            document.getElementById('ttEnd').value = btn.dataset.end;
            document.getElementById('ttStatus').value = btn.dataset.status;
            var modal = getModal(); if (modal) { modal.show(); }
        });
    });

    document.querySelectorAll('.delete-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            if (!confirm('Delete this timetable entry?')) { return; }
            document.getElementById('deleteId').value = btn.dataset.id;
            document.getElementById('deleteForm').submit();
        });
    });

    document.querySelectorAll('.publish-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.getElementById('publishId').value = btn.dataset.id;
            document.getElementById('publishAction').value = btn.dataset.status;
            document.getElementById('publishForm').submit();
        });
    });

    var selectAll = document.getElementById('selectAllEntries');
    if (selectAll) { selectAll.addEventListener('change', function(){ document.querySelectorAll('.entry-select').forEach(function(c){ c.checked = selectAll.checked; }); }); }

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
    bindSectionFilter('filterClass', 'filterSection');
    bindSectionFilter('ttClass', 'ttSection');

    <?php if ($errors !== []): ?>
    window.addEventListener('load', function(){ var modal = getModal(); if (modal) { modal.show(); } });
    <?php endif; ?>
})();
</script>

<?php require_once('includes/footer.php'); ?>
