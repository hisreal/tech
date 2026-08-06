<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('accountant');

use App\Models\SettingsModel;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: settings.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: settings.php');
    exit;
}

$prefix = trim((string) ($_POST['receipt_prefix'] ?? ''));
$start = (int) ($_POST['receipt_start'] ?? 0);
$footer = trim((string) ($_POST['receipt_footer'] ?? ''));

if ($prefix === '' || $start < 1) {
    sms_flash_set('error', 'Enter a valid receipt prefix and starting number.');
    header('Location: settings.php');
    exit;
}

$settingsModel = new SettingsModel();
$before = $settingsModel->all();
$old = [
    'receipt_prefix' => $before['finance.receipt_prefix']['value'] ?? null,
    'receipt_start_number' => $before['finance.receipt_start_number']['value'] ?? null,
    'receipt_footer' => $before['finance.receipt_footer']['value'] ?? null,
];

$settingsModel->upsertMany([
    'finance.receipt_prefix' => ['value' => $prefix, 'type' => 'string', 'group' => 'finance'],
    'finance.receipt_start_number' => ['value' => (string) $start, 'type' => 'number', 'group' => 'finance'],
    'finance.receipt_footer' => ['value' => $footer, 'type' => 'string', 'group' => 'finance'],
], (int) sms_current_user()['id']);

$settingsModel->audit(sms_current_user(), 'receipts', $old, ['receipt_prefix' => $prefix, 'receipt_start_number' => $start, 'receipt_footer' => $footer]);

sms_flash_set('success', 'Receipt settings saved successfully.');
header('Location: settings.php');
exit;
