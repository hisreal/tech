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

$examId = (int) ($_GET['exam_id'] ?? 0);
$examList = $cbtService->listExams([], 1, 200)['data'];
$exam = $examId ? $cbtService->findExam($examId) : null;
$questions = $examId ? $cbtService->listQuestions($examId) : [];
$locked = $exam && in_array($exam['status'], ['active', 'completed'], true);

$totalMarks = array_sum(array_column($questions, 'mark'));

$cards = [
    ['title' => 'Questions', 'value' => count($questions), 'description' => 'Added to this exam', 'icon' => 'fa-list-ol', 'color' => 'success'],
    ['title' => 'Total Marks', 'value' => (int) $totalMarks, 'description' => 'Sum of all question marks', 'icon' => 'fa-calculator', 'color' => 'blue'],
    ['title' => 'Exam Status', 'value' => $exam ? ucfirst($exam['status']) : '-', 'description' => 'Current workflow stage', 'icon' => 'fa-diagram-project', 'color' => 'warning'],
    ['title' => 'Duration', 'value' => $exam ? $exam['duration_minutes'] . ' mins' : '-', 'description' => 'Time allowed', 'icon' => 'fa-clock', 'color' => 'success'],
];
?>
<div class="admin-cbt-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> CBT Management <i class="fa-solid fa-angle-right mx-1"></i> Questions</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-list-ol"></i> Questions</span>
                <h3 class="mt-3 mb-2">Question Bank</h3>
                <p class="text-muted mb-0">Add, edit, and delete multiple-choice questions for a CBT exam.</p>
            </div>
            <a class="module-btn btn-outline-soft" href="cbt-exams.php"><i class="fa-solid fa-arrow-left"></i> Back to Exams</a>
        </div>
    </section>

    <section class="module-card">
        <h4>Select Exam</h4>
        <form method="get">
            <div class="filter-grid">
                <div class="full"><label>CBT Exam</label><select class="form-select" name="exam_id" required>
                    <option value="">Select an exam</option>
                    <?php foreach ($examList as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $examId === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sms_e($item['title'] . ' (' . $item['subject_name'] . ' - ' . $item['class_name'] . ')'); ?></option><?php endforeach; ?>
                </select></div>
                <div class="d-flex align-items-end"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Load Questions</button></div>
            </div>
        </form>
    </section>

    <?php if ($exam): ?>
        <?php sms_cbt_render_cards($cards); ?>

        <?php if ($locked): ?>
            <div class="alert alert-warning">This exam is <?php echo sms_e($exam['status']); ?> and its questions can no longer be changed. Deactivate the exam from the Exams page to edit questions again.</div>
        <?php endif; ?>

        <section class="module-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div><h4 class="mb-1"><?php echo sms_e($exam['title']); ?></h4><p class="text-muted mb-0"><?php echo sms_e($exam['subject_name']); ?> &middot; <?php echo sms_e($exam['class_name'] . ($exam['section_name'] ? ' - ' . $exam['section_name'] : '')); ?></p></div>
                <?php if (!$locked): ?><button class="module-btn btn-primary-soft" id="addQuestionBtn" type="button"><i class="fa-solid fa-plus"></i> Add Question</button><?php endif; ?>
            </div>
            <div class="table-shell"><table class="table cbt-table">
                <thead><tr><th>#</th><th>Question</th><th>Correct Option</th><th>Mark</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($questions as $i => $question): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo sms_e($question['question_text']); ?></td>
                        <td><span class="status-badge status-active"><?php echo sms_e($question['correct_option']); ?></span></td>
                        <td><?php echo sms_e($question['mark']); ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if (!$locked): ?>
                                <button class="action-btn edit-question-btn" type="button" title="Edit Question"
                                    data-id="<?php echo (int) $question['id']; ?>" data-text="<?php echo sms_e($question['question_text']); ?>"
                                    data-a="<?php echo sms_e($question['option_a']); ?>" data-b="<?php echo sms_e($question['option_b']); ?>"
                                    data-c="<?php echo sms_e($question['option_c']); ?>" data-d="<?php echo sms_e($question['option_d']); ?>"
                                    data-correct="<?php echo sms_e($question['correct_option']); ?>" data-mark="<?php echo sms_e($question['mark']); ?>">
                                    <i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="cbt-question-delete.php" onsubmit="return confirm('Delete this question?');" style="display:inline">
                                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $question['id']; ?>">
                                    <input type="hidden" name="exam_id" value="<?php echo (int) $examId; ?>">
                                    <button class="action-btn" type="submit" title="Delete Question"><i class="fa-solid fa-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$questions): ?><tr><td colspan="5" class="text-center text-muted py-4">No questions added yet.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </section>

        <div class="modal fade" id="questionModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content" method="post" action="cbt-question-store.php" id="questionForm">
                    <div class="modal-header"><h5 class="modal-title" id="questionModalTitle">Add Question</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                        <input type="hidden" name="id" id="questionId">
                        <input type="hidden" name="exam_id" value="<?php echo (int) $examId; ?>">
                        <div class="form-grid">
                            <div class="full"><label>Question Text</label><textarea class="form-control" name="question_text" id="questionText" required></textarea></div>
                            <div><label>Option A</label><input class="form-control" name="option_a" id="optionA" required></div>
                            <div><label>Option B</label><input class="form-control" name="option_b" id="optionB" required></div>
                            <div><label>Option C</label><input class="form-control" name="option_c" id="optionC" required></div>
                            <div><label>Option D</label><input class="form-control" name="option_d" id="optionD" required></div>
                            <div><label>Correct Option</label><select class="form-select" name="correct_option" id="questionCorrect" required><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option></select></div>
                            <div><label>Mark</label><input class="form-control" type="number" step="0.5" min="0.5" name="mark" id="questionMark" value="1"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="module-btn btn-muted-soft" data-bs-dismiss="modal" type="button">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Question</button></div>
                </form>
            </div>
        </div>
    <?php elseif ($examId): ?>
        <section class="module-card"><p class="text-muted fw-bold mb-0">Exam not found.</p></section>
    <?php endif; ?>
</div></div></div>
<?php if ($exam && !$locked): ?>
<script data-cfasync="false" type="text/javascript">
(function () {
    var modalEl = document.getElementById('questionModal');
    function getModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }
    var form = document.getElementById('questionForm');
    var title = document.getElementById('questionModalTitle');

    document.getElementById('addQuestionBtn').addEventListener('click', function () {
        title.textContent = 'Add Question';
        form.reset();
        document.getElementById('questionId').value = '';
        document.getElementById('questionMark').value = '1';
        var modal = getModal(); if (modal) { modal.show(); }
    });

    document.querySelectorAll('.edit-question-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            title.textContent = 'Edit Question';
            document.getElementById('questionId').value = btn.dataset.id;
            document.getElementById('questionText').value = btn.dataset.text;
            document.getElementById('optionA').value = btn.dataset.a;
            document.getElementById('optionB').value = btn.dataset.b;
            document.getElementById('optionC').value = btn.dataset.c;
            document.getElementById('optionD').value = btn.dataset.d;
            document.getElementById('questionCorrect').value = btn.dataset.correct;
            document.getElementById('questionMark').value = btn.dataset.mark;
            var modal = getModal(); if (modal) { modal.show(); }
        });
    });
})();
</script>
<?php endif; ?>
<?php require_once('includes/footer.php'); ?>
