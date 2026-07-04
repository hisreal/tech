<?php
/**
 * Shared login form processor used by every role-specific login endpoint.
 */

require_once __DIR__ . '/helpers/auth.php';

$portal = $smsLoginPortal ?? '';
$fallback = $smsLoginFallback ?? '../login.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $fallback);
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try logging in again.');
    header('Location: ' . $fallback);
    exit;
}

$result = sms_auth()->attempt(
    (string) ($_POST['username'] ?? ''),
    (string) ($_POST['password'] ?? ''),
    $portal,
    isset($_POST['remember']) && (string) $_POST['remember'] === '1'
);

if ($result['success'] === true) {
    header('Location: ' . $result['redirect']);
    exit;
}

sms_flash_set('error', $result['message']);
$_SESSION['_old_login_identifier'] = (string) ($_POST['username'] ?? '');
header('Location: ' . $fallback);
exit;