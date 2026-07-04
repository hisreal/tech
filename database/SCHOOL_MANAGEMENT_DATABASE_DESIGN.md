# School Management System Database Architecture

This document is the database design companion for `database/school_management_schema.sql`. The schema is normalized for MySQL/InnoDB, keeps authentication separate from profile records, and uses junction tables for all many-to-many relationships.

## ERD

```mermaid
erDiagram
  USERS ||--o{ USER_ROLES : has
  ROLES ||--o{ USER_ROLES : assigned
  ROLES ||--o{ ROLE_PERMISSIONS : grants
  PERMISSIONS ||--o{ ROLE_PERMISSIONS : included
  USERS ||--o{ USER_SESSIONS : opens
  USERS ||--o{ LOGIN_ATTEMPTS : attempts
  USERS ||--o{ PASSWORD_RESETS : requests

  USERS ||--o| STUDENTS : authenticates
  USERS ||--o| STAFF : authenticates
  DEPARTMENTS ||--o{ STAFF : groups
  DEPARTMENTS ||--o{ SUBJECTS : owns

  ACADEMIC_SESSIONS ||--o{ TERMS : contains
  CLASSES ||--o{ SECTIONS : contains
  SUBJECTS ||--o{ SUBJECT_CLASSES : offered_for
  CLASSES ||--o{ SUBJECT_CLASSES : studies
  STUDENTS ||--o{ STUDENT_ENROLLMENTS : enrolled
  ACADEMIC_SESSIONS ||--o{ STUDENT_ENROLLMENTS : in_session
  CLASSES ||--o{ STUDENT_ENROLLMENTS : class
  SECTIONS ||--o{ STUDENT_ENROLLMENTS : section

  STUDENTS ||--o{ STUDENT_GUARDIANS : linked
  GUARDIANS ||--o{ STUDENT_GUARDIANS : cares_for
  STUDENTS ||--o{ STUDENT_DOCUMENTS : owns
  STAFF ||--o{ STAFF_DOCUMENTS : owns

  STAFF ||--o{ TEACHER_SUBJECTS : teaches
  SUBJECTS ||--o{ TEACHER_SUBJECTS : taught_by
  STAFF ||--o{ TEACHER_CLASSES : assigned
  CLASSES ||--o{ TEACHER_CLASSES : assigned_to
  SECTIONS ||--o{ TEACHER_CLASSES : assigned_to

  STUDENTS ||--o{ STUDENT_ATTENDANCE : has
  STAFF ||--o{ TEACHER_ATTENDANCE : has
  ACADEMIC_SESSIONS ||--o{ SCHOOL_CALENDAR : schedules
  TERMS ||--o{ SCHOOL_CALENDAR : schedules

  ACADEMIC_SESSIONS ||--o{ TIMETABLE_ENTRIES : schedules
  TERMS ||--o{ TIMETABLE_ENTRIES : schedules
  CLASSES ||--o{ TIMETABLE_ENTRIES : attends
  SECTIONS ||--o{ TIMETABLE_ENTRIES : attends
  SUBJECTS ||--o{ TIMETABLE_ENTRIES : taught
  STAFF ||--o{ TIMETABLE_ENTRIES : teaches
  VENUES ||--o{ TIMETABLE_ENTRIES : hosts

  ACADEMIC_SESSIONS ||--o{ RESULT_BATCHES : owns
  TERMS ||--o{ RESULT_BATCHES : owns
  CLASSES ||--o{ RESULT_BATCHES : owns
  SUBJECTS ||--o{ RESULT_BATCHES : owns
  STAFF ||--o{ RESULT_BATCHES : submits
  RESULT_BATCHES ||--o{ STUDENT_RESULTS : contains
  STUDENTS ||--o{ STUDENT_RESULTS : earns
  STUDENTS ||--o{ RESULT_SCORES : report_card

  ACADEMIC_SESSIONS ||--o{ CBT_EXAMS : owns
  TERMS ||--o{ CBT_EXAMS : owns
  SUBJECTS ||--o{ CBT_EXAMS : tests
  CLASSES ||--o{ CBT_EXAMS : targets
  STAFF ||--o{ CBT_EXAMS : creates
  CBT_EXAMS ||--o{ CBT_QUESTIONS : has
  CBT_EXAMS ||--o{ CBT_ATTEMPTS : receives
  STUDENTS ||--o{ CBT_ATTEMPTS : writes
  CBT_ATTEMPTS ||--o{ CBT_ATTEMPT_ANSWERS : records
  CBT_QUESTIONS ||--o{ CBT_ATTEMPT_ANSWERS : answered

  ACADEMIC_SESSIONS ||--o{ FEE_STRUCTURES : prices
  TERMS ||--o{ FEE_STRUCTURES : prices
  CLASSES ||--o{ FEE_STRUCTURES : prices
  FEE_STRUCTURES ||--o{ FEE_STRUCTURE_ITEMS : contains
  FEE_ITEMS ||--o{ FEE_STRUCTURE_ITEMS : priced
  STUDENTS ||--o{ INVOICES : billed
  INVOICES ||--o{ INVOICE_ITEMS : contains
  INVOICES ||--o{ PAYMENTS : paid_by
  STUDENTS ||--o{ PAYMENTS : pays
  PAYMENTS ||--o{ RECEIPTS : issues
  USERS ||--o{ EXPENSES : records

  USERS ||--o{ AUDIT_LOGS : actor
  USERS ||--o{ SCHOOL_SETTINGS : updates
  USERS ||--o{ GENERATED_FILES : generates
```

