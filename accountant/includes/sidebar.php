<?php
/**
 * Accountant module navigation.
 */

$smsModule = 'accountant';
$smsDashboardLink = 'dashboard.php';
$smsProfileSummary = null;
$smsNavItems = [
    ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'fa-solid fa-house', 'pages' => ['dashboard.php']],
    ['label' => 'Student Fees', 'href' => 'student-fees.php', 'icon' => 'fa-solid fa-money-check-dollar', 'pages' => ['student-fees.php']],
    ['label' => 'Outstanding Fees', 'href' => 'outstanding-fees.php', 'icon' => 'fa-solid fa-scale-unbalanced', 'pages' => ['outstanding-fees.php']],
    ['label' => 'Fee Structure', 'href' => 'fee-structure.php', 'icon' => 'fa-solid fa-sliders', 'pages' => ['fee-structure.php']],
    ['label' => 'Fee Collection', 'href' => 'fee-collection.php', 'icon' => 'fa-solid fa-cash-register', 'pages' => ['fee-collection.php']],
    ['label' => 'Receipt Management', 'href' => 'receipt-management.php', 'icon' => 'fa-solid fa-receipt', 'pages' => ['receipt-management.php']],
    ['label' => 'Payment History', 'href' => 'payment-history.php', 'icon' => 'fa-solid fa-clock-rotate-left', 'pages' => ['payment-history.php']],
    ['label' => 'Expense Management', 'href' => 'expense-management.php', 'icon' => 'fa-solid fa-file-invoice-dollar', 'pages' => ['expense-management.php']],
    ['label' => 'Financial Reports', 'href' => 'financial-reports.php', 'icon' => 'fa-solid fa-chart-pie', 'pages' => ['financial-reports.php']],
    ['label' => 'Salary Structure', 'href' => 'salary-structure.php', 'icon' => 'fa-solid fa-sack-dollar', 'pages' => ['salary-structure.php']],
    ['label' => 'Allowances', 'href' => 'allowances.php', 'icon' => 'fa-solid fa-hand-holding-dollar', 'pages' => ['allowances.php']],
    ['label' => 'Deductions', 'href' => 'deductions.php', 'icon' => 'fa-solid fa-hand-holding-hand', 'pages' => ['deductions.php']],
    ['label' => 'Payslips', 'href' => 'payslips.php', 'icon' => 'fa-solid fa-file-invoice', 'pages' => ['payslips.php']],
    ['label' => 'Payroll Payment History', 'href' => 'payroll-payment-history.php', 'icon' => 'fa-solid fa-clock-rotate-left', 'pages' => ['payroll-payment-history.php']],
    ['label' => 'Payroll Reports', 'href' => 'payroll-reports.php', 'icon' => 'fa-solid fa-chart-column', 'pages' => ['payroll-reports.php']],
    ['label' => 'My Profile', 'href' => 'profile.php', 'icon' => 'fa-solid fa-user-graduate', 'pages' => ['profile.php']],
    ['label' => 'Settings', 'href' => 'settings.php', 'icon' => 'fa-solid fa-gear', 'pages' => ['settings.php']],
    ['label' => 'Logout', 'href' => 'logout.php', 'icon' => 'fa-solid fa-right-from-bracket', 'pages' => ['logout.php']],
];
