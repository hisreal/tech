<?php
/**
 * Student module navigation and database-backed profile summary.
 */

use App\Services\StudentSidebarService;

$smsModule = 'student';
$smsDashboardLink = 'index.php';
$smsProfileSummary = (new StudentSidebarService())->summary(sms_current_user());
$smsNavItems = [
    ['label' => 'Dashboard', 'href' => 'index.php', 'icon' => 'fa-solid fa-house', 'pages' => ['index.php']],
    ['label' => 'My Profile', 'href' => 'profile.php', 'icon' => 'fa-solid fa-user-graduate', 'pages' => ['profile.php', 'edit-profile.php']],
    ['label' => 'Attendance', 'href' => 'attendance.php', 'icon' => 'fa-solid fa-calendar-check', 'pages' => ['attendance.php']],
    ['label' => 'Results', 'href' => 'check-result.php', 'icon' => 'fa-solid fa-chart-line', 'pages' => ['check-result.php', 'report-card.html']],
    ['label' => 'CBT / Computer Based Test', 'href' => 'quiz.php', 'icon' => 'fa-solid fa-laptop-code', 'pages' => ['quiz.php', 'quiz-question.php']],
    ['label' => 'Timetable', 'href' => 'timetable.php', 'icon' => 'fa-solid fa-calendar-days', 'pages' => ['timetable.php']],
    ['label' => 'Change Password', 'href' => 'change-password.php', 'icon' => 'fa-solid fa-key', 'pages' => ['change-password.php']],
];
