<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\ResultService;

$resultService = new ResultService();
require_once('includes/header.php');
require_once('includes/result-page-helper.php');
require_once('includes/result-module-styles.php');

$errors = Session::errors();

$sessionId = (int) ($_GET['session_id'] ?? $resultService->currentSessionId() ?? 0);
$termId = (int) ($_GET['term_id'] ?? $resultService->currentTermId() ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);
$sectionId = (int) ($_GET['section_id'] ?? 0);
$subjectId = (int) ($_GET['subject_id'] ?? 0);
$teacherId = (int) ($_GET['teacher_id'] ?? 0);

$sessions = $resultService->sessionsForSelect();
$terms = $resultService->termsForSelect($sessionId ?: null);
$classes = $resultService->classesForSelect();
$sections = $resultService->sectionsForSelect($classId ?: null);
$subjects = $resultService->subjectsForSelect($classId ?: null);
$teachers = $resultService->teachersForSelect();

$batch = null;
$roster = [];
$scores = [];
if ($sessionId && $termId && $classId && $subjectId) {
    $batch = $resultService->findOrCreateBatch($sessionId, $termId, $classId, $sectionId ?: null, $subjectId, $teacherId ?: null);
    $roster = $resultService->rosterForBatch($classId, $sectionId ?: null, $sessionId);
    $scores = $resultService->existingScores((int) $batch['id']);
}

$cards = [
    ['title' => 'Roster Size', 'value' => count($roster), 'description' => 'Students in this class/section', 'icon' => 'fa-users', 'color' => 'success'],
    ['title' => 'Scores Entered', 'value' => count($scores), 'description' => 'Students already scored', 'icon' => 'fa-pen', 'color' => 'blue'],
    ['title' => 'Batch Status', 'value' => $batch ? ucfirst($batch['status']) : '-', 'description' => 'Current workflow stage', 'icon' => 'fa-diagram-project', 'color' => 'warning'],
    ['title' => 'Pass Mark', 'value' => $resultService->generalSettings()['pass_mark'] . '%', 'description' => 'Configured benchmark', 'icon' => 'fa-check-circle', 'color' => 'success'],
];

function sms_se_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);

    return 'score-entry.php?' . http_build_query($query);
}
?>
<div class="admin-result-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Score Entry</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-pen-to-square"></i> Score Entry</span>
                <h3 class="mt-3 mb-2">Score Entry</h3>
                <p class="text-muted mb-0">Enter CA, exam, and practical scores per subject. Totals, grades, and subject positions are calculated automatically on save.</p>
            </div>
        </div>
    </section>

    <?php sms_result_render_cards($cards); ?>

    <section class="module-card">
        <h4>Select Class &amp; Subject</h4>
        <form method="get" id="selectorForm">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id" required>
                    <option value="">Select Session</option>
                    <?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $sessionId === (int) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div><label>Term</label><select class="form-select" name="term_id" required>
                    <option value="">Select Term</option>
                    <?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $termId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div><label>Class</label><select class="form-select" name="class_id" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo $classId === (int) $c['id'] ? 'selected' : ''; ?>><?php echo sms_e($c['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div><label>Section</label><select class="form-select" name="section_id">
                    <option value="">All Sections</option>
                    <?php foreach ($sections as $sec): ?><option value="<?php echo (int) $sec['id']; ?>" <?php echo $sectionId === (int) $sec['id'] ? 'selected' : ''; ?>><?php echo sms_e($sec['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div><label>Subject</label><select class="form-select" name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $sub): ?><option value="<?php echo (int) $sub['id']; ?>" <?php echo $subjectId === (int) $sub['id'] ? 'selected' : ''; ?>><?php echo sms_e($sub['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div><label>Teacher</label><select class="form-select" name="teacher_id">
                    <option value="">Unassigned</option>
                    <?php foreach ($teachers as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $teacherId === (int) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['first_name'] . ' ' . $t['last_name']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Load Roster</button></div>
            </div>
        </form>
    </section>

    <?php if ($batch && $roster): ?>
        <section class="module-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Enter Scores</h4>
                    <p class="text-muted mb-0">CA1 + CA2 + CA3 + Exam + Practical = Total (each capped at 0-100). Grade is applied automatically from Grade Settings.</p>
                </div>
                <span class="status-badge status-<?php echo sms_e($batch['status']); ?>"><i class="fa-solid fa-circle"></i> <?php echo sms_e(ucfirst($batch['status'])); ?></span>
            </div>

            <?php if ($batch['status'] === 'locked'): ?>
                <div class="alert alert-warning">This result batch is locked. Scores cannot be edited until it is unlocked from the Results page.</div>
            <?php endif; ?>

            <form method="post" action="score-save.php" id="scoreForm">
                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                <input type="hidden" name="batch_id" value="<?php echo (int) $batch['id']; ?>">
                <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                <div class="table-shell">
                    <table class="table result-table" id="scoreTable">
                        <thead><tr><th>Reg. No.</th><th>Student</th><th>CA1 (20)</th><th>CA2 (20)</th><th>CA3 (20)</th><th>Exam (40)</th><th>Practical (0-100)</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($roster as $student): ?>
                            <?php $existing = $scores[$student['id']] ?? null; ?>
                            <tr data-row>
                                <td><?php echo sms_e($student['registration_no']); ?></td>
                                <td><?php echo sms_e($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][ca1]" value="<?php echo sms_e((string) ($existing['ca1'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
                                <td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][ca2]" value="<?php echo sms_e((string) ($existing['ca2'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
                                <td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][ca3]" value="<?php echo sms_e((string) ($existing['ca3'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
                                <td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][exam]" value="<?php echo sms_e((string) ($existing['exam'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
                                <td><input class="form-control score-input" type="number" min="0" max="100" step="0.01" name="scores[<?php echo (int) $student['id']; ?>][practical]" value="<?php echo sms_e((string) ($existing['practical'] ?? 0)); ?>" <?php echo $batch['status'] === 'locked' ? 'disabled' : ''; ?>></td>
                                <td class="row-total fw-bold"><?php echo sms_e((string) ($existing['total'] ?? '0.00')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($batch['status'] !== 'locked'): ?>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Scores</button>
                </div>
                <?php endif; ?>
            </form>

            <?php if ($batch['status'] === 'draft'): ?>
                <form method="post" action="score-submit.php" class="d-flex justify-content-end mt-2">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="batch_id" value="<?php echo (int) $batch['id']; ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <button class="module-btn btn-outline-soft" type="submit"><i class="fa-solid fa-paper-plane"></i> Submit for Approval</button>
                </form>
            <?php endif; ?>
        </section>
    <?php elseif ($sessionId && $termId && $classId && $subjectId): ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">No active students found for this class/section in the selected session.</p></section>
    <?php else: ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">Select session, term, class, and subject above to load the student roster.</p></section>
    <?php endif; ?>
</div></div></div>
<script>
(function () {
    document.querySelectorAll('#scoreTable tbody tr[data-row]').forEach(function (row) {
        var inputs = row.querySelectorAll('.score-input');
        var totalCell = row.querySelector('.row-total');
        function recalc() {
            var sum = 0;
            inputs.forEach(function (input) { sum += parseFloat(input.value || '0') || 0; });
            totalCell.textContent = sum.toFixed(2);
        }
        inputs.forEach(function (input) { input.addEventListener('input', recalc); });
    });
})();
</script>
<?php require_once('includes/footer.php'); ?>
