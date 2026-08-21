<?php
/**
 * Admin module navigation.
 *
 * Navigation items are consumed by the shared sidebar renderer. Related
 * management workflows use children so admin navigation stays organized
 * without duplicating sidebar markup across pages.
 */

$smsModule = 'admin';
$smsDashboardLink = 'dashboard.php';
$smsProfileSummary = null;
$smsNavItems = [
    ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'fa-solid fa-house', 'pages' => ['dashboard.php']],
    [
        'label' => 'Student Management',
        'href' => 'student-list.php',
        'icon' => 'fa-solid fa-user-graduate',
        'pages' => ['student-list.php', 'add-student.php', 'bulk-student-upload.php', 'promote-students.php', 'student-reports.php'],
        'children' => [
            ['label' => 'All Students', 'href' => 'student-list.php', 'icon' => 'fa-solid fa-users', 'pages' => ['student-list.php']],
            ['label' => 'Add Student', 'href' => 'add-student.php', 'icon' => 'fa-solid fa-user-plus', 'pages' => ['add-student.php']],
            ['label' => 'Bulk Upload', 'href' => 'bulk-student-upload.php', 'icon' => 'fa-solid fa-file-arrow-up', 'pages' => ['bulk-student-upload.php']],
            ['label' => 'Promote Students', 'href' => 'promote-students.php', 'icon' => 'fa-solid fa-arrow-up-right-dots', 'pages' => ['promote-students.php']],
            ['label' => 'Student Reports', 'href' => 'student-reports.php', 'icon' => 'fa-solid fa-chart-pie', 'pages' => ['student-reports.php']],
        ],
    ],
    ['label' => 'Academic Management', 'href' => 'academic-management.php', 'icon' => 'fa-solid fa-school-flag', 'pages' => ['academic-management.php']],
    [
        'label' => 'Attendance Management',
        'href' => 'attendance-records.php',
        'icon' => 'fa-solid fa-clipboard-check',
        'pages' => ['attendance-records.php', 'attendance-reports.php', 'attendance-analytics.php'],
        'children' => [
            ['label' => 'Attendance Records', 'href' => 'attendance-records.php', 'icon' => 'fa-solid fa-clipboard-list', 'pages' => ['attendance-records.php']],
            ['label' => 'Attendance Reports', 'href' => 'attendance-reports.php', 'icon' => 'fa-solid fa-file-signature', 'pages' => ['attendance-reports.php']],
            ['label' => 'Attendance Analytics', 'href' => 'attendance-analytics.php', 'icon' => 'fa-solid fa-chart-line', 'pages' => ['attendance-analytics.php']],
        ],
    ],
    [
        'label' => 'Result Management',
        'href' => 'results.php',
        'icon' => 'fa-solid fa-square-poll-vertical',
        'pages' => ['results.php', 'broadsheet.php', 'score-entry.php', 'report-cards.php', 'result-settings.php'],
        'children' => [
            ['label' => 'Results', 'href' => 'results.php', 'icon' => 'fa-solid fa-square-poll-vertical', 'pages' => ['results.php']],
            ['label' => 'Broadsheet', 'href' => 'broadsheet.php', 'icon' => 'fa-solid fa-table-list', 'pages' => ['broadsheet.php']],
            ['label' => 'Score Entry', 'href' => 'score-entry.php', 'icon' => 'fa-solid fa-pen-to-square', 'pages' => ['score-entry.php']],
            ['label' => 'Report Cards', 'href' => 'report-cards.php', 'icon' => 'fa-solid fa-file-lines', 'pages' => ['report-cards.php']],
            ['label' => 'Result Settings', 'href' => 'result-settings.php', 'icon' => 'fa-solid fa-gears', 'pages' => ['result-settings.php']],
        ],
    ],
    [
        'label' => 'Timetable Management',
        'href' => 'timetables.php',
        'icon' => 'fa-solid fa-calendar-days',
        'pages' => ['timetables.php', 'timetable-settings.php'],
        'children' => [
            ['label' => 'Timetables', 'href' => 'timetables.php', 'icon' => 'fa-solid fa-calendar-days', 'pages' => ['timetables.php']],
            ['label' => 'Timetable Settings', 'href' => 'timetable-settings.php', 'icon' => 'fa-solid fa-gears', 'pages' => ['timetable-settings.php']],
        ],
    ],
    [
        'label' => 'CBT Management',
        'href' => 'cbt-dashboard.php',
        'icon' => 'fa-solid fa-laptop-code',
        'pages' => ['cbt-dashboard.php', 'cbt-exams.php', 'cbt-questions.php', 'cbt-attempts-results.php', 'cbt-settings.php'],
        'children' => [
            ['label' => 'Dashboard', 'href' => 'cbt-dashboard.php', 'icon' => 'fa-solid fa-gauge-high', 'pages' => ['cbt-dashboard.php']],
            ['label' => 'Exams', 'href' => 'cbt-exams.php', 'icon' => 'fa-solid fa-laptop-file', 'pages' => ['cbt-exams.php']],
            ['label' => 'Questions', 'href' => 'cbt-questions.php', 'icon' => 'fa-solid fa-list-ol', 'pages' => ['cbt-questions.php']],
            ['label' => 'Attempts & Results', 'href' => 'cbt-attempts-results.php', 'icon' => 'fa-solid fa-square-poll-horizontal', 'pages' => ['cbt-attempts-results.php']],
            ['label' => 'CBT Settings', 'href' => 'cbt-settings.php', 'icon' => 'fa-solid fa-gears', 'pages' => ['cbt-settings.php']],
        ],
    ],
    [
        'label' => 'Teacher Management',
        'href' => 'teachers.php',
        'icon' => 'fa-solid fa-chalkboard-user',
        'pages' => ['teachers.php', 'add-teacher.php', 'edit-teacher.php', 'teacher-profile.php', 'teacher-reports.php'],
        'children' => [
            ['label' => 'All Teachers', 'href' => 'teachers.php', 'icon' => 'fa-solid fa-users-gear', 'pages' => ['teachers.php']],
            ['label' => 'Add Teacher', 'href' => 'add-teacher.php', 'icon' => 'fa-solid fa-user-plus', 'pages' => ['add-teacher.php']],
            ['label' => 'Teacher Profiles', 'href' => 'teacher-profile.php', 'icon' => 'fa-solid fa-id-card', 'pages' => ['teacher-profile.php', 'edit-teacher.php']],
            ['label' => 'Teacher Reports', 'href' => 'teacher-reports.php', 'icon' => 'fa-solid fa-chart-pie', 'pages' => ['teacher-reports.php']],
        ],
    ],
    [
        'label' => 'Accountant Management',
        'href' => 'accountants.php',
        'icon' => 'fa-solid fa-calculator',
        'pages' => ['accountants.php', 'add-accountant.php', 'edit-accountant.php', 'accountant-profile.php'],
        'children' => [
            ['label' => 'All Accountants', 'href' => 'accountants.php', 'icon' => 'fa-solid fa-users-gear', 'pages' => ['accountants.php']],
            ['label' => 'Add Accountant', 'href' => 'add-accountant.php', 'icon' => 'fa-solid fa-user-plus', 'pages' => ['add-accountant.php']],
        ],
    ],
    ['label' => 'School Settings', 'href' => 'school-settings.php', 'icon' => 'fa-solid fa-gears', 'pages' => ['school-settings.php']],
    ['label' => 'Audit Logs', 'href' => 'audit-logs.php', 'icon' => 'fa-solid fa-shield-halved', 'pages' => ['audit-logs.php']],
    ['label' => 'Settings', 'href' => 'profile.php', 'icon' => 'fa-solid fa-gear', 'pages' => ['profile.php']],
    ['label' => 'Logout', 'href' => 'logout.php', 'icon' => 'fa-solid fa-right-from-bracket', 'pages' => ['logout.php']],
];