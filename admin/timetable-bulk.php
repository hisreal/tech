<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth(['super-admin', 'admin']);

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
$ids = array_map('intval', (array) ($_POST['ids'] ?? []));
$action = (string) ($_POST['bulk_action'] ?? '');

if (!$ids) {
    sms_flash_set('error', 'Please select at least one timetable entry.');
    header('Location: ' . $redirectUrl);
    exit;
}

$result = $action === 'delete'
    ? $timetableService->bulkDelete($ids, sms_current_user())
    : $timetableService->bulkSetStatus($ids, $action, sms_current_user());

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
