<?php
/**
 * Shared dashboard header layout.
 *
 * Module include files set $smsModule, $smsNavItems, and optional
 * $smsProfileSummary before requiring this file.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../assets.php';

$smsModule = $smsModule ?? 'student';
$smsAssetPrefix = $smsAssetPrefix ?? '../';
$smsCurrentPage = sms_current_page();
$smsNavItems = $smsNavItems ?? [];
$smsProfileSummary = $smsProfileSummary ?? null;

sms_require_auth($smsModule);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="School Management System dashboard">
    <meta name="keywords" content="school management system, student portal, teacher portal, accountant dashboard">
    <meta name="author" content="Brighter Future Standard School">
    <meta name="robots" content="index, follow">
    <title><?php echo sms_e(sms_config('app_name', 'School Management System')); ?></title>
    <?php sms_render_head_assets($smsAssetPrefix); ?>
</head>
<body class="student-dashboard-layout <?php echo sms_e($smsModule); ?>-dashboard-layout">
    <div class="main-wrapper">
        <input type="checkbox" id="studentSidebarControl" class="student-sidebar-control" aria-hidden="true">

        <?php require __DIR__ . '/navbar.php'; ?>
        <?php require __DIR__ . '/sidebar.php'; ?>

        <label class="student-sidebar-backdrop" for="studentSidebarControl" data-student-sidebar-close aria-label="Close dashboard navigation"></label>

        <div class="content">
            <div class="container">
