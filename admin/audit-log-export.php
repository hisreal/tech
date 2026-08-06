<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\AuditLogService;

$auditService = new AuditLogService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$filters = [
    'date_from' => trim((string) ($_GET['date_from'] ?? '')),
    'date_to' => trim((string) ($_GET['date_to'] ?? '')),
    'role' => trim((string) ($_GET['role'] ?? '')),
    'module' => trim((string) ($_GET['module'] ?? '')),
    'action' => trim((string) ($_GET['action'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'search' => trim((string) ($_GET['search'] ?? '')),
];

$rows = $auditService->list($filters, 1, 20000)['data'];

$headers = ['Date & Time', 'User', 'Role', 'Module', 'Action', 'Description', 'IP Address', 'Status'];
$data = [];
foreach ($rows as $row) {
    $data[] = [
        date('Y-m-d H:i:s', strtotime((string) $row['created_at'])),
        $row['actor_name'],
        $row['actor_role_label'],
        ucfirst((string) $row['module']),
        ucwords(str_replace(['.', '_'], ' ', (string) $row['action'])),
        $row['description'],
        $row['ip_address'] ?? '-',
        ucfirst((string) $row['status']),
    ];
}

$filename = 'audit_logs_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Audit Logs', $headers, $data);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Audit Logs', '', $headers, $data, ['Total Records' => count($data)]);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $data);
