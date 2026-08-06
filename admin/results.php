<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\ResultService;

$resultService = new ResultService();
require_once('includes/header.php');
require_once('includes/result-page-helper.php');
require_once('includes/result-module-styles.php');

$sessionId = (string) ($_GET['session_id'] ?? '');
$termId = (string) ($_GET['term_id'] ?? '');
$classId = (string) ($_GET['class_id'] ?? '');
$subjectId = (string) ($_GET['subject_id'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = [
    'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId,
    'subject_id' => $subjectId, 'status' => $status, 'search' => $search,
];

$sessions = $resultService->sessionsForSelect();
$terms = $resultService->termsForSelect();
$classes = $resultService->classesForSelect();
$sections = $resultService->sectionsForSelect();
$subjects = $resultService->subjectsForSelect();
$statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'published' => 'Published', 'locked' => 'Locked'];

$tableResult = $resultService->listBatches($filters, $page, 10);

$submittedCount = (int) ($resultService->listBatches(array_merge($filters, ['status' => 'submitted']), 1, 1)['meta']['total'] ?? 0);
$publishedCount = (int) ($resultService->listBatches(array_merge($filters, ['status' => 'published']), 1, 1)['meta']['total'] ?? 0);
$lockedCount = (int) ($resultService->listBatches(array_merge($filters, ['status' => 'locked']), 1, 1)['meta']['total'] ?? 0);

$cards = [
    ['title' => 'Result Batches', 'value' => number_format((int) $tableResult['meta']['total']), 'description' => 'Matching current filters', 'icon' => 'fa-layer-group', 'color' => 'success'],
    ['title' => 'Awaiting Approval', 'value' => number_format($submittedCount), 'description' => 'Submitted by teachers', 'icon' => 'fa-paper-plane', 'color' => 'warning'],
    ['title' => 'Published', 'value' => number_format($publishedCount), 'description' => 'Visible to students', 'icon' => 'fa-bullhorn', 'color' => 'blue'],
    ['title' => 'Locked', 'value' => number_format($lockedCount), 'description' => 'Protected result records', 'icon' => 'fa-lock', 'color' => 'success'],
];

// --- Broadsheet (own selector, independent of the batch table filters) ---
$bsSessionId = (int) ($_GET['bs_session_id'] ?? $resultService->currentSessionId() ?? 0);
$bsTermId = (int) ($_GET['bs_term_id'] ?? $resultService->currentTermId() ?? 0);
$bsClassId = (int) ($_GET['bs_class_id'] ?? 0);
$bsSectionId = (int) ($_GET['bs_section_id'] ?? 0);
$broadsheet = null;
if ($bsSessionId && $bsTermId && $bsClassId) {
    $broadsheet = $resultService->broadsheet($bsSessionId, $bsTermId, $bsClassId, $bsSectionId ?: null);
}

function sms_res_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);

    return 'results.php?' . http_build_query($query);
}
?>
<div class="admin-result-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Results</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-square-poll-vertical"></i> Results</span>
                <h3 class="mt-3 mb-2">Results</h3>
                <p class="text-muted mb-0">Central hub for approving, publishing, locking, unlocking, exporting, and reviewing class broadsheets.</p>
            </div>
            <a class="module-btn btn-outline-soft" href="score-entry.php"><i class="fa-solid fa-pen-to-square"></i> Score Entry</a>
        </div>
    </section>

    <?php sms_result_render_cards($cards); ?>

    <section class="module-card">
        <h4>Search &amp; Filter Results</h4>
        <form method="get" class="result-filter-form">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id"><option value="">All Sessions</option><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $sessionId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="term_id"><option value="">All Terms</option><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $termId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $classId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Subject</label><select class="form-select" name="subject_id"><option value="">All Subjects</option><?php foreach ($subjects as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $subjectId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Result Status</label><select class="form-select" name="status"><option value="">All Statuses</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $status === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="full"><label>Search</label><input class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Class, teacher, subject"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="results.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Results Table</h4><p class="text-muted mb-0">Workflow actions follow the draft &rarr; submitted &rarr; approved &rarr; published &rarr; locked pipeline.</p></div>
            <div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="result-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'pdf']))); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a class="module-btn btn-outline-soft" href="result-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'excel']))); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a class="module-btn btn-outline-soft" href="result-export.php?<?php echo sms_e(http_build_query(array_merge($_GET, ['format' => 'csv']))); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
        <form method="post" action="result-batch-action.php" id="bulkForm">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button class="module-btn btn-outline-soft" type="submit" name="bulk_action" value="submit"><i class="fa-solid fa-paper-plane"></i> Submit Selected</button>
                <button class="module-btn btn-primary-soft" type="submit" name="bulk_action" value="approve"><i class="fa-solid fa-check-double"></i> Approve Selected</button>
                <button class="module-btn btn-primary-soft" type="submit" name="bulk_action" value="publish"><i class="fa-solid fa-bullhorn"></i> Publish Selected</button>
                <button class="module-btn btn-muted-soft" type="submit" name="bulk_action" value="lock"><i class="fa-solid fa-lock"></i> Lock Selected</button>
                <button class="module-btn btn-muted-soft" type="submit" name="bulk_action" value="unlock"><i class="fa-solid fa-lock-open"></i> Unlock Selected</button>
            </div>
            <div class="table-shell"><table class="table result-table">
                <thead><tr><th><input class="form-check-input" type="checkbox" id="selectAllBatches"></th><th>Class</th><th>Subject</th><th>Teacher</th><th>Session</th><th>Term</th><th>Students</th><th>Average</th><th>Status</th><th>Last Updated</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($tableResult['data'] as $item): ?>
                    <tr data-status="<?php echo sms_e($item['status']); ?>">
                        <td><input class="form-check-input batch-select" type="checkbox" name="ids[]" value="<?php echo (int) $item['id']; ?>"></td>
                        <td><?php echo sms_e($item['class_name'] . ($item['section_name'] ? ' - ' . $item['section_name'] : '')); ?></td>
                        <td><?php echo sms_e($item['subject_name']); ?></td>
                        <td><?php echo sms_e(trim(($item['teacher_first_name'] ?? '') . ' ' . ($item['teacher_last_name'] ?? '')) ?: 'Unassigned'); ?></td>
                        <td><?php echo sms_e($item['session_name']); ?></td>
                        <td><?php echo sms_e($item['term_name']); ?></td>
                        <td><?php echo (int) $item['student_count']; ?></td>
                        <td><?php echo $item['average_score'] !== null ? sms_e(number_format((float) $item['average_score'], 1)) : '-'; ?></td>
                        <td><?php echo sms_result_render_badge(ucfirst($item['status'])); ?></td>
                        <td><?php echo sms_e(date('Y-m-d H:i', strtotime($item['updated_at']))); ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="module-btn btn-muted-soft dropdown-toggle" data-bs-toggle="dropdown" type="button">Actions</button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="score-entry.php?session_id=<?php echo (int) $item['session_id']; ?>&term_id=<?php echo (int) $item['term_id']; ?>&class_id=<?php echo (int) $item['class_id']; ?>&section_id=<?php echo (int) ($item['section_id'] ?? 0); ?>&subject_id=<?php echo (int) $item['subject_id']; ?>"><i class="fa-solid fa-eye me-2"></i>View / Enter Scores</a>
                                    <?php if ($item['status'] === 'draft'): ?><button class="dropdown-item single-action" type="button" data-action="submit" data-id="<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-paper-plane me-2"></i>Submit</button><?php endif; ?>
                                    <?php if ($item['status'] === 'submitted'): ?><button class="dropdown-item single-action" type="button" data-action="approve" data-id="<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-check-double me-2"></i>Approve</button><?php endif; ?>
                                    <?php if ($item['status'] === 'approved'): ?><button class="dropdown-item single-action" type="button" data-action="publish" data-id="<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-bullhorn me-2"></i>Publish</button><?php endif; ?>
                                    <?php if (in_array($item['status'], ['approved', 'published'], true)): ?><button class="dropdown-item single-action" type="button" data-action="lock" data-id="<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-lock me-2"></i>Lock</button><?php endif; ?>
                                    <?php if ($item['status'] === 'locked'): ?><button class="dropdown-item single-action" type="button" data-action="unlock" data-id="<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-lock-open me-2"></i>Unlock</button><?php endif; ?>
                                    <a class="dropdown-item" href="<?php echo sms_e(sms_res_query(['bs_session_id' => $item['session_id'], 'bs_term_id' => $item['term_id'], 'bs_class_id' => $item['class_id'], 'bs_section_id' => $item['section_id'] ?? ''])); ?>#broadsheet"><i class="fa-solid fa-table-list me-2"></i>View Broadsheet</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$tableResult['data']): ?><tr><td colspan="11" class="text-center text-muted py-4">No result batches match your search.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </form>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <span class="text-muted fw-bold"><?php echo (int) $tableResult['meta']['total']; ?> record(s) &middot; page <?php echo (int) $tableResult['meta']['page']; ?> of <?php echo (int) $tableResult['meta']['last_page']; ?></span>
            <?php if ($tableResult['meta']['last_page'] > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php for ($p = 1; $p <= $tableResult['meta']['last_page']; $p++): ?>
                        <a class="module-btn <?php echo $p === (int) $tableResult['meta']['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_res_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="module-card" id="broadsheet">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Class Broadsheet</h4><p class="text-muted mb-0">Pivoted subject scores per student for a session, term, and class.</p></div>
            <?php if ($broadsheet): ?><div class="d-flex flex-wrap gap-2">
                <a class="module-btn btn-outline-soft" href="broadsheet-export.php?<?php echo sms_e(http_build_query(['session_id' => $bsSessionId, 'term_id' => $bsTermId, 'class_id' => $bsClassId, 'section_id' => $bsSectionId, 'format' => 'pdf'])); ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a class="module-btn btn-outline-soft" href="broadsheet-export.php?<?php echo sms_e(http_build_query(['session_id' => $bsSessionId, 'term_id' => $bsTermId, 'class_id' => $bsClassId, 'section_id' => $bsSectionId, 'format' => 'excel'])); ?>"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a class="module-btn btn-outline-soft" href="broadsheet-export.php?<?php echo sms_e(http_build_query(['session_id' => $bsSessionId, 'term_id' => $bsTermId, 'class_id' => $bsClassId, 'section_id' => $bsSectionId, 'format' => 'csv'])); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
                <button class="module-btn btn-muted-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            </div><?php endif; ?>
        </div>
        <form method="get" class="mb-3">
            <input type="hidden" name="session_id" value="<?php echo sms_e($sessionId); ?>">
            <input type="hidden" name="term_id" value="<?php echo sms_e($termId); ?>">
            <input type="hidden" name="class_id" value="<?php echo sms_e($classId); ?>">
            <input type="hidden" name="subject_id" value="<?php echo sms_e($subjectId); ?>">
            <input type="hidden" name="status" value="<?php echo sms_e($status); ?>">
            <input type="hidden" name="search" value="<?php echo sms_e($search); ?>">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="bs_session_id"><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsSessionId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="bs_term_id"><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsTermId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" name="bs_class_id"><option value="">Select Class</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsClassId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Section</label><select class="form-select" name="bs_section_id"><option value="">All Sections</option><?php foreach ($sections as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $bsSectionId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
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

    <form method="post" action="result-batch-action.php" id="singleActionForm" style="display:none">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="ids[]" id="singleActionId">
        <input type="hidden" name="bulk_action" id="singleActionValue">
        <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
    </form>
</div></div></div>
<script>
(function () {
    var selectAll = document.getElementById('selectAllBatches');
    if (selectAll) { selectAll.addEventListener('change', function () { document.querySelectorAll('.batch-select').forEach(function (c) { c.checked = selectAll.checked; }); }); }

    var singleForm = document.getElementById('singleActionForm');
    document.querySelectorAll('.single-action').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('singleActionId').value = button.dataset.id;
            document.getElementById('singleActionValue').value = button.dataset.action;
            singleForm.submit();
        });
    });
})();
</script>
<?php require_once('includes/footer.php'); ?>
