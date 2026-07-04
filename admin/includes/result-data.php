<?php
/**
 * Placeholder Result Management datasets.
 * Replace these arrays with MySQL-backed repository calls during backend integration.
 */
$resultSessions = ['2025/2026', '2026/2027'];
$resultTerms = ['First Term', 'Second Term', 'Third Term'];
$resultClasses = ['JSS 1A', 'JSS 2B', 'JSS 3A', 'SS 1 Science', 'SS 2 Science', 'SS 3 Arts'];
$resultSubjects = ['Mathematics', 'English Language', 'Physics', 'Chemistry', 'Computer Science', 'Biology'];
$resultStatuses = ['Draft', 'Submitted', 'Approved', 'Published', 'Locked'];

$resultBatches = [
    ['id' => 1, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 2 Science', 'subject' => 'Mathematics', 'teacher' => 'John Musa', 'students' => 42, 'status' => 'Submitted', 'average' => '72%', 'submitted_at' => '2026-07-01'],
    ['id' => 2, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'JSS 1A', 'subject' => 'English Language', 'teacher' => 'Grace Audu', 'students' => 38, 'status' => 'Approved', 'average' => '68%', 'submitted_at' => '2026-07-02'],
    ['id' => 3, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 1 Science', 'subject' => 'Physics', 'teacher' => 'Peter James', 'students' => 35, 'status' => 'Published', 'average' => '75%', 'submitted_at' => '2026-07-02'],
    ['id' => 4, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'teacher' => 'Hauwa Lawal', 'students' => 40, 'status' => 'Locked', 'average' => '81%', 'submitted_at' => '2026-07-03'],
    ['id' => 5, 'session' => '2025/2026', 'term' => 'First Term', 'class' => 'SS 3 Arts', 'subject' => 'Civic Education', 'teacher' => 'Emmanuel Ade', 'students' => 31, 'status' => 'Draft', 'average' => '64%', 'submitted_at' => '-'],
];

$gradeSettings = [
    ['id' => 1, 'score_range' => '70 - 100', 'grade' => 'A', 'point' => '5.0', 'remark' => 'Excellent', 'status' => 'Active'],
    ['id' => 2, 'score_range' => '60 - 69', 'grade' => 'B', 'point' => '4.0', 'remark' => 'Very Good', 'status' => 'Active'],
    ['id' => 3, 'score_range' => '50 - 59', 'grade' => 'C', 'point' => '3.0', 'remark' => 'Good', 'status' => 'Active'],
    ['id' => 4, 'score_range' => '40 - 49', 'grade' => 'D', 'point' => '2.0', 'remark' => 'Fair', 'status' => 'Active'],
    ['id' => 5, 'score_range' => '0 - 39', 'grade' => 'F', 'point' => '0.0', 'remark' => 'Needs Improvement', 'status' => 'Active'],
];

$remarkSettings = [
    ['id' => 1, 'category' => 'Principal Remark', 'range' => '70 - 100', 'message' => 'Excellent result. Keep it up.', 'status' => 'Active'],
    ['id' => 2, 'category' => 'Class Teacher Remark', 'range' => '60 - 69', 'message' => 'Very good performance with room for improvement.', 'status' => 'Active'],
    ['id' => 3, 'category' => 'Promotion Remark', 'range' => '50 - 100', 'message' => 'Promoted to the next class.', 'status' => 'Active'],
    ['id' => 4, 'category' => 'Improvement Remark', 'range' => '0 - 49', 'message' => 'Requires closer academic support.', 'status' => 'Active'],
];

$broadsheetRows = [
    ['position' => '1st', 'reg_no' => 'REG-2026-001', 'student' => 'Musa Ibrahim', 'class' => 'SS 2 Science', 'total' => 684, 'average' => '85.5%', 'grade' => 'A', 'remark' => 'Excellent'],
    ['position' => '2nd', 'reg_no' => 'REG-2026-002', 'student' => 'Aisha Bello', 'class' => 'SS 2 Science', 'total' => 641, 'average' => '80.1%', 'grade' => 'A', 'remark' => 'Excellent'],
    ['position' => '3rd', 'reg_no' => 'REG-2026-003', 'student' => 'Daniel Okafor', 'class' => 'SS 2 Science', 'total' => 590, 'average' => '73.8%', 'grade' => 'A', 'remark' => 'Very Good'],
    ['position' => '4th', 'reg_no' => 'REG-2026-004', 'student' => 'Fatima Sani', 'class' => 'SS 2 Science', 'total' => 552, 'average' => '69%', 'grade' => 'B', 'remark' => 'Very Good'],
];

$resultReportCardSubjects = [
    ['subject' => 'Mathematics', 'ca1' => 18, 'ca2' => 17, 'ca3' => 19, 'exam' => 42, 'total' => 96, 'grade' => 'A', 'remark' => 'Excellent'],
    ['subject' => 'English Language', 'ca1' => 15, 'ca2' => 16, 'ca3' => 17, 'exam' => 38, 'total' => 86, 'grade' => 'A', 'remark' => 'Excellent'],
    ['subject' => 'Physics', 'ca1' => 14, 'ca2' => 15, 'ca3' => 16, 'exam' => 35, 'total' => 80, 'grade' => 'A', 'remark' => 'Very Good'],
    ['subject' => 'Chemistry', 'ca1' => 13, 'ca2' => 14, 'ca3' => 15, 'exam' => 34, 'total' => 76, 'grade' => 'A', 'remark' => 'Very Good'],
    ['subject' => 'Computer Science', 'ca1' => 19, 'ca2' => 18, 'ca3' => 18, 'exam' => 40, 'total' => 95, 'grade' => 'A', 'remark' => 'Excellent'],
];

$resultGeneralSettings = [
    'pass_mark' => 50,
    'enable_position_calculation' => true,
    'show_position_on_report_card' => true,
    'show_average' => true,
    'auto_publish_results' => false,
    'auto_lock_published_results' => true,
];
function sms_result_count(array $items, string $field, string $value): int
{
    return count(array_filter($items, fn($item) => (string)($item[$field] ?? '') === $value));
}

function sms_result_status_class(string $status): string
{
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
?>