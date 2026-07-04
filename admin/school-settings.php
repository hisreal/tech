<?php
require_once __DIR__ . '/../includes/helpers/auth.php';

use App\Controllers\SettingsController;

sms_require_auth(['super-admin', 'admin']);

$controller = new SettingsController();
$currentUser = sms_current_user();
$activeTab = $_SESSION['_school_settings_active_tab'] ?? 'school-info';
$errors = $_SESSION['_school_settings_errors'] ?? [];
unset($_SESSION['_school_settings_active_tab'], $_SESSION['_school_settings_errors']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $section = (string) ($_POST['settings_section'] ?? '');
    $activeTab = (string) ($_POST['active_tab'] ?? 'school-info');
    $_SESSION['_school_settings_active_tab'] = $activeTab;

    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
    } else {
        $result = $controller->update($section, $_POST, $_FILES['school_logo'] ?? null, $currentUser);
        sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
        if (!empty($result['errors'])) {
            $_SESSION['_school_settings_errors'] = $result['errors'];
        }
    }

    header('Location: school-settings.php#' . $activeTab);
    exit;
}

$pageData = $controller->index();
$settings = $pageData['settings'];
$sessions = $pageData['sessions'];
$terms = $pageData['terms'];

$currentSessionId = (int) ($settings['academic.current_session_id'] ?? 0);
$currentTermId = (int) ($settings['academic.current_term_id'] ?? 0);
$currentSessionName = 'Not Set';
$currentTermName = 'Not Set';
foreach ($sessions as $session) { if ((int) $session['id'] === $currentSessionId) { $currentSessionName = (string) $session['name']; break; } }
foreach ($terms as $term) { if ((int) $term['id'] === $currentTermId) { $currentTermName = (string) $term['name']; break; } }

