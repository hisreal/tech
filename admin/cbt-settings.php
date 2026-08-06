<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\CBTService;

$cbtService = new CBTService();
require_once('includes/header.php');
require_once('includes/cbt-page-helper.php');
require_once('includes/cbt-module-styles.php');

$flashMessages = sms_flash();
$errors = Session::errors();
$old = Session::oldAll();

$cbtSettings = $cbtService->generalSettings();

function sms_cbts_field(array $old, array $settings, string $key): string
{
    if (array_key_exists($key, $old)) {
        return sms_e($old[$key]);
    }

    return sms_e((string) ($settings[$key] ?? ''));
}

function sms_cbts_bool_select(array $old, array $settings, string $key, string $name, array $labels = ['Enabled', 'Disabled']): void
{
    $current = array_key_exists($name, $old) ? (bool) $old[$name] : (bool) ($settings[$key] ?? false);
    ?>
    <select class="form-select" name="<?php echo sms_e($name); ?>">
        <option value="1" <?php echo $current ? 'selected' : ''; ?>><?php echo sms_e($labels[0]); ?></option>
        <option value="0" <?php echo !$current ? 'selected' : ''; ?>><?php echo sms_e($labels[1]); ?></option>
    </select>
    <?php
}

$cards = [
    ['title' => 'Pass Mark', 'value' => $cbtSettings['pass_mark'] . '%', 'description' => 'Default CBT pass mark', 'icon' => 'fa-check-circle', 'color' => 'success'],
    ['title' => 'Default Duration', 'value' => $cbtSettings['default_duration'] . ' mins', 'description' => 'Default exam length', 'icon' => 'fa-clock', 'color' => 'blue'],
    ['title' => 'Max Attempts', 'value' => $cbtSettings['maximum_attempts'], 'description' => 'Attempts per student', 'icon' => 'fa-rotate', 'color' => 'warning'],
    ['title' => 'Security Mode', 'value' => $cbtSettings['fullscreen_mode'] ? 'On' : 'Off', 'description' => 'Fullscreen enabled', 'icon' => 'fa-shield-halved', 'color' => 'success'],
];
?>
<div class="admin-cbt-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> CBT Settings</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-gears"></i> CBT Settings</span>
                <h3 class="mt-3 mb-2">CBT Settings</h3>
                <p class="text-muted mb-0">Configure CBT defaults, exam behavior, review rules, and security controls.</p>
            </div>
            <button class="module-btn btn-primary-soft" form="cbtSettingsForm" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </div>
    </section>

    <?php sms_cbt_render_cards($cards); ?>

    <form id="cbtSettingsForm" method="post" action="cbt-settings-save.php">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <section class="module-card">
            <h4>General Settings</h4>
            <div class="filter-grid">
                <div><label>Pass Mark (%)</label><input class="form-control" type="number" min="0" max="100" name="pass_mark" value="<?php echo sms_cbts_field($old, $cbtSettings, 'pass_mark'); ?>"></div>
                <div><label>Default Duration (minutes)</label><input class="form-control" type="number" min="1" name="default_duration" value="<?php echo sms_cbts_field($old, $cbtSettings, 'default_duration'); ?>"></div>
                <div><label>Maximum Attempts</label><input class="form-control" type="number" min="1" name="maximum_attempts" value="<?php echo sms_cbts_field($old, $cbtSettings, 'maximum_attempts'); ?>"></div>
            </div>
        </section>
        <section class="module-card">
            <h4>Exam Settings</h4>
            <div class="filter-grid">
                <div><label>Randomize Questions</label><?php sms_cbts_bool_select($old, $cbtSettings, 'randomize_questions', 'randomize_questions'); ?></div>
                <div><label>Randomize Answers</label><?php sms_cbts_bool_select($old, $cbtSettings, 'randomize_answers', 'randomize_answers'); ?></div>
                <div><label>Auto Submit on Timeout</label><?php sms_cbts_bool_select($old, $cbtSettings, 'auto_submit', 'auto_submit'); ?></div>
                <div><label>Show Result Immediately</label><?php sms_cbts_bool_select($old, $cbtSettings, 'show_result_immediately', 'show_result_immediately', ['Yes', 'No']); ?></div>
                <div><label>Allow Review After Submission</label><?php sms_cbts_bool_select($old, $cbtSettings, 'allow_review', 'allow_review', ['Yes', 'No']); ?></div>
            </div>
        </section>
        <section class="module-card">
            <h4>Security Settings</h4>
            <div class="filter-grid">
                <div><label>Fullscreen Mode</label><?php sms_cbts_bool_select($old, $cbtSettings, 'fullscreen_mode', 'fullscreen_mode'); ?></div>
                <div><label>Prevent Multiple Login</label><?php sms_cbts_bool_select($old, $cbtSettings, 'prevent_multiple_login', 'prevent_multiple_login'); ?></div>
                <div><label>Auto Logout on Blur</label><?php sms_cbts_bool_select($old, $cbtSettings, 'auto_logout', 'auto_logout'); ?></div>
                <div><label>Browser Restrictions</label><?php sms_cbts_bool_select($old, $cbtSettings, 'browser_restrictions', 'browser_restrictions'); ?></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit">Save Changes</button><a class="module-btn btn-muted-soft" href="cbt-settings.php">Reset</a></div>
            </div>
        </section>
    </form>
</div></div></div>
<?php require_once('includes/footer.php'); ?>
