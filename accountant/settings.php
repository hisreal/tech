<?php require_once('includes/header.php'); ?>

<?php

use App\Models\SettingsModel;
use App\Services\FinanceService;

$settingsModel = new SettingsModel();
$financeService = new FinanceService();
$allSettings = $settingsModel->all();
$currentUser = sms_current_user();

$sessions = $financeService->sessionsForSelect();
$terms = $financeService->termsForSelect();
$currentSessionId = $financeService->currentSessionId();
$currentSession = null;
foreach ($sessions as $s) { if ((int) $s['id'] === $currentSessionId) { $currentSession = $s['name']; break; } }

$receiptSettings = [
	'school_name' => $allSettings['school.name']['value'] ?? 'Not set',
	'address' => $allSettings['school.address']['value'] ?? 'Not set',
	'phone' => $allSettings['school.phone']['value'] ?? 'Not set',
	'email' => $allSettings['school.email']['value'] ?? 'Not set',
	'prefix' => $allSettings['finance.receipt_prefix']['value'] ?? 'RCP',
	'start_number' => $allSettings['finance.receipt_start_number']['value'] ?? '1',
	'footer' => $allSettings['finance.receipt_footer']['value'] ?? 'Thank you for your payment.',
];

$systemInfo = [
	'Application Version' => 'SMS Finance v1.0.0',
	'Database Status' => 'Connected',
	'Logged-in User' => (string) ($currentUser['full_name'] ?? $currentUser['username'] ?? 'Accountant'),
	'User Role' => 'Accountant',
	'Server Time' => date('Y-m-d H:i:s'),
];

function stValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>

