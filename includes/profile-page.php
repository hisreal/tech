<?php
/**
 * Shared profile page renderer and form handler for all authenticated portals.
 */

require_once __DIR__ . '/helpers/auth.php';

use App\Services\ProfileService;

$profilePortal = $profilePortal ?? 'admin';
$currentUser = sms_current_user();
if (!$currentUser) {
    sms_require_auth($profilePortal === 'admin' ? ['super-admin', 'admin'] : $profilePortal);
    $currentUser = sms_current_user();
}

$service = new ProfileService();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
    } elseif (($_POST['profile_action'] ?? '') === 'update_profile') {
        $result = $service->updateProfile($currentUser, $_POST, $_FILES['profile_photo'] ?? null);
        sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
        if (!empty($result['errors'])) { $_SESSION['_profile_errors'] = $result['errors']; }
    } elseif (($_POST['profile_action'] ?? '') === 'change_password') {
        $result = $service->changePassword($currentUser, $_POST);
        sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
        if (!empty($result['errors'])) { $_SESSION['_profile_errors'] = $result['errors']; }
    }
    header('Location: profile.php');
    exit;
}

$profile = $service->getProfile($currentUser);
$errors = $_SESSION['_profile_errors'] ?? [];
unset($_SESSION['_profile_errors']);
$flashMessages = sms_flash();

$fullName = trim((string) ($profile['full_name'] ?? '')) ?: (string) ($profile['username'] ?? 'User');
$email = (string) ($profile['account_email'] ?? $profile['email'] ?? '');
$phone = (string) ($profile['phone'] ?? '');
$gender = (string) ($profile['gender'] ?? '');
$dob = (string) ($profile['date_of_birth'] ?? '');
$address = (string) ($profile['address'] ?? '');
$religion = (string) ($profile['religion'] ?? '');
$nationality = (string) ($profile['nationality'] ?? '');
$state = (string) ($profile['state'] ?? '');
$localGovernment = (string) ($profile['local_government'] ?? '');
$emergencyContact = (string) ($profile['emergency_contact'] ?? '');
$completion = $profile['profile_completion'] ?? null;
$status = (string) ($profile['account_status'] ?? $profile['status'] ?? 'active');
$photo = (string) ($profile['profile_photo'] ?? '../assets/img/avatar/avatar1.jpg');
$isStudent = (($currentUser['role'] ?? '') === 'student');

$roleSpecific = [];
if ($isStudent) {
    $roleSpecific = [
        ['Registration Number', $profile['registration_no'] ?? '', 'fa-id-card'],
        ['Admission Number', $profile['admission_no'] ?? '', 'fa-address-card'],
        ['Class', $profile['class_name'] ?? '', 'fa-school'],
        ['Section / Arm', $profile['section_name'] ?? '', 'fa-layer-group'],
        ['Parent / Guardian', $profile['guardian_name'] ?? '', 'fa-user-shield'],
        ['Parent Phone', $profile['guardian_phone'] ?? '', 'fa-phone'],
        ['Admission Date', $profile['admission_date'] ?? '', 'fa-calendar-check'],
    ];
} else {
    $roleSpecific = [
        ['Staff ID', $profile['staff_no'] ?? $profile['display_id'] ?? '', 'fa-id-card'],
        ['Department', $profile['department'] ?? '', 'fa-building-columns'],
        ['Position', $profile['designation'] ?? $profile['position'] ?? '', 'fa-briefcase'],
        ['Employment Status', $profile['employment_status'] ?? '', 'fa-circle-check'],
        ['Employment Date', $profile['employment_date'] ?? '', 'fa-calendar-check'],
        ['Qualification', $profile['qualification'] ?? '', 'fa-graduation-cap'],
    ];
}

