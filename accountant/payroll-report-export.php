<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Helpers\ReportExporter;
use App\Services\PayrollService;

$payrollService = new PayrollService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$filterRun = (int) ($_GET['payroll_run_id'] ?? 0) ?: null;
$summary = $payrollService->reportsSummary($filterRun);

$headers = ['Department', 'Total Net Pay'];
$rows = [];
foreach ($summary['by_department'] as $dept) {
    $rows[] = [$dept['department'], $dept['total']];
}

$filename = 'payroll_report_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Payroll Report', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Payroll Report', 'Cost by department', $headers, $rows, [
        'Total Basic Salary' => '₦' . number_format($summary['total_basic'], 2),
        'Total Allowances' => '₦' . number_format($summary['total_allowances'], 2),
        'Total Deductions' => '₦' . number_format($summary['total_deductions'], 2),
        'Total Net Pay' => '₦' . number_format($summary['total_net_pay'], 2),
        'Staff Count' => $summary['staff_count'],
    ]);
    ReportExporter::pdf($filename, $html);
}

ReportExporter::csv($filename, $headers, $rows);
