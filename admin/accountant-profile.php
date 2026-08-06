<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Services\AccountantService;

$accountantService = new AccountantService();
$accountantId = (int) ($_GET['accountant_id'] ?? 0);
$accountant = $accountantId > 0 ? $accountantService->find($accountantId) : null;

if ($accountant === null) {
    sms_flash_set('error', 'Accountant not found.');
    header('Location: accountants.php');
    exit;
}

$flashMessages = sms_flash();

require_once('includes/header.php');
require_once('includes/accountant-module-styles.php');

$fullName = trim($accountant['first_name'] . ' ' . $accountant['last_name']);
$photoUrl = !empty($accountant['passport_path']) ? '../' . ltrim((string) $accountant['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
$permissions = $accountantService->permissionsForRole('accountant');

$profileSummaryCards = [
    ['title' => 'Payments Processed', 'value' => $accountant['payments_processed'], 'icon' => 'fa-money-bill-transfer', 'color' => 'success'],
    ['title' => 'Receipts Generated', 'value' => $accountant['receipts_generated'], 'icon' => 'fa-receipt', 'color' => 'blue'],
    ['title' => 'Expenses Recorded', 'value' => $accountant['expenses_recorded'], 'icon' => 'fa-file-invoice-dollar', 'color' => 'warning'],
    ['title' => 'Documents on File', 'value' => count($accountant['documents']), 'icon' => 'fa-folder-open', 'color' => 'success'],
];
?>
<div class="admin-accountant-module">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Accountant Management <i class="fa-solid fa-angle-right mx-1"></i> Accountant Profile</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img class="profile-photo" src="<?php echo sms_e($photoUrl); ?>" alt="Accountant passport">
                <div>
                    <span class="module-kicker"><i class="fa-solid fa-id-card"></i> Accountant Profile</span>
                    <h3 class="mt-3 mb-1"><?php echo sms_e($fullName); ?></h3>
                    <p class="text-muted fw-bold mb-0"><?php echo sms_e((string) $accountant['staff_no']); ?> | <?php echo sms_e($accountant['department_name'] ?? 'Unassigned'); ?></p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="module-btn btn-primary-soft" href="edit-accountant.php?accountant_id=<?php echo (int) $accountantId; ?>"><i class="fa-solid fa-pen"></i> Edit Profile</a>
                <button class="module-btn btn-danger-soft" data-bs-toggle="modal" data-bs-target="#deleteAccountantModal" type="button"><i class="fa-solid fa-trash"></i> Delete Accountant</button>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4" aria-label="Accountant profile summary cards">
        <?php foreach ($profileSummaryCards as $card): ?>
            <div class="col-sm-6 col-xl-3">
                <?php sms_render_component('statistics-card', $card); ?>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="module-card">
        <h4>Personal Information</h4>
        <div class="info-grid">
            <div><label>Full Name</label><p><?php echo sms_e($fullName); ?></p></div>
            <div><label>Staff ID</label><p><?php echo sms_e((string) $accountant['staff_no']); ?></p></div>
            <div><label>Gender</label><p><?php echo sms_e(ucfirst((string) ($accountant['gender'] ?? 'Not set'))); ?></p></div>
            <div><label>Date of Birth</label><p><?php echo sms_e((string) ($accountant['date_of_birth'] ?? 'Not set')); ?></p></div>
            <div><label>Phone</label><p><?php echo sms_e((string) ($accountant['phone'] ?? 'Not set')); ?></p></div>
            <div><label>Email</label><p><?php echo sms_e((string) ($accountant['email'] ?? 'Not set')); ?></p></div>
            <div class="full"><label>Address</label><p><?php echo sms_e((string) ($accountant['address'] ?? 'Not set')); ?></p></div>
        </div>
    </section>

    <section class="module-card">
        <h4>Employment Information</h4>
        <div class="info-grid">
            <div><label>Department</label><p><?php echo sms_e($accountant['department_name'] ?? 'Unassigned'); ?></p></div>
            <div><label>Designation</label><p><?php echo sms_e((string) ($accountant['designation'] ?? 'Not set')); ?></p></div>
            <div><label>Qualification</label><p><?php echo sms_e((string) ($accountant['qualification'] ?? 'Not set')); ?></p></div>
            <div><label>Certification</label><p><?php echo sms_e((string) ($accountant['specialization'] ?? 'Not set')); ?></p></div>
            <div><label>Employment Date</label><p><?php echo sms_e((string) ($accountant['employment_date'] ?? 'Not set')); ?></p></div>
            <div><label>Employment Status</label><p><span class="status-badge"><?php echo sms_e(ucfirst(str_replace('_', ' ', (string) $accountant['employment_status']))); ?></span></p></div>
            <div><label>Experience</label><p><?php echo sms_e((string) $accountant['years_experience']); ?> Years</p></div>
        </div>
    </section>

    <section class="module-card">
        <h4>Permissions</h4>
        <p class="text-muted fw-bold">Granted by the Accountant role in Roles & Permissions.</p>
        <div class="chip-list">
            <?php foreach ($permissions as $permission): ?>
                <span class="chip" title="<?php echo sms_e($permission['description']); ?>"><i class="fa-solid fa-lock me-1"></i><?php echo sms_e(ucfirst($permission['module'])); ?> &middot; <?php echo sms_e(ucfirst($permission['action'])); ?></span>
            <?php endforeach; ?>
            <?php if (!$permissions): ?><span class="text-muted">No permissions have been assigned to the Accountant role yet.</span><?php endif; ?>
        </div>
    </section>

    <?php if (!empty($accountant['documents'])): ?>
    <section class="module-card">
        <h4>Uploaded Documents</h4>
        <?php foreach ($accountant['documents'] as $doc): ?><a class="module-btn btn-outline-soft me-2 mb-2" href="../<?php echo sms_e(ltrim((string) $doc['file_path'], '/')); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file"></i> <?php echo sms_e($doc['document_type']); ?></a><?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>

<div class="modal fade" id="deleteAccountantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="accountant-delete.php">
            <div class="modal-header"><h5>Delete Accountant</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                <input type="hidden" name="accountant_id" value="<?php echo (int) $accountantId; ?>">
                <p>Are you sure you want to delete this accountant?</p>
                <p class="text-muted fw-bold">This marks the accountant as deleted and disables their login. Records are preserved and this can be reversed by editing the accountant's status.</p>
            </div>
            <div class="modal-footer"><button class="module-btn btn-muted-soft" type="button" data-bs-dismiss="modal">Cancel</button><button class="module-btn btn-danger-soft" type="submit">Delete</button></div>
        </form>
    </div>
</div>
</div>
</div>
<?php require_once('includes/footer.php'); ?>