$commonFields = [
    ['Full Name', $fullName, 'fa-user'],
    ['Username', $profile['username'] ?? '', 'fa-user-lock'],
    ['Email Address', $email, 'fa-envelope'],
    ['Phone Number', $phone, 'fa-phone'],
    ['Gender', $gender, 'fa-venus-mars'],
    ['Date of Birth', $dob, 'fa-cake-candles'],
    ['Address', $address, 'fa-location-dot'],
    ['Account Status', $status, 'fa-circle-check'],
];
if ($isStudent) {
    $commonFields[] = ['Religion', $religion, 'fa-hands-praying'];
    $commonFields[] = ['Nationality', $nationality, 'fa-flag'];
    $commonFields[] = ['State of Origin', $state, 'fa-map'];
    $commonFields[] = ['LGA', $localGovernment, 'fa-map-location-dot'];
    $commonFields[] = ['Emergency Contact', $emergencyContact, 'fa-truck-medical'];
}

$accountFields = [
    ['User ID', $profile['user_id'] ?? '', 'fa-hashtag'],
    ['Account Role', $profile['role_label'] ?? '', 'fa-user-shield'],
    ['Date Created', $profile['created_at'] ?? '', 'fa-calendar-plus'],
    ['Last Updated', $profile['updated_at'] ?? '', 'fa-rotate'],
    ['Last Login', $profile['last_login_at'] ?? '', 'fa-clock-rotate-left'],
];

