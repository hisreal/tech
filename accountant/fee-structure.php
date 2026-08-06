<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\FinanceService;

$financeService = new FinanceService();

$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');

$sessions = $financeService->sessionsForSelect();
$terms = $financeService->termsForSelect();
$classes = $financeService->classesForSelect();
$sections = $financeService->sectionsForSelect();
$categories = ['Tuition Fee', 'Registration Fee', 'Examination Fee', 'Laboratory Fee', 'Library Fee', 'Sports Fee', 'Development Levy', 'ICT Fee', 'Hostel Fee', 'Transport Fee', 'PTA Levy'];
$statuses = ['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft', 'archived' => 'Archived'];

$filterSession = trim((string) ($_GET['session_id'] ?? ''));
$filterTerm = trim((string) ($_GET['term_id'] ?? ''));
$filterClass = trim((string) ($_GET['class_id'] ?? ''));
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$filterSearch = trim((string) ($_GET['search'] ?? ''));

$allStructures = $financeService->listFeeStructures();
$structures = array_values(array_filter($allStructures, function ($row) use ($filterSession, $filterTerm, $filterClass, $filterStatus, $filterSearch) {
    if ($filterSession !== '' && (string) $row['session_id'] !== $filterSession) { return false; }
    if ($filterTerm !== '' && (string) $row['term_id'] !== $filterTerm) { return false; }
    if ($filterClass !== '' && (string) $row['class_id'] !== $filterClass) { return false; }
    if ($filterStatus !== '' && $row['status'] !== $filterStatus) { return false; }
    if ($filterSearch !== '' && stripos($row['class_name'] . ' ' . $row['category'], $filterSearch) === false) { return false; }
    return true;
}));

