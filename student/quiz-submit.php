<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('student');

use App\Services\CBTService;

$examId = (int) ($_POST['exam_id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: quiz.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: quiz-question.php?exam_id=' . $examId);
    exit;
}

$cbtService = new CBTService();
$studentId = $cbtService->studentIdForUser((int) sms_current_user()['id']);
$attemptId = (int) ($_POST['attempt_id'] ?? 0);
$attempt = $cbtService->attemptWithExam($attemptId);

if (!$studentId || !$attempt || (int) $attempt['student_id'] !== $studentId) {
    sms_flash_set('error', 'This attempt could not be found.');
    header('Location: quiz.php');
    exit;
}

$cbtService->submitAttempt($attemptId);

header('Location: quiz-question.php?exam_id=' . $examId);
exit;