## Table Groups

### Authentication and RBAC

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `users` | Login identity for every portal user | `id`, `username`, `email`, `password_hash`, `user_type`, `status` | One user can map to one student or one staff profile. |
| `roles` | Role definitions | `id`, `name`, `slug` | Seeded roles: Super Admin, Admin, Teacher, Accountant, Student, Parent. |
| `permissions` | Permission registry | `module`, `action`, `slug` | Slugs follow `module.action`. |
| `user_roles` | User-role junction | `user_id`, `role_id` | Composite primary key. |
| `role_permissions` | Role-permission junction | `role_id`, `permission_id` | Composite primary key. |
| `login_attempts` | Login security history | `username`, `user_id`, `ip_address`, `was_successful` | Supports lockout/rate-limit logic. |
| `user_sessions` | Persistent sessions | `user_id`, `session_token_hash`, `expires_at`, `revoked_at` | Store only token hashes. |
| `password_resets` | Password reset workflow | `user_id`, `token_hash`, `expires_at`, `used_at` | Store only token hashes. |

### Academic Core

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `academic_sessions` | School years | `name`, `start_date`, `end_date`, `status` | Example: `2025/2026`. |
| `terms` | Terms inside sessions | `session_id`, `name`, dates, `status` | Unique per session/name. |
| `departments` | Academic and administrative units | `name`, `description`, `status` | Used by staff and subjects. |
| `classes` | Class levels | `name`, `level`, `status` | Example: JSS 1, SS 2. |
| `sections` | Arms/streams inside a class | `class_id`, `name`, `capacity` | Example: A, B, Science, Arts. |
| `subjects` | Subject catalog | `code`, `name`, `department_id`, `subject_type` | Core/elective separation. |
| `subject_classes` | Subject-class availability | `subject_id`, `class_id` | Normalizes subject lists per class. |
| `school_calendar` | Academic calendar events | `session_id`, `term_id`, `title`, dates | Exams, holidays, PTA, sports. |

### People and Enrollment

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `students` | Student biodata | `user_id`, `admission_no`, `registration_no`, names, contact, health fields | `user_id` is nullable for offline records. |
| `guardians` | Parent/guardian contacts | `full_name`, `relationship`, `phone`, `email` | Reusable across siblings. |
| `student_guardians` | Student-guardian junction | `student_id`, `guardian_id`, `is_primary` | Composite primary key. |
| `staff` | Teachers, accountants, admins, support | `user_id`, `staff_no`, `staff_type`, biodata, employment fields | `staff_type` differentiates teacher/accountant/admin. |
| `teacher_subjects` | Subjects a teacher can teach | `teacher_id`, `subject_id` | `teacher_id` references `staff.id`. |
| `teacher_classes` | Classes/sections assigned to teacher | `teacher_id`, `class_id`, `section_id` | Supports class teachers and subject teachers. |
| `student_enrollments` | Student placement per session | `student_id`, `session_id`, `class_id`, `section_id`, `roll_number` | Unique student/session. |
| `promotion_history` | Promotion/repeat trail | from/to session/class/section | Keeps academic movement auditable. |
| `student_documents` | Student uploads | `student_id`, `document_type`, `file_path` | Certificates, medical files, etc. |
| `staff_documents` | Staff uploads | `staff_id`, `document_type`, `file_path` | Credentials, contracts, certificates. |