<style>
	/* Accountant settings module: scoped ERP-style configuration layout. */
	.accountant-settings-page{--st-primary:#0f766e;--st-primary-dark:#115e59;--st-primary-soft:rgba(15,118,110,.1);--st-success:#16a34a;--st-warning:#f59e0b;--st-danger:#dc2626;--st-blue:#2563eb;--st-ink:#10201d;--st-muted:#64748b;--st-border:rgba(15,118,110,.18);--st-shadow:0 22px 60px rgba(15,23,42,.09);padding-bottom:34px}.accountant-settings-page .settings-hero,.accountant-settings-page .settings-card{background:rgba(255,255,255,.98);border:1px solid var(--st-border);box-shadow:var(--st-shadow)}.accountant-settings-page .settings-hero{padding:28px;border-radius:26px;margin-bottom:22px;background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98))}.accountant-settings-page .breadcrumb-line{color:var(--st-muted);font-size:13px;font-weight:800;margin-bottom:10px}.accountant-settings-page .breadcrumb-line a{color:var(--st-primary-dark);text-decoration:none}.accountant-settings-page .settings-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:var(--st-primary-soft);color:var(--st-primary-dark);font-size:12px;font-weight:900;text-transform:uppercase}.accountant-settings-page h3,.accountant-settings-page h4,.accountant-settings-page h5{color:var(--st-ink);font-weight:900}.accountant-settings-page .settings-card{border-radius:24px;padding:24px;margin-bottom:22px}.accountant-settings-page .section-title{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:18px}.accountant-settings-page .badge-soft{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:999px;background:var(--st-primary-soft);color:var(--st-primary-dark);font-weight:900}.accountant-settings-page .form-label{color:var(--st-ink);font-size:13px;font-weight:900}.accountant-settings-page .form-control,.accountant-settings-page .form-select{min-height:48px;border:1px solid rgba(148,163,184,.34);border-radius:15px;font-weight:800;box-shadow:none}.accountant-settings-page textarea.form-control{min-height:90px}.accountant-settings-page .form-control:focus,.accountant-settings-page .form-select:focus{border-color:rgba(15,118,110,.72);box-shadow:0 0 0 4px rgba(15,118,110,.12)}.accountant-settings-page .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.accountant-settings-page .info-card{padding:16px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.24)}.accountant-settings-page .info-card span{display:block;color:var(--st-muted);font-size:12px;font-weight:900;text-transform:uppercase}.accountant-settings-page .info-card strong{display:block;margin-top:6px;color:var(--st-ink);font-weight:900}.accountant-settings-page .settings-btn{min-height:46px;border:0;border-radius:15px;background:linear-gradient(135deg,var(--st-primary),var(--st-primary-dark));color:#fff;font-weight:900;box-shadow:0 14px 30px rgba(15,118,110,.22)}.accountant-settings-page .settings-btn:hover{color:#fff;transform:translateY(-2px)}.accountant-settings-page .hint{color:var(--st-muted);font-size:12px;font-weight:700}.accountant-settings-page .readonly-field{background:#f1f5f9}
</style>

<div class="accountant-settings-page">
	<?php foreach (sms_flash() as $type => $messages): ?>
		<?php foreach ($messages as $message): ?>
			<div class="alert alert-<?php echo $type === 'error' ? 'danger' : stValue($type); ?>" role="alert"><?php echo stValue($message); ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>

	<section class="settings-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Settings</div>
		<span class="settings-kicker"><i class="fa-solid fa-gear"></i> Accountant Preferences</span>
		<h3 class="mt-3 mb-2">Settings</h3>
		<p class="text-muted mb-0">Configure receipt details used on printed payment receipts.</p>
	</section>

	<section class="settings-card">
		<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-sliders"></i> Current Academic Context</span><h4 class="mt-3 mb-0">Active session &amp; term</h4></div><span class="hint">Managed by the administrator in School Settings</span></div>
		<div class="info-grid">
			<div class="info-card"><span>Active Session</span><strong><?php echo stValue($currentSession ?? 'Not set'); ?></strong></div>
			<div class="info-card"><span>Total Sessions</span><strong><?php echo count($sessions); ?></strong></div>
			<div class="info-card"><span>Total Terms</span><strong><?php echo count($terms); ?></strong></div>
		</div>
	</section>

	<section class="settings-card">
		<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-receipt"></i> Receipt Settings</span><h4 class="mt-3 mb-0">Customize payment receipts</h4></div></div>
		<form method="post" action="settings-save.php">
			<input type="hidden" name="_token" value="<?php echo stValue(sms_csrf_token()); ?>">
			<div class="row g-3">
				<div class="col-md-6"><label class="form-label">School Name</label><input class="form-control readonly-field" value="<?php echo stValue($receiptSettings['school_name']); ?>" readonly></div>
				<div class="col-md-6"><label class="form-label">School Phone Number</label><input class="form-control readonly-field" value="<?php echo stValue($receiptSettings['phone']); ?>" readonly></div>
				<div class="col-md-6"><label class="form-label">School Email</label><input class="form-control readonly-field" value="<?php echo stValue($receiptSettings['email']); ?>" readonly></div>
				<div class="col-md-3"><label class="form-label">Receipt Prefix</label><input class="form-control" name="receipt_prefix" value="<?php echo stValue($receiptSettings['prefix']); ?>" maxlength="10" required></div>
				<div class="col-md-3"><label class="form-label">Starting Receipt Number</label><input type="number" min="1" class="form-control" name="receipt_start" value="<?php echo stValue($receiptSettings['start_number']); ?>" required></div>
				<div class="col-12"><label class="form-label">School Address</label><textarea class="form-control readonly-field" readonly><?php echo stValue($receiptSettings['address']); ?></textarea></div>
				<div class="col-12"><label class="form-label">Receipt Footer Message</label><textarea class="form-control" name="receipt_footer"><?php echo stValue($receiptSettings['footer']); ?></textarea></div>
			</div>
			<p class="hint mt-2 mb-3">School name, address, phone, and email are managed by the administrator in School Settings.</p>
			<button type="submit" class="btn settings-btn"><i class="fa-solid fa-floppy-disk me-2"></i>Save Receipt Settings</button>
		</form>
	</section>

	<section class="settings-card">
		<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-circle-info"></i> System Information</span><h4 class="mt-3 mb-0">Read-only account and platform status</h4></div></div>
		<div class="info-grid">
			<?php foreach ($systemInfo as $label => $value): ?>
				<div class="info-card"><span><?php echo stValue($label); ?></span><strong><?php echo stValue($value); ?></strong></div>
			<?php endforeach; ?>
		</div>
	</section>
</div>

</div>
</div>

<?php require_once('includes/footer.php'); ?>
