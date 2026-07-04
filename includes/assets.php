<?php
/**
 * Centralized asset loader.
 *
 * Role layouts call sms_render_head_assets() and sms_render_footer_assets()
 * instead of repeating CSS and JavaScript includes on every module header.
 */

require_once __DIR__ . '/helpers/functions.php';

function sms_asset_version(string $relativePath, string $prefix = '../'): string
{
    $absolutePath = realpath(__DIR__ . '/../' . ltrim($relativePath, '/'));
    $version = $absolutePath && file_exists($absolutePath) ? filemtime($absolutePath) : time();

    return sms_asset($relativePath, $prefix) . '?v=' . $version;
}

function sms_render_head_assets(string $prefix = '../'): void
{
    ?>
    <link rel="shortcut icon" href="<?php echo sms_asset('assets/img/favicon.png', $prefix); ?>">
    <link rel="apple-touch-icon" href="<?php echo sms_asset('assets/img/apple-icon.png', $prefix); ?>">
    <script src="<?php echo sms_asset('assets/js/theme-script.js', $prefix); ?>" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
    <link rel="stylesheet" href="<?php echo sms_asset('assets/css/bootstrap.min.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset('assets/plugins/fontawesome/css/fontawesome.min.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset('assets/plugins/fontawesome/css/all.min.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset_version('assets/css/feather.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset_version('assets/css/iconsax.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset_version('assets/css/style.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset_version('assets/css/student-sidebar.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset_version('assets/css/student-quiz.css', $prefix); ?>">
    <link rel="stylesheet" href="<?php echo sms_asset_version('assets/css/student-portal.css', $prefix); ?>">
    <?php
}

function sms_render_footer_assets(string $prefix = '../'): void
{
    ?>
    <script src="<?php echo sms_asset('assets/js/jquery-3.7.1.min.js', $prefix); ?>" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
    <script src="<?php echo sms_asset('assets/js/bootstrap.bundle.min.js', $prefix); ?>" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
    <script src="<?php echo sms_asset('assets/plugins/theia-sticky-sidebar/ResizeSensor.js', $prefix); ?>" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
    <script src="<?php echo sms_asset('assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js', $prefix); ?>" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
    <script src="<?php echo sms_asset('assets/js/script.js', $prefix); ?>" type="d220a6bb4a7797d6ac44761a-text/javascript"></script>
    <script src="<?php echo sms_asset('assets/js/rocket-loader.min.js', $prefix); ?>" data-cf-settings="d220a6bb4a7797d6ac44761a-|49" defer></script>
    <?php
}
