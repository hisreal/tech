<?php
/**
 * Shared Teacher Management placeholder data.
 *
 * Future integration: replace these arrays with repository/controller calls that
 * query teachers, teacher_subjects, teacher_classes, attendance, timetable,
 * performance metrics, and audit logs by teacher_id.
 */

$teacherDepartments = ['Science', 'Languages', 'Commercial', 'Arts', 'ICT'];
$teacherDesignations = ['Teacher', 'Senior Teacher', 'Class Teacher', 'Head of Department'];
$teacherStatuses = ['Active', 'Inactive', 'On Leave', 'Suspended', 'Deleted'];
$teacherSubjects = ['Mathematics', 'English Language', 'Physics', 'Computer Science', 'Biology', 'Economics', 'Chemistry'];
$teacherClasses = ['JSS 1A', 'JSS 2B', 'JSS 3A', 'SS 1 Science', 'SS 2 Science', 'SS 3 Arts'];
$contractTypes = ['Permanent', 'Contract', 'Part Time'];

$teacherRecords = [
    ['teacher_id' => 1, 'passport' => '../assets/img/user/user-01.jpg', 'staff_id' => 'TCH001', 'first_name' => 'John', 'middle_name' => '', 'last_name' => 'Musa', 'full_name' => 'John Musa', 'gender' => 'Male', 'date_of_birth' => '1987-04-12', 'phone' => '08031234567', 'email' => 'john.musa@school.test', 'address' => 'No. 12 School Road, Katsina', 'department' => 'Science', 'designation' => 'Senior Teacher', 'employment_date' => '2020-09-14', 'status' => 'Active', 'qualification' => 'B.Sc Mathematics', 'experience' => 6, 'salary_grade' => 'Grade 08', 'contract_type' => 'Permanent', 'subjects' => ['Mathematics', 'Physics'], 'classes' => ['SS 1 Science', 'SS 2 Science'], 'attendance_rate' => 96, 'results_submitted' => 18, 'performance_score' => 92, 'total_students' => 120],
    ['teacher_id' => 2, 'passport' => '../assets/img/user/user-02.jpg', 'staff_id' => 'TCH002', 'first_name' => 'Amina', 'middle_name' => '', 'last_name' => 'Bello', 'full_name' => 'Amina Bello', 'gender' => 'Female', 'date_of_birth' => '1990-08-21', 'phone' => '08122223333', 'email' => 'amina.bello@school.test', 'address' => 'GRA Layout, Katsina', 'department' => 'Languages', 'designation' => 'Teacher', 'employment_date' => '2021-01-10', 'status' => 'Active', 'qualification' => 'B.A English', 'experience' => 5, 'salary_grade' => 'Grade 07', 'contract_type' => 'Permanent', 'subjects' => ['English Language'], 'classes' => ['JSS 1A', 'JSS 2B'], 'attendance_rate' => 94, 'results_submitted' => 12, 'performance_score' => 89, 'total_students' => 86],
    ['teacher_id' => 3, 'passport' => '../assets/img/user/user-03.jpg', 'staff_id' => 'TCH003', 'first_name' => 'Chinedu', 'middle_name' => '', 'last_name' => 'Okafor', 'full_name' => 'Chinedu Okafor', 'gender' => 'Male', 'date_of_birth' => '1985-11-02', 'phone' => '07061112223', 'email' => 'chinedu.okafor@school.test', 'address' => 'ICT Quarters, Katsina', 'department' => 'ICT', 'designation' => 'Class Teacher', 'employment_date' => '2019-05-20', 'status' => 'On Leave', 'qualification' => 'B.Sc Computer Science', 'experience' => 8, 'salary_grade' => 'Grade 09', 'contract_type' => 'Permanent', 'subjects' => ['Computer Science'], 'classes' => ['SS 1 Science'], 'attendance_rate' => 88, 'results_submitted' => 9, 'performance_score' => 85, 'total_students' => 42],
    ['teacher_id' => 4, 'passport' => '../assets/img/user/user-04.jpg', 'staff_id' => 'TCH004', 'first_name' => 'Fatima', 'middle_name' => '', 'last_name' => 'Sani', 'full_name' => 'Fatima Sani', 'gender' => 'Female', 'date_of_birth' => '1983-02-18', 'phone' => '09034445556', 'email' => 'fatima.sani@school.test', 'address' => 'Dutsin Safe Road, Katsina', 'department' => 'Science', 'designation' => 'Head of Department', 'employment_date' => '2018-10-01', 'status' => 'Active', 'qualification' => 'M.Ed Biology', 'experience' => 10, 'salary_grade' => 'Grade 10', 'contract_type' => 'Permanent', 'subjects' => ['Biology'], 'classes' => ['SS 2 Science'], 'attendance_rate' => 98, 'results_submitted' => 20, 'performance_score' => 95, 'total_students' => 58],
];

$teacherAttendanceHistory = [
    ['date' => '2026-07-01', 'check_in' => '07:42 AM', 'check_out' => '03:08 PM', 'status' => 'Present'],
    ['date' => '2026-07-02', 'check_in' => '08:12 AM', 'check_out' => '03:02 PM', 'status' => 'Late'],
    ['date' => '2026-07-03', 'check_in' => '07:39 AM', 'check_out' => '03:15 PM', 'status' => 'Present'],
];

$teacherTimetable = [
    ['day' => 'Monday', 'time' => '8:00 AM - 9:00 AM', 'class' => 'SS 1 Science', 'subject' => 'Mathematics', 'venue' => 'Room 12'],
    ['day' => 'Tuesday', 'time' => '10:00 AM - 11:00 AM', 'class' => 'SS 2 Science', 'subject' => 'Physics', 'venue' => 'Science Lab'],
    ['day' => 'Wednesday', 'time' => '11:00 AM - 12:00 PM', 'class' => 'JSS 2B', 'subject' => 'Computer Science', 'venue' => 'ICT Lab'],
    ['day' => 'Thursday', 'time' => '9:00 AM - 10:00 AM', 'class' => 'SS 1 Science', 'subject' => 'Mathematics', 'venue' => 'Room 12'],
    ['day' => 'Friday', 'time' => '12:00 PM - 1:00 PM', 'class' => 'SS 2 Science', 'subject' => 'Physics', 'venue' => 'Science Lab'],
];

function sms_admin_teacher_find(array $teachers, ?int $teacherId): array
{
    foreach ($teachers as $teacher) {
        if ((int) $teacher['teacher_id'] === (int) $teacherId) {
            return $teacher;
        }
    }

    return $teachers[0];
}

function sms_admin_teacher_selected_id(): int
{
    return (int) ($_GET['teacher_id'] ?? $_POST['teacher_id'] ?? 1);
}