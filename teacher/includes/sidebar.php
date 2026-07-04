<?php
/**
 * Teacher module navigation.
 */

$smsModule = 'teacher';
$smsDashboardLink = 'dashboard.php';
$smsProfileSummary = null;
$smsNavItems = [
    ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'fa-solid fa-house', 'pages' => ['dashboard.php']],
    ['label' => 'My Profile', 'href' => 'profile.php', 'icon' => 'fa-solid fa-user-graduate', 'pages' => ['profile.php', 'edit-profile.php']],
    ['label' => 'Attendance Management', 'href' => 'attendance.php', 'icon' => 'fa-solid fa-calendar-check', 'pages' => ['attendance.php']],
    ['label' => 'Results Management', 'href' => 'result-management.php', 'icon' => 'fa-solid fa-chart-line', 'pages' => ['result-management.php', 'check-result.php', 'report-card.html']],
    ['label' => 'CBT Management', 'href' => 'cbt-management.php', 'icon' => 'fa-solid fa-laptop-code', 'pages' => ['cbt-management.php']],
    ['label' => 'Timetable', 'href' => 'timetable.php', 'icon' => 'fa-solid fa-calendar-days', 'pages' => ['timetable.php']],
    ['label' => 'Change Password', 'href' => 'change-password.php', 'icon' => 'fa-solid fa-key', 'pages' => ['change-password.php']],
];
