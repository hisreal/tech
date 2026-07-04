<?php require_once('includes/header.php'); ?>

<?php
// Accountant settings placeholder data. Replace with persisted database-backed settings later.
$sessions = ['2025/2026', '2026/2027'];
$terms = ['First Term', 'Second Term', 'Third Term'];
$currencies = ['Nigerian Naira (₦)', 'US Dollar ($)', 'British Pound (£)', 'Euro (€)'];
$dateFormats = ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'];
$receiptSettings = [
	'school_name' => 'Brighter Future Standard School, Katsina',
	'address' => 'Along Old KTTV, Gawu Road, Near Layout Primary School, Katsina',
	'phone' => '08169192710',
	'email' => 'accounts@brighterfuture.edu.ng',
	'prefix' => 'RCP',
	'start_number' => '000123',
	'footer' => 'Thank you for your payment.'
];
$systemInfo = [
	'Application Version' => 'SMS Finance v1.0.0',
	'Database Status' => 'Connected placeholder',
	'Last Data Sync' => '02/07/2026 09:30 AM',
	'Last Backup' => '01/07/2026 06:00 PM',
	'Logged-in User' => 'John Ibrahim',
	'User Role' => 'Accountant'
];
function stValue($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>

<style>
	/* Accountant settings module: scoped ERP-style configuration layout. */
	.accountant-settings-page{--st-primary:#0f766e;--st-primary-dark:#115e59;--st-primary-soft:rgba(15,118,110,.1);--st-success:#16a34a;--st-warning:#f59e0b;--st-danger:#dc2626;--st-blue:#2563eb;--st-ink:#10201d;--st-muted:#64748b;--st-border:rgba(15,118,110,.18);--st-shadow:0 22px 60px rgba(15,23,42,.09);padding-bottom:34px}.accountant-settings-page .settings-hero,.accountant-settings-page .settings-card,.accountant-settings-page .settings-toast{background:rgba(255,255,255,.98);border:1px solid var(--st-border);box-shadow:var(--st-shadow)}.accountant-settings-page .settings-hero{padding:28px;border-radius:26px;margin-bottom:22px;background:linear-gradient(135deg,rgba(240,253,244,.98),rgba(255,255,255,.98))}.accountant-settings-page .breadcrumb-line{color:var(--st-muted);font-size:13px;font-weight:800;margin-bottom:10px}.accountant-settings-page .breadcrumb-line a{color:var(--st-primary-dark);text-decoration:none}.accountant-settings-page .settings-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:var(--st-primary-soft);color:var(--st-primary-dark);font-size:12px;font-weight:900;text-transform:uppercase}.accountant-settings-page h3,.accountant-settings-page h4,.accountant-settings-page h5{color:var(--st-ink);font-weight:900}.accountant-settings-page .settings-layout{display:grid;grid-template-columns:280px minmax(0,1fr);gap:22px}.accountant-settings-page .settings-tabs{position:sticky;top:18px;display:grid;gap:10px;align-self:start;padding:16px;border-radius:22px;background:#fff;border:1px solid var(--st-border);box-shadow:0 16px 42px rgba(15,23,42,.08)}.accountant-settings-page .settings-tab{display:flex;align-items:center;gap:10px;width:100%;border:0;border-radius:15px;background:#f8fafc;color:var(--st-ink);padding:13px 14px;font-weight:900;text-align:left;transition:all .18s ease}.accountant-settings-page .settings-tab i{width:20px;color:var(--st-primary)}.accountant-settings-page .settings-tab:hover,.accountant-settings-page .settings-tab.active{background:linear-gradient(135deg,var(--st-primary),var(--st-primary-dark));color:#fff;transform:translateY(-2px);box-shadow:0 14px 26px rgba(15,118,110,.2)}.accountant-settings-page .settings-tab:hover i,.accountant-settings-page .settings-tab.active i{color:#fff}.accountant-settings-page .settings-panel{display:none}.accountant-settings-page .settings-panel.active{display:block;animation:fadeIn .18s ease}.accountant-settings-page .settings-card{border-radius:24px;padding:24px;margin-bottom:22px}.accountant-settings-page .section-title{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:18px}.accountant-settings-page .section-title .icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:16px;background:var(--st-primary-soft);color:var(--st-primary)}.accountant-settings-page .form-label{color:var(--st-ink);font-size:13px;font-weight:900}.accountant-settings-page .form-control,.accountant-settings-page .form-select{min-height:48px;border:1px solid rgba(148,163,184,.34);border-radius:15px;font-weight:800;box-shadow:none}.accountant-settings-page textarea.form-control{min-height:110px}.accountant-settings-page .form-control:focus,.accountant-settings-page .form-select:focus{border-color:rgba(15,118,110,.72);box-shadow:0 0 0 4px rgba(15,118,110,.12)}.accountant-settings-page .setting-check,.accountant-settings-page .setting-switch{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 15px;border:1px solid rgba(148,163,184,.24);background:#f8fafc;border-radius:16px;margin-bottom:10px}.accountant-settings-page .setting-check label,.accountant-settings-page .setting-switch label{font-weight:900;color:var(--st-ink);margin:0}.accountant-settings-page .form-check-input:checked{background-color:var(--st-primary);border-color:var(--st-primary)}.accountant-settings-page .export-grid,.accountant-settings-page .quick-grid,.accountant-settings-page .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.accountant-settings-page .export-card,.accountant-settings-page .info-card{padding:16px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.24)}.accountant-settings-page .export-card{display:flex;align-items:center;justify-content:space-between;gap:12px}.accountant-settings-page .info-card span{display:block;color:var(--st-muted);font-size:12px;font-weight:900;text-transform:uppercase}.accountant-settings-page .info-card strong{display:block;margin-top:6px;color:var(--st-ink);font-weight:900}.accountant-settings-page .settings-actions{position:sticky;bottom:16px;z-index:3;display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding:14px;border-radius:20px;background:rgba(255,255,255,.92);border:1px solid var(--st-border);box-shadow:0 18px 46px rgba(15,23,42,.12);backdrop-filter:blur(12px)}.accountant-settings-page .settings-btn{min-height:46px;border:0;border-radius:15px;background:linear-gradient(135deg,var(--st-primary),var(--st-primary-dark));color:#fff;font-weight:900;box-shadow:0 14px 30px rgba(15,118,110,.22)}.accountant-settings-page .settings-btn:hover{color:#fff;transform:translateY(-2px)}.accountant-settings-page .settings-toast{position:fixed;right:24px;bottom:24px;z-index:9999;display:none;align-items:center;gap:12px;padding:14px 18px;border-radius:18px;color:var(--st-primary-dark);font-weight:900}.accountant-settings-page .settings-toast.show{display:flex;animation:fadeIn .18s ease}.accountant-settings-page .badge-soft{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:999px;background:var(--st-primary-soft);color:var(--st-primary-dark);font-weight:900}.accountant-settings-page .hint{color:var(--st-muted);font-size:12px;font-weight:700}@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}@media(max-width:991.98px){.accountant-settings-page .settings-layout{grid-template-columns:1fr}.accountant-settings-page .settings-tabs{position:static;grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:575.98px){.accountant-settings-page .settings-hero,.accountant-settings-page .settings-card{padding:20px;border-radius:20px}.accountant-settings-page .settings-tabs{grid-template-columns:1fr}.accountant-settings-page .settings-actions .btn,.accountant-settings-page .settings-btn{width:100%}.accountant-settings-page .setting-check,.accountant-settings-page .setting-switch{align-items:flex-start;flex-direction:column}}
</style>

<div class="accountant-settings-page">
	<!-- Page header and breadcrumb. -->
	<section class="settings-hero">
		<div class="breadcrumb-line"><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right mx-2"></i> Settings</div>
		<span class="settings-kicker"><i class="fa-solid fa-gear"></i> Accountant Preferences</span>
		<h3 class="mt-3 mb-2">Settings</h3>
		<p class="text-muted mb-0">Configure financial preferences, receipt rules, payment methods, notifications, security, backups, and interface options.</p>
	</section>

	<div class="settings-layout">
		<!-- Tab navigation for organized settings sections. -->
		<aside class="settings-tabs" aria-label="Accountant settings sections">
			<button class="settings-tab active" data-panel="general" type="button"><i class="fa-solid fa-sliders"></i> General</button>
			<button class="settings-tab" data-panel="receipts" type="button"><i class="fa-solid fa-receipt"></i> Receipts</button>
			<button class="settings-tab" data-panel="invoices" type="button"><i class="fa-solid fa-file-invoice"></i> Invoices</button>
			<button class="settings-tab" data-panel="payments" type="button"><i class="fa-solid fa-credit-card"></i> Payments</button>
			<button class="settings-tab" data-panel="notifications" type="button"><i class="fa-solid fa-bell"></i> Notifications</button>
			<button class="settings-tab" data-panel="security" type="button"><i class="fa-solid fa-shield-halved"></i> Security</button>
			<button class="settings-tab" data-panel="backup" type="button"><i class="fa-solid fa-download"></i> Backup & Export</button>
			<button class="settings-tab" data-panel="appearance" type="button"><i class="fa-solid fa-palette"></i> Appearance</button>
			<button class="settings-tab" data-panel="system" type="button"><i class="fa-solid fa-circle-info"></i> System Info</button>
		</aside>

		<main>
			<form id="settingsForm" novalidate>
				<!-- General financial preferences. -->
				<section class="settings-panel active" id="panel-general">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-school"></i> General Settings</span><h4 class="mt-3 mb-0">Default finance preferences</h4></div><span class="hint">Ready for future database persistence</span></div>
						<div class="row g-3">
							<div class="col-md-6"><label class="form-label">Default Academic Session</label><select class="form-select" name="default_session"><?php foreach($sessions as $session): ?><option><?php echo stValue($session); ?></option><?php endforeach; ?></select></div>
							<div class="col-md-6"><label class="form-label">Default Term</label><select class="form-select" name="default_term"><?php foreach($terms as $term): ?><option><?php echo stValue($term); ?></option><?php endforeach; ?></select></div>
							<div class="col-md-6"><label class="form-label">Currency</label><select class="form-select" name="currency"><?php foreach($currencies as $currency): ?><option><?php echo stValue($currency); ?></option><?php endforeach; ?></select></div>
							<div class="col-md-6"><label class="form-label">Date Format</label><select class="form-select" name="date_format"><?php foreach($dateFormats as $format): ?><option><?php echo stValue($format); ?></option><?php endforeach; ?></select></div>
						</div>
					</div>
				</section>

				<!-- Receipt customization settings. -->
				<section class="settings-panel" id="panel-receipts">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-receipt"></i> Receipt Settings</span><h4 class="mt-3 mb-0">Customize payment receipts</h4></div></div>
						<div class="row g-3">
							<div class="col-md-6"><label class="form-label">School Name</label><input class="form-control" name="school_name" value="<?php echo stValue($receiptSettings['school_name']); ?>"></div>
							<div class="col-md-6"><label class="form-label">School Phone Number</label><input class="form-control" name="school_phone" value="<?php echo stValue($receiptSettings['phone']); ?>"></div>
							<div class="col-md-6"><label class="form-label">School Email</label><input type="email" class="form-control" name="school_email" value="<?php echo stValue($receiptSettings['email']); ?>"></div>
							<div class="col-md-3"><label class="form-label">Receipt Prefix</label><input class="form-control" name="receipt_prefix" value="<?php echo stValue($receiptSettings['prefix']); ?>"></div>
							<div class="col-md-3"><label class="form-label">Starting Receipt Number</label><input type="number" class="form-control" name="receipt_start" value="<?php echo stValue($receiptSettings['start_number']); ?>"></div>
							<div class="col-12"><label class="form-label">School Address</label><textarea class="form-control" name="school_address"><?php echo stValue($receiptSettings['address']); ?></textarea></div>
							<div class="col-12"><label class="form-label">Footer Message</label><textarea class="form-control" name="footer_message"><?php echo stValue($receiptSettings['footer']); ?></textarea></div>
						</div>
						<div class="row g-3 mt-2">
							<?php $receiptChecks = ['Show School Logo', 'Show Authorized Signature', 'Show School Stamp', 'Auto Print After Payment']; foreach($receiptChecks as $index => $label): ?>
								<div class="col-md-6"><div class="setting-check"><label for="receipt<?php echo $index; ?>"><?php echo stValue($label); ?></label><input class="form-check-input" id="receipt<?php echo $index; ?>" type="checkbox" <?php echo $index < 3 ? 'checked' : ''; ?>></div></div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<!-- Invoice numbering and payment terms. -->
				<section class="settings-panel" id="panel-invoices">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-file-invoice"></i> Invoice Settings</span><h4 class="mt-3 mb-0">Numbering and payment terms</h4></div></div>
						<div class="row g-3">
							<div class="col-md-4"><label class="form-label">Invoice Prefix</label><input class="form-control" value="INV" name="invoice_prefix"></div>
							<div class="col-md-4"><label class="form-label">Starting Invoice Number</label><input type="number" class="form-control" value="1001" name="invoice_start"></div>
							<div class="col-md-4"><label class="form-label">Payment Due Days</label><input type="number" class="form-control" value="14" name="payment_due_days"></div>
							<div class="col-12"><label class="form-label">Default Payment Terms</label><textarea class="form-control" name="payment_terms">All school fees are due before the end of the selected term unless an approved payment plan exists.</textarea></div>
						</div>
					</div>
				</section>

				<!-- Accepted payment method configuration. -->
				<section class="settings-panel" id="panel-payments">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-credit-card"></i> Payment Settings</span><h4 class="mt-3 mb-0">Accepted payment methods</h4></div></div>
						<div class="row g-3">
							<?php $paymentMethods = ['Cash', 'Bank Transfer', 'POS', 'Online Payment', 'Cheque']; foreach($paymentMethods as $i => $method): ?>
								<div class="col-md-6"><div class="setting-check"><label for="method<?php echo $i; ?>"><i class="fa-solid fa-circle-check text-success me-2"></i><?php echo stValue($method); ?></label><input class="form-check-input" id="method<?php echo $i; ?>" type="checkbox" <?php echo $i < 4 ? 'checked' : ''; ?>></div></div>
							<?php endforeach; ?>
							<div class="col-md-6"><label class="form-label">Default Payment Method</label><select class="form-select"><option>Bank Transfer</option><option>Cash</option><option>POS</option><option>Online Payment</option><option>Cheque</option></select></div>
						</div>
					</div>
				</section>

				<!-- Notification toggle preferences. -->
				<section class="settings-panel" id="panel-notifications">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-bell"></i> Notification Settings</span><h4 class="mt-3 mb-0">Alerts and finance reminders</h4></div></div>
						<div class="row g-3">
							<?php $notifications = ['Payment Notifications', 'Outstanding Fee Alerts', 'Daily Financial Summary', 'Monthly Financial Report', 'Payroll Notifications', 'Email Notifications', 'SMS Notifications']; foreach($notifications as $i => $notification): ?>
								<div class="col-md-6"><div class="setting-switch"><label for="notify<?php echo $i; ?>"><?php echo stValue($notification); ?></label><div class="form-check form-switch m-0"><input class="form-check-input" id="notify<?php echo $i; ?>" type="checkbox" <?php echo $i !== 6 ? 'checked' : ''; ?>></div></div></div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<!-- Security and session controls. -->
				<section class="settings-panel" id="panel-security">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-shield-halved"></i> Security Settings</span><h4 class="mt-3 mb-0">Account protection controls</h4></div></div>
						<div class="row g-3">
							<div class="col-md-6"><div class="setting-switch"><label>Two-Factor Authentication <span class="hint d-block">Placeholder for future implementation</span></label><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox"></div></div></div>
							<div class="col-md-6"><label class="form-label">Session Timeout Duration</label><select class="form-select"><option>15 minutes</option><option selected>30 minutes</option><option>1 hour</option><option>2 hours</option></select></div>
							<div class="col-md-6"><div class="setting-switch"><label>Auto Logout After Inactivity</label><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" checked></div></div></div>
							<div class="col-md-6"><div class="setting-switch"><label>Login Notifications</label><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" checked></div></div></div>
							<div class="col-12"><div class="action-row d-flex flex-wrap gap-2"><button type="button" class="btn settings-btn save-section"><i class="fa-solid fa-floppy-disk me-2"></i>Save Security Settings</button><button type="button" class="btn btn-outline-secondary reset-section"><i class="fa-solid fa-rotate-left me-2"></i>Reset to Default</button></div></div>
						</div>
					</div>
				</section>

				<!-- Backup and export shortcuts. -->
				<section class="settings-panel" id="panel-backup">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-download"></i> Backup & Export Settings</span><h4 class="mt-3 mb-0">Finance data export shortcuts</h4></div><span class="hint">Database backup hooks can be attached later</span></div>
						<div class="export-grid">
							<?php $exports = [['Export Payment Records','fa-money-bill-transfer'],['Export Receipts','fa-receipt'],['Export Expenses','fa-file-invoice-dollar'],['Export Financial Reports','fa-chart-pie']]; foreach($exports as $export): ?>
								<div class="export-card"><div><i class="fa-solid <?php echo $export[1]; ?> text-success me-2"></i><strong><?php echo stValue($export[0]); ?></strong><span class="hint d-block">Placeholder export action</span></div><button type="button" class="btn btn-outline-success btn-sm export-action">Export</button></div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<!-- Appearance placeholders for future UI personalization. -->
				<section class="settings-panel" id="panel-appearance">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-palette"></i> Appearance Settings</span><h4 class="mt-3 mb-0">Dashboard personalization</h4></div></div>
						<div class="row g-3">
							<div class="col-md-6"><label class="form-label">Dashboard Theme</label><select class="form-select"><option>Light</option><option>Dark</option></select></div>
							<div class="col-md-6"><label class="form-label">Sidebar Style</label><select class="form-select"><option>Expanded</option><option>Compact</option></select></div>
							<div class="col-md-6"><label class="form-label">Card Layout</label><select class="form-select"><option>Comfortable Cards</option><option>Dense Cards</option></select></div>
							<div class="col-md-6"><label class="form-label">Table Density</label><select class="form-select"><option>Comfortable</option><option>Compact</option></select></div>
						</div>
					</div>
				</section>

				<!-- Read-only system information. -->
				<section class="settings-panel" id="panel-system">
					<div class="settings-card">
						<div class="section-title"><div><span class="badge-soft"><i class="fa-solid fa-circle-info"></i> System Information</span><h4 class="mt-3 mb-0">Read-only account and platform status</h4></div></div>
						<div class="info-grid">
							<?php foreach($systemInfo as $label => $value): ?>
								<div class="info-card"><span><?php echo stValue($label); ?></span><strong><?php echo stValue($value); ?></strong></div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<!-- Global save and reset actions. -->
				<div class="settings-actions">
					<button type="button" class="btn settings-btn" id="saveSettings"><i class="fa-solid fa-floppy-disk me-2"></i>Save Settings</button>
					<button type="button" class="btn btn-outline-secondary" id="resetSettings"><i class="fa-solid fa-rotate-left me-2"></i>Reset to Default</button>
					<a href="dashboard.php" class="btn btn-outline-danger"><i class="fa-solid fa-xmark me-2"></i>Cancel</a>
				</div>
			</form>
		</main>
	</div>

	<div class="settings-toast" id="settingsToast"><i class="fa-solid fa-circle-check text-success"></i><span>Settings saved successfully.</span></div>
</div>

</div>
</div>

<script data-cfasync="false" type="text/javascript">
// Settings page behavior: tab navigation, placeholder saves, resets, and export feedback.
(function(){
	function qs(selector, scope){ return (scope || document).querySelector(selector); }
	function qsa(selector, scope){ return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); }
	function showToast(message){ var toast = qs('#settingsToast'); toast.querySelector('span').textContent = message; toast.classList.add('show'); window.clearTimeout(showToast.timer); showToast.timer = window.setTimeout(function(){ toast.classList.remove('show'); }, 2600); }
	qsa('.settings-tab').forEach(function(tab){ tab.addEventListener('click', function(){ qsa('.settings-tab').forEach(function(item){ item.classList.remove('active'); }); qsa('.settings-panel').forEach(function(panel){ panel.classList.remove('active'); }); tab.classList.add('active'); qs('#panel-' + tab.dataset.panel).classList.add('active'); }); });
	qs('#saveSettings').addEventListener('click', function(){ showToast('Settings saved successfully. Database persistence can be connected later.'); });
	qs('#resetSettings').addEventListener('click', function(){ qs('#settingsForm').reset(); showToast('Settings reset to the default placeholder values.'); });
	qsa('.save-section').forEach(function(btn){ btn.addEventListener('click', function(){ showToast('Security settings saved successfully.'); }); });
	qsa('.reset-section').forEach(function(btn){ btn.addEventListener('click', function(){ showToast('Security settings reset to default.'); }); });
	qsa('.export-action').forEach(function(btn){ btn.addEventListener('click', function(){ var title = btn.closest('.export-card').querySelector('strong').textContent; showToast(title + ' queued for export.'); }); });
}());
</script>

<?php require_once('includes/footer.php'); ?>
