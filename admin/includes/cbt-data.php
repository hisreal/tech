<?php
/**
 * Placeholder CBT Management datasets.
 * Replace arrays with MySQL repository queries when backend integration begins.
 */
$cbtSessions = ['2025/2026', '2026/2027'];
$cbtTerms = ['First Term', 'Second Term', 'Third Term'];
$cbtClasses = ['JSS 1A', 'JSS 2B', 'JSS 3A', 'SS 1 Science', 'SS 2 Science', 'SS 3 Arts'];
$cbtSubjects = ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Computer Science', 'Biology'];
$cbtStatuses = ['Draft', 'Published', 'Active', 'Completed', 'Archived'];

$cbtExams = [
    ['id' => 1, 'title' => 'First Term Mathematics CBT', 'session' => '2025/2026', 'term' => 'First Term', 'subject' => 'Mathematics', 'teacher' => 'John Musa', 'class' => 'SS 2 Science', 'duration' => '45 mins', 'questions' => 40, 'attempts' => 118, 'average' => '74%', 'status' => 'Active', 'updated_at' => '2026-07-03'],
    ['id' => 2, 'title' => 'JSS 1 English Language Test', 'session' => '2025/2026', 'term' => 'First Term', 'subject' => 'English Language', 'teacher' => 'Grace Audu', 'class' => 'JSS 1A', 'duration' => '35 mins', 'questions' => 30, 'attempts' => 96, 'average' => '68%', 'status' => 'Published', 'updated_at' => '2026-07-02'],
    ['id' => 3, 'title' => 'Physics Practical Theory CBT', 'session' => '2025/2026', 'term' => 'First Term', 'subject' => 'Physics', 'teacher' => 'Peter James', 'class' => 'SS 1 Science', 'duration' => '50 mins', 'questions' => 45, 'attempts' => 84, 'average' => '71%', 'status' => 'Completed', 'updated_at' => '2026-07-01'],
    ['id' => 4, 'title' => 'Computer Science Basics', 'session' => '2025/2026', 'term' => 'First Term', 'subject' => 'Computer Science', 'teacher' => 'Hauwa Lawal', 'class' => 'JSS 2B', 'duration' => '30 mins', 'questions' => 25, 'attempts' => 76, 'average' => '81%', 'status' => 'Draft', 'updated_at' => '2026-06-30'],
    ['id' => 5, 'title' => 'SS3 Biology Revision Exam', 'session' => '2025/2026', 'term' => 'First Term', 'subject' => 'Biology', 'teacher' => 'Emmanuel Ade', 'class' => 'SS 3 Arts', 'duration' => '60 mins', 'questions' => 50, 'attempts' => 62, 'average' => '66%', 'status' => 'Archived', 'updated_at' => '2026-06-26'],
];

$cbtQuestions = [
    ['number' => 1, 'question' => 'What is the value of 5 x 5?', 'options' => ['20', '25', '30', '35'], 'answer' => 'B'],
    ['number' => 2, 'question' => 'Which symbol represents oxygen?', 'options' => ['O', 'Ox', 'Og', 'Oy'], 'answer' => 'A'],
    ['number' => 3, 'question' => 'A computer CPU is responsible for what?', 'options' => ['Printing', 'Processing', 'Scanning', 'Typing'], 'answer' => 'B'],
    ['number' => 4, 'question' => 'The sum of angles in a triangle is?', 'options' => ['90 degrees', '120 degrees', '180 degrees', '360 degrees'], 'answer' => 'C'],
];

$cbtAttempts = [
    ['id' => 1, 'student' => 'Musa Ibrahim', 'reg_no' => 'REG-2026-001', 'class' => 'SS 2 Science', 'subject' => 'Mathematics', 'exam' => 'First Term Mathematics CBT', 'start_time' => '08:10 AM', 'end_time' => '08:52 AM', 'score' => 34, 'grade' => 'A', 'percentage' => '85%', 'status' => 'Passed'],
    ['id' => 2, 'student' => 'Aisha Bello', 'reg_no' => 'REG-2026-002', 'class' => 'SS 2 Science', 'subject' => 'Mathematics', 'exam' => 'First Term Mathematics CBT', 'start_time' => '08:15 AM', 'end_time' => '08:59 AM', 'score' => 28, 'grade' => 'B', 'percentage' => '70%', 'status' => 'Passed'],
    ['id' => 3, 'student' => 'Daniel Okafor', 'reg_no' => 'REG-2026-003', 'class' => 'JSS 1A', 'subject' => 'English Language', 'exam' => 'JSS 1 English Language Test', 'start_time' => '09:00 AM', 'end_time' => '09:32 AM', 'score' => 19, 'grade' => 'D', 'percentage' => '47.5%', 'status' => 'Failed'],
    ['id' => 4, 'student' => 'Fatima Sani', 'reg_no' => 'REG-2026-004', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'exam' => 'Computer Science Basics', 'start_time' => '10:05 AM', 'end_time' => '10:33 AM', 'score' => 23, 'grade' => 'A', 'percentage' => '92%', 'status' => 'Passed'],
];

$cbtActivities = [
    ['type' => 'Created', 'title' => 'Computer Science Basics', 'meta' => 'Created by Hauwa Lawal'],
    ['type' => 'Completed', 'title' => 'Physics Practical Theory CBT', 'meta' => '84 attempts submitted'],
    ['type' => 'Attempt', 'title' => 'Musa Ibrahim scored 85%', 'meta' => 'First Term Mathematics CBT'],
    ['type' => 'Published', 'title' => 'JSS 1 English Language Test', 'meta' => 'Published by Administrator'],
];

$cbtSettings = [
    'pass_mark' => 50,
    'default_duration' => 30,
    'maximum_attempts' => 1,
    'randomize_questions' => true,
    'randomize_answers' => true,
    'auto_submit' => true,
    'show_result_immediately' => true,
    'allow_review' => true,
    'fullscreen_mode' => true,
    'prevent_multiple_login' => true,
    'auto_logout' => true,
    'browser_restrictions' => false,
];

function sms_cbt_count(array $items, string $field, string $value): int
{
    return count(array_filter($items, fn($item) => (string)($item[$field] ?? '') === $value));
}

function sms_cbt_total_questions(array $items): int
{
    return array_sum(array_map(fn($item) => (int)($item['questions'] ?? 0), $items));
}

function sms_cbt_status_class(string $status): string
{
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
?>