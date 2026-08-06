<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_require_auth('student');

use App\Services\CBTService;

$examId = (int) ($_POST['exam_id'] ?? 0);
$currentQ = max(0, (int) ($_POST['current_q'] ?? 0));

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: quiz.php');
    exit;
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    sms_flash_set('error', 'Your session expired. Please try again.');
    header('Location: quiz-question.php?exam_id=' . $examId . '&q=' . $currentQ);
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

$cbtService->saveAnswer($attemptId, (int) ($_POST['question_id'] ?? 0), $_POST['selected_option'] ?? null);

$navTarget = (string) ($_POST['nav_target'] ?? 'save');
$target = match (true) {
    $navTarget === 'prev' => $currentQ - 1,
    $navTarget === 'next' => $currentQ + 1,
    $navTarget === 'save' => $currentQ,
    is_numeric($navTarget) => (int) $navTarget,
    default => $currentQ,
};
$target = max(0, $target);

header('Location: quiz-question.php?exam_id=' . $examId . '&q=' . $target);
exit;
