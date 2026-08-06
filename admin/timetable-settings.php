<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\TimetableService;

$timetableService = new TimetableService();
require_once('includes/header.php');
require_once('includes/timetable-module-styles.php');

$flashMessages = sms_flash();
$errors = Session::errors();
$old = Session::oldAll();

$timetableSettings = $timetableService->generalSettings();
$timetablePeriods = $timetableService->periodsForSelect();
$workingDays = $timetableService->allWorkingDays();
$venues = $timetableService->venuesForSelect();

function sms_tt_field(array $old, array $settings, string $oldKey, string $settingsKey): string
{
    if (array_key_exists($oldKey, $old)) {
        return sms_e($old[$oldKey]);
    }
    return sms_e((string) ($settings[$settingsKey] ?? ''));
}

$cards = [
    ['title' => 'Opening Time', 'value' => $timetableSettings['opening_time'], 'description' => 'School day starts', 'icon' => 'fa-door-open', 'color' => 'success'],
    ['title' => 'Closing Time', 'value' => $timetableSettings['closing_time'], 'description' => 'School day ends', 'icon' => 'fa-door-closed', 'color' => 'blue'],
    ['title' => 'Periods Per Day', 'value' => $timetableSettings['periods_per_day'], 'description' => 'Default daily periods', 'icon' => 'fa-list-ol', 'color' => 'warning'],
    ['title' => 'Conflict Detection', 'value' => $timetableSettings['enable_conflict_detection'] ? 'On' : 'Off', 'description' => 'Overlap protection', 'icon' => 'fa-shield-halved', 'color' => 'success'],
];
?>
<div class="admin-timetable-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Timetable Management <i class="fa-solid fa-angle-right mx-1"></i> Timetable Settings</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-gears"></i> Timetable Settings</span>
                <h3 class="mt-3 mb-2">Timetable Settings</h3>
                <p class="text-muted mb-0">Configure global school hours, working days, periods, breaks, venues, and scheduling rules.</p>
            </div>
            <button class="module-btn btn-primary-soft" form="timetableSettingsForm" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </div>
    </section>

    <section class="row g-3 mb-4"><?php foreach ($cards as $card): ?><div class="col-sm-6 col-xl-3"><?php sms_render_component('statistics-card', $card); ?></div><?php endforeach; ?></section>

    <form id="timetableSettingsForm" method="post" action="timetable-settings-save.php">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <section class="module-card">
            <h4>School Hours</h4>
            <div class="filter-grid">
                <div><label>School Opening Time</label><input class="form-control" type="time" name="opening_time" value="<?php echo sms_tt_field($old, $timetableSettings, 'opening_time', 'opening_time'); ?>"></div>
                <div><label>School Closing Time</label><input class="form-control" type="time" name="closing_time" value="<?php echo sms_tt_field($old, $timetableSettings, 'closing_time', 'closing_time'); ?>"></div>
                <div><label>Default Lesson Duration (minutes)</label><input class="form-control" type="number" min="1" name="default_lesson_duration" value="<?php echo sms_tt_field($old, $timetableSettings, 'default_lesson_duration', 'default_lesson_duration'); ?>"></div>
                <div><label>Break Duration (minutes)</label><input class="form-control" type="number" min="0" name="break_duration" value="<?php echo sms_tt_field($old, $timetableSettings, 'break_duration', 'break_duration'); ?>"></div>
                <div><label>Number of Periods Per Day</label><input class="form-control" type="number" min="1" name="periods_per_day" value="<?php echo sms_tt_field($old, $timetableSettings, 'periods_per_day', 'periods_per_day'); ?>"></div>
            </div>
        </section>
        <section class="module-card">
            <h4>General Settings</h4>
            <div class="filter-grid">
                <div><label>Enable Conflict Detection</label><select class="form-select" name="enable_conflict_detection"><option value="1" <?php echo $timetableSettings['enable_conflict_detection'] ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo !$timetableSettings['enable_conflict_detection'] ? 'selected' : ''; ?>>Disabled</option></select></div>
                <div><label>Allow Double Periods</label><select class="form-select" name="allow_double_periods"><option value="1" <?php echo $timetableSettings['allow_double_periods'] ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo !$timetableSettings['allow_double_periods'] ? 'selected' : ''; ?>>Disabled</option></select></div>
                <div><label>Auto Assign Break Time</label><select class="form-select" name="auto_assign_break_time"><option value="1" <?php echo $timetableSettings['auto_assign_break_time'] ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo !$timetableSettings['auto_assign_break_time'] ? 'selected' : ''; ?>>Disabled</option></select></div>
                <div><label>Default Venue</label><select class="form-select" name="default_venue_id"><option value="">Optional</option><?php foreach ($venues as $venue): ?><option value="<?php echo (int) $venue['id']; ?>" <?php echo $timetableSettings['default_venue_id'] === (int) $venue['id'] ? 'selected' : ''; ?>><?php echo sms_e($venue['name']); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit">Save Changes</button><a class="module-btn btn-muted-soft" href="timetable-settings.php">Reset</a></div>
            </div>
        </section>
    </form>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Working Days</h4><p class="text-muted mb-0">Only enabled days appear as scheduling options in Add Timetable.</p></div>
            <button class="module-btn btn-primary-soft" type="submit" form="workingDaysForm"><i class="fa-solid fa-floppy-disk"></i> Save Working Days</button>
        </div>
        <form id="workingDaysForm" method="post" action="timetable-working-days-save.php">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <div class="row g-3 mt-1">
                <?php foreach ($workingDays as $day): ?>
                    <div class="col-sm-6 col-xl-3"><label class="day-toggle"><span><?php echo sms_e($day['day_name']); ?></span><input class="form-check-input" type="checkbox" name="days[]" value="<?php echo sms_e($day['day_name']); ?>" <?php echo $day['is_enabled'] ? 'checked' : ''; ?>></label></div>
                <?php endforeach; ?>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Default Time Slots</h4><p class="text-muted mb-0">Edit school lesson periods and break slots.</p></div>
            <button class="module-btn btn-primary-soft" id="addPeriodBtn" type="button"><i class="fa-solid fa-plus"></i> Add Period</button>
        </div>
        <div class="table-shell">
            <table class="table">
                <thead><tr><th>Period</th><th>Start</th><th>End</th><th>Break?</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($timetablePeriods as $period): ?>
                        <tr>
                            <td><?php echo sms_e($period['period_name']); ?></td>
                            <td><?php echo sms_e(substr($period['start_time'], 0, 5)); ?></td>
                            <td><?php echo sms_e(substr($period['end_time'], 0, 5)); ?></td>
                            <td><?php echo $period['is_break'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn edit-period-btn" type="button" title="Edit Period"
                                        data-id="<?php echo (int) $period['id']; ?>" data-name="<?php echo sms_e($period['period_name']); ?>"
                                        data-start="<?php echo sms_e(substr($period['start_time'], 0, 5)); ?>" data-end="<?php echo sms_e(substr($period['end_time'], 0, 5)); ?>" data-break="<?php echo $period['is_break'] ? '1' : '0'; ?>">
                                        <i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="timetable-period-delete.php" onsubmit="return confirm('Delete this period?');" style="display:inline">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $period['id']; ?>">
                                        <button class="action-btn" type="submit" title="Delete Period"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$timetablePeriods): ?><tr><td colspan="5" class="text-center text-muted py-4">No periods defined yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="modal fade" id="periodModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="timetable-period-store.php" id="periodForm">
                <div class="modal-header"><h5 class="modal-title" id="periodModalTitle">Add Period</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="periodId">
                    <div class="form-grid">
                        <div class="full"><label>Period Name</label><input class="form-control" name="period_name" id="periodName" required placeholder="Period 1"></div>
                        <div><label>Start</label><input class="form-control" name="start_time" id="periodStart" type="time" required></div>
                        <div><label>End</label><input class="form-control" name="end_time" id="periodEnd" type="time" required></div>
                        <div><label>Break Period?</label><select class="form-select" name="is_break" id="periodIsBreak"><option value="0">No</option><option value="1">Yes</option></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="module-btn btn-muted-soft" data-bs-dismiss="modal" type="button">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Period</button></div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
<script data-cfasync="false" type="text/javascript">
(function(){
    var modalEl = document.getElementById('periodModal');
    function getModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }
    var form = document.getElementById('periodForm');
    var title = document.getElementById('periodModalTitle');

    document.getElementById('addPeriodBtn').addEventListener('click', function(){
        title.textContent = 'Add Period';
        form.reset();
        document.getElementById('periodId').value = '';
        var modal = getModal(); if (modal) { modal.show(); }
    });

    document.querySelectorAll('.edit-period-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            title.textContent = 'Edit Period';
            document.getElementById('periodId').value = btn.dataset.id;
            document.getElementById('periodName').value = btn.dataset.name;
            document.getElementById('periodStart').value = btn.dataset.start;
            document.getElementById('periodEnd').value = btn.dataset.end;
            document.getElementById('periodIsBreak').value = btn.dataset.break;
            var modal = getModal(); if (modal) { modal.show(); }
        });
    });
})();
</script>
<?php require_once('includes/footer.php'); ?>
