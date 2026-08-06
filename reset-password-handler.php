<?php
require_once __DIR__ . '/includes/helpers/auth.php';

$token = (string) ($_POST['token'] ?? '');
$portal = preg_replace('/[^a-z]/', '', (string) ($_POST['portal'] ?? 'admin'));
$fallback = 'reset-password.php?token=' . urlencode($token) . '&portal=' . urlencode($portal);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $fallback);
    exit;
}

$result = sms_auth()->resetPassword(
    $token,
    (string) ($_POST['password'] ?? ''),
    (string) ($_POST['password_confirmation'] ?? '')
);

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    if (!empty($result['errors'])) {
        $_SESSION['_reset_errors'] = $result['errors'];
    }
    header('Location: ' . $fallback);
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: ' . (in_array($result['portal'] ?? '', ['teacher', 'student', 'accountant'], true) ? $result['portal'] . '/login.php' : 'admin/login.php'));
exit;