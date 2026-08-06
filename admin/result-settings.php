<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\ResultService;

$resultService = new ResultService();
require_once('includes/header.php');
require_once('includes/result-page-helper.php');
require_once('includes/result-module-styles.php');

$flashMessages = sms_flash();
$errors = Session::errors();
$old = Session::oldAll();

$grades = $resultService->listGrades();
$remarks = $resultService->listRemarks();
$generalSettings = $resultService->generalSettings();

$cards = [
    ['title' => 'Grade Rules', 'value' => count($grades), 'description' => 'Configured score ranges', 'icon' => 'fa-sliders', 'color' => 'success'],
    ['title' => 'Remark Rules', 'value' => count($remarks), 'description' => 'Reusable result remarks', 'icon' => 'fa-comment-dots', 'color' => 'blue'],
    ['title' => 'Pass Mark', 'value' => $generalSettings['pass_mark'] . '%', 'description' => 'Default promotion benchmark', 'icon' => 'fa-check-circle', 'color' => 'success'],
    ['title' => 'Auto Lock', 'value' => $generalSettings['auto_lock_published_results'] ? 'On' : 'Off', 'description' => 'Published result protection', 'icon' => 'fa-lock', 'color' => 'warning'],
];
?>
<div class="admin-result-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Result Management <i class="fa-solid fa-angle-right mx-1"></i> Result Settings</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-gears"></i> Result Settings</span>
                <h3 class="mt-3 mb-2">Result Settings</h3>
                <p class="text-muted mb-0">Manage grading, remarks, pass mark, publication, position, and report-card display preferences.</p>
            </div>
            <button class="module-btn btn-primary-soft" form="generalResultSettings" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Settings</button>
        </div>
    </section>

    <?php sms_result_render_cards($cards); ?>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Grade Settings</h4><p class="text-muted mb-0">Add, edit, and delete grade score ranges.</p></div>
            <button class="module-btn btn-primary-soft" id="addGradeBtn" type="button"><i class="fa-solid fa-plus"></i> Add Grade</button>
        </div>
        <div class="table-shell"><table class="table result-table">
            <thead><tr><th>Grade</th><th>Min</th><th>Max</th><th>Remark</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($grades as $grade): ?>
                <tr>
                    <td><?php echo sms_e($grade['grade']); ?></td>
                    <td><?php echo sms_e($grade['min_score']); ?></td>
                    <td><?php echo sms_e($grade['max_score']); ?></td>
                    <td><?php echo sms_e($grade['remark']); ?></td>
                    <td><?php echo sms_result_render_badge(ucfirst($grade['status'])); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="action-btn edit-grade-btn" type="button" title="Edit Grade"
                                data-id="<?php echo (int) $grade['id']; ?>" data-grade="<?php echo sms_e($grade['grade']); ?>"
                                data-min="<?php echo sms_e($grade['min_score']); ?>" data-max="<?php echo sms_e($grade['max_score']); ?>"
                                data-remark="<?php echo sms_e($grade['remark']); ?>" data-status="<?php echo sms_e($grade['status']); ?>">
                                <i class="fa-solid fa-pen"></i></button>
                            <form method="post" action="grade-delete.php" onsubmit="return confirm('Delete this grade?');" style="display:inline">
                                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $grade['id']; ?>">
                                <button class="action-btn" type="submit" title="Delete Grade"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$grades): ?><tr><td colspan="6" class="text-center text-muted py-4">No grades configured yet.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>

    <section class="module-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div><h4 class="mb-1">Remarks Settings</h4><p class="text-muted mb-0">Add, edit, and delete teacher/principal/general result remarks.</p></div>
            <button class="module-btn btn-primary-soft" id="addRemarkBtn" type="button"><i class="fa-solid fa-plus"></i> Add Remark</button>
        </div>
        <div class="table-shell"><table class="table result-table">
            <thead><tr><th>Category</th><th>Min Avg</th><th>Max Avg</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($remarks as $remark): ?>
                <tr>
                    <td><?php echo sms_e(ucfirst($remark['category'])); ?></td>
                    <td><?php echo sms_e($remark['min_average'] ?? '-'); ?></td>
                    <td><?php echo sms_e($remark['max_average'] ?? '-'); ?></td>
                    <td><?php echo sms_e($remark['remark']); ?></td>
                    <td><?php echo sms_result_render_badge(ucfirst($remark['status'])); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="action-btn edit-remark-btn" type="button" title="Edit Remark"
                                data-id="<?php echo (int) $remark['id']; ?>" data-category="<?php echo sms_e($remark['category']); ?>"
                                data-min="<?php echo sms_e((string) $remark['min_average']); ?>" data-max="<?php echo sms_e((string) $remark['max_average']); ?>"
                                data-remark="<?php echo sms_e($remark['remark']); ?>" data-status="<?php echo sms_e($remark['status']); ?>">
                                <i class="fa-solid fa-pen"></i></button>
                            <form method="post" action="remark-delete.php" onsubmit="return confirm('Delete this remark?');" style="display:inline">
                                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $remark['id']; ?>">
                                <button class="action-btn" type="submit" title="Delete Remark"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$remarks): ?><tr><td colspan="6" class="text-center text-muted py-4">No remarks configured yet.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>

    <section class="module-card">
        <h4>General Settings</h4>
        <form id="generalResultSettings" method="post" action="result-settings-save.php">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <div class="filter-grid">
                <div><label>Pass Mark (%)</label><input class="form-control" type="number" min="0" max="100" name="pass_mark" value="<?php echo sms_e((string) ($old['pass_mark'] ?? $generalSettings['pass_mark'])); ?>"></div>
                <div><label>Enable Position Calculation</label><select class="form-select" name="enable_position_calculation"><option value="1" <?php echo $generalSettings['enable_position_calculation'] ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo !$generalSettings['enable_position_calculation'] ? 'selected' : ''; ?>>Disabled</option></select></div>
                <div><label>Show Position on Report Card</label><select class="form-select" name="show_position_on_report_card"><option value="1" <?php echo $generalSettings['show_position_on_report_card'] ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo !$generalSettings['show_position_on_report_card'] ? 'selected' : ''; ?>>No</option></select></div>
                <div><label>Show Average</label><select class="form-select" name="show_average"><option value="1" <?php echo $generalSettings['show_average'] ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo !$generalSettings['show_average'] ? 'selected' : ''; ?>>No</option></select></div>
                <div><label>Auto Publish Results</label><select class="form-select" name="auto_publish_results"><option value="0" <?php echo !$generalSettings['auto_publish_results'] ? 'selected' : ''; ?>>No</option><option value="1" <?php echo $generalSettings['auto_publish_results'] ? 'selected' : ''; ?>>Yes</option></select></div>
                <div><label>Auto Lock Published Results</label><select class="form-select" name="auto_lock_published_results"><option value="1" <?php echo $generalSettings['auto_lock_published_results'] ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo !$generalSettings['auto_lock_published_results'] ? 'selected' : ''; ?>>No</option></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit">Save Settings</button><a class="module-btn btn-muted-soft" href="result-settings.php">Reset</a></div>
            </div>
        </form>
    </section>

    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="grade-store.php" id="gradeForm">
                <div class="modal-header"><h5 class="modal-title" id="gradeModalTitle">Add Grade</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="gradeId">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <div class="form-grid">
                        <div><label>Grade</label><input class="form-control" name="grade" id="gradeLabel" required maxlength="10"></div>
                        <div><label>Min Score</label><input class="form-control" name="min_score" id="gradeMin" type="number" step="0.01" min="0" max="100" required></div>
                        <div><label>Max Score</label><input class="form-control" name="max_score" id="gradeMax" type="number" step="0.01" min="0" max="100" required></div>
                        <div><label>Status</label><select class="form-select" name="status" id="gradeStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="full"><label>Remark</label><input class="form-control" name="remark" id="gradeRemark" required></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="module-btn btn-muted-soft" data-bs-dismiss="modal" type="button">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Grade</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="remarkModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="remark-store.php" id="remarkForm">
                <div class="modal-header"><h5 class="modal-title" id="remarkModalTitle">Add Remark</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="remarkId">
                    <input type="hidden" name="redirect_query" value="<?php echo sms_e(http_build_query($_GET)); ?>">
                    <div class="form-grid">
                        <div><label>Category</label><select class="form-select" name="category" id="remarkCategory"><option value="teacher">Teacher</option><option value="principal">Principal</option><option value="general">General</option></select></div>
                        <div><label>Status</label><select class="form-select" name="status" id="remarkStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div><label>Min Average</label><input class="form-control" name="min_average" id="remarkMin" type="number" step="0.01" min="0" max="100"></div>
                        <div><label>Max Average</label><input class="form-control" name="max_average" id="remarkMax" type="number" step="0.01" min="0" max="100"></div>
                        <div class="full"><label>Message</label><textarea class="form-control" name="remark" id="remarkMessage" required></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="module-btn btn-muted-soft" data-bs-dismiss="modal" type="button">Cancel</button><button class="module-btn btn-primary-soft" type="submit">Save Remark</button></div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