$schoolSettings = [
    'school_name' => $settings['school.name'] ?? '',
    'school_motto' => $settings['school.motto'] ?? '',
    'school_logo' => $controller->logoUrl((string) ($settings['school.logo'] ?? '')),
    'school_address' => $settings['school.address'] ?? '',
    'phone' => $settings['school.phone'] ?? '',
    'email' => $settings['school.email'] ?? '',
    'website' => $settings['school.website'] ?? '',
    'school_type' => $settings['school.type'] ?? '',
    'principal_name' => $settings['school.principal_name'] ?? '',
    'pass_mark' => $settings['result.pass_mark'] ?? 50,
    'grading_system' => $settings['result.grading_system'] ?? 'A-F Grading Scale',
    'opening_time' => $settings['timetable.opening_time'] ?? '08:00',
    'closing_time' => $settings['timetable.closing_time'] ?? '15:00',
    'attendance_start_time' => $settings['attendance.start_time'] ?? '08:05',
    'late_arrival_threshold' => $settings['attendance.late_arrival_threshold'] ?? '08:30',
    'attendance_grace_period' => $settings['attendance.grace_period_minutes'] ?? 10,
];
$settingCategories = [
    ['id' => 'school-info', 'section' => 'school_information', 'label' => 'School Information', 'icon' => 'fa-school'],
    ['id' => 'academic-settings', 'section' => 'academic_settings', 'label' => 'Academic Settings', 'icon' => 'fa-graduation-cap'],
    ['id' => 'attendance-settings', 'section' => 'attendance_settings', 'label' => 'Attendance Settings', 'icon' => 'fa-clipboard-check'],
    ['id' => 'cbt-settings', 'section' => 'cbt_settings', 'label' => 'CBT Settings', 'icon' => 'fa-computer'],
];
$cards = [
    ['title' => 'Settings Groups', 'value' => count($settingCategories), 'description' => 'Configured categories', 'icon' => 'fa-layer-group', 'color' => 'success'],
    ['title' => 'Current Session', 'value' => $currentSessionName, 'description' => 'Active academic session', 'icon' => 'fa-calendar-days', 'color' => 'blue'],
    ['title' => 'Current Term', 'value' => $currentTermName, 'description' => 'Active academic term', 'icon' => 'fa-calendar-week', 'color' => 'warning'],
    ['title' => 'Audit Logs', 'value' => 'On', 'description' => 'Every change is recorded', 'icon' => 'fa-shield-halved', 'color' => 'success'],
];
function sms_school_settings_cards(array $cards): void { echo '<section class="row g-3 mb-4">'; foreach ($cards as $card) { echo '<div class="col-sm-6 col-xl-3">'; sms_render_component('statistics-card', $card); echo '</div>'; } echo '</section>'; }
function sms_checked(mixed $value): string { return $value ? 'checked' : ''; }
function sms_selected(mixed $a, mixed $b): string { return (string) $a === (string) $b ? 'selected' : ''; }
function sms_error(array $errors, string $field): void { if (isset($errors[$field])) { echo '<small class="text-danger fw-bold">' . sms_e($errors[$field]) . '</small>'; } }
$flashMessages = sms_flash();
?>
<?php require_once('includes/header.php'); ?>
<?php require_once('includes/attendance-module-styles.php'); ?>
<style>
.admin-school-settings .settings-shell{display:grid;grid-template-columns:280px minmax(0,1fr);gap:22px}.admin-school-settings .settings-nav{position:sticky;top:90px;align-self:start}.admin-school-settings .settings-nav button{width:100%;border:0;border-radius:16px;background:#f8fafc;color:var(--at-ink);padding:14px 15px;margin-bottom:10px;display:flex;align-items:center;gap:10px;font-weight:900;text-align:left;transition:.18s}.admin-school-settings .settings-nav button.active,.admin-school-settings .settings-nav button:hover{background:var(--at-primary);color:#fff;box-shadow:0 12px 24px rgba(15,118,110,.2)}.admin-school-settings .settings-panel{display:none}.admin-school-settings .settings-panel.active{display:block}.admin-school-settings .logo-preview{width:106px;height:106px;border-radius:24px;border:1px solid var(--at-border);background:#f8fafc;display:flex;align-items:center;justify-content:center;padding:10px}.admin-school-settings .logo-preview img{max-width:86px;max-height:86px;object-fit:contain}.admin-school-settings .switch-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px;border-radius:16px;background:#f8fafc;font-weight:900}.admin-school-settings .mobile-settings-select{display:none}.admin-school-settings .saving-indicator{display:none;font-weight:900;color:var(--at-primary)}.admin-school-settings form.is-saving .saving-indicator{display:inline-flex;align-items:center;gap:8px}@media(max-width:991.98px){.admin-school-settings .settings-shell{grid-template-columns:1fr}.admin-school-settings .settings-nav{position:static;display:none}.admin-school-settings .mobile-settings-select{display:block;margin-bottom:16px}}
</style>
<div class="admin-attendance-module admin-school-settings">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> School Settings</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-gears"></i> School Settings</span><h3 class="mt-3 mb-2">School Settings</h3><p class="text-muted mb-0">Central configuration area for global school information, academic rules, attendance preferences, and CBT defaults.</p></div><button class="module-btn btn-primary-soft" type="button" id="saveActiveSection"><i class="fa-solid fa-floppy-disk"></i> Save Active Section</button></div></section>
    <?php sms_school_settings_cards($cards); ?>
    <div class="settings-shell">
        <aside class="module-card settings-nav" aria-label="School settings categories"><?php foreach ($settingCategories as $index => $category): ?><button class="settings-tab <?php echo $category['id'] === $activeTab ? 'active' : ''; ?>" data-target="<?php echo sms_e($category['id']); ?>" type="button"><i class="fa-solid <?php echo sms_e($category['icon']); ?>"></i> <?php echo sms_e($category['label']); ?></button><?php endforeach; ?></aside>
        <main>
            <select class="form-select mobile-settings-select" id="mobileSettingsSelect"><?php foreach ($settingCategories as $category): ?><option value="<?php echo sms_e($category['id']); ?>" <?php echo sms_selected($category['id'], $activeTab); ?>><?php echo sms_e($category['label']); ?></option><?php endforeach; ?></select>

            <section class="module-card settings-panel <?php echo $activeTab === 'school-info' ? 'active' : ''; ?>" id="school-info"><div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3"><div><h4>School Information</h4><p class="text-muted mb-0">Update official school identity, contact details, logo, and principal information.</p></div><div class="logo-preview"><img src="<?php echo sms_e($schoolSettings['school_logo']); ?>" alt="School Logo" id="schoolLogoPreview"></div></div><form id="schoolInfoForm" method="post" data-settings-form="School Information" enctype="multipart/form-data"><input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>"><input type="hidden" name="settings_section" value="school_information"><input type="hidden" name="active_tab" value="school-info"><div class="form-grid"><div><label>School Name</label><input class="form-control" name="school_name" value="<?php echo sms_e($schoolSettings['school_name']); ?>" required><?php sms_error($errors, 'school_name'); ?></div><div><label>School Motto</label><input class="form-control" name="school_motto" value="<?php echo sms_e($schoolSettings['school_motto']); ?>"></div><div class="full"><label>School Logo</label><input class="form-control" type="file" name="school_logo" id="schoolLogoInput" accept="image/png,image/jpeg,image/gif,image/webp"><small class="text-muted fw-bold">Accepted: JPG, PNG, GIF, WEBP. Maximum size: 2MB.</small><?php sms_error($errors, 'school_logo'); ?></div><div class="full"><label>School Address</label><textarea class="form-control" name="school_address"><?php echo sms_e($schoolSettings['school_address']); ?></textarea></div><div><label>Phone Number</label><input class="form-control" name="phone" value="<?php echo sms_e($schoolSettings['phone']); ?>"></div><div><label>Email Address</label><input class="form-control" type="email" name="email" value="<?php echo sms_e($schoolSettings['email']); ?>"><?php sms_error($errors, 'email'); ?></div><div><label>Website</label><input class="form-control" type="url" name="website" value="<?php echo sms_e($schoolSettings['website']); ?>"><?php sms_error($errors, 'website'); ?></div><div><label>School Type</label><input class="form-control" name="school_type" value="<?php echo sms_e($schoolSettings['school_type']); ?>"></div><div><label>Principal Name</label><input class="form-control" name="principal_name" value="<?php echo sms_e($schoolSettings['principal_name']); ?>"></div></div><div class="d-flex flex-wrap gap-2 mt-3"><span class="saving-indicator"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button></div></form></section>

            <section class="module-card settings-panel <?php echo $activeTab === 'academic-settings' ? 'active' : ''; ?>" id="academic-settings"><h4>Academic Settings</h4><p class="text-muted">Configure active session, term, pass mark, grading, results, and promotion behavior.</p><form method="post" data-settings-form="Academic Settings"><input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>"><input type="hidden" name="settings_section" value="academic_settings"><input type="hidden" name="active_tab" value="academic-settings"><div class="form-grid"><div><label>Current Academic Session</label><select class="form-select" name="current_session_id" id="currentSessionSelect"><?php foreach ($sessions as $session): ?><option value="<?php echo (int) $session['id']; ?>" <?php echo sms_selected($session['id'], $currentSessionId); ?>><?php echo sms_e($session['name']); ?> (<?php echo sms_e($session['status']); ?>)</option><?php endforeach; ?></select><?php sms_error($errors, 'current_session_id'); ?></div><div><label>Current Term</label><select class="form-select" name="current_term_id" id="currentTermSelect"><?php foreach ($terms as $term): ?><option value="<?php echo (int) $term['id']; ?>" data-session="<?php echo (int) $term['session_id']; ?>" <?php echo sms_selected($term['id'], $currentTermId); ?>><?php echo sms_e($term['name']); ?> (<?php echo sms_e($term['status']); ?>)</option><?php endforeach; ?></select><?php sms_error($errors, 'current_term_id'); ?></div><div><label>Default Pass Mark</label><input class="form-control" type="number" min="0" max="100" step="0.01" name="pass_mark" value="<?php echo sms_e($schoolSettings['pass_mark']); ?>"><?php sms_error($errors, 'pass_mark'); ?></div><div><label>Grading System</label><select class="form-select" name="grading_system"><?php foreach (['A-F Grading Scale','Percentage Based','Point Based'] as $option): ?><option <?php echo sms_selected($option, $schoolSettings['grading_system']); ?>><?php echo sms_e($option); ?></option><?php endforeach; ?></select></div><div><label>Position Calculation</label><select class="form-select" name="enable_position_calculation"><option value="1" <?php echo sms_selected((int) ($settings['result.enable_position_calculation'] ?? true), 1); ?>>Enabled</option><option value="0" <?php echo sms_selected((int) ($settings['result.enable_position_calculation'] ?? true), 0); ?>>Disabled</option></select></div><div><label>Auto Promote Students</label><select class="form-select" name="auto_promote_students"><option value="1" <?php echo sms_selected((int) ($settings['academic.auto_promote_students'] ?? false), 1); ?>>Enabled</option><option value="0" <?php echo sms_selected((int) ($settings['academic.auto_promote_students'] ?? false), 0); ?>>Disabled</option></select></div><div><label>Auto Publish Results</label><select class="form-select" name="auto_publish_results"><option value="1" <?php echo sms_selected((int) ($settings['result.auto_publish_results'] ?? false), 1); ?>>Enabled</option><option value="0" <?php echo sms_selected((int) ($settings['result.auto_publish_results'] ?? false), 0); ?>>Disabled</option></select></div></div><div class="d-flex flex-wrap gap-2 mt-3"><span class="saving-indicator"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div></form></section>

            <section class="module-card settings-panel <?php echo $activeTab === 'attendance-settings' ? 'active' : ''; ?>" id="attendance-settings"><h4>Attendance Settings</h4><p class="text-muted">Configure school hours, attendance time rules, grace periods, and enabled attendance modules.</p><form method="post" data-settings-form="Attendance Settings"><input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>"><input type="hidden" name="settings_section" value="attendance_settings"><input type="hidden" name="active_tab" value="attendance-settings"><div class="form-grid"><div><label>School Opening Time</label><input class="form-control" type="time" name="opening_time" value="<?php echo sms_e($schoolSettings['opening_time']); ?>"><?php sms_error($errors, 'opening_time'); ?></div><div><label>School Closing Time</label><input class="form-control" type="time" name="closing_time" value="<?php echo sms_e($schoolSettings['closing_time']); ?>"><?php sms_error($errors, 'closing_time'); ?></div><div><label>Attendance Start Time</label><input class="form-control" type="time" name="attendance_start_time" value="<?php echo sms_e($schoolSettings['attendance_start_time']); ?>"><?php sms_error($errors, 'attendance_start_time'); ?></div><div><label>Late Arrival Threshold</label><input class="form-control" type="time" name="late_arrival_threshold" value="<?php echo sms_e($schoolSettings['late_arrival_threshold']); ?>"><?php sms_error($errors, 'late_arrival_threshold'); ?></div><div><label>Attendance Grace Period</label><input class="form-control" type="number" min="0" max="120" name="attendance_grace_period" value="<?php echo sms_e($schoolSettings['attendance_grace_period']); ?>"><?php sms_error($errors, 'attendance_grace_period'); ?></div></div><div class="row g-3 mt-1"><div class="col-md-6"><label class="switch-row"><span>Enable Teacher Attendance</span><input class="form-check-input" name="enable_teacher_attendance" value="1" type="checkbox" <?php echo sms_checked($settings['attendance.enable_teacher_attendance'] ?? true); ?>></label></div><div class="col-md-6"><label class="switch-row"><span>Enable Student Attendance</span><input class="form-check-input" name="enable_student_attendance" value="1" type="checkbox" <?php echo sms_checked($settings['attendance.enable_student_attendance'] ?? true); ?>></label></div></div><div class="d-flex flex-wrap gap-2 mt-3"><span class="saving-indicator"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div></form></section>

            <section class="module-card settings-panel <?php echo $activeTab === 'cbt-settings' ? 'active' : ''; ?>" id="cbt-settings"><h4>CBT Settings</h4><p class="text-muted">Configure default examination duration, attempts, randomization, submission, result display, and review behavior.</p><form method="post" data-settings-form="CBT Settings"><input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>"><input type="hidden" name="settings_section" value="cbt_settings"><input type="hidden" name="active_tab" value="cbt-settings"><div class="form-grid"><div><label>Default Exam Duration</label><input class="form-control" type="number" min="1" max="600" name="default_duration_minutes" value="<?php echo sms_e($settings['cbt.default_duration_minutes'] ?? 30); ?>"><?php sms_error($errors, 'default_duration_minutes'); ?></div><div><label>Default Pass Mark</label><input class="form-control" type="number" min="0" max="100" step="0.01" name="default_pass_mark" value="<?php echo sms_e($settings['cbt.default_pass_mark'] ?? 50); ?>"><?php sms_error($errors, 'default_pass_mark'); ?></div><div><label>Maximum Attempts</label><input class="form-control" type="number" min="1" max="20" name="maximum_attempts" value="<?php echo sms_e($settings['cbt.maximum_attempts'] ?? 1); ?>"><?php sms_error($errors, 'maximum_attempts'); ?></div></div><div class="row g-3 mt-1"><div class="col-md-6"><label class="switch-row"><span>Randomize Questions</span><input class="form-check-input" name="randomize_questions" value="1" type="checkbox" <?php echo sms_checked($settings['cbt.randomize_questions'] ?? true); ?>></label></div><div class="col-md-6"><label class="switch-row"><span>Randomize Answers</span><input class="form-check-input" name="randomize_answers" value="1" type="checkbox" <?php echo sms_checked($settings['cbt.randomize_answers'] ?? true); ?>></label></div><div class="col-md-6"><label class="switch-row"><span>Auto Submit</span><input class="form-check-input" name="auto_submit" value="1" type="checkbox" <?php echo sms_checked($settings['cbt.auto_submit'] ?? true); ?>></label></div><div class="col-md-6"><label class="switch-row"><span>Show Results Immediately</span><input class="form-check-input" name="show_results_immediately" value="1" type="checkbox" <?php echo sms_checked($settings['cbt.show_results_immediately'] ?? false); ?>></label></div><div class="col-md-6"><label class="switch-row"><span>Allow Review After Exam</span><input class="form-check-input" name="allow_review_after_exam" value="1" type="checkbox" <?php echo sms_checked($settings['cbt.allow_review_after_exam'] ?? true); ?>></label></div></div><div class="d-flex flex-wrap gap-2 mt-3"><span class="saving-indicator"><i class="fa-solid fa-spinner fa-spin"></i> Saving...</span><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button><button class="module-btn btn-muted-soft" type="reset">Reset</button></div></form></section>
        </main>
    </div>
</div></div></div>
<script>
(function(){
    function activate(target){document.querySelectorAll('.settings-tab').forEach(function(tab){tab.classList.toggle('active',tab.dataset.target===target);});document.querySelectorAll('.settings-panel').forEach(function(panel){panel.classList.toggle('active',panel.id===target);});document.querySelectorAll('input[name="active_tab"]').forEach(function(input){input.value=target;});var mobile=document.getElementById('mobileSettingsSelect');if(mobile){mobile.value=target;}history.replaceState(null,'','#'+target);}
    document.querySelectorAll('.settings-tab').forEach(function(tab){tab.addEventListener('click',function(){activate(tab.dataset.target);});});
    var mobile=document.getElementById('mobileSettingsSelect');if(mobile){mobile.addEventListener('change',function(){activate(mobile.value);});}
    var initial=location.hash?location.hash.substring(1):'<?php echo sms_e($activeTab); ?>'; if(document.getElementById(initial)){activate(initial);}
    var saveActive=document.getElementById('saveActiveSection'); if(saveActive){saveActive.addEventListener('click',function(){var panel=document.querySelector('.settings-panel.active');var form=panel?panel.querySelector('form'):null;if(form){form.requestSubmit();}});}
    document.querySelectorAll('[data-settings-form]').forEach(function(form){form.addEventListener('submit',function(){form.classList.add('is-saving');form.querySelectorAll('button[type="submit"]').forEach(function(btn){btn.disabled=true;});});});
    var logoInput=document.getElementById('schoolLogoInput'); if(logoInput){logoInput.addEventListener('change',function(){var file=this.files&&this.files[0]; if(file){document.getElementById('schoolLogoPreview').src=URL.createObjectURL(file);}});}
    var sessionSelect=document.getElementById('currentSessionSelect'); var termSelect=document.getElementById('currentTermSelect'); function filterTerms(){if(!sessionSelect||!termSelect){return;} var selected=sessionSelect.value; var firstVisible=null; termSelect.querySelectorAll('option').forEach(function(option){var show=option.dataset.session===selected; option.hidden=!show; if(show&&!firstVisible){firstVisible=option;}}); if(termSelect.selectedOptions[0]&&termSelect.selectedOptions[0].hidden&&firstVisible){termSelect.value=firstVisible.value;}} if(sessionSelect){sessionSelect.addEventListener('change',filterTerms); filterTerms();}
})();
</script>
<?php require_once('includes/footer.php'); ?>
