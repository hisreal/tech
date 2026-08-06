<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\ResultService;

$resultService = new ResultService();
require_once('includes/header.php');
require_once('includes/result-page-helper.php');
require_once('includes/result-module-styles.php');

$sessions = $resultService->sessionsForSelect();
$terms = $resultService->termsForSelect();

$sessionId = (int) ($_GET['session_id'] ?? $resultService->currentSessionId() ?? 0);
$termId = (int) ($_GET['term_id'] ?? $resultService->currentTermId() ?? 0);
$query = trim((string) ($_GET['q'] ?? ''));

$student = $query !== '' ? $resultService->findStudentByQuery($query) : null;
$card = ($student && $sessionId && $termId) ? $resultService->reportCard((int) $student['id'], $sessionId, $termId) : null;

$totalScore = $card ? array_sum(array_column($card['subjects'], 'total')) : 0;
$averageScore = $card ? (float) ($card['summary']['average_score'] ?? 0) : 0;
$grade = $card ? ($card['summary']['grade'] ?? '-') : '-';
$position = $card && $card['summary'] ? (($card['summary']['position_in_class'] ?? '-') . ($card['class_size'] ? ' of ' . $card['class_size'] : '')) : '-';

$cards = [
    ['title' => 'Student Total', 'value' => $card ? $totalScore : '-', 'description' => 'Total score across subjects', 'icon' => 'fa-calculator', 'color' => 'success'],
    ['title' => 'Average', 'value' => $card ? $averageScore . '%' : '-', 'description' => 'From published results', 'icon' => 'fa-chart-line', 'color' => 'blue'],
    ['title' => 'Grade', 'value' => $grade, 'description' => 'From configured grade scale', 'icon' => 'fa-award', 'color' => 'warning'],
    ['title' => 'Position', 'value' => $position, 'description' => 'Class position', 'icon' => 'fa-ranking-star', 'color' => 'success'],
];
?>
<div class="admin-result-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Report Cards</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-file-lines"></i> Report Cards</span>
                <h3 class="mt-3 mb-2">Report Cards</h3>
                <p class="text-muted mb-0">Generate printable student report cards with scores, attendance, position, and official remarks.</p>
            </div>
            <?php if ($card): ?><button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button><?php endif; ?>
        </div>
    </section>

    <?php sms_result_render_cards($cards); ?>

    <section class="module-card">
        <h4>Report Card Filters</h4>
        <form method="get">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id"><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="term_id"><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div>
                <div class="full"><label>Student</label><input class="form-control" name="q" value="<?php echo sms_e($query); ?>" placeholder="Registration number or student name" required></div>
                <div class="d-flex align-items-end gap-2">
                    <button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-eye"></i> Preview</button>
                    <?php if ($card): ?>
                        <a class="module-btn btn-outline-soft" href="report-card-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'pdf']))); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                        <a class="module-btn btn-outline-soft" href="report-card-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'excel']))); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a>
                        <a class="module-btn btn-outline-soft" href="report-card-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                        <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>

    <?php if ($query !== '' && !$student): ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">No student found matching "<?php echo sms_e($query); ?>".</p></section>
    <?php elseif ($student && !$card): ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">Select a valid session and term to load this student's report card.</p></section>
    <?php elseif ($card): ?>
        <section class="module-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div><h4 class="mb-1">Student Report Card Preview</h4><p class="text-muted mb-0"><?php echo sms_e($card['session_name']); ?> &middot; <?php echo sms_e($card['term_name']); ?></p></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="metric-row"><span>Student</span><span><?php echo sms_e($card['student']['first_name'] . ' ' . $card['student']['last_name']); ?></span></div></div>
                <div class="col-md-3"><div class="metric-row"><span>Reg No.</span><span><?php echo sms_e($card['student']['registration_no']); ?></span></div></div>
                <div class="col-md-3"><div class="metric-row"><span>Class</span><span><?php echo sms_e(($card['enrollment']['class_name'] ?? '-') . (!empty($card['enrollment']['section_name']) ? ' - ' . $card['enrollment']['section_name'] : '')); ?></span></div></div>
                <div class="col-md-3"><div class="metric-row"><span>Attendance</span><span><?php $total = (int) ($card['summary']['attendance_present'] ?? 0) + (int) ($card['summary']['attendance_absent'] ?? 0); echo $total > 0 ? round(($card['summary']['attendance_present'] / $total) * 100) . '%' : 'N/A'; ?></span></div></div>
            </div>
            <?php if (!$card['subjects']): ?>
                <p class="text-muted fw-bold">No published subject results found for this student in the selected term yet.</p>
            <?php else: ?>
                <div class="table-shell"><table class="table result-table">
                    <thead><tr><th>Subject</th><th>1st CA</th><th>2nd CA</th><th>3rd CA</th><th>Exam</th><th>Practical</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead>
                    <tbody>
                    <?php foreach ($card['subjects'] as $subject): ?>
                        <tr>
                            <td><?php echo sms_e($subject['subject_name']); ?></td>
                            <td><?php echo sms_e($subject['ca1']); ?></td>
                            <td><?php echo sms_e($subject['ca2']); ?></td>
                            <td><?php echo sms_e($subject['ca3']); ?></td>
                            <td><?php echo sms_e($subject['exam']); ?></td>
                            <td><?php echo sms_e($subject['practical']); ?></td>
                            <td><?php echo sms_e($subject['total']); ?></td>
                            <td><span class="status-badge status-approved"><?php echo sms_e($subject['grade'] ?? '-'); ?></span></td>
                            <td><?php echo sms_e($subject['remark'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
            <div class="two-grid mt-3">
                <div class="module-card mb-0"><h5>Teacher's Remark</h5><p class="mb-0 text-muted"><?php echo sms_e($card['teacher_remark'] ?? 'No remark configured for this average yet.'); ?></p></div>
                <div class="module-card mb-0"><h5>Principal's Remark</h5><p class="mb-0 text-muted"><?php echo sms_e($card['principal_remark'] ?? 'No remark configured for this average yet.'); ?></p></div>
            </div>
        </section>
    <?php else: ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">Search for a student above to preview their report card.</p></section>
    <?php endif; ?>
</div></div></div>
<?php require_once('includes/footer.php'); ?>
