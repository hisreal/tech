<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('teacher');

use App\Services\ResultService;
use App\Services\TeacherService;

$redirectQuery = (string) ($_POST['redirect_query'] ?? '');
$redirectUrl = 'result-management.php' . ($redirectQuery !== '' ? '?' . $redirectQuery : '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $redirectUrl);
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: ' . $redirectUrl);
    exit;
}

$resultService = new ResultService();
$teacherService = new TeacherService();
$currentUser = sms_current_user();
$teacherId = $teacherService->teacherIdForUser((int) $currentUser['id']);

$batchId = (int) ($_POST['batch_id'] ?? 0);
$batch = $resultService->findBatch($batchId);

if (!$teacherId || !$batch || !$resultService->teacherOwnsClassSection($teacherId, (int) $batch['class_id'], $batch['section_id'] !== null ? (int) $batch['section_id'] : null) || !$resultService->teacherOwnsSubject($teacherId, (int) $batch['subject_id'])) {
    sms_flash_set('error', 'You are not assigned to that class and subject.');
    header('Location: ' . $redirectUrl);
    exit;
}

$scores = (array) ($_POST['scores'] ?? []);
$result = $resultService->saveScores($batchId, $scores, $currentUser);

sms_flash_set($result['success'] ? 'success' : 'error', $result['message']);
header('Location: ' . $redirectUrl);
exit;