### Attendance and Timetable

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `student_attendance` | Daily student attendance | `student_id`, `session_id`, `term_id`, `attendance_date`, `status` | Unique student/date. Status includes present, absent, late, excused, leave. |
| `teacher_attendance` | Daily staff attendance | `staff_id`, `attendance_date`, `check_in`, `check_out`, `status` | Unique staff/date. |
| `venues` | Rooms/labs/halls | `name`, `venue_type`, `capacity` | Used by timetable. |
| `school_periods` | Period/break definitions | `period_name`, `start_time`, `end_time`, `is_break` | Defines the daily grid. |
| `working_days` | Enabled school days | `day_name`, `is_enabled` | Monday-Friday seeded. |
| `timetable_entries` | Class timetable rows | session, term, class, section, subject, teacher, venue, day, times | Indexed by class/day and teacher/day. |

### Results and Report Cards

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `grade_settings` | Score-to-grade bands | `grade`, `min_score`, `max_score`, `remark` | Seeded A-F. |
| `remark_settings` | Auto remarks | `category`, average range, `remark` | Teacher/principal/general remarks. |
| `result_batches` | A subject result sheet | session, term, class, section, subject, teacher, workflow status | Draft to locked workflow. |
| `student_results` | Subject result per student | `result_batch_id`, `student_id`, CA/exam/practical/total | Unique student per batch. |
| `result_scores` | Term report-card summary | `session_id`, `term_id`, `student_id`, totals, average, remarks | One summary per student per term. |
| `generated_files` | Produced files | `module`, `file_type`, `entity_table`, `entity_id`, `file_path` | Report cards, receipts, broadsheets, financial reports. |

### CBT

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `cbt_exams` | CBT exam setup | session, term, subject, class, teacher, duration, pass mark, randomization flags, status | Status supports draft, published, active, completed, inactive, archived. |
| `cbt_questions` | Exam questions | `exam_id`, question text, options A-D, correct option, mark | Simple MCQ model now; can be extended later. |
| `cbt_attempts` | Student attempts | `exam_id`, `student_id`, start/end, score, percentage, status, IP/user agent | Supports in-progress and auto-submitted attempts. |
| `cbt_attempt_answers` | Answer detail | `attempt_id`, `question_id`, selected option, correctness, awarded mark | Unique attempt/question. |

### Finance

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `fee_structures` | Class/session/term fee template | session, term, class, section, name, status | Draft/active/archive workflow. |
| `fee_items` | Fee catalog | `name`, `description`, `status` | Tuition, ICT levy, lab fee. |
| `fee_structure_items` | Priced fee lines | `fee_structure_id`, `fee_item_id`, `amount`, `is_required` | Unique fee item per structure. |
| `invoices` | Student bill | `invoice_no`, student/session/term/class, totals, status | Tracks total, paid, balance. |
| `invoice_items` | Invoice line items | `invoice_id`, `fee_item_id`, `item_name`, `amount`, status | Keeps historical item name even if catalog changes. |
| `payments` | Payment transactions | `transaction_no`, `invoice_id`, `student_id`, amount, method, status | Allows invoice-linked and standalone payments. |
| `receipts` | Receipt records | `receipt_no`, `payment_id`, `issued_by`, `issued_at` | One payment can generate receipt history. |
| `expenses` | School spending | `expense_no`, category, amount, approval workflow | Supports accountant dashboards and financial reports. |

### Settings and Audit

| Table | Purpose | Key columns | Notes |
| --- | --- | --- | --- |
| `school_settings` | Key-value settings | `setting_key`, `setting_value`, `value_type`, `setting_group` | Covers school profile, CBT, result, timetable, finance defaults. |
| `audit_logs` | Immutable activity trail | `actor_user_id`, module, action, entity, old/new JSON | All write operations should add entries later. |

## Key Normalization Rules

1. Authentication data stays in `users`; role access stays in RBAC tables; student/staff biodata stays in profile tables.
2. Students are not stored directly on classes. Placement is session-aware through `student_enrollments`.
3. Teachers, accountants, and admins are all staff records. Their capabilities are determined by `staff_type` plus user roles/permissions.
4. Subjects are globally cataloged in `subjects` and connected to classes through `subject_classes`.
5. Result entry is split into `result_batches` and `student_results`; report-card totals are stored separately in `result_scores`.
6. Fee templates are split into `fee_structures`, `fee_items`, and `fee_structure_items`; generated bills are copied into invoices and invoice lines.
7. Every operational module has workflow statuses and timestamp metadata for approval, publishing, locking, or archiving.
8. File outputs and uploads store file paths only; binary files should remain on disk/object storage.

## Index and Constraint Strategy

