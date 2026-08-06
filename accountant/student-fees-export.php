<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Helpers\ReportExporter;
use App\Services\FinanceService;

$financeService = new FinanceService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$sessionId = (int) ($_GET['session_id'] ?? 0);
$termId = (int) ($_GET['term_id'] ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);
$status = trim((string) ($_GET['status'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));

$rows = $financeService->listAllInvoices([
    'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'status' => $status, 'search' => $search,
], 1, 20000)['data'];

$headers = ['Registration No.', 'Student Name', 'Class', 'Session', 'Term', 'Total Fees', 'Amount Paid', 'Balance', 'Status'];
$data = [];
foreach ($rows as $inv) {
    $balance = (float) $inv['balance'];
    $statusLabel = $balance <= 0 ? 'Paid' : ((float) $inv['amount_paid'] > 0 ? 'Partially Paid' : 'Outstanding');
    $data[] = [
        $inv['registration_no'], $inv['first_name'] . ' ' . $inv['last_name'], $inv['class_name'],
        $inv['session_name'], $inv['term_name'], $inv['total_amount'], $inv['amount_paid'], $balance, $statusLabel,
    ];
}

$filename = 'student_fees_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Student Fees', $headers, $data);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Student Fee Records', '', $headers, $data);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $data);
