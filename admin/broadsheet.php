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
$classes = $resultService->classesForSelect();
$sections = $resultService->sectionsForSelect();

$bsSessionId = (int) ($_GET['session_id'] ?? $resultService->currentSessionId() ?? 0);
$bsTermId = (int) ($_GET['term_id'] ?? $resultService->currentTermId() ?? 0);
$bsClassId = (int) ($_GET['class_id'] ?? 0);
$bsSectionId = (int) ($_GET['section_id'] ?? 0);
$broadsheet = null;
if ($bsSessionId && $bsTermId && $bsClassId) {
    $broadsheet = $resultService->broadsheet($bsSessionId, $bsTermId, $bsClassId, $bsSectionId ?: null);
}
?>
<div class="admin-result-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Broadsheet</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-table-list"></i> Broadsheet</span>
                <h3 class="mt-3 mb-2">Class Broadsheet</h3>
                <p class="text-muted mb-0">Pivoted subject scores per student for a session, term, and class.</p>
            </div>
            <a class="module-btn btn-outline-soft" href="results.php"><i class="fa-solid fa-arrow-left"></i> Back to Results</a>
        </div>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Generate Broadsheet</h4><p class="text-muted mb-0">Select a session, term, and class to pivot every published subject score for that class.</p></div>
            <?php if ($broadsheet): ?><div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="broadsheet-export.php?<?php echo sms_e(http_build_query(['session_id' => $bsSessionId, 'term_id' => $bsTermId, 'class_id' => $bsClassId, 'section_id' => $bsSectionId, 'format' => 'pdf'])); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a class="module-btn btn-outline-soft" href="broadsheet-export.php?<?php echo sms_e(http_build_query(['session_id' => $bsSessionId, 'term_id' => $bsTermId, 'class_id' => $bsClassId, 'section_id' => $bsSectionId, 'format' => 'excel'])); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a class="module-btn btn-outline-soft" href="broadsheet-export.php?<?php echo sms_e(http_build_query(['session_id' => $bsSessionId, 'term_id' => $bsTermId, 'class_id' => $bsClassId, 'section_id' => $bsSectionId, 'format' => 'csv'])); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div><?php endif; ?>
        </div>
        <form method="get" class="mb-3">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id"><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsSessionId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="term_id"><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsTermId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" name="class_id"><option value="">Select Class</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsClassId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select" name="section_id"><option value="">All Sections</option><?php foreach ($sections as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsSectionId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-table-list"></i> Generate</button></div>
            </div>
        </form>
        <?php if ($broadsheet && $broadsheet['rows']): ?>
            <div class="table-shell"><table class="table result-table">
                <thead><tr><th>Position</th><th>Reg. No.</th><th>Student</th><?php foreach ($broadsheet['subjects'] as $subject): ?><th><?php echo sms_e($subject['name']); ?></th><?php endforeach; ?><th>Total</th><th>Average</th><th>Grade</th></tr></thead>
                <tbody>
                <?php foreach ($broadsheet['rows'] as $row): ?>
                    <tr>
                        <td><?php echo sms_e((string) ($row['summary']['position_in_class'] ?? '-')); ?></td>
                        <td><?php echo sms_e($row['student']['registration_no']); ?></td>
                        <td><?php echo sms_e($row['student']['first_name'] . ' ' . $row['student']['last_name']); ?></td>
                        <?php foreach ($broadsheet['subjects'] as $subject): ?><td><?php echo sms_e((string) ($row['scores'][$subject['id']]['total'] ?? '-')); ?></td><?php endforeach; ?>
                        <td><?php echo sms_e((string) ($row['summary']['total_score'] ?? '-')); ?></td>
                        <td><?php echo sms_e((string) ($row['summary']['average_score'] ?? '-')); ?></td>
                        <td><?php echo sms_e((string) ($row['summary']['grade'] ?? '-')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php elseif ($broadsheet): ?>
            <p class="text-muted fw-bold mb-0">No students found for this class/section.</p>
        <?php else: ?>
            <p class="text-muted fw-bold mb-0">Select a session, term, and class above to generate the broadsheet.</p>
        <?php endif; ?>
    </section>
</div></div></div>
<?php require_once('includes/footer.php'); ?>