- Natural identifiers are unique: `users.username`, `users.email`, `students.admission_no`, `students.registration_no`, `staff.staff_no`, invoice/payment/receipt/expense numbers.
- Junction tables use composite primary keys: `user_roles`, `role_permissions`, `subject_classes`, `teacher_subjects`, `student_guardians`.
- Workflow tables have status indexes where dashboards will filter often: results, CBT, invoices, payments, expenses.
- Date-heavy reporting tables have date indexes: attendance, payments, receipts, expenses, audit logs.
- Foreign keys use cascade only for dependent children, such as answers under attempts or invoice items under invoices. Historical references to users generally use `ON DELETE SET NULL`.
- Check constraints protect non-negative money and score values, and ensure start time is before end time.

## Module Mapping

| Module | Primary tables | Notes |
| --- | --- | --- |
| Students | `students`, `guardians`, `student_guardians`, `student_enrollments`, `student_documents`, `users`, `user_roles` | Supports admission, profile, guardian details, documents, class placement, and portal login. |
| Teachers | `staff`, `teacher_subjects`, `teacher_classes`, `teacher_attendance`, `timetable_entries`, `result_batches`, `users`, `user_roles` | Teachers are staff with `staff_type='teacher'`. |
| Accountants | `staff`, `payments`, `receipts`, `expenses`, `invoices`, `fee_structures`, `users`, `user_roles` | Accountants are staff with finance permissions. |
| Academic Management | `academic_sessions`, `terms`, `departments`, `classes`, `sections`, `subjects`, `subject_classes`, `school_calendar`, `promotion_history` | Manages school structure and yearly progression. |
| Attendance | `student_attendance`, `teacher_attendance`, `academic_sessions`, `terms`, `classes`, `sections`, `staff`, `students` | Daily records drive reports and analytics. |
| Results | `grade_settings`, `remark_settings`, `result_batches`, `student_results`, `result_scores`, `generated_files` | Supports submission, approval, publishing, locking, report cards, and broadsheets. |
| CBT | `cbt_exams`, `cbt_questions`, `cbt_attempts`, `cbt_attempt_answers`, `school_settings` | Settings table holds global CBT defaults and security toggles. |
| Finance | `fee_structures`, `fee_items`, `fee_structure_items`, `invoices`, `invoice_items`, `payments`, `receipts`, `expenses`, `generated_files` | Supports fee setup, collection, receipts, outstanding fees, and reports. |
| Timetable | `venues`, `school_periods`, `working_days`, `timetable_entries`, `teacher_subjects`, `teacher_classes` | Conflict detection can query teacher/day and class/day indexes. |
| Audit Logs | `audit_logs`, `users` | Every future CRUD mutation should record module/action/entity plus JSON before/after values. |
| School Settings | `school_settings` | Centralized key-value configuration for school identity and module defaults. |
| Authentication | `users`, `login_attempts`, `user_sessions`, `password_resets` | Separates login lifecycle from profile records. |
| Roles and Permissions | `roles`, `permissions`, `user_roles`, `role_permissions` | Future UI should check permission slugs, not hard-coded user types. |

## Seed Data Included

The SQL file seeds these minimum records:

- Roles: Super Admin, Admin, Teacher, Accountant, Student, Parent.
- Permissions for students, teachers, accountants, academic, attendance, results, CBT, finance, timetable, settings, audit, and roles.
- Role-permission mappings for Super Admin, Admin, Teacher, and Accountant.
- Academic session `2025/2026`, upcoming session `2026/2027`, and three terms.
- Departments: Science, Languages, Commercial, ICT, Finance.
- Classes and sections: JSS 1 A, JSS 2 B, SS 1 Science, SS 2 Science.
- Subjects and class availability for Mathematics, English Language, Physics, Computer Science, Biology.
- Sample admin, teacher, accountant, and student users with placeholder password hashes.
- Staff, student, guardian, enrollment, teacher subject/class assignments.
- Grade bands A-F.
- School periods, venues, working days, fee items, and school/module settings.

Replace placeholder password hashes before using the seed users for real login.

## Future Extension Points

- Add `parents` profile table only if parent portal needs biodata beyond `guardians` plus `users`.
- Add `payroll_*` tables when payroll moves beyond accountant profile counters.
- Add `question_types` or normalized `cbt_question_options` when CBT needs theory, images, multiple answers, or question banks.
- Add `notification_templates` and `notifications` when SMS/email/portal notifications are implemented.
- Add accounting ledger tables if double-entry accounting becomes a requirement.
