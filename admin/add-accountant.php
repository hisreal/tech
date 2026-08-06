<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\AccountantService;

$accountantService = new AccountantService();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
        header('Location: add-accountant.php');
        exit;
    }

    $result = $accountantService->create($_POST, $_FILES, sms_current_user());

    if (!$result['success']) {
        sms_flash_set('error', $result['message']);
        Session::flashInput($_POST);
        Session::flashErrors($result['errors'] ?? []);
        header('Location: add-accountant.php');
        exit;
    }

    $credentials = $result['credentials'] ?? [];
    sms_flash_set('success', $result['message'] . ' Username: ' . ($credentials['username'] ?? '') . ' (the password you set is active immediately; the accountant will be required to change it on first login).');

    if ((string) ($_POST['submit_mode'] ?? 'save') === 'save-add') {
        header('Location: add-accountant.php');
    } else {
        header('Location: accountants.php');
    }
    exit;
}

$flashMessages = sms_flash();
$errors = Session::errors();
$old = Session::oldAll();

require_once('includes/header.php');
require_once('includes/accountant-module-styles.php');

$departments = $accountantService->departmentsForSelect();
$permissions = $accountantService->permissionsForRole('accountant');
$statuses = ['Active', 'Inactive', 'On Leave', 'Suspended'];
$certifications = ['ICAN', 'ANAN', 'ACCA', 'CPA'];
$generatedStaffNo = $accountantService->generateStaffNo();

