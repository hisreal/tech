<?php
require_once __DIR__ . '/../includes/helpers/auth.php';

use App\Services\StudentImportService;

sms_require_auth(['super-admin', 'admin']);
$service = new StudentImportService();

if (($_GET['download'] ?? '') === 'template') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="student-import-template.csv"');
    echo $service->templateCsv();
    exit;
}

$preview = $_SESSION['_student_import_preview'] ?? null;
$credentials = $_SESSION['_student_import_credentials'] ?? [];
unset($_SESSION['_student_import_credentials']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!sms_verify_csrf($_POST['_token'] ?? null)) {
        sms_flash_set('error', 'Your session expired. Please try again.');
    } elseif (($_POST['import_action'] ?? '') === 'preview') {
        unset($_SESSION['_student_import_credentials']);
        $preview = $service->preview($_FILES['student_file'] ?? []);
        if ($preview['success']) {
            $_SESSION['_student_import_preview'] = $preview;
            sms_flash_set('success', 'File validated. Review the rows below before importing.');
        } else {
            unset($_SESSION['_student_import_preview']);
            sms_flash_set('error', $preview['message']);
            $preview = null;
        }
    } elseif (($_POST['import_action'] ?? '') === 'confirm') {
        $preview = $_SESSION['_student_import_preview'] ?? null;
        if (!$preview) {
            sms_flash_set('error', 'Upload and preview a file before confirming import.');
        } else {
            $importResult = $service->import($preview['rows'], sms_current_user());
            sms_flash_set($importResult['success'] ? 'success' : 'error', $importResult['message']);
            $_SESSION['_student_import_credentials'] = $importResult['credentials'] ?? [];
            unset($_SESSION['_student_import_preview']);
            $preview = null;
            header('Location: bulk-student-upload.php');
            exit;
        }
    } elseif (($_POST['import_action'] ?? '') === 'clear') {
        unset($_SESSION['_student_import_preview'], $_SESSION['_student_import_credentials']);
        $preview = null;
        $credentials = [];
        sms_flash_set('info', 'Import preview cleared.');
    }
}

