<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\CBTService;

$cbtService = new CBTService();
require_once('includes/header.php');
require_once('includes/cbt-page-helper.php');
require_once('includes/cbt-module-styles.php');

$sessionId = (string) ($_GET['session_id'] ?? '');
$termId = (string) ($_GET['term_id'] ?? '');
$classId = (string) ($_GET['class_id'] ?? '');
$subjectId = (string) ($_GET['subject_id'] ?? '');
$examId = (string) ($_GET['exam_id'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'subject_id' => $subjectId, 'exam_id' => $examId, 'status' => $status, 'search' => $search];

$sessions = $cbtService->sessionsForSelect();
$terms = $cbtService->termsForSelect();
$classes = $cbtService->classesForSelect();
$subjects = $cbtService->subjectsForSelect();
$exams = $cbtService->listExams([], 1, 500)['data'];

$tableResult = $cbtService->listAttempts($filters, $page, 10);
$reports = $cbtService->reportsSummary($filters);

$cards = [
    ['title' => 'Highest Score', 'value' => $reports['highest'] . '%', 'description' => 'Best attempt score', 'icon' => 'fa-trophy', 'color' => 'warning'],
    ['title' => 'Lowest Score', 'value' => $reports['lowest'] . '%', 'description' => 'Lowest attempt score', 'icon' => 'fa-arrow-trend-down', 'color' => 'danger'],
    ['title' => 'Average Score', 'value' => $reports['average'] . '%', 'description' => 'Across attempts', 'icon' => 'fa-chart-line', 'color' => 'blue'],
    ['title' => 'Pass Rate', 'value' => $reports['pass_rate'] . '%', 'description' => 'Passed attempts', 'icon' => 'fa-circle-check', 'color' => 'success'],
    ['title' => 'Best Class', 'value' => $reports['best_class'], 'description' => 'Best performing class', 'icon' => 'fa-school', 'color' => 'success'],
];

function sms_cbta_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);

    return 'cbt-attempts-results.php?' . http_build_query($query);
}
?>
<div class="admin-cbt-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> Attempts &amp; Results</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-square-poll-horizontal"></i> Attempts &amp; Results</span>
                <h3 class="mt-3 mb-2">Attempts &amp; Results</h3>
                <p class="text-muted mb-0">Real student CBT attempts, auto-marked scores, grades, percentages, and exports.</p>
            </div>
            <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </section>

    <?php sms_cbt_render_cards($cards); ?>

    <section class="module-card">
        <h4>Search &amp; Filter</h4>
        <form method="get">
            <div class="filter-grid">
                <div><label>Academic Session</label><select class="form-select" name="session_id"><option value="">All Sessions</option><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $sessionId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Term</label><select class="form-select" name="term_id"><option value="">All Terms</option><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $termId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Class</label><select class="form-select" name="class_id"><option value="">All Classes</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $classId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Subject</label><select class="form-select" name="subject_id"><option value="">All Subjects</option><?php foreach ($subjects as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $subjectId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div><label>Exam</label><select class="form-select" name="exam_id"><option value="">All Exams</option><?php foreach ($exams as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $examId === (string) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['title']); ?></option><?php endforeach; ?></select></div>
                <div><label>Status</label><select class="form-select" name="status"><option value="">All Statuses</option><option value="passed" <?php echo $status === 'passed' ? 'selected' : ''; ?>>Passed</option><option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option></select></div>
                <div class="full"><label>Search</label><input class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Student, reg no, or exam"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="cbt-attempts-results.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Student Attempts &amp; Results</h4><p class="text-muted mb-0">Scores are auto-marked immediately on submission.</p></div>
            <a class="module-btn btn-outline-soft" href="cbt-attempts-export.php?<?php echo sms_e(http_build_query($_GET)); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
        </div>
        <div class="table-shell"><table class="table cbt-table">
            <thead><tr><th>Student</th><th>Registration Number</th><th>Class</th><th>Subject</th><th>Exam</th><th>Started</th><th>Submitted</th><th>Score</th><th>Grade</th><th>Percentage</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($tableResult['data'] as $attempt): ?>
                <?php $passed = (float) $attempt['percentage'] >= (float) $attempt['pass_mark']; ?>
                <tr data-status="<?php echo $passed ? 'passed' : 'failed'; ?>">
                    <td><?php echo sms_e($attempt['first_name'] . ' ' . $attempt['last_name']); ?></td>
                    <td><?php echo sms_e($attempt['registration_no']); ?></td>
                    <td><?php echo sms_e($attempt['class_name']); ?></td>
                    <td><?php echo sms_e($attempt['subject_name']); ?></td>
                    <td><?php echo sms_e($attempt['exam_title']); ?></td>
                    <td><?php echo sms_e(date('Y-m-d H:i', strtotime($attempt['started_at']))); ?></td>
                    <td><?php echo $attempt['ended_at'] ? sms_e(date('Y-m-d H:i', strtotime($attempt['ended_at']))) : '-'; ?></td>
                    <td><?php echo sms_e($attempt['score']); ?></td>
                    <td><?php echo sms_e($attempt['grade'] ?? '-'); ?></td>
                    <td><?php echo sms_e($attempt['percentage']); ?>%</td>
                    <td><?php echo sms_cbt_render_badge($passed ? 'Passed' : 'Failed'); ?></td>
                    <td><a class="module-btn btn-muted-soft" href="cbt-attempt-review.php?attempt_id=<?php echo (int) $attempt['id']; ?>"><i class="fa-solid fa-eye"></i> View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tableResult['data']): ?><tr><td colspan="12" class="text-center text-muted py-4">No CBT attempts match your search.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <span class="text-muted fw-bold"><?php echo (int) $tableResult['meta']['total']; ?> record(s) &middot; page <?php echo (int) $tableResult['meta']['page']; ?> of <?php echo (int) $tableResult['meta']['last_page']; ?></span>
            <?php if ($tableResult['meta']['last_page'] > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php for ($p = 1; $p <= $tableResult['meta']['last_page']; $p++): ?>
                        <a class="module-btn <?php echo $p === (int) $tableResult['meta']['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_cbta_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div></div></div>
<?php require_once('includes/footer.php'); ?>