<script data-cfasync="false" type="text/javascript">
(function(){
    var gradeModalEl = document.getElementById('gradeModal');
    function getGradeModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(gradeModalEl) : null; }
    var gradeForm = document.getElementById('gradeForm');
    var gradeTitle = document.getElementById('gradeModalTitle');

    document.getElementById('addGradeBtn').addEventListener('click', function(){
        gradeTitle.textContent = 'Add Grade';
        gradeForm.reset();
        document.getElementById('gradeId').value = '';
        var modal = getGradeModal(); if (modal) { modal.show(); }
    });

    document.querySelectorAll('.edit-grade-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            gradeTitle.textContent = 'Edit Grade';
            document.getElementById('gradeId').value = btn.dataset.id;
            document.getElementById('gradeLabel').value = btn.dataset.grade;
            document.getElementById('gradeMin').value = btn.dataset.min;
            document.getElementById('gradeMax').value = btn.dataset.max;
            document.getElementById('gradeRemark').value = btn.dataset.remark;
            document.getElementById('gradeStatus').value = btn.dataset.status;
            var modal = getGradeModal(); if (modal) { modal.show(); }
        });
    });

    var remarkModalEl = document.getElementById('remarkModal');
    function getRemarkModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(remarkModalEl) : null; }
    var remarkForm = document.getElementById('remarkForm');
    var remarkTitle = document.getElementById('remarkModalTitle');

    document.getElementById('addRemarkBtn').addEventListener('click', function(){
        remarkTitle.textContent = 'Add Remark';
        remarkForm.reset();
        document.getElementById('remarkId').value = '';
        var modal = getRemarkModal(); if (modal) { modal.show(); }
    });

    document.querySelectorAll('.edit-remark-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            remarkTitle.textContent = 'Edit Remark';
            document.getElementById('remarkId').value = btn.dataset.id;
            document.getElementById('remarkCategory').value = btn.dataset.category;
            document.getElementById('remarkMin').value = btn.dataset.min === 'null' ? '' : btn.dataset.min;
            document.getElementById('remarkMax').value = btn.dataset.max === 'null' ? '' : btn.dataset.max;
            document.getElementById('remarkMessage').value = btn.dataset.remark;
            document.getElementById('remarkStatus').value = btn.dataset.status;
            var modal = getRemarkModal(); if (modal) { modal.show(); }
        });
    });
})();
</script>
<?php require_once('includes/footer.php'); ?>
