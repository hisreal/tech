<?php
require_once __DIR__ . '/includes/helpers/auth.php';

$portal = preg_replace('/[^a-z]/', '', (string) ($_POST['portal'] ?? 'admin'));
$fallback = 'forgot-password.php?portal=' . urlencode($portal);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $fallback);
    exit;
}

$result = sms_auth()->createPasswordReset((string) ($_POST['identifier'] ?? ''));
sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);

if ($result['success'] && isset($result['token'])) {
    $_SESSION['_dev_reset_token'] = $result['token'];
    header('Location: reset-password.php?token=' . urlencode($result['token']));
    exit;
}

header('Location: ' . $fallback);
exit;