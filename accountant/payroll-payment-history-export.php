<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

$payrollService = new PayrollService();
$filters = [
    'staff_id' => (string) ($_GET['staff_id'] ?? ''), 'payment_method' => (string) ($_GET['payment_method'] ?? ''),
    'date_from' => (string) ($_GET['date_from'] ?? ''), 'date_to' => (string) ($_GET['date_to'] ?? ''), 'search' => (string) ($_GET['search'] ?? ''),
];
$rows = $payrollService->listPaymentHistory($filters, 1, 5000)['data'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="payroll_payment_history_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date Paid', 'Staff', 'Staff No.', 'Period', 'Amount', 'Method', 'Reference']);
foreach ($rows as $row) {
    fputcsv($out, [
        $row['paid_at'], $row['first_name'] . ' ' . $row['last_name'], $row['staff_no'],
        PayrollService::monthName((int) $row['period_month']) . ' ' . $row['period_year'], $row['amount'], ucfirst(str_replace('_', ' ', $row['payment_method'])), $row['reference_no'],
    ]);
}
fclose($out);
exit;
