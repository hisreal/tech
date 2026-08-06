<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
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
$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');
require_once('includes/accountant-module-styles.php');

$departments = $accountantService->departmentsForSelect();
$statuses = ['Active', 'Inactive', 'On Leave', 'Suspended'];
$certifications = ['ICAN', 'ANAN', 'ACCA', 'CPA'];
$photoUrl = !empty($accountant['passport_path']) ? '../' . ltrim((string) $accountant['passport_path'], '/') : '../assets/img/avatar/avatar1.jpg';
$currentEmploymentStatus = ucfirst(str_replace('_', ' ', (string) $accountant['employment_status']));

function sms_accountant_field(array $old, array $accountant, string $oldKey, string $accountantKey, string $default = ''): string
{
    if (array_key_exists($oldKey, $old)) {
        return sms_e($old[$oldKey]);
    }
    return sms_e((string) ($accountant[$accountantKey] ?? $default));
}
?>
<div class="admin-accountant-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Accountant Management <i class="fa-solid fa-angle-right mx-1"></i> Edit Accountant</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-pen"></i> Edit Accountant</span>
                <h3 class="mt-3 mb-2"><?php echo sms_e(trim($accountant['first_name'] . ' ' . $accountant['last_name'])); ?></h3>
                <p class="text-muted mb-0">Update personal, contact, employment, and profile photo details.</p>
            </div>
            <a class="module-btn btn-outline-soft" href="accountant-profile.php?accountant_id=<?php echo (int) $accountantId; ?>">View Profile</a>
        </div>
    </section>

    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <form id="editAccountantForm" method="post" action="accountant-update.php" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="accountant_id" value="<?php echo (int) $accountantId; ?>">
        <section class="module-card">
            <h4 class="mb-3">Personal & Employment Information</h4>
            <div class="form-grid">
                <div class="full"><label>Replace Profile Photo</label><input class="form-control" type="file" name="profile_photo" accept=".jpg,.jpeg,.png"></div>
                <div><label>First Name</label><input class="form-control" name="first_name" value="<?php echo sms_accountant_field($old, $accountant, 'first_name', 'first_name'); ?>" required><?php if (isset($errors['first_name'])): ?><span class="field-error"><?php echo sms_e($errors['first_name']); ?></span><?php endif; ?></div>
                <div><label>Middle Name</label><input class="form-control" name="middle_name" value="<?php echo sms_accountant_field($old, $accountant, 'middle_name', 'middle_name'); ?>"></div>
                <div><label>Last Name</label><input class="form-control" name="last_name" value="<?php echo sms_accountant_field($old, $accountant, 'last_name', 'last_name'); ?>" required><?php if (isset($errors['last_name'])): ?><span class="field-error"><?php echo sms_e($errors['last_name']); ?></span><?php endif; ?></div>
                <div><label>Phone</label><input class="form-control" name="phone" value="<?php echo sms_accountant_field($old, $accountant, 'phone', 'phone'); ?>" required><?php if (isset($errors['phone'])): ?><span class="field-error"><?php echo sms_e($errors['phone']); ?></span><?php endif; ?></div>
                <div><label>Email</label><input class="form-control" type="email" name="email" value="<?php echo sms_accountant_field($old, $accountant, 'email', 'email'); ?>" required><?php if (isset($errors['email'])): ?><span class="field-error"><?php echo sms_e($errors['email']); ?></span><?php endif; ?></div>
                <div><label>Department</label><select class="form-select" name="department"><option value="">Unassigned</option><?php foreach ($departments as $department): ?><option value="<?php echo (int) $department['id']; ?>" <?php echo (string) ($old['department'] ?? (string) $accountant['department_id']) === (string) $department['id'] ? 'selected' : ''; ?>><?php echo sms_e($department['name']); ?></option><?php endforeach; ?></select><?php if (isset($errors['department_id'])): ?><span class="field-error"><?php echo sms_e($errors['department_id']); ?></span><?php endif; ?></div>
                <div><label>Employment Status</label><select class="form-select" name="status"><?php foreach ($statuses as $status): ?><option <?php echo ($old['employment_status'] ?? $currentEmploymentStatus) === $status ? 'selected' : ''; ?>><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div><label>Qualification</label><input class="form-control" name="qualification" value="<?php echo sms_accountant_field($old, $accountant, 'qualification', 'qualification'); ?>"></div>
                <div><label>Years of Experience</label><input class="form-control" type="number" min="0" name="experience" value="<?php echo sms_accountant_field($old, $accountant, 'experience', 'years_experience'); ?>"></div>
                <div><label>Certification</label><select class="form-select" name="certification"><option value="">None</option><?php foreach ($certifications as $cert): ?><option <?php echo ($old['certification'] ?? (string) $accountant['specialization']) === $cert ? 'selected' : ''; ?>><?php echo sms_e($cert); ?></option><?php endforeach; ?></select></div>
                <div class="full"><label>Address</label><textarea class="form-control" name="address"><?php echo sms_accountant_field($old, $accountant, 'address', 'address'); ?></textarea></div>
            </div>
        </section>
        <?php if (!empty($accountant['documents'])): ?>
        <section class="module-card">
            <h4 class="mb-3">Uploaded Documents</h4>
            <?php foreach ($accountant['documents'] as $doc): ?><a class="module-btn btn-outline-soft me-2 mb-2" href="../<?php echo sms_e(ltrim((string) $doc['file_path'], '/')); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file"></i> <?php echo sms_e($doc['document_type']); ?></a><?php endforeach; ?>
        </section>
        <?php endif; ?>
        <section class="module-card"><div class="d-flex justify-content-end gap-2 flex-wrap"><button class="module-btn btn-primary-soft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button><a class="module-btn btn-muted-soft" href="accountants.php">Cancel</a></div></section>
    </form>
</div>
</div>
</div>
<script>
document.getElementById('editAccountantForm').addEventListener('submit', function(e){ if (!this.checkValidity()) { this.reportValidity(); e.preventDefault(); } });
</script>
<?php require_once('includes/footer.php'); ?>
