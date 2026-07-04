<?php
/**
 * Placeholder Timetable Management datasets.
 * Future MySQL integration should replace text display values with joins from IDs.
 */
$timetableSessions = [['id' => 1, 'name' => '2025/2026'], ['id' => 2, 'name' => '2026/2027']];
$timetableTerms = [['id' => 1, 'name' => 'First Term'], ['id' => 2, 'name' => 'Second Term'], ['id' => 3, 'name' => 'Third Term']];
$timetableClasses = [['id' => 1, 'name' => 'JSS 1A'], ['id' => 2, 'name' => 'JSS 2B'], ['id' => 3, 'name' => 'SS 1 Science'], ['id' => 4, 'name' => 'SS 2 Science']];
$timetableSections = [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B'], ['id' => 3, 'name' => 'Science'], ['id' => 4, 'name' => 'Arts']];
$timetableSubjects = [['id' => 1, 'name' => 'Mathematics'], ['id' => 2, 'name' => 'English Language'], ['id' => 3, 'name' => 'Physics'], ['id' => 4, 'name' => 'Computer Science'], ['id' => 5, 'name' => 'Biology']];
$timetableTeachers = [['id' => 1, 'name' => 'John Musa', 'department' => 'Science'], ['id' => 2, 'name' => 'Grace Audu', 'department' => 'Languages'], ['id' => 3, 'name' => 'Peter James', 'department' => 'ICT'], ['id' => 4, 'name' => 'Hauwa Lawal', 'department' => 'Mathematics']];
$timetableDepartments = ['Science', 'Languages', 'ICT', 'Mathematics', 'Humanities'];
$timetableVenues = [['id' => 1, 'name' => 'Room 12'], ['id' => 2, 'name' => 'Science Laboratory'], ['id' => 3, 'name' => 'Computer Lab'], ['id' => 4, 'name' => 'Room 5']];
$timetableDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timetableStatuses = ['Draft', 'Published', 'Active', 'Unpublished'];

$timetableEntries = [
    ['id' => 1, 'session_id' => 1, 'term_id' => 1, 'class_id' => 4, 'section_id' => 3, 'subject_id' => 1, 'teacher_id' => 1, 'venue_id' => 1, 'day' => 'Monday', 'start_time' => '08:00 AM', 'end_time' => '08:40 AM', 'subject' => 'Mathematics', 'teacher' => 'John Musa', 'class' => 'SS 2 Science', 'section' => 'Science', 'venue' => 'Room 12', 'status' => 'Published'],
    ['id' => 2, 'session_id' => 1, 'term_id' => 1, 'class_id' => 4, 'section_id' => 3, 'subject_id' => 3, 'teacher_id' => 2, 'venue_id' => 2, 'day' => 'Tuesday', 'start_time' => '09:20 AM', 'end_time' => '10:00 AM', 'subject' => 'Physics', 'teacher' => 'Grace Audu', 'class' => 'SS 2 Science', 'section' => 'Science', 'venue' => 'Science Laboratory', 'status' => 'Active'],
    ['id' => 3, 'session_id' => 1, 'term_id' => 1, 'class_id' => 2, 'section_id' => 2, 'subject_id' => 4, 'teacher_id' => 3, 'venue_id' => 3, 'day' => 'Wednesday', 'start_time' => '10:00 AM', 'end_time' => '10:40 AM', 'subject' => 'Computer Science', 'teacher' => 'Peter James', 'class' => 'JSS 2B', 'section' => 'B', 'venue' => 'Computer Lab', 'status' => 'Draft'],
    ['id' => 4, 'session_id' => 1, 'term_id' => 1, 'class_id' => 1, 'section_id' => 1, 'subject_id' => 2, 'teacher_id' => 2, 'venue_id' => 4, 'day' => 'Thursday', 'start_time' => '11:20 AM', 'end_time' => '12:00 PM', 'subject' => 'English Language', 'teacher' => 'Grace Audu', 'class' => 'JSS 1A', 'section' => 'A', 'venue' => 'Room 5', 'status' => 'Unpublished'],
    ['id' => 5, 'session_id' => 1, 'term_id' => 1, 'class_id' => 3, 'section_id' => 3, 'subject_id' => 5, 'teacher_id' => 4, 'venue_id' => 2, 'day' => 'Friday', 'start_time' => '12:00 PM', 'end_time' => '12:40 PM', 'subject' => 'Biology', 'teacher' => 'Hauwa Lawal', 'class' => 'SS 1 Science', 'section' => 'Science', 'venue' => 'Science Laboratory', 'status' => 'Published'],
];

$timetablePeriods = [
    ['id' => 1, 'period' => 'Period 1', 'start' => '08:00 AM', 'end' => '08:40 AM'],
    ['id' => 2, 'period' => 'Period 2', 'start' => '08:40 AM', 'end' => '09:20 AM'],
    ['id' => 3, 'period' => 'Period 3', 'start' => '09:20 AM', 'end' => '10:00 AM'],
    ['id' => 4, 'period' => 'Break', 'start' => '10:00 AM', 'end' => '10:30 AM'],
    ['id' => 5, 'period' => 'Period 4', 'start' => '10:30 AM', 'end' => '11:10 AM'],
    ['id' => 6, 'period' => 'Period 5', 'start' => '11:10 AM', 'end' => '11:50 AM'],
];

$timetableSettings = [
    'opening_time' => '08:00',
    'closing_time' => '15:00',
    'default_lesson_duration' => 40,
    'break_duration' => 30,
    'periods_per_day' => 8,
    'enable_conflict_detection' => true,
    'allow_double_periods' => true,
    'auto_assign_break_time' => true,
    'default_venue' => 'Room 12',
];

function sms_timetable_count(array $items, string $field, string $value): int
{
    return count(array_filter($items, fn($item) => (string)($item[$field] ?? '') === $value));
}

function sms_timetable_status_class(string $status): string
{
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
?>