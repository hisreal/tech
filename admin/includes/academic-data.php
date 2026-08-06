<?php
/** Academic Management data, backed by App\Services\AcademicService. Every tab is search/filter/pagination aware. */

use App\Services\AcademicService;

$academicService = new AcademicService();

/** Reads and sanitizes GET params for one tab's search/filter/page state. */
function sms_academic_params(string $prefix, array $fields): array
{
    $params = ['search' => trim((string) ($_GET[$prefix . '_search'] ?? ''))];
    foreach ($fields as $field) {
        $params[$field] = trim((string) ($_GET[$prefix . '_' . $field] ?? ''));
    }
    $params['page'] = max(1, (int) ($_GET[$prefix . '_page'] ?? 1));

    return $params;
}

$sessionParams = sms_academic_params('sessions', ['status']);
$termParams = sms_academic_params('terms', ['session', 'status']);
$classParams = sms_academic_params('classes', ['level', 'status']);
$sectionParams = sms_academic_params('sections', ['class', 'status']);
$departmentParams = sms_academic_params('departments', ['status']);
$subjectParams = sms_academic_params('subjects', ['department', 'type', 'status']);
$calendarParams = sms_academic_params('calendar', ['type', 'status', 'session']);

$sessionsResult = $academicService->listSessions(['search' => $sessionParams['search'], 'status' => $sessionParams['status']], $sessionParams['page']);
$termsResult = $academicService->listTerms(['search' => $termParams['search'], 'session_id' => $termParams['session'], 'status' => $termParams['status']], $termParams['page']);
$classesResult = $academicService->listClasses(['search' => $classParams['search'], 'level' => $classParams['level'], 'status' => $classParams['status']], $classParams['page']);
$sectionsResult = $academicService->listSections(['search' => $sectionParams['search'], 'class_id' => $sectionParams['class'], 'status' => $sectionParams['status']], $sectionParams['page']);
$departmentsResult = $academicService->listDepartments(['search' => $departmentParams['search'], 'status' => $departmentParams['status']], $departmentParams['page']);
$subjectsResult = $academicService->listSubjects(['search' => $subjectParams['search'], 'department_id' => $subjectParams['department'], 'subject_type' => $subjectParams['type'], 'status' => $subjectParams['status']], $subjectParams['page']);
$calendarResult = $academicService->listCalendarEvents(['search' => $calendarParams['search'], 'event_type' => $calendarParams['type'], 'status' => $calendarParams['status'], 'session_id' => $calendarParams['session']], $calendarParams['page']);

$academicSessions = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'start_date' => (string) $row['start_date'],
        'end_date' => (string) $row['end_date'],
        'status' => ucfirst((string) $row['status']),
    ];
}, $sessionsResult['data']);
$academicSessionsMeta = $sessionsResult['meta'];

$academicTerms = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'session_id' => (int) $row['session_id'],
        'session' => (string) $row['session_name'],
        'name' => (string) $row['name'],
        'start_date' => (string) $row['start_date'],
        'end_date' => (string) $row['end_date'],
        'status' => ucfirst((string) $row['status']),
    ];
}, $termsResult['data']);
$academicTermsMeta = $termsResult['meta'];

$academicClasses = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'level' => ucfirst((string) $row['level']),
        'status' => ucfirst((string) $row['status']),
    ];
}, $classesResult['data']);
$academicClassesMeta = $classesResult['meta'];

$academicSections = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'class_id' => (int) $row['class_id'],
        'class' => (string) $row['class_name'],
        'name' => (string) $row['name'],
        'capacity' => $row['capacity'] !== null ? (int) $row['capacity'] : '',
        'status' => ucfirst((string) $row['status']),
    ];
}, $sectionsResult['data']);
$academicSectionsMeta = $sectionsResult['meta'];

$academicDepartments = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'description' => (string) ($row['description'] ?? ''),
        'status' => ucfirst((string) $row['status']),
    ];
}, $departmentsResult['data']);
$academicDepartmentsMeta = $departmentsResult['meta'];

$academicSubjects = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'code' => (string) $row['code'],
        'name' => (string) $row['name'],
        'department_id' => (int) ($row['department_id'] ?? 0),
        'department' => (string) ($row['department_name'] ?? 'Unassigned'),
        'level' => (string) ($row['class_names'] ?? ''),
        'class_ids' => $row['class_ids'] ? array_map('intval', explode(',', (string) $row['class_ids'])) : [],
        'type' => ucfirst((string) $row['subject_type']),
        'status' => ucfirst((string) $row['status']),
    ];
}, $subjectsResult['data']);
$academicSubjectsMeta = $subjectsResult['meta'];

$schoolEvents = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'session_id' => (int) ($row['session_id'] ?? 0),
        'term_id' => (int) ($row['term_id'] ?? 0),
        'title' => (string) $row['title'],
        'type' => (string) $row['event_type'],
        'start_date' => (string) $row['start_date'],
        'end_date' => (string) ($row['end_date'] ?? ''),
        'location' => (string) ($row['location'] ?? ''),
        'status' => ucfirst((string) $row['status']),
    ];
}, $calendarResult['data']);
$schoolEventsMeta = $calendarResult['meta'];

$academicClassOptions = $academicService->classesForSelect();
$academicDepartmentOptions = $academicService->departmentsForSelect();
$academicSessionOptions = $academicService->sessionsForSelect();
$academicTermOptions = $academicService->termsForSelect();
$academicStatuses = ['Active','Inactive','Completed','Upcoming','Scheduled'];
$academicCalendarEventTypes = ['examination','holiday','pta_meeting','staff_meeting','sports','graduation','orientation','other'];
$academicCalendarStatuses = ['scheduled','cancelled','completed'];
?>
