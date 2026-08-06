<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\TimetableService;

$timetableService = new TimetableService();
$teacherId = (int) ($_GET['teacher_id'] ?? 0);
$teachers = $timetableService->teachersForSelect();
$teacher = null;
foreach ($teachers as $item) {
    if ((int) $item['id'] === $teacherId) {
        $teacher = $item;
        break;
    }
}

if ($teacher === null && $teachers) {
    $teacher = $teachers[0];
    $teacherId = (int) $teacher['id'];
}

require_once('includes/header.php');
require_once('includes/timetable-module-styles.php');

$sessionId = $timetableService->currentSessionId();
$termId = $timetableService->currentTermId();
$entries = $teacherId ? $timetableService->grid(['session_id' => $sessionId, 'term_id' => $termId, 'teacher_id' => $teacherId]) : [];
$workingDays = $timetableService->workingDays();
$fullName = $teacher ? trim($teacher['first_name'] . ' ' . $teacher['last_name']) : 'No teacher found';
?>
<div class="admin-timetable-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Timetable Management <i class="fa-solid fa-angle-right mx-1"></i> Teacher Timetable</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-calendar-days"></i> Timetable</span>
                <h3 class="mt-3 mb-2"><?php echo sms_e($fullName); ?></h3>
                <p class="text-muted mb-0">Real weekly timetable sourced from published and draft timetable entries.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="module-btn btn-primary-soft" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Timetable</button>
                <a class="module-btn btn-outline-soft" href="timetable-export.php?teacher_id=<?php echo (int) $teacherId; ?>"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
            </div>
        </div>
    </section>

    <section class="module-card">
        <label for="teacherSelect">Select Teacher</label>
        <select class="form-select" id="teacherSelect" onchange="window.location='teacher-timetable.php?teacher_id='+this.value">
            <?php foreach ($teachers as $item): ?>
                <option value="<?php echo (int) $item['id']; ?>" <?php echo (int) $item['id'] === $teacherId ? 'selected' : ''; ?>><?php echo sms_e(trim($item['first_name'] . ' ' . $item['last_name'])); ?></option>
            <?php endforeach; ?>
        </select>
    </section>

    <section class="module-card">
        <div class="table-shell">
            <table class="table">
                <thead><tr><th>Day</th><th>Time</th><th>Class</th><th>Subject</th><th>Venue</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($entries as $row): ?>
                        <tr>
                            <td><?php echo sms_e($row['day_name']); ?></td>
                            <td><?php echo sms_e(substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5)); ?></td>
                            <td><?php echo sms_e($row['class_name'] . ($row['section_name'] ? ' - ' . $row['section_name'] : '')); ?></td>
                            <td><?php echo sms_e($row['subject_name']); ?></td>
                            <td><?php echo sms_e($row['venue_name'] ?? '-'); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($row['status']); ?>"><?php echo sms_e(ucfirst($row['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$entries): ?><tr><td colspan="6" class="text-center text-muted py-4">No timetable entries for this teacher yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="module-card">
        <h4 class="mb-3">Weekly Timetable Layout</h4>
        <div class="row g-3">
            <?php foreach ($workingDays as $day): ?>
                <div class="col-md">
                    <div class="module-card h-100 mb-0">
                        <h5><?php echo sms_e($day); ?></h5>
                        <?php $dayEntries = array_filter($entries, fn ($row) => $row['day_name'] === $day); ?>
                        <?php foreach ($dayEntries as $row): ?>
                            <div class="tt-entry mb-2"><strong><?php echo sms_e(substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5)); ?></strong><span><?php echo sms_e($row['class_name']); ?> - <?php echo sms_e($row['subject_name']); ?></span></div>
                        <?php endforeach; ?>
                        <?php if (!$dayEntries): ?><p class="text-muted mb-0">No lessons.</p><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
