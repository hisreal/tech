<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\TimetableService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'timetables.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: timetables.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$timetableService = new TimetableService();
$id = (int) ($_POST['id'] ?? 0);
$result = $timetableService->update($id, $_POST, sms_current_user());

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
