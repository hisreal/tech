<?php
/**
 * Shared mobile top navigation.
 */
?>
<header class="student-mobile-header">
    <a class="student-mobile-brand" href="<?php echo sms_e($smsDashboardLink ?? 'dashboard.php'); ?>" aria-label="<?php echo sms_e(ucfirst($smsModule)); ?> dashboard">
        <img src="<?php echo sms_asset(sms_config('school_logo', 'assets/img/logo/school-logo.png'), $smsAssetPrefix); ?>" alt="Logo">
    </a>
    <label class="student-sidebar-toggle" for="studentSidebarControl" role="button" tabindex="0" aria-label="Open dashboard navigation" aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
    </label>
</header>
