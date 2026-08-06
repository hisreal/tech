<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

use App\Core\Session;
use App\Services\TimetableService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: timetable-settings.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: timetable-settings.php');
    exit;
}

$timetableService = new TimetableService();
$result = $timetableService->saveGeneralSettings($_POST, sms_current_user());

if (!$result['success']) {
    sms_flash_set('error', $result['message']);
    Session::flashInput($_POST);
    Session::flashErrors($result['errors'] ?? []);
    header('Location: timetable-settings.php');
    exit;
}

sms_flash_set('success', $result['message']);
header('Location: timetable-settings.php');
exit;
