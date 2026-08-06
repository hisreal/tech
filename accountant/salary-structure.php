<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Core\Session;
use App\Services\PayrollService;

$payrollService = new PayrollService();

$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');
require_once('includes/payroll-styles.php');

$staffList = $payrollService->staffForSelect();
$statuses = ['active' => 'Active', 'inactive' => 'Inactive'];

$filterStaff = trim((string) ($_GET['staff_id'] ?? ''));
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$filterSearch = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $payrollService->listSalaryStructures(['staff_id' => $filterStaff, 'status' => $filterStatus, 'search' => $filterSearch], $page, 10);
$activeCount = count(array_filter($payrollService->listSalaryStructures(['status' => 'active'], 1, 500)['data'], static fn ($r) => $r['status'] === 'active'));

function prMoney($amount) { return '₦' . number_format((float) $amount, 2); }
function prSalQuery(array $overrides = []): string { return 'salary-structure.php?' . http_build_query(array_merge($_GET, $overrides)); }
?>
<div class="payroll-page">
    <section class="pr-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payroll Management <i class="fa-solid fa-chevron-right mx-2"></i> Salary Structure</div>
        <span class="pr-kicker"><i class="fa-solid fa-sack-dollar"></i> Payroll Administration</span>
        <h3 class="mt-3 mb-2">Salary Structure</h3>
        <p class="text-muted mb-0">Set and manage each staff member's basic salary. Only one active record applies per staff member at a time.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="notice is-visible <?php echo $type === 'error' ? 'error' : 'success'; ?>"><i class="fa-solid fa-circle-info"></i><span><?php echo sms_e($message); ?></span></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-users"></i></span><h4><?php echo count($staffList); ?></h4><p class="text-muted mb-0">Active Staff</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-file-invoice-dollar"></i></span><h4><?php echo (int) $result['meta']['total']; ?></h4><p class="text-muted mb-0">Salary Records</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-check-circle"></i></span><h4><?php echo $activeCount; ?></h4><p class="text-muted mb-0">Active Structures</p></div></div>
    </section>

    <section class="pr-card">
        <h4 class="mb-3">Set Salary Structure</h4>
        <form method="post" action="salary-store.php" class="row g-3">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <div class="col-md-4"><label class="form-label">Staff Member</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-user-tie"></i></span><select class="form-select" name="staff_id" required><option value="">Select staff</option><?php foreach ($staffList as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo ($old['staff_id'] ?? '') === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['staff_no'] . ')'); ?></option><?php endforeach; ?></select></div><?php if (isset($errors['staff_id'])): ?><span class="field-error"><?php echo sms_e($errors['staff_id']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Basic Salary</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-naira-sign"></i></span><input type="number" min="0" step="0.01" class="form-control" name="basic_salary" required value="<?php echo sms_e($old['basic_salary'] ?? ''); ?>"></div><?php if (isset($errors['basic_salary'])): ?><span class="field-error"><?php echo sms_e($errors['basic_salary']); ?></span><?php endif; ?></div>
            <div class="col-md-3"><label class="form-label">Effective Date</label><div class="field-wrap"><span class="field-icon"><i class="fa-solid fa-calendar"></i></span><input type="date" class="form-control" name="effective_date" required value="<?php echo sms_e($old['effective_date'] ?? date('Y-m-d')); ?>"></div><?php if (isset($errors['effective_date'])): ?><span class="field-error"><?php echo sms_e($errors['effective_date']); ?></span><?php endif; ?></div>
            <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo ($old['status'] ?? 'active') === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><div class="action-row"><button type="submit" class="btn pr-btn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Salary Structure</button></div></div>
        </form>
    </section>

    <section class="pr-card">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Staff</label><select class="form-select" name="staff_id" style="padding-left:12px;"><option value="">All</option><?php foreach ($staffList as $s): ?><option value="<?php echo (int) $s['id']; ?>" <?php echo $filterStaff === (string) $s['id'] ? 'selected' : ''; ?>><?php echo sms_e($s['first_name'] . ' ' . $s['last_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><option value="">All</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $filterStatus === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Search</label><input class="form-control" name="search" style="padding-left:12px;" placeholder="Staff name or number" value="<?php echo sms_e($filterSearch); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn pr-btn w-100"><i class="fa-solid fa-search me-2"></i>Search</button></div>
        </form>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Salary Records</h4><p class="text-muted mb-0"><?php echo (int) $result['meta']['total']; ?> record(s) found.</p></div><a class="btn btn-outline-secondary" href="salary-structure.php"><i class="fa-solid fa-rotate-left me-2"></i>Reset Filters</a></div>
        <div class="table-scroll">
            <table class="table pr-table align-middle">
                <thead><tr><th>Staff</th><th>Staff No.</th><th>Department</th><th>Basic Salary</th><th>Effective Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($result['data'] as $row): ?>
                        <tr>
                            <td><?php echo sms_e($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo sms_e($row['staff_no']); ?></td>
                            <td><?php echo sms_e($row['department_name'] ?? '-'); ?></td>
                            <td><?php echo prMoney($row['basic_salary']); ?></td>
                            <td><?php echo sms_e($row['effective_date']); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($row['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($row['status'])); ?></span></td>
                            <td>
                                <div class="action-row">
                                    <button type="button" class="btn btn-sm btn-outline-success edit-row" title="Edit"
                                        data-id="<?php echo (int) $row['id']; ?>" data-staff="<?php echo (int) $row['staff_id']; ?>"
                                        data-salary="<?php echo sms_e((string) $row['basic_salary']); ?>" data-date="<?php echo sms_e($row['effective_date']); ?>" data-status="<?php echo sms_e($row['status']); ?>">
                                        <i class="fa-solid fa-pen"></i> Edit</button>
                                    <form method="post" action="salary-delete.php" style="display:inline" onsubmit="return confirm('Delete this salary record?');">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$result['data']): ?><tr><td colspan="7" class="text-center text-muted fw-bold py-4">No salary records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($result['meta']['last_page'] > 1): ?>
        <div class="toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted fw-bold"><?php echo (int) $result['meta']['total']; ?> record(s) &middot; page <?php echo (int) $result['meta']['page']; ?> of <?php echo (int) $result['meta']['last_page']; ?></span>
            <div class="d-flex gap-2 flex-wrap">
                <?php for ($p = 1; $p <= $result['meta']['last_page']; $p++): ?>
                    <a class="btn btn-sm <?php echo $p === (int) $result['meta']['page'] ? 'pr-btn' : 'btn-outline-secondary'; ?>" href="<?php echo sms_e(prSalQuery(['page' => $p])); ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="salary-store.php">
                <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white">Edit Salary Structure</h5><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="staff_id" id="editStaffHidden">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Staff Member</label><select class="form-select" id="editStaff" style="padding-left:12px;" disabled><?php foreach ($staffList as $s): ?><option value="<?php echo (int) $s['id']; ?>"><?php echo sms_e($s['first_name'] . ' ' . $s['last_name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Basic Salary</label><input type="number" min="0" step="0.01" class="form-control" name="basic_salary" id="editSalary" style="padding-left:12px;" required></div>
                        <div class="col-md-6"><label class="form-label">Effective Date</label><input type="date" class="form-control" name="effective_date" id="editDate" style="padding-left:12px;" required></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status" id="editStatus" style="padding-left:12px;"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo sms_e($value); ?>"><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn pr-btn">Update</button></div>
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
            document.getElementById('editStaffHidden').value = btn.dataset.staff;
            document.getElementById('editStaff').value = btn.dataset.staff;
            document.getElementById('editSalary').value = btn.dataset.salary;
            document.getElementById('editDate').value = btn.dataset.date;
            document.getElementById('editStatus').value = btn.dataset.status;
            var modal = getModal();
            if (modal) { modal.show(); }
        });
    });
    <?php if ($errors !== []): ?>
    window.addEventListener('load', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });
    <?php endif; ?>
})();
</script>
<?php require_once('includes/footer.php'); ?>
