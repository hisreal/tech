<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Helpers\ReportExporter;
use App\Services\FinanceService;

$financeService = new FinanceService();
$dateFrom = trim((string) ($_GET['date_from'] ?? date('Y-m-01')));
$dateTo = trim((string) ($_GET['date_to'] ?? date('Y-m-d')));
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$transactions = $financeService->transactionsFeed($dateFrom, $dateTo, 5000);
$summary = $financeService->summary($dateFrom, $dateTo);

$headers = ['Date', 'Transaction ID', 'Description', 'Category', 'Amount', 'Type', 'Status'];
$rows = [];
foreach ($transactions as $t) {
    $rows[] = [$t['date'], $t['id'], $t['description'], $t['category'], $t['amount'], $t['type'], $t['status']];
}

$filename = 'financial_report_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Financial Report', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Financial Report', $dateFrom . ' to ' . $dateTo, $headers, $rows, [
        'Total Revenue' => '₦' . number_format((float) $summary['revenue']), 'Total Expenses' => '₦' . number_format((float) $summary['expenses']), 'Net Income' => '₦' . number_format((float) $summary['net_income']),
    ]);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $rows);