$flashMessages = sms_flash();
require_once __DIR__ . '/includes/header.php';
?>
<style>
.bulk-import-page{--bi-primary:#0f766e;--bi-dark:#115e59;--bi-soft:rgba(15,118,110,.1);--bi-border:rgba(15,118,110,.18);--bi-ink:#10201d;--bi-muted:#64748b;--bi-shadow:0 22px 56px rgba(15,23,42,.08);padding-bottom:34px}.bulk-import-page .module-hero,.bulk-import-page .module-card,.bulk-import-page .summary-card{background:rgba(255,255,255,.98);border:1px solid var(--bi-border);box-shadow:var(--bi-shadow)}.bulk-import-page .module-hero{padding:26px;border-radius:24px;margin-bottom:22px;background:linear-gradient(135deg,rgba(240,253,244,.98),#fff)}.bulk-import-page .module-card{border-radius:22px;padding:22px;margin-bottom:22px}.bulk-import-page .module-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:var(--bi-soft);color:var(--bi-dark);font-size:12px;font-weight:900;text-transform:uppercase}.bulk-import-page h3,.bulk-import-page h4,.bulk-import-page h5{color:var(--bi-ink);font-weight:900}.bulk-import-page .module-btn{border:0;min-height:44px;display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:14px;padding:10px 15px;font-weight:900;text-decoration:none}.bulk-import-page .btn-primary-soft{background:var(--bi-primary);color:#fff;box-shadow:0 12px 24px rgba(15,118,110,.22)}.bulk-import-page .btn-outline-soft{background:#fff;color:var(--bi-dark);border:1px solid var(--bi-border)}.bulk-import-page .btn-muted-soft{background:#f1f5f9;color:var(--bi-ink)}.bulk-import-page .upload-box{padding:22px;border:1px dashed rgba(15,118,110,.4);border-radius:18px;background:rgba(240,253,244,.48)}.bulk-import-page .form-control{min-height:48px;border-radius:14px;font-weight:750}.bulk-import-page .summary-card{border-radius:18px;padding:18px;height:100%}.bulk-import-page .summary-card strong{display:block;font-size:26px;color:var(--bi-ink)}.bulk-import-page .table-wrap{overflow:auto;border:1px solid rgba(148,163,184,.22);border-radius:18px}.bulk-import-page table{min-width:860px;margin:0}.bulk-import-page thead th{background:#f0fdf4;color:var(--bi-dark);font-size:12px;text-transform:uppercase}.bulk-import-page td{vertical-align:top;font-weight:700}.bulk-import-page .badge-valid{background:rgba(22,163,74,.12);color:#15803d}.bulk-import-page .badge-invalid{background:rgba(220,38,38,.1);color:#dc2626}.bulk-import-page .error-list{margin:0;padding-left:18px;color:#dc2626;font-size:12px}.bulk-import-page .steps li{margin-bottom:8px;color:var(--bi-muted);font-weight:750}.bulk-import-page .credential-note{padding:14px 16px;border-radius:16px;background:rgba(245,158,11,.12);color:#92400e;font-weight:850}@media(max-width:575.98px){.bulk-import-page .module-hero,.bulk-import-page .module-card{padding:18px;border-radius:18px}.bulk-import-page .module-btn{width:100%}}
</style>
<div class="bulk-import-page">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <section class="module-hero">
        <div class="text-muted fw-bold mb-2">Dashboard <i class="fa-solid fa-angle-right mx-1"></i> Student Management <i class="fa-solid fa-angle-right mx-1"></i> Bulk Upload</div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="module-kicker"><i class="fa-solid fa-file-excel"></i> Bulk Student Upload</span>
                <h3 class="mt-3 mb-2">Import Students with Four Fields</h3>
                <p class="text-muted mb-0">The template only needs registration number, first name, last name, and class. Students complete the rest after login.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="bulk-student-upload.php?download=template" class="module-btn btn-outline-soft"><i class="fa-solid fa-download"></i> Export Bulk Template</a>
                <a href="student-list.php" class="module-btn btn-muted-soft"><i class="fa-solid fa-users"></i> All Students</a>
            </div>
        </div>
    </section>

    <?php if ($credentials): ?>
        <section class="module-card">
            <h4 class="mb-2">Generated Student Login Details</h4>
            <div class="credential-note mb-3">Copy these temporary passwords now. They are displayed once and are not stored in plain text.</div>
            <div class="table-wrap">
                <table class="table align-middle">
                    <thead><tr><th>Registration No</th><th>Name</th><th>Username</th><th>Temporary Password</th></tr></thead>
                    <tbody>
                    <?php foreach ($credentials as $credential): ?>
                        <tr>
                            <td><?php echo sms_e($credential['registration_no'] ?? ''); ?></td>
                            <td><?php echo sms_e($credential['full_name'] ?? ''); ?></td>
                            <td><?php echo sms_e($credential['username'] ?? ''); ?></td>
                            <td><code><?php echo sms_e($credential['temporary_password'] ?? ''); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="row g-3 mb-4">
        <div class="col-md-4"><div class="summary-card"><span class="text-muted fw-bold">Valid Rows</span><strong><?php echo (int)($preview['valid_count'] ?? 0); ?></strong></div></div>
        <div class="col-md-4"><div class="summary-card"><span class="text-muted fw-bold">Invalid Rows</span><strong><?php echo (int)($preview['invalid_count'] ?? 0); ?></strong></div></div>
        <div class="col-md-4"><div class="summary-card"><span class="text-muted fw-bold">Total Rows</span><strong><?php echo isset($preview['rows']) ? count($preview['rows']) : 0; ?></strong></div></div>
    </section>

    <section class="module-card">
        <h4 class="mb-3">Upload Completed Template</h4>
        <div class="row g-4">
            <div class="col-lg-7">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                    <input type="hidden" name="import_action" value="preview">
                    <div class="upload-box">
                        <label class="form-label fw-bold" for="studentFile">Select completed CSV file</label>
                        <input type="file" class="form-control" id="studentFile" name="student_file" accept=".csv,text/csv" required>
                        <p class="text-muted mb-0 mt-2">Required columns: registration_no, first_name, last_name, class.</p>
                    </div>
                    <button class="module-btn btn-primary-soft mt-3" type="submit"><i class="fa-solid fa-magnifying-glass-chart"></i> Validate File</button>
                </form>
            </div>
            <div class="col-lg-5">
                <h5>Import Steps</h5>
                <ol class="steps ps-3">
                    <li>Export the four-column template.</li>
                    <li>Fill registration number, first name, last name, and class.</li>
                    <li>Upload the CSV file for validation.</li>
                    <li>Confirm import to create active student accounts.</li>
                    <li>Give students their generated temporary passwords.</li>
                </ol>
            </div>
        </div>
    </section>

    <?php if ($preview && !empty($preview['rows'])): ?>
        <section class="module-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div><h4 class="mb-1">Validation Preview</h4><p class="text-muted mb-0">Only valid rows will be imported.</p></div>
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" onsubmit="return confirm('Import all valid rows now?');">
                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                        <input type="hidden" name="import_action" value="confirm">
                        <button class="module-btn btn-primary-soft" type="submit" <?php echo ((int)$preview['valid_count'] < 1) ? 'disabled' : ''; ?>><i class="fa-solid fa-database"></i> Confirm Import</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                        <input type="hidden" name="import_action" value="clear">
                        <button class="module-btn btn-muted-soft" type="submit"><i class="fa-solid fa-eraser"></i> Clear Preview</button>
                    </form>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table align-middle">
                    <thead><tr><th>Line</th><th>Status</th><th>Registration No</th><th>First Name</th><th>Last Name</th><th>Class</th><th>Errors</th></tr></thead>
                    <tbody>
                    <?php foreach ($preview['rows'] as $row): $data = $row['data']; ?>
                        <tr>
                            <td><?php echo (int)$row['line']; ?></td>
                            <td><span class="badge <?php echo $row['valid'] ? 'badge-valid' : 'badge-invalid'; ?>"><?php echo $row['valid'] ? 'Valid' : 'Invalid'; ?></span></td>
                            <td><?php echo sms_e($data['registration_no'] ?? ''); ?></td>
                            <td><?php echo sms_e($data['first_name'] ?? ''); ?></td>
                            <td><?php echo sms_e($data['last_name'] ?? ''); ?></td>
                            <td><?php echo sms_e($data['class'] ?? ''); ?></td>
                            <td><?php if (!$row['valid']): ?><ul class="error-list"><?php foreach ($row['errors'] as $error): ?><li><?php echo sms_e($error); ?></li><?php endforeach; ?></ul><?php else: ?>-<?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>
</div></div>
<?php require_once('includes/footer.php'); ?>