function fsMoney($amount) { return '₦' . number_format((float) $amount); }
?>
<?php require_once('includes/fee-structure-styles.php'); ?>
<div class="fee-structure-page">
    <section class="fs-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Fee Structure Management</div>
        <span class="fs-kicker"><i class="fa-solid fa-sliders"></i> Fee Administration</span>
        <h3 class="mt-3 mb-2">Fee Structure Management</h3>
        <p class="text-muted mb-0">Create, manage, and maintain school fee structures for different classes, terms, and academic sessions.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="notice is-visible <?php echo $type === 'error' ? 'error' : 'success'; ?>"><i class="fa-solid fa-circle-info"></i><span><?php echo sms_e($message); ?></span></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-calendar-check"></i></span><h4><?php echo count($sessions); ?></h4><p class="text-muted mb-0">Academic Sessions</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-file-invoice-dollar"></i></span><h4><?php echo count($allStructures); ?></h4><p class="text-muted mb-0">Fee Structures</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-school"></i></span><h4><?php echo count($classes); ?></h4><p class="text-muted mb-0">Classes Covered</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon warning"><i class="fa-solid fa-tags"></i></span><h4><?php echo count($categories); ?></h4><p class="text-muted mb-0">Fee Categories</p></div></div>
    </section>

    <section class="fs-card">
        <h4 class="mb-3">Create Fee Structure</h4>
        <form method="post" action="fee-structure-store.php" class="row g-3">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <div class="col-md-3"><label class="form-label">Academic Session</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><select class="form-select" name="session_id" required><option value="">Select session</option><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo ($old['session_id'] ?? '') === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div><?php if (isset($errors['session_id'])): ?><span class="field-error"><?php echo sms_e($errors['session_id']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Term</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-layer-group"></i></span><select class="form-select" name="term_id" required><option value="">Select term</option><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo ($old['term_id'] ?? '') === (string) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div><?php if (isset($errors['term_id'])): ?><span class="field-error"><?php echo sms_e($errors['term_id']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Class</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-school"></i></span><select class="form-select" id="classInput" name="class_id" required><option value="">Select class</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo ($old['class_id'] ?? '') === (string) $c['id'] ? 'selected' : ''; ?>><?php echo sms_e($c['name']); ?></option><?php endforeach; ?></select></div><?php if (isset($errors['class_id'])): ?><span class="field-error"><?php echo sms_e($errors['class_id']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Section</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-users-rectangle"></i></span><select class="form-select" id="sectionInput" name="section_id"><option value="">Whole Class</option><?php foreach ($sections as $sec): ?><option value="<?php echo (int) $sec['id']; ?>" data-class="<?php echo (int) $sec['class_id']; ?>" <?php echo ($old['section_id'] ?? '') === (string) $sec['id'] ? 'selected' : ''; ?>><?php echo sms_e($sec['name']); ?></option><?php endforeach; ?></select></div></div>
            <div class="col-md-4"><label class="form-label">Fee Category</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-tag"></i></span><input class="form-control" name="category" list="categoryList" placeholder="Select or type a category" required value="<?php echo sms_e($old['category'] ?? ''); ?>"><datalist id="categoryList"><?php foreach ($categories as $cat): ?><option value="<?php echo sms_e($cat); ?>"><?php endforeach; ?></datalist></div><?php if (isset($errors['category'])): ?><span class="field-error"><?php echo sms_e($errors['category']); ?></span><?php endif; ?></div>
            <div class="col-md-2"><label class="form-label">Fee Amount</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" min="1" step="0.01" class="form-control" name="amount" required value="<?php echo sms_e($old['amount'] ?? ''); ?>"></div><?php if (isset($errors['amount'])): ?><span class="field-error"><?php echo sms_e($errors['amount']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Status</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-toggle-on"></i></span><select class="form-select" name="status"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo ($old['status'] ?? 'active') === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div></div>
            <div class="col-12"><div class="action-row"><button type="submit" class="btn fs-btn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Fee Structure</button><a href="fee-structure.php" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left me-2"></i>Reset</a></div></div>
        </form>
    </section>

    <section class="fs-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-2"><label class="form-label">Session</label><select class="form-select" name="session_id" style="padding-left:12px;"><option value="">All</option><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $filterSession === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Term</label><select class="form-select" name="term_id" style="padding-left:12px;"><option value="">All</option><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>" <?php echo $filterTerm === (string) $t['id'] ? 'selected' : ''; ?>><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Class</label><select class="form-select" name="class_id" style="padding-left:12px;"><option value="">All</option><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>" <?php echo $filterClass === (string) $c['id'] ? 'selected' : ''; ?>><?php echo sms_e($c['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><option value="">All</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $filterStatus === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Search</label><input class="form-control" name="search" style="padding-left:12px;" placeholder="Class or category" value="<?php echo sms_e($filterSearch); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn fs-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
        </form>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Fee Structure Table</h4><p class="text-muted mb-0"><?php echo count($structures); ?> record(s) found.</p></div><div class="bulk-actions"><a class="btn btn-outline-secondary" href="fee-structure.php"><i class="fa-solid fa-rotate-left me-2"></i>Reset Filters</a><button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button></div></div>
        <div class="table-scroll">
            <table class="table structure-table align-middle">
                <thead><tr><th>Session</th><th>Term</th><th>Class</th><th>Section</th><th>Fee Category</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($structures as $row): ?>
                        <tr>
                            <td><?php echo sms_e($row['session_name']); ?></td>
                            <td><?php echo sms_e($row['term_name']); ?></td>
                            <td><?php echo sms_e($row['class_name']); ?></td>
                            <td><?php echo sms_e($row['section_name'] ?? 'Whole Class'); ?></td>
                            <td><?php echo sms_e($row['category']); ?></td>
                            <td><?php echo fsMoney($row['amount']); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($row['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($row['status'])); ?></span></td>
                            <td>
                                <div class="action-row">
                                    <button type="button" class="btn btn-sm btn-outline-success edit-row" title="Edit"
                                        data-id="<?php echo (int) $row['id']; ?>" data-session="<?php echo (int) $row['session_id']; ?>" data-term="<?php echo (int) $row['term_id']; ?>"
                                        data-class="<?php echo (int) $row['class_id']; ?>" data-section="<?php echo (int) ($row['section_id'] ?? 0); ?>"
                                        data-category="<?php echo sms_e($row['category']); ?>" data-amount="<?php echo sms_e((string) $row['amount']); ?>" data-status="<?php echo sms_e($row['status']); ?>">
                                        <i class="fa-solid fa-pen"></i> Edit</button>
                                    <form method="post" action="fee-structure-status.php" style="display:inline">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $row['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-toggle-on"></i> Toggle</button>
                                    </form>
                                    <form method="post" action="fee-structure-delete.php" style="display:inline" onsubmit="return confirm('Delete this fee structure?');">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$structures): ?><tr><td colspan="8" class="text-center text-muted fw-bold py-4">No fee structures found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Edit fee structure modal. -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" action="fee-structure-store.php">
                <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white">Edit Fee Structure</h5><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Session</label><select class="form-select" name="session_id" id="editSession" style="padding-left:12px;"><?php foreach ($sessions as $s): ?><option value="<?php echo (int) $s['id']; ?>"><?php echo sms_e($s['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Term</label><select class="form-select" name="term_id" id="editTerm" style="padding-left:12px;"><?php foreach ($terms as $t): ?><option value="<?php echo (int) $t['id']; ?>"><?php echo sms_e($t['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="class_id" id="editClass" style="padding-left:12px;"><?php foreach ($classes as $c): ?><option value="<?php echo (int) $c['id']; ?>"><?php echo sms_e($c['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Section</label><select class="form-select" name="section_id" id="editSectionSel" style="padding-left:12px;"><option value="">Whole Class</option><?php foreach ($sections as $sec): ?><option value="<?php echo (int) $sec['id']; ?>" data-class="<?php echo (int) $sec['class_id']; ?>"><?php echo sms_e($sec['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Fee Category</label><input class="form-control" name="category" id="editCategory" style="padding-left:12px;" required></div>
                        <div class="col-md-3"><label class="form-label">Amount</label><input type="number" min="1" step="0.01" class="form-control" name="amount" id="editAmount" style="padding-left:12px;" required></div>
                        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status" id="editStatus" style="padding-left:12px;"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>"><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn fs-btn">Update Fee Structure</button></div>
            </form>
        </div>
    </div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    var modalEl = document.getElementById('editModal');
    function getModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }
    document.querySelectorAll('.edit-row').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editSession').value = btn.dataset.session;
            document.getElementById('editTerm').value = btn.dataset.term;
            document.getElementById('editClass').value = btn.dataset.class;
            document.getElementById('editSectionSel').value = btn.dataset.section || '';
            document.getElementById('editCategory').value = btn.dataset.category;
            document.getElementById('editAmount').value = btn.dataset.amount;
            document.getElementById('editStatus').value = btn.dataset.status;
            var modal = getModal();
            if (modal) { modal.show(); }
        });
    });
    function bindSectionFilter(classSelectId, sectionSelectId){
        var classSelect = document.getElementById(classSelectId);
        var sectionSelect = document.getElementById(sectionSelectId);
        if (!classSelect || !sectionSelect) { return; }
        function filter(){
            var selected = classSelect.value;
            Array.prototype.forEach.call(sectionSelect.options, function(option){
                if (!option.value) { return; }
                option.hidden = selected !== '' && option.dataset.class !== selected;
            });
        }
        classSelect.addEventListener('change', filter);
        filter();
    }
    bindSectionFilter('classInput', 'sectionInput');
    bindSectionFilter('editClass', 'editSectionSel');
    <?php if ($errors !== []): ?>
    window.addEventListener('load', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });
    <?php endif; ?>
})();
</script>

<?php require_once('includes/footer.php'); ?>