function sms_accountant_old(array $old, string $key, string $default = ''): string
{
    return sms_e($old[$key] ?? $default);
}
?>
<div class="admin-accountant-module">
    <section class="module-hero">
        <div class="breadcrumb-line">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Accountant Management <i class="fa-solid fa-angle-right mx-1"></i> Add Accountant</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-user-plus"></i> Accountant Registration</span>
                <h3 class="mt-3 mb-2">Add Accountant</h3>
                <p class="text-muted mb-0">Register an accountant, create login details, and attach documents.</p>
            </div>
            <a class="module-btn btn-outline-soft" href="accountants.php">All Accountants</a>
        </div>
    </section>

    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <form id="accountantForm" method="post" action="add-accountant.php" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
        <input type="hidden" name="submit_mode" id="submitMode" value="save">

        <section class="module-card">
            <h4>Personal Information</h4>
            <div class="form-grid">
                <div class="full"><label>Passport Upload</label><input class="form-control tracked-file" type="file" name="passport" accept=".jpg,.jpeg,.png"><div class="file-list">No file selected</div></div>
                <div><label>Staff ID</label><input class="form-control" value="<?php echo sms_e($generatedStaffNo); ?>" readonly><small class="text-muted fw-bold">Assigned automatically when you save.</small></div>
                <div><label>First Name</label><input class="form-control" name="first_name" required value="<?php echo sms_accountant_old($old, 'first_name'); ?>"><?php if (isset($errors['first_name'])): ?><span class="field-error"><?php echo sms_e($errors['first_name']); ?></span><?php endif; ?></div>
                <div><label>Middle Name</label><input class="form-control" name="middle_name" value="<?php echo sms_accountant_old($old, 'middle_name'); ?>"></div>
                <div><label>Last Name</label><input class="form-control" name="last_name" required value="<?php echo sms_accountant_old($old, 'last_name'); ?>"><?php if (isset($errors['last_name'])): ?><span class="field-error"><?php echo sms_e($errors['last_name']); ?></span><?php endif; ?></div>
                <div><label>Gender</label><select class="form-select" name="gender"><option value="">Select Gender</option><option value="male" <?php echo ($old['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option><option value="female" <?php echo ($old['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option></select></div>
                <div><label>Date of Birth</label><input class="form-control" type="date" name="date_of_birth" value="<?php echo sms_accountant_old($old, 'date_of_birth'); ?>"><?php if (isset($errors['date_of_birth'])): ?><span class="field-error"><?php echo sms_e($errors['date_of_birth']); ?></span><?php endif; ?></div>
                <div><label>Nationality</label><input class="form-control" name="nationality" value="<?php echo sms_accountant_old($old, 'nationality', 'Nigerian'); ?>"></div>
                <div><label>State</label><input class="form-control" name="state" value="<?php echo sms_accountant_old($old, 'state'); ?>"></div>
                <div><label>Local Government</label><input class="form-control" name="local_government" value="<?php echo sms_accountant_old($old, 'local_government'); ?>"></div>
                <div><label>Phone Number</label><input class="form-control" name="phone" required value="<?php echo sms_accountant_old($old, 'phone'); ?>"><?php if (isset($errors['phone'])): ?><span class="field-error"><?php echo sms_e($errors['phone']); ?></span><?php endif; ?></div>
                <div><label>Email Address</label><input class="form-control" type="email" name="email" required value="<?php echo sms_accountant_old($old, 'email'); ?>"><?php if (isset($errors['email'])): ?><span class="field-error"><?php echo sms_e($errors['email']); ?></span><?php endif; ?></div>
                <div class="full"><label>Residential Address</label><textarea class="form-control" name="address"><?php echo sms_accountant_old($old, 'address'); ?></textarea></div>
            </div>
        </section>

        <section class="module-card">
            <h4>Employment & Professional Information</h4>
            <div class="form-grid">
                <div><label>Department</label><select class="form-select" name="department"><option value="">Unassigned</option><?php foreach ($departments as $department): ?><option value="<?php echo (int) $department['id']; ?>" <?php echo (string) ($old['department'] ?? '') === (string) $department['id'] ? 'selected' : ''; ?>><?php echo sms_e($department['name']); ?></option><?php endforeach; ?></select><?php if (isset($errors['department_id'])): ?><span class="field-error"><?php echo sms_e($errors['department_id']); ?></span><?php endif; ?></div>
                <div><label>Designation</label><input class="form-control" name="designation" value="Accountant" readonly></div>
                <div><label>Employment Date</label><input class="form-control" type="date" name="employment_date" value="<?php echo sms_accountant_old($old, 'employment_date', date('Y-m-d')); ?>"></div>
                <div><label>Employment Status</label><select class="form-select" name="status"><?php foreach ($statuses as $status): ?><option <?php echo ($old['employment_status'] ?? 'Active') === $status ? 'selected' : ''; ?>><?php echo sms_e($status); ?></option><?php endforeach; ?></select></div>
                <div><label>Qualification</label><input class="form-control" name="qualification" value="<?php echo sms_accountant_old($old, 'qualification'); ?>"></div>
                <div><label>Years of Experience</label><input class="form-control" type="number" min="0" name="experience" value="<?php echo sms_accountant_old($old, 'experience'); ?>"><?php if (isset($errors['experience'])): ?><span class="field-error"><?php echo sms_e($errors['experience']); ?></span><?php endif; ?></div>
                <div><label>Professional Certification</label><select class="form-select" name="certification"><option value="">None</option><?php foreach ($certifications as $cert): ?><option <?php echo ($old['certification'] ?? '') === $cert ? 'selected' : ''; ?>><?php echo sms_e($cert); ?></option><?php endforeach; ?></select></div>
            </div>
        </section>

        <section class="module-card">
            <h4>Account Information</h4>
            <div class="form-grid">
                <div><label>Username</label><input class="form-control" name="username" required value="<?php echo sms_accountant_old($old, 'username'); ?>"><?php if (isset($errors['username'])): ?><span class="field-error"><?php echo sms_e($errors['username']); ?></span><?php endif; ?></div>
                <div><label>Password</label><input class="form-control" id="password" type="password" name="password" required><div class="password-meter"><span id="passwordMeter"></span></div><small id="passwordStrengthText" class="text-muted fw-bold">Password strength: Not started</small><?php if (isset($errors['password'])): ?><span class="field-error"><?php echo sms_e($errors['password']); ?></span><?php endif; ?></div>
                <div><label>Confirm Password</label><input class="form-control" id="confirmPassword" type="password" name="confirm_password" required><?php if (isset($errors['confirm_password'])): ?><span class="field-error"><?php echo sms_e($errors['confirm_password']); ?></span><?php endif; ?></div>
            </div>
        </section>

        <section class="module-card">
            <h4>Permissions</h4>
            <p class="text-muted fw-bold">Read-only. Access is granted by the Accountant role in Roles & Permissions.</p>
            <div class="chip-list">
                <?php foreach ($permissions as $permission): ?>
                    <span class="chip" title="<?php echo sms_e($permission['description']); ?>"><i class="fa-solid fa-lock me-1"></i><?php echo sms_e(ucfirst($permission['module'])); ?> &middot; <?php echo sms_e(ucfirst($permission['action'])); ?></span>
                <?php endforeach; ?>
                <?php if (!$permissions): ?><span class="text-muted">No permissions have been assigned to the Accountant role yet.</span><?php endif; ?>
            </div>
        </section>

        <section class="module-card">
            <h4>Documents</h4>
            <div class="form-grid">
                <div><label>Passport Photograph</label><input class="form-control tracked-file" type="file" name="passport_photo" accept=".jpg,.jpeg,.png"><div class="file-list">No file selected</div></div>
                <div><label>CV / Resume</label><input class="form-control tracked-file" type="file" name="cv" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"><div class="file-list">No file selected</div></div>
                <div><label>Academic Certificates</label><input class="form-control tracked-file" type="file" name="certificates[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple><div class="file-list">No file selected</div></div>
                <div><label>Appointment Letter</label><input class="form-control tracked-file" type="file" name="appointment_letter" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"><div class="file-list">No file selected</div></div>
                <div><label>Identification Document</label><input class="form-control tracked-file" type="file" name="id_document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"><div class="file-list">No file selected</div></div>
            </div>
        </section>

        <section class="module-card">
            <div class="d-flex justify-content-end flex-wrap gap-2">
                <button class="module-btn btn-primary-soft" type="submit" data-mode="save"><i class="fa-solid fa-floppy-disk"></i> Save Accountant</button>
                <button class="module-btn btn-outline-soft" type="submit" data-mode="save-add"><i class="fa-solid fa-plus"></i> Save & Add Another</button>
                <button class="module-btn btn-muted-soft" type="reset"><i class="fa-solid fa-rotate-left"></i> Reset Form</button>
                <a class="module-btn btn-danger-soft" href="accountants.php"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </section>
    </form>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
(function(){
    var form = document.getElementById('accountantForm');
    var submitMode = document.getElementById('submitMode');
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirmPassword');
    var meter = document.getElementById('passwordMeter');
    var strengthText = document.getElementById('passwordStrengthText');

    function updatePasswordStrength() {
        var value = password.value;
        var score = 0;
        if (value.length >= 8) { score++; }
        if (/[A-Z]/.test(value)) { score++; }
        if (/[0-9]/.test(value)) { score++; }
        if (/[^A-Za-z0-9]/.test(value)) { score++; }
        var labels = ['Not started', 'Weak', 'Fair', 'Good', 'Strong'];
        var colors = ['#dc2626', '#dc2626', '#d97706', '#2563eb', '#16a34a'];
        meter.style.width = (score * 25) + '%';
        meter.style.background = colors[score];
        strengthText.textContent = 'Password strength: ' + labels[score];
    }

    Array.prototype.forEach.call(form.querySelectorAll('button[type="submit"]'), function(button){ button.addEventListener('click', function(){ submitMode.value = button.dataset.mode || 'save'; }); });
    Array.prototype.forEach.call(document.querySelectorAll('.tracked-file'), function(input){
        input.addEventListener('change', function(){
            var list = input.parentElement.querySelector('.file-list');
            var names = [].map.call(input.files, function(file){ return file.name; });
            list.textContent = names.length ? names.join(', ') : 'No file selected';
        });
    });
    password.addEventListener('input', updatePasswordStrength);
    form.addEventListener('submit', function(event){
        if (password.value !== confirmPassword.value) { confirmPassword.setCustomValidity('Passwords do not match.'); } else { confirmPassword.setCustomValidity(''); }
        if (!form.checkValidity()) { form.reportValidity(); event.preventDefault(); }
    });
})();
</script>

<?php require_once('includes/footer.php'); ?>
