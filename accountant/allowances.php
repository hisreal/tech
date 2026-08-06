<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Services\PayrollService;

$payrollService = new PayrollService();
require_once('includes/header.php');
require_once('includes/payroll-styles.php');

$staffList = $payrollService->staffForSelect();
$allowanceTypes = $payrollService->listAllowanceTypes();
$assignments = $payrollService->allStaffAllowances();
$totalAssigned = array_sum(array_map(static fn ($a) => $a['status'] === 'active' ? (float) $a['amount'] : 0, $assignments));

function prMoney2($amount) { return '₦' . number_format((float) $amount, 2); }
?>
<div class="payroll-page">
    <section class="pr-hero">
        <div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Payroll Management <i class="fa-solid fa-chevron-right mx-2"></i> Allowances</div>
        <span class="pr-kicker"><i class="fa-solid fa-hand-holding-dollar"></i> Payroll Administration</span>
        <h3 class="mt-3 mb-2">Allowances</h3>
        <p class="text-muted mb-0">Manage reusable allowance types and assign them to individual staff members.</p>
    </section>

    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="notice is-visible <?php echo $type === 'error' ? 'error' : 'success'; ?>"><i class="fa-solid fa-circle-info"></i><span><?php echo sms_e($message); ?></span></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon"><i class="fa-solid fa-tags"></i></span><h4><?php echo count($allowanceTypes); ?></h4><p class="text-muted mb-0">Allowance Types</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon success"><i class="fa-solid fa-user-check"></i></span><h4><?php echo count($assignments); ?></h4><p class="text-muted mb-0">Staff Assignments</p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="summary-card"><span class="summary-icon blue"><i class="fa-solid fa-sack-dollar"></i></span><h4><?php echo prMoney2($totalAssigned); ?></h4><p class="text-muted mb-0">Total Active Allowances / Month</p></div></div>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Allowance Types</h4><p class="text-muted mb-0">Reusable allowance definitions.</p></div><button type="button" class="btn pr-btn" id="addTypeBtn"><i class="fa-solid fa-plus me-2"></i>Add Allowance Type</button></div>
        <div class="table-scroll">
            <table class="table pr-table align-middle">
                <thead><tr><th>Name</th><th>Calculation</th><th>Default Amount</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($allowanceTypes as $type): ?>
                        <tr>
                            <td><?php echo sms_e($type['name']); ?></td>
                            <td><?php echo sms_e(ucfirst($type['calculation_type'])); ?></td>
                            <td><?php echo prMoney2($type['default_amount']); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($type['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($type['status'])); ?></span></td>
                            <td>
                                <div class="action-row">
                                    <button type="button" class="btn btn-sm btn-outline-success edit-type-btn" title="Edit"
                                        data-id="<?php echo (int) $type['id']; ?>" data-name="<?php echo sms_e($type['name']); ?>"
                                        data-calc="<?php echo sms_e($type['calculation_type']); ?>" data-amount="<?php echo sms_e((string) $type['default_amount']); ?>" data-status="<?php echo sms_e($type['status']); ?>">
                                        <i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="allowance-type-delete.php" style="display:inline" onsubmit="return confirm('Delete this allowance type?');">
                                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $type['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$allowanceTypes): ?><tr><td colspan="5" class="text-center text-muted fw-bold py-4">No allowance types configured yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="table-card">
        <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3"><div><h4 class="mb-1">Staff Allowance Assignments</h4><p class="text-muted mb-0">Assign an allowance type to a specific staff member.</p></div><button type="button" class="btn pr-btn" id="addAssignBtn"><i class="fa-solid fa-plus me-2"></i>Assign Allowance</button></div>
        <div class="table-scroll">
            <table class="table pr-table align-middle">
                <thead><tr><th>Staff</th><th>Staff No.</th><th>Allowance</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td><?php echo sms_e($a['first_name'] . ' ' . $a['last_name']); ?></td>
                            <td><?php echo sms_e($a['staff_no']); ?></td>
                            <td><?php echo sms_e($a['name']); ?></td>
                            <td><?php echo prMoney2($a['amount']); ?></td>
                            <td><span class="status-badge status-<?php echo sms_e($a['status']); ?>"><i class="fa-solid fa-circle"></i><?php echo sms_e(ucfirst($a['status'])); ?></span></td>
                            <td>
                                <form method="post" action="staff-allowance-delete.php" style="display:inline" onsubmit="return confirm('Remove this allowance from the staff member?');">
                                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$assignments): ?><tr><td colspan="6" class="text-center text-muted fw-bold py-4">No allowances assigned yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="modal fade" id="typeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="allowance-type-store.php">
                <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white" id="typeModalTitle">Add Allowance Type</h5><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="id" id="typeId">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Name</label><input class="form-control" name="name" id="typeName" style="padding-left:12px;" required></div>
                        <div class="col-md-6"><label class="form-label">Calculation Type</label><select class="form-select" name="calculation_type" id="typeCalc" style="padding-left:12px;"><option value="fixed">Fixed Amount</option><option value="percentage">Percentage of Basic</option></select></div>
                        <div class="col-md-6"><label class="form-label">Default Amount</label><input type="number" min="0" step="0.01" class="form-control" name="default_amount" id="typeAmount" style="padding-left:12px;" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status" id="typeStatus" style="padding-left:12px;"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn pr-btn">Save Type</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="staff-allowance-store.php">
                <div class="modal-header" style="background:linear-gradient(135deg,#0f766e,#115e59);color:#fff;"><h5 class="modal-title text-white">Assign Allowance</h5><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Staff Member</label><select class="form-select" name="staff_id" style="padding-left:12px;" required><option value="">Select staff</option><?php foreach ($staffList as $s): ?><option value="<?php echo (int) $s['id']; ?>"><?php echo sms_e($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['staff_no'] . ')'); ?></option><?php endforeach; ?></select></div>
                        <div class="col-12"><label class="form-label">Allowance Type</label><select class="form-select" name="allowance_type_id" id="assignType" style="padding-left:12px;" required><option value="">Select type</option><?php foreach ($allowanceTypes as $type): ?><option value="<?php echo (int) $type['id']; ?>" data-amount="<?php echo sms_e((string) $type['default_amount']); ?>"><?php echo sms_e($type['name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Amount</label><input type="number" min="0" step="0.01" class="form-control" name="amount" id="assignAmount" style="padding-left:12px;" required></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status" style="padding-left:12px;"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn pr-btn">Assign</button></div>
            </form>
        </div>
    </div>
</div>

</div>
</div>
<script data-cfasync="false" type="text/javascript">
(function(){
    var typeModalEl = document.getElementById('typeModal');
    function getTypeModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(typeModalEl) : null; }
    document.getElementById('addTypeBtn').addEventListener('click', function(){
        document.getElementById('typeModalTitle').textContent = 'Add Allowance Type';
        document.getElementById('typeId').value = '';
        document.getElementById('typeName').value = '';
        document.getElementById('typeCalc').value = 'fixed';
        document.getElementById('typeAmount').value = '0';
        document.getElementById('typeStatus').value = 'active';
        var modal = getTypeModal(); if (modal) { modal.show(); }
    });
    document.querySelectorAll('.edit-type-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.getElementById('typeModalTitle').textContent = 'Edit Allowance Type';
            document.getElementById('typeId').value = btn.dataset.id;
            document.getElementById('typeName').value = btn.dataset.name;
            document.getElementById('typeCalc').value = btn.dataset.calc;
            document.getElementById('typeAmount').value = btn.dataset.amount;
            document.getElementById('typeStatus').value = btn.dataset.status;
            var modal = getTypeModal(); if (modal) { modal.show(); }
        });
    });

    var assignModalEl = document.getElementById('assignModal');
    function getAssignModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(assignModalEl) : null; }
    document.getElementById('addAssignBtn').addEventListener('click', function(){
        var modal = getAssignModal(); if (modal) { modal.show(); }
    });
    document.getElementById('assignType').addEventListener('change', function(){
        var option = this.options[this.selectedIndex];
        document.getElementById('assignAmount').value = option.dataset.amount || 0;
    });
})();
</script>
<?php require_once('includes/footer.php'); ?>
