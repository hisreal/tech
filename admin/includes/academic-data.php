<?php
/** Shared Academic Management placeholder data. Replace arrays with MySQL-backed repositories later. */
$academicSessions = [
 ['id'=>1,'name'=>'2024/2025','start_date'=>'2024-09-09','end_date'=>'2025-07-25','status'=>'Completed'],
 ['id'=>2,'name'=>'2025/2026','start_date'=>'2025-09-08','end_date'=>'2026-07-24','status'=>'Active'],
 ['id'=>3,'name'=>'2026/2027','start_date'=>'2026-09-07','end_date'=>'2027-07-23','status'=>'Upcoming'],
];
$academicTerms = [
 ['id'=>1,'session'=>'2025/2026','name'=>'First Term','start_date'=>'2025-09-08','end_date'=>'2025-12-12','status'=>'Active'],
 ['id'=>2,'session'=>'2025/2026','name'=>'Second Term','start_date'=>'2026-01-12','end_date'=>'2026-04-03','status'=>'Inactive'],
 ['id'=>3,'session'=>'2025/2026','name'=>'Third Term','start_date'=>'2026-04-27','end_date'=>'2026-07-24','status'=>'Inactive'],
];
$academicClasses = [
 ['id'=>1,'name'=>'JSS1','level'=>'Junior','teacher'=>'Future Assignment','students'=>125,'status'=>'Active'],
 ['id'=>2,'name'=>'JSS2','level'=>'Junior','teacher'=>'Future Assignment','students'=>118,'status'=>'Active'],
 ['id'=>3,'name'=>'SS1','level'=>'Senior','teacher'=>'Future Assignment','students'=>132,'status'=>'Active'],
 ['id'=>4,'name'=>'SS2','level'=>'Senior','teacher'=>'Future Assignment','students'=>109,'status'=>'Active'],
];
$academicSections = [
 ['id'=>1,'name'=>'A','class'=>'JSS1','capacity'=>45,'status'=>'Active'],
 ['id'=>2,'name'=>'B','class'=>'JSS1','capacity'=>45,'status'=>'Active'],
 ['id'=>3,'name'=>'Science','class'=>'SS1','capacity'=>50,'status'=>'Active'],
 ['id'=>4,'name'=>'Commercial','class'=>'SS1','capacity'=>40,'status'=>'Active'],
];
$academicDepartments = [
 ['id'=>1,'name'=>'Science','description'=>'Science and laboratory subjects','hod'=>'Future Assignment','teachers'=>12,'status'=>'Active'],
 ['id'=>2,'name'=>'Arts','description'=>'Arts and humanities','hod'=>'Future Assignment','teachers'=>8,'status'=>'Active'],
 ['id'=>3,'name'=>'Commercial','description'=>'Business and commercial studies','hod'=>'Future Assignment','teachers'=>7,'status'=>'Active'],
 ['id'=>4,'name'=>'Administration','description'=>'Administrative academic unit','hod'=>'Future Assignment','teachers'=>5,'status'=>'Active'],
];
$academicSubjects = [
 ['id'=>1,'code'=>'MTH','name'=>'Mathematics','department'=>'Science','level'=>'JSS1, JSS2, SS1, SS2','type'=>'Core','status'=>'Active'],
 ['id'=>2,'code'=>'ENG','name'=>'English Language','department'=>'Arts','level'=>'JSS1, JSS2, SS1, SS2','type'=>'Core','status'=>'Active'],
 ['id'=>3,'code'=>'PHY','name'=>'Physics','department'=>'Science','level'=>'SS1, SS2','type'=>'Elective','status'=>'Active'],
 ['id'=>4,'code'=>'ECO','name'=>'Economics','department'=>'Commercial','level'=>'SS1, SS2','type'=>'Elective','status'=>'Active'],
];
$schoolEvents = [
 ['id'=>1,'title'=>'First Term Examination','type'=>'Examination','description'=>'End of term examination','start_date'=>'2025-12-01','end_date'=>'2025-12-12','location'=>'School Campus','status'=>'Upcoming'],
 ['id'=>2,'title'=>'Independence Holiday','type'=>'Holiday','description'=>'National holiday','start_date'=>'2025-10-01','end_date'=>'2025-10-01','location'=>'Nationwide','status'=>'Upcoming'],
 ['id'=>3,'title'=>'PTA Meeting','type'=>'Meeting','description'=>'Parent teacher meeting','start_date'=>'2025-11-14','end_date'=>'2025-11-14','location'=>'School Hall','status'=>'Upcoming'],
 ['id'=>4,'title'=>'Inter-house Sports','type'=>'Sports','description'=>'Annual sport event','start_date'=>'2026-02-20','end_date'=>'2026-02-20','location'=>'Sports Field','status'=>'Scheduled'],
];
$academicStatuses = ['Active','Inactive','Completed','Upcoming','Scheduled'];
?>