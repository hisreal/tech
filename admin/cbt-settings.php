<?php require_once('includes/header.php'); ?>
<?php require_once('includes/cbt-data.php'); ?>
<?php require_once('includes/cbt-page-helper.php'); ?>
<?php require_once('includes/cbt-module-styles.php'); ?>
<?php
$cards = [
    ['title' => 'Pass Mark', 'value' => $cbtSettings['pass_mark'] . '%', 'description' => 'Default CBT pass mark', 'icon' => 'fa-check-circle', 'color' => 'success'],
    ['title' => 'Default Duration', 'value' => $cbtSettings['default_duration'] . ' mins', 'description' => 'Default exam length', 'icon' => 'fa-clock', 'color' => 'blue'],
    ['title' => 'Max Attempts', 'value' => $cbtSettings['maximum_attempts'], 'description' => 'Attempts per student', 'icon' => 'fa-rotate', 'color' => 'warning'],
    ['title' => 'Security Mode', 'value' => $cbtSettings['fullscreen_mode'] ? 'On' : 'Off', 'description' => 'Fullscreen enabled', 'icon' => 'fa-shield-halved', 'color' => 'success'],
];
?>
<div class="admin-cbt-module">
    <section class="module-hero"><div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> CBT Settings</div><div class="d-flex align-items-center justify-content-between flex-wrap gap-3"><div><span class="module-kicker"><i class="fa-solid fa-gears"></i> CBT Settings</span><h3 class="mt-3 mb-2">CBT Settings</h3><p class="text-muted mb-0">Configure CBT defaults, exam behavior, review rules, and security controls.</p></div><button class="module-btn btn-primary-soft" form="cbtSettingsForm" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button></div></section>
    <?php sms_cbt_render_cards($cards); ?>
    <form id="cbtSettingsForm" data-placeholder-form>
        <section class="module-card"><h4>General Settings</h4><div class="filter-grid"><div><label>Pass Mark</label><input class="form-control" type="number" value="<?php echo sms_e($cbtSettings['pass_mark']); ?>"></div><div><label>Default Duration</label><input class="form-control" type="number" value="<?php echo sms_e($cbtSettings['default_duration']); ?>"></div><div><label>Maximum Attempts</label><input class="form-control" type="number" value="<?php echo sms_e($cbtSettings['maximum_attempts']); ?>"></div></div></section>
        <section class="module-card"><h4>Exam Settings</h4><div class="filter-grid"><div><label>Randomize Questions</label><select class="form-select"><option selected>Enabled</option><option>Disabled</option></select></div><div><label>Randomize Answers</label><select class="form-select"><option selected>Enabled</option><option>Disabled</option></select></div><div><label>Auto Submit</label><select class="form-select"><option selected>Enabled</option><option>Disabled</option></select></div><div><label>Show Result Immediately</label><select class="form-select"><option selected>Yes</option><option>No</option></select></div><div><label>Allow Review</label><select class="form-select"><option selected>Yes</option><option>No</option></select></div></div></section>
        <section class="module-card"><h4>Security Settings</h4><div class="filter-grid"><div><label>Fullscreen Mode</label><select class="form-select"><option selected>Enabled</option><option>Disabled</option></select></div><div><label>Prevent Multiple Login</label><select class="form-select"><option selected>Enabled</option><option>Disabled</option></select></div><div><label>Auto Logout</label><select class="form-select"><option selected>Enabled</option><option>Disabled</option></select></div><div><label>Browser Restrictions</label><select class="form-select"><option>Placeholder Only</option><option>Enabled</option><option>Disabled</option></select></div><div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit">Save Changes</button><button class="module-btn btn-muted-soft" type="reset">Reset Defaults</button></div></div></section>
    </form>
</div></div></div>
<?php sms_cbt_render_common_script(); ?>
<?php require_once('includes/footer.php'); ?>