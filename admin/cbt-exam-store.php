<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\CBTService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'cbt-exams.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: cbt-exams.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$cbtService = new CBTService();
$id = (int) ($_POST['id'] ?? 0) ?: null;
$result = $cbtService->saveExam($_POST, $id, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: ' . $redirectUrl);
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: ' . $redirectUrl);
exit;
