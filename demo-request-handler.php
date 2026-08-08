<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers/auth.php';

use App\Models\SettingsModel;
use App\Services\Mailer;

header('Content-Type: application/json');

function demoRequestRespond(bool $success, string $message): never
{
    http_response_code($success ? 200 : 422);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    demoRequestRespond(false, 'Invalid request method.');
}

if (!sms_verify_csrf($_POST['_token'] ?? null)) {
    demoRequestRespond(false, 'Your session expired. Please refresh the page and try again.');
}

// Honeypot: real visitors never see or fill this field; bots that fill
// every input do. Pretend success without sending anything.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    demoRequestRespond(true, 'Thank you — our team will reach out within one business day.');
}

$schoolName = trim((string) ($_POST['school_name'] ?? ''));
$contactPerson = trim((string) ($_POST['contact_person'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$schoolType = trim((string) ($_POST['school_type'] ?? ''));
$studentPopulation = trim((string) ($_POST['student_population'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$errors = [];
if ($schoolName === '') { $errors[] = 'School name is required.'; }
if ($contactPerson === '') { $errors[] = 'Contact person is required.'; }
if (!preg_match('/^[+0-9()\-\s]{7,20}$/', $phone)) { $errors[] = 'A valid phone number is required.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid email address is required.'; }
if ($schoolType === '') { $errors[] = 'School type is required.'; }
if ($studentPopulation === '') { $errors[] = 'Student population is required.'; }

if ($errors) {
    demoRequestRespond(false, implode(' ', $errors));
}

$schoolTypeLabels = [
    'private' => 'Private School',
    'public' => 'Public School',
    'college' => 'College',
    'tutorial' => 'Tutorial Centre',
    'group' => 'Educational Group',
];
$populationLabels = [
    'under-100' => 'Under 100',
    '100-500' => '100 - 500',
    '500-2000' => '500 - 2,000',
    '2000-plus' => '2,000+',
];

$settings = (new SettingsModel())->all();
$brandName = (string) ($settings['school.name']['value'] ?? 'School Management System');
$notifyTo = (string) (app_config('mail.to_address') ?: ($settings['school.email']['value'] ?? ''));

$schoolTypeLabel = $schoolTypeLabels[$schoolType] ?? $schoolType;
$populationLabel = $populationLabels[$studentPopulation] ?? $studentPopulation;

$notifyBody = "A new demo request was submitted on {$brandName}.\n\n"
    . "School Name: {$schoolName}\n"
    . "Contact Person: {$contactPerson}\n"
    . "Phone: {$phone}\n"
    . "Email: {$email}\n"
    . "School Type: {$schoolTypeLabel}\n"
    . "Student Population: {$populationLabel}\n"
    . 'Message: ' . ($message !== '' ? $message : '(none)') . "\n\n"
    . 'Submitted: ' . date('Y-m-d H:i:s');

if ($notifyTo === '') {
    demoRequestRespond(false, 'This site has no notification email configured yet (MAIL_TO_ADDRESS or School Settings > Email). Please contact us directly for now.');
}

$notifySent = Mailer::send($notifyTo, "New Demo Request \xE2\x80\x94 {$schoolName}", $notifyBody);

// A non-null lastError means SMTP was attempted (mail is configured) and
// failed — even if send() still returned true via the local log-file
// fallback. That fallback exists so nothing is silently lost, but for this
// form a "success" that never actually emailed anyone is not a success:
// surface the real reason instead of a false positive.
if (!$notifySent || Mailer::$lastError !== null) {
    $reason = Mailer::$lastError ?? 'Unknown mail delivery error.';
    demoRequestRespond(false, "We couldn't send your request right now ({$reason}). Please try again later or contact us directly.");
}

$replyBody = "Hi {$contactPerson},\n\n"
    . "Thank you for your interest in {$brandName}. We've received your demo request and our team will reach out within one business day to schedule your walkthrough.\n\n"
    . "Here's a summary of what you submitted:\n"
    . "School: {$schoolName}\n"
    . "School Type: {$schoolTypeLabel}\n"
    . "Student Population: {$populationLabel}\n\n"
    . "If any of this needs correcting, just reply to this email.\n\n"
    . "Best regards,\n"
    . $brandName;

$replySent = Mailer::send($email, "We've received your demo request \xE2\x80\x94 {$brandName}", $replyBody);

demoRequestRespond(true, $replySent
    ? 'Thank you — our team will reach out within one business day. A confirmation has been sent to your email.'
    : 'Thank you — your request was received and our team will reach out within one business day.');
