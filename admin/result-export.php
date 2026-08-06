<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Helpers\ReportExporter;
use App\Services\ResultService;

$resultService = new ResultService();
$format = in_array($_GET['format'] ?? 'csv', ['csv', 'excel', 'pdf'], true) ? $_GET['format'] : 'csv';

$filters = [
    'session_id' => (string) ($_GET['session_id'] ?? ''), 'term_id' => (string) ($_GET['term_id'] ?? ''),
    'class_id' => (string) ($_GET['class_id'] ?? ''), 'subject_id' => (string) ($_GET['subject_id'] ?? ''),
    'status' => (string) ($_GET['status'] ?? ''), 'search' => (string) ($_GET['search'] ?? ''),
];
$data = $resultService->listBatches($filters, 1, 5000)['data'];

$headers = ['Session', 'Term', 'Class', 'Section', 'Subject', 'Teacher', 'Students', 'Average', 'Status', 'Last Updated'];
$rows = [];
foreach ($data as $row) {
    $rows[] = [
        $row['session_name'], $row['term_name'], $row['class_name'], $row['section_name'] ?? '',
        $row['subject_name'], trim(($row['teacher_first_name'] ?? '') . ' ' . ($row['teacher_last_name'] ?? '')),
        $row['student_count'], $row['average_score'], ucfirst($row['status']), $row['updated_at'],
    ];
}

$filename = 'result_batches_' . date('Ymd_His');

if ($format === 'excel') {
    ReportExporter::excel($filename, 'Result Batches', $headers, $rows);
}

if ($format === 'pdf') {
    $html = ReportExporter::tableHtml('Result Batches', '', $headers, $rows);
    ReportExporter::pdf($filename, $html, 'landscape');
}

ReportExporter::csv($filename, $headers, $rows);