require_once __DIR__ . '/../' . $profilePortal . '/includes/header.php';
?>
<style>
.profile-db-page{--p-primary:#0f766e;--p-dark:#115e59;--p-soft:rgba(15,118,110,.1);--p-border:rgba(15,118,110,.18);--p-muted:#64748b;--p-ink:#10201d;--p-shadow:0 22px 60px rgba(15,23,42,.09);padding-bottom:34px}.profile-db-page .profile-hero,.profile-db-page .profile-card,.profile-db-page .info-card{background:rgba(255,255,255,.98);border:1px solid var(--p-border);box-shadow:var(--p-shadow)}.profile-db-page .profile-hero{padding:28px;border-radius:26px;margin-bottom:22px;background:linear-gradient(135deg,rgba(240,253,244,.98),#fff)}.profile-db-page .profile-photo{width:132px;height:132px;border-radius:30px;object-fit:cover;border:6px solid #fff;box-shadow:0 18px 38px rgba(15,23,42,.16)}.profile-db-page h3,.profile-db-page h4,.profile-db-page h5{color:var(--p-ink);font-weight:900}.profile-db-page .role-chip,.profile-db-page .status-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;font-weight:900;font-size:12px}.profile-db-page .role-chip{background:var(--p-soft);color:var(--p-dark)}.profile-db-page .status-chip{background:rgba(22,163,74,.12);color:#16a34a}.profile-db-page .profile-card{border-radius:24px;padding:24px;margin-bottom:22px}.profile-db-page .info-card{height:100%;padding:17px;border-radius:18px;transition:transform .18s ease,box-shadow .18s ease}.profile-db-page .info-card:hover{transform:translateY(-3px);box-shadow:0 18px 42px rgba(15,23,42,.11)}.profile-db-page .field-icon{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:var(--p-soft);color:var(--p-primary);flex:0 0 auto}.profile-db-page .field-label{color:var(--p-muted);font-size:12px;font-weight:900;text-transform:uppercase}.profile-db-page .field-value{color:var(--p-ink);font-weight:850;word-break:break-word}.profile-db-page .form-label{font-weight:900;color:var(--p-ink)}.profile-db-page .form-control,.profile-db-page .form-select{min-height:48px;border-radius:15px;font-weight:750}.profile-db-page .main-btn{min-height:46px;border:0;border-radius:15px;background:linear-gradient(135deg,var(--p-primary),var(--p-dark));color:#fff;font-weight:900}.profile-db-page .main-btn:hover{color:#fff}.profile-db-page .preview-img{width:96px;height:96px;border-radius:22px;object-fit:cover;border:4px solid #fff;box-shadow:0 12px 28px rgba(15,23,42,.14)}.profile-db-page .error-text{color:#dc2626;font-size:12px;font-weight:800}.profile-db-page .assignment-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:999px;background:var(--p-soft);color:var(--p-dark);font-weight:850;margin:0 7px 7px 0}.profile-db-page .completion-box{padding:16px 18px;border-radius:18px;background:rgba(15,118,110,.08);border:1px solid var(--p-border);margin-bottom:18px}.profile-db-page .completion-bar{height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden}.profile-db-page .completion-fill{height:100%;background:linear-gradient(135deg,var(--p-primary),var(--p-dark));border-radius:999px}.profile-db-page .completion-missing{font-size:12px;font-weight:800;color:var(--p-muted)}@media(max-width:767.98px){.profile-db-page .profile-hero,.profile-db-page .profile-card{padding:20px;border-radius:20px}.profile-db-page .profile-photo{width:104px;height:104px}}
</style>
<div class="profile-db-page">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="profile-hero">
        <div class="row align-items-center row-gap-4">
            <div class="col-xl-8">
                <div class="d-flex align-items-center flex-wrap gap-4">
                    <img src="<?php echo sms_e($photo); ?>" class="profile-photo" alt="Profile photo">
                    <div>
                        <span class="role-chip"><i class="fa-solid fa-user-shield"></i><?php echo sms_e($profile['role_label'] ?? 'Profile'); ?></span>
                        <h3 class="mt-3 mb-1"><?php echo sms_e($fullName); ?></h3>
                        <p class="text-muted fw-bold mb-2"><?php echo sms_e($profile['display_id'] ?? $profile['username'] ?? ''); ?></p>
                        <span class="status-chip"><i class="fa-solid fa-circle-check"></i><?php echo sms_e(ucfirst($status)); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 text-xl-end">
                <a href="#editProfile" class="btn main-btn px-4"><i class="fa-solid fa-user-pen me-2"></i>Edit Profile</a>
            </div>
        </div>
    </section>

    <?php if ($isStudent && $completion): ?>
        <div class="completion-box">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <strong><?php echo ($completion['complete'] ?? false) ? 'Profile Complete' : 'Profile Incomplete'; ?></strong>
                <span class="role-chip"><?php echo (int) ($completion['percentage'] ?? 0); ?>% Complete</span>
            </div>
            <div class="completion-bar"><div class="completion-fill" style="width: <?php echo (int) ($completion['percentage'] ?? 0); ?>%"></div></div>
            <?php if (!($completion['complete'] ?? false)): ?>
                <div class="completion-missing mt-2">Complete your gender, date of birth, address, religion, nationality, state of origin, LGA, and phone number.</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php $sections = [['Common Information', $commonFields], ['Role Information', $roleSpecific], ['Account Information', $accountFields]]; ?>
    <?php foreach ($sections as $section): ?>
        <section class="profile-card">
            <h4 class="mb-3"><?php echo sms_e($section[0]); ?></h4>
            <div class="row g-3">
                <?php foreach ($section[1] as $field): ?>
                    <div class="col-md-6 col-xl-4"><div class="info-card"><div class="d-flex gap-3"><span class="field-icon"><i class="fa-solid <?php echo sms_e($field[2]); ?>"></i></span><div><div class="field-label"><?php echo sms_e($field[0]); ?></div><div class="field-value"><?php echo sms_e($field[1] ?: 'Not set'); ?></div></div></div></div></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php if (!empty($profile['assigned_classes']) || !empty($profile['subjects'])): ?>
        <section class="profile-card">
            <h4 class="mb-3">Teaching Assignments</h4>
            <h6>Assigned Classes</h6>
            <?php foreach (($profile['assigned_classes'] ?? []) as $class): ?><span class="assignment-chip"><i class="fa-solid fa-school"></i><?php echo sms_e($class); ?></span><?php endforeach; ?>
            <h6 class="mt-3">Subjects Handled</h6>
            <?php foreach (($profile['subjects'] ?? []) as $subject): ?><span class="assignment-chip"><i class="fa-solid fa-book"></i><?php echo sms_e($subject); ?></span><?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="profile-card" id="editProfile">
        <h4 class="mb-3">Edit Profile</h4>
        <form method="post" enctype="multipart/form-data" class="row g-3" id="profileForm">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <input type="hidden" name="profile_action" value="update_profile">
            <div class="col-md-8"><label class="form-label">Full Name</label><input class="form-control" name="full_name" value="<?php echo sms_e($fullName); ?>" required><?php if(isset($errors['full_name'])): ?><div class="error-text"><?php echo sms_e($errors['full_name']); ?></div><?php endif; ?></div>
            <div class="col-md-4"><label class="form-label">Gender</label><select class="form-select" name="gender"><option value="">Select</option><?php foreach(['male','female','other'] as $option): ?><option value="<?php echo $option; ?>" <?php echo strtolower($gender)===$option?'selected':''; ?>><?php echo ucfirst($option); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?php echo sms_e($email); ?>"><?php if(isset($errors['email'])): ?><div class="error-text"><?php echo sms_e($errors['email']); ?></div><?php endif; ?></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo sms_e($phone); ?>"><?php if(isset($errors['phone'])): ?><div class="error-text"><?php echo sms_e($errors['phone']); ?></div><?php endif; ?></div>
            <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="date_of_birth" value="<?php echo sms_e($dob); ?>"></div>
            <?php if ($isStudent): ?>
                <div class="col-md-6"><label class="form-label">Religion</label><input class="form-control" name="religion" value="<?php echo sms_e($religion); ?>"></div>
                <div class="col-md-6"><label class="form-label">Nationality</label><input class="form-control" name="nationality" value="<?php echo sms_e($nationality); ?>"></div>
                <div class="col-md-6"><label class="form-label">State of Origin</label><input class="form-control" name="state" value="<?php echo sms_e($state); ?>"></div>
                <div class="col-md-6"><label class="form-label">Local Government Area</label><input class="form-control" name="local_government" value="<?php echo sms_e($localGovernment); ?>"></div>
                <div class="col-md-6"><label class="form-label">Emergency Contact</label><input class="form-control" name="emergency_contact" value="<?php echo sms_e($emergencyContact); ?>"></div>
            <?php endif; ?>
            <div class="col-md-6"><label class="form-label">Profile Photo</label><div class="d-flex align-items-center gap-3"><img src="<?php echo sms_e($photo); ?>" id="photoPreview" class="preview-img" alt="Preview"><input type="file" class="form-control" name="profile_photo" id="profilePhoto" accept="image/png,image/jpeg,image/webp"></div></div>
            <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3"><?php echo sms_e($address); ?></textarea></div>
            <div class="col-12"><button class="btn main-btn px-4" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Save Profile</button></div>
        </form>
    </section>

    <section class="profile-card">
        <h4 class="mb-3">Change Password</h4>
        <form method="post" class="row g-3" id="passwordForm">
            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
            <input type="hidden" name="profile_action" value="change_password">
            <div class="col-md-4"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required><?php if(isset($errors['current_password'])): ?><div class="error-text"><?php echo sms_e($errors['current_password']); ?></div><?php endif; ?></div>
            <div class="col-md-4"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" required><?php if(isset($errors['new_password'])): ?><div class="error-text"><?php echo sms_e($errors['new_password']); ?></div><?php endif; ?></div>
            <div class="col-md-4"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" name="confirm_password" required><?php if(isset($errors['confirm_password'])): ?><div class="error-text"><?php echo sms_e($errors['confirm_password']); ?></div><?php endif; ?></div>
            <div class="col-12"><button class="btn main-btn px-4" type="submit"><i class="fa-solid fa-key me-2"></i>Change Password</button></div>
        </form>
    </section>
</div>
<script>
document.getElementById('profilePhoto')?.addEventListener('change', function(){ const file=this.files&&this.files[0]; if(file){ document.getElementById('photoPreview').src=URL.createObjectURL(file); }});
document.getElementById('passwordForm')?.addEventListener('submit', function(e){ if(!confirm('Change your account password now?')){ e.preventDefault(); }});
</script>
</div></div>
<?php require_once __DIR__ . '/../' . $profilePortal . '/includes/footer.php'; ?>
