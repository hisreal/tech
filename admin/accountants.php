<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AccountantService;

$accountantService = new AccountantService();
require_once('includes/header.php');

$search = trim((string) ($_GET['search'] ?? ''));
$departmentFilter = trim((string) ($_GET['department'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $accountantService->list([
    'search' => $search,
    'department_id' => $departmentFilter,
    'status' => $statusFilter,
], $page, 10);

$accountants = $result['data'];
$meta = $result['meta'];

$departmentOptions = $accountantService->departmentsForSelect();
$statusOptions = ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave', 'suspended' => 'Suspended', 'deleted' => 'Deleted'];

$allAccountantsForStats = $accountantService->list([], 1, 500)['data'];
$totalAccountants = (int) $result['meta']['total'];
$activeAccountants = count(array_filter($allAccountantsForStats, static fn (array $row): bool => $row['employment_status'] === 'active'));
$departmentCount = count($departmentOptions);

$summaryCards = [
    ['title' => 'Total Accountants', 'value' => number_format($totalAccountants), 'icon' => 'fa-calculator', 'color' => 'success'],
    ['title' => 'Active Accountants', 'value' => number_format($activeAccountants), 'icon' => 'fa-user-check', 'color' => 'blue'],
    ['title' => 'Departments', 'value' => number_format($departmentCount), 'icon' => 'fa-building-columns', 'color' => 'warning'],
];

function sms_accountant_query(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    unset($query['page']);
    return 'accountants.php?' . http_build_query($query);
}
?>
<?php require_once('includes/accountant-module-styles.php'); ?>
<div class="admin-accountant-module">
    <?php foreach (sms_flash() as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Accountant Management <i class="fa-solid fa-angle-right mx-1"></i> All Accountants</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-calculator"></i> All Accountants</span>
                <h3 class="mt-3 mb-2">Accountant Management</h3>
                <p class="text-muted mb-0">View, search, add, edit, and manage accountant accounts.</p>
            </div>
            <a href="add-accountant.php" class="module-btn btn-primary-soft"><i class="fa-solid fa-user-plus"></i> Add Accountant</a>
        </div>
    </section>

    <section class="row g-3 mb-4" aria-label="Accountant summary cards">
        <?php foreach ($summaryCards as $card): ?>
            <div class="col-sm-6 col-xl-4">
                <?php sms_render_component('dashboard-card', $card); ?>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Search & Filter</h4>
                <p class="text-muted mb-0">Filter accountants by name/staff ID, department, or status.</p>
            </div>
        </div>
        <form method="get">
            <div class="filter-grid">
                <div><label for="search">Search</label><input class="form-control" id="search" name="search" placeholder="Staff ID or name" value="<?php echo sms_e($search); ?>"></div>
                <div><label for="departmentFilter">Department</label><select class="form-select" id="departmentFilter" name="department"><option value="">All Departments</option><?php foreach ($departmentOptions as $option): ?><option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $option['id'] === $departmentFilter ? 'selected' : ''; ?>><?php echo sms_e($option['name']); ?></option><?php endforeach; ?></select></div>
                <div><label for="statusFilter">Employment Status</label><select class="form-select" id="statusFilter" name="status"><option value="">All Statuses</option><?php foreach ($statusOptions as $value => $label): ?><option value="<?php echo sms_e($value); ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>><?php echo sms_e($label); ?></option><?php endforeach; ?></select></div>
                <div class="d-flex align-items-end gap-2"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button><a class="module-btn btn-muted-soft" href="accountants.php"><i class="fa-solid fa-rotate-left"></i> Reset</a></div>
            </div>
        </form>
    </section>

    <section class="module-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Accountant Records</h4>
                <p class="text-muted mb-0"><?php echo (int) $meta['total']; ?> record(s) found.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="module-btn btn-outline-soft" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print List</button>
            </div>
        </div>
        <div class="table-shell">
            <table class="table align-middle" id="accountantTable">
                <thead>
                    <tr>
                        <th>Passport</th><th>Staff ID</th><th>Full Name</th><th>Department</th><th>Phone</th><th>Email</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accountants as $accountant): ?>
                        <?php
                        $fullName = trim($accountant['first_name'] . ' ' . $accountant['last_name']);
                        $photo = !empty($accountant['passport_path']) ? '../' . ltrim((string) $accountant['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
                        $statusClass = 'status-' . strtolower((string) $accountant['employment_status']);
                        ?>
                        <tr>
                            <td><img class="accountant-passport" src="<?php echo sms_e($photo); ?>" alt="<?php echo sms_e($fullName); ?> passport"></td>
                            <td><?php echo sms_e($accountant['staff_no']); ?></td>
                            <td><?php echo sms_e($fullName); ?></td>
                            <td><?php echo sms_e($accountant['department_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo sms_e($accountant['phone'] ?? ''); ?></td>
                            <td><?php echo sms_e($accountant['email'] ?? ''); ?></td>
                            <td><span class="status-badge <?php echo sms_e($statusClass); ?>"><i class="fa-solid fa-circle"></i> <?php echo sms_e(ucfirst(str_replace('_', ' ', (string) $accountant['employment_status']))); ?></span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="action-menu-btn" type="button" id="accountantAction<?php echo (int) $accountant['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Accountant actions"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end accountant-actions-menu" aria-labelledby="accountantAction<?php echo (int) $accountant['id']; ?>">
                                        <li><a class="dropdown-item" href="accountant-profile.php?accountant_id=<?php echo (int) $accountant['id']; ?>"><i class="fa-solid fa-eye"></i> View Profile</a></li>
                                        <li><a class="dropdown-item" href="edit-accountant.php?accountant_id=<?php echo (int) $accountant['id']; ?>"><i class="fa-solid fa-pen"></i> Edit Accountant</a></li>
                                        <li><button class="dropdown-item text-danger delete-accountant-btn" type="button" data-accountant-id="<?php echo (int) $accountant['id']; ?>" data-name="<?php echo sms_e($fullName); ?>"><i class="fa-solid fa-trash"></i> Delete Accountant</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$accountants): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No accountants match your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
            <span class="text-muted fw-bold"><?php echo (int) $meta['total']; ?> record(s) &middot; page <?php echo (int) $meta['page']; ?> of <?php echo (int) $meta['last_page']; ?></span>
            <?php if ($meta['last_page'] > 1): ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="module-btn btn-muted-soft" style="<?php echo $meta['page'] <= 1 ? 'opacity:.4;pointer-events:none' : ''; ?>" href="<?php echo sms_e(sms_accountant_query(['page' => max(1, $meta['page'] - 1)])); ?>">Previous</a>
                    <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
                        <a class="module-btn <?php echo $p === (int) $meta['page'] ? 'btn-primary-soft' : 'btn-muted-soft'; ?>" href="<?php echo sms_e(sms_accountant_query(['page' => $p])); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>
                    <a class="module-btn btn-muted-soft" style="<?php echo $meta['page'] >= $meta['last_page'] ? 'opacity:.4;pointer-events:none' : ''; ?>" href="<?php echo sms_e(sms_accountant_query(['page' => min($meta['last_page'], $meta['page'] + 1)])); ?>">Next</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="modal fade" id="deleteAccountantModal" tabindex="-1" aria-labelledby="deleteAccountantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="accountant-delete.php">
            <div class="modal-header"><h5 class="modal-title" id="deleteAccountantModalLabel">Delete Accountant</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                <input type="hidden" name="accountant_id" id="deleteAccountantId">
                <p class="mb-0">Are you sure you want to delete this accountant?</p>
                <p class="text-muted fw-bold mb-0">This marks the accountant as deleted and disables their login. Records are preserved and this can be reversed by editing the accountant's status.</p>
                <p class="mt-2 mb-0"><strong id="deleteAccountantName">Accountant record</strong></p>
            </div>
            <div class="modal-footer"><button type="button" class="module-btn btn-muted-soft" data-bs-dismiss="modal">Cancel</button><button type="submit" class="module-btn btn-danger-soft">Delete Accountant</button></div>
        </form>
    </div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    function getDeleteModal(){ return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteAccountantModal')) : null; }
    Array.prototype.forEach.call(document.querySelectorAll('.delete-accountant-btn'), function(button){
        button.addEventListener('click', function(){
            document.getElementById('deleteAccountantId').value = button.dataset.accountantId || '';
            document.getElementById('deleteAccountantName').textContent = button.dataset.name || 'Accountant record';
            var deleteModal = getDeleteModal(); if (deleteModal) { deleteModal.show(); }
        });
    });
    ['departmentFilter', 'statusFilter'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) { el.addEventListener('change', function(){ el.form.submit(); }); }
    });
})();
</script>

<?php require_once('includes/footer.php'); ?>
