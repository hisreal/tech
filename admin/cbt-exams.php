<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\CBTService;

$cbtService = new CBTService();
require_once('includes/header.php');
require_once('includes/cbt-page-helper.php');
require_once('includes/cbt-module-styles.php');

$errors = Session::errors();
$old = Session::oldAll();

$sessionId = (string) ($_GET['session_id'] ?? '');
$termId = (string) ($_GET['term_id'] ?? '');
$classId = (string) ($_GET['class_id'] ?? '');
$subjectId = (string) ($_GET['subject_id'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = ['session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId, 'subject_id' => $subjectId, 'status' => $status, 'search' => $search];

$sessions = $cbtService->sessionsForSelect();
$terms = $cbtService->termsForSelect();
$currentSessionId = $cbtService->currentSessionId();
$currentTermId = $cbtService->currentTermId();
$classes = $cbtService->classesForSelect();
$sections = $cbtService->sectionsForSelect();
$subjects = $cbtService->subjectsForSelect();
$teachers = $cbtService->teachersForSelect();
$statuses = ['draft' => 'Draft', 'published' => 'Published', 'active' => 'Active', 'completed' => 'Completed', 'inactive' => 'Inactive', 'archived' => 'Archived'];

$tableResult = $cbtService->listExams($filters, $page, 10);

$stats = $cbtService->dashboardStats();

$cards = [
    ['title' => 'Total Exams', 'value' => number_format((int) $tableResult['meta']['total']), 'description' => 'Matching current filters', 'icon' => 'fa-laptop-file', 'color' => 'success'],
    ['title' => 'Active', 'value' => number_format((int) $stats['active_exams']), 'description' => 'Available to students', 'icon' => 'fa-toggle-on', 'color' => 'success'],
    ['title' => 'Published', 'value' => number_format((int) $stats['published_exams']), 'description' => 'Ready for activation', 'icon' => 'fa-bullhorn', 'color' => 'blue'],
    ['title' => 'Archived', 'value' => number_format((int) $stats['archived_exams']), 'description' => 'Historical exams', 'icon' => 'fa-box-archive', 'color' => 'warning'],
];

function sms_cbtx_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);

    return 'cbt-exams.php?' . http_build_query($query);
}

function sms_cbtx_old(array $old, string $key, string $default = ''): string
{
    return sms_e($old[$key] ?? $default);
}
?>
<div class="admin-cbt-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> Exams</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-laptop-file"></i> Exams</span>
                <h3 class="mt-3 mb-2">CBT Exams</h3>
                <p class="text-muted mb-0">Manage examinations, publication, activation, archival, and question banks.</p>
            </div>
            <button class="module-btn btn-primary-soft" id="addExamBtn" type="button"><i class="fa-solid fa-plus"></i> Add Exam</button>
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
                <div><label>Status</label><select class="form-select" name="status"><option value="">All Statuses</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $status === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="full"><label>Search</label><input class="form-control" name="search" value="<?php echo sms_e($search); ?>" placeholder="Exam title or teacher"></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-search"></i> Search</button><a class="module-btn btn-muted-soft" href="cbt-exams.php">Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Exams Table</h4><p class="text-muted mb-0">Manage each exam's questions from the Questions page.</p></div>
            <a class="module-btn btn-outline-soft" href="cbt-exam-export.php?<?php echo sms_e(http_build_query($_GET)); ?>"><i class="fa-solid fa-file-csv"></i> CSV</a>
        </div>
        <div class="table-shell"><table class="table cbt-table">
            <thead><tr><th>Exam Title</th><th>Subject</th><th>Teacher</th><th>Class</th><th>Duration</th><th>Questions</th><th>Attempts</th><th>Average</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($tableResult['data'] as $exam): ?>
                <tr data-status="<?php echo sms_e($exam['status']); ?>">
                    <td><?php echo sms_e($exam['title']); ?></td>
                    <td><?php echo sms_e($exam['subject_name']); ?></td>
                    <td><?php echo sms_e(trim(($exam['teacher_first_name'] ?? '') . ' ' . ($exam['teacher_last_name'] ?? '')) ?: 'Unassigned'); ?></td>
                    <td><?php echo sms_e($exam['class_name'] . ($exam['section_name'] ? ' - ' . $exam['section_name'] : '')); ?></td>
                    <td><?php echo (int) $exam['duration_minutes']; ?> mins</td>
                    <td><?php echo (int) $exam['question_count']; ?></td>
                    <td><?php echo (int) $exam['attempt_count']; ?></td>
                    <td><?php echo $exam['average_percentage'] !== null ? sms_e(number_format((float) $exam['average_percentage'], 1)) . '%' : '-'; ?></td>
                    <td><?php echo sms_cbt_render_badge(ucfirst($exam['status'])); ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="module-btn btn-muted-soft dropdown-toggle" data-bs-toggle="dropdown" type="button">Actions</button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="cbt-questions.php?exam_id=<?php echo (int) $exam['id']; ?>"><i class="fa-solid fa-list-ol me-2"></i>Manage Questions</a>
                                <?php if (!in_array($exam['status'], ['active', 'completed'], true)): ?>
                                    <button class="dropdown-item edit-exam-btn" type="button"
                                        data-id="<?php echo (int) $exam['id']; ?>" data-title="<?php echo sms_e($exam['title']); ?>"
                                        data-session="<?php echo (int) $exam['session_id']; ?>" data-term="<?php echo (int) $exam['term_id']; ?>"
                                        data-subject="<?php echo (int) $exam['subject_id']; ?>" data-class="<?php echo (int) $exam['class_id']; ?>"
                                        data-section="<?php echo (int) ($exam['section_id'] ?? 0); ?>" data-duration="<?php echo (int) $exam['duration_minutes']; ?>"
                                        data-pass="<?php echo sms_e($exam['pass_mark']); ?>" data-attempts="<?php echo (int) $exam['maximum_attempts']; ?>"
                                        data-description="<?php echo sms_e((string) $exam['description']); ?>" data-instructions="<?php echo sms_e((string) $exam['instructions']); ?>">
                                        <i class="fa-solid fa-pen me-2"></i>Edit</button>
                                <?php endif; ?>
                                <?php if ($exam['status'] === 'draft'): ?><button class="dropdown-item single-action" type="button" data-action="published" data-id="<?php echo (int) $exam['id']; ?>"><i class="fa-solid fa-bullhorn me-2"></i>Publish</button><?php endif; ?>
                                <?php if (in_array($exam['status'], ['published', 'inactive'], true)): ?><button class="dropdown-item single-action" type="button" data-action="active" data-id="<?php echo (int) $exam['id']; ?>"><i class="fa-solid fa-toggle-on me-2"></i>Activate</button><?php endif; ?>
                                <?php if ($exam['status'] === 'active'): ?><button class="dropdown-item single-action" type="button" data-action="inactive" data-id="<?php echo (int) $exam['id']; ?>"><i class="fa-solid fa-toggle-off me-2"></i>Deactivate</button><?php endif; ?>
                                <?php if ($exam['status'] === 'active'): ?><button class="dropdown-item single-action" type="button" data-action="completed" data-id="<?php echo (int) $exam['id']; ?>"><i class="fa-solid fa-circle-check me-2"></i>Mark Completed</button><?php endif; ?>
                                <?php if (!in_array($exam['status'], ['archived'], true)): ?><button class="dropdown-item single-action" type="button" data-action="archived" data-id="<?php echo (int) $exam['id']; ?>"><i class="fa-solid fa-box-archive me-2"></i>Archive</button><?php endif; ?>
                                <?php if ((int) $exam['attempt_count'] === 0): ?>
                                <form method="post" action="cbt-exam-delete.php" onsubmit="return confirm('Delete this exam and all of its questions?');" style="display:inline">
                                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $exam['id']; ?>">
                                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                                    <button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-trash me-2"></i>Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tableResult['data']): ?><tr><td colspan="10" class="text-center text-muted py-4">No CBT exams match your search.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <span class="text-muted fw-bold"><?php echo (int) $tableResult['meta']['total']; ?> record(s) &middot; page <?php echo (int) $tableResult['meta']['page']; ?> of <?php echo (int) $tableResult['meta']['last_page']; ?></span>
            <?php if ($tableResult['meta']['last_page'] > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php for ($p = 1; $p <= $tableResult['meta']['last_page']; $p++): ?>
                        <a class="module-btn <?php echo $p === (int) $tableResult['meta']['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_cbtx_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="modal fade" id="examModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" action="cbt-exam-store.php" id="examForm">
                <div class="modal-header"><h5 class="modal-title" id="examModalTitle">Add CBT Exam</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="examId" value="<?php echo sms_e($old['id'] ?? ''); ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <?php if (isset($errors['title'])): ?><div class="alert alert-danger"><?php echo sms_e($errors['title']); ?></div><?php endif; ?>
                    <div class="form-grid">
                        <div class="full"><label>Exam Title</label><input class="form-control" name="title" id="examTitle" value="<?php echo sms_cbtx_old($old, 'title'); ?>" required></div>
                        <div><label>Academic Session</label><select class="form-select" name="session_id" id="examSession" required><option value="">Select</option><?php foreach ($sessions as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $currentSessionId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Term</label><select class="form-select" name="term_id" id="examTerm" required><option value="">Select</option><?php foreach ($terms as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $currentTermId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Subject</label><select class="form-select" name="subject_id" id="examSubject" required><option value="">Select</option><?php foreach ($subjects as $item): ?><option value="<?php echo (int) $item['id']; ?>"><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Class</label><select class="form-select" name="class_id" id="examClass" required><option value="">Select</option><?php foreach ($classes as $item): ?><option value="<?php echo (int) $item['id']; ?>"><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Section</label><select class="form-select" name="section_id" id="examSection"><option value="">Whole Class</option><?php foreach ($sections as $item): ?><option value="<?php echo (int) $item['id']; ?>" data-class="<?php echo (int) $item['class_id']; ?>"><?php echo sms_e($item['name']); ?></option><?php endforeach; ?></select></div>
                        <div><label>Duration (minutes)</label><input class="form-control" type="number" min="1" name="duration_minutes" id="examDuration" value="<?php echo sms_cbtx_old($old, 'duration_minutes', '30'); ?>" required></div>
                        <div><label>Pass Mark (%)</label><input class="form-control" type="number" min="0" max="100" name="pass_mark" id="examPassMark" value="<?php echo sms_cbtx_old($old, 'pass_mark', (string) $cbtService->generalSettings()['pass_mark']); ?>"></div>
                        <div><label>Maximum Attempts</label><input class="form-control" type="number" min="1" name="maximum_attempts" id="examAttempts" value="<?php echo sms_cbtx_old($old, 'maximum_attempts', (string) $cbtService->generalSettings()['maximum_attempts']); ?>"></div>
                        <div class="full"><label>Description</label><textarea class="form-control" name="description" id="examDescription"><?php echo sms_cbtx_old($old, 'description'); ?></textarea></div>
                        <div class="full"><label>Instructions</label><textarea class="form-control" name="instructions" id="examInstructions"><?php echo sms_cbtx_old($old, 'instructions'); ?></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="module-btn btn-muted-soft" data-bs-dismiss="modal" type="button">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Exam</button></div>
            </form>
        </div>
    </div>

    <form method="post" action="cbt-exam-status.php" id="singleActionForm" style="display:none">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="id" id="singleActionId">
        <input type="hidden" name="status" id="singleActionValue">
        <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
    </form>
</div></div></div>
<script data-cfasync="false" type="text/javascript">
(function () {
    var modalEl = document.getElementById('examModal');
    function getModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }
    var form = document.getElementById('examForm');
    var title = document.getElementById('examModalTitle');

    document.getElementById('addExamBtn').addEventListener('click', function () {
        title.textContent = 'Add CBT Exam';
        form.reset();
        form.action = 'cbt-exam-store.php';
        document.getElementById('examId').value = '';
        var modal = getModal(); if (modal) { modal.show(); }
    });

    document.querySelectorAll('.edit-exam-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            title.textContent = 'Edit CBT Exam';
            document.getElementById('examId').value = btn.dataset.id;
            document.getElementById('examTitle').value = btn.dataset.title;
            document.getElementById('examSession').value = btn.dataset.session;
            document.getElementById('examTerm').value = btn.dataset.term;
            document.getElementById('examSubject').value = btn.dataset.subject;
            document.getElementById('examClass').value = btn.dataset.class;
            document.getElementById('examSection').value = btn.dataset.section;
            document.getElementById('examDuration').value = btn.dataset.duration;
            document.getElementById('examPassMark').value = btn.dataset.pass;
            document.getElementById('examAttempts').value = btn.dataset.attempts;
            document.getElementById('examDescription').value = btn.dataset.description;
            document.getElementById('examInstructions').value = btn.dataset.instructions;
            var modal = getModal(); if (modal) { modal.show(); }
        });
    });

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
