-- School Management System normalized MySQL schema
-- Database: school_management
-- Generated for future PHP/MySQL integration. No CRUD code is included here.

CREATE DATABASE IF NOT EXISTS school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_management;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- Authentication, roles, and permissions
-- =========================================================
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL,
  email VARCHAR(150) NULL,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('admin','student','teacher','accountant','parent') NOT NULL,
  status ENUM('active','inactive','suspended','deleted') NOT NULL DEFAULT 'active',
  password_must_change TINYINT(1) NOT NULL DEFAULT 0,
  temp_password_created_at DATETIME NULL,
  last_login_at DATETIME NULL,
  email_verified_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_type_status (user_type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  description TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_roles_name (name),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module VARCHAR(80) NOT NULL,
  action VARCHAR(80) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_permissions_slug (slug),
  KEY idx_permissions_module_action (module, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- Academic core
-- =========================================================
CREATE TABLE IF NOT EXISTS academic_sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('active','inactive','completed','upcoming') NOT NULL DEFAULT 'inactive',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_academic_sessions_name (name),
  KEY idx_academic_sessions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS terms (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  name ENUM('First Term','Second Term','Third Term') NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('active','inactive','completed') NOT NULL DEFAULT 'inactive',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_terms_session_name (session_id, name),
  KEY idx_terms_status (status),
  CONSTRAINT fk_terms_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS departments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_departments_name (name),
  KEY idx_departments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  level ENUM('creche','nursery','primary','junior','senior') NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_classes_name (name),
  KEY idx_classes_level_status (level, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  capacity INT UNSIGNED NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sections_class_name (class_id, name),
  KEY idx_sections_status (status),
  CONSTRAINT fk_sections_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(120) NOT NULL,
  department_id BIGINT UNSIGNED NULL,
  subject_type ENUM('core','elective') NOT NULL DEFAULT 'core',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_subjects_code (code),
  KEY idx_subjects_department (department_id),
  KEY idx_subjects_type_status (subject_type, status),
  CONSTRAINT fk_subjects_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subject_classes (
  subject_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (subject_id, class_id),
  CONSTRAINT fk_subject_classes_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT fk_subject_classes_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- People, enrollments, assignments, and documents
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  admission_no VARCHAR(50) NULL,
  registration_no VARCHAR(50) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  middle_name VARCHAR(80) NULL,
  last_name VARCHAR(80) NOT NULL,
  gender ENUM('male','female','other') NULL,
  date_of_birth DATE NULL,
  blood_group VARCHAR(10) NULL,
  genotype VARCHAR(10) NULL,
  religion VARCHAR(80) NULL,
  nationality VARCHAR(80) NULL,
  state VARCHAR(100) NULL,
  local_government VARCHAR(100) NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  address TEXT NULL,
  passport_path VARCHAR(255) NULL,
  medical_conditions TEXT NULL,
  allergies TEXT NULL,
  emergency_contact VARCHAR(120) NULL,
  status ENUM('active','graduated','withdrawn','suspended','deleted') DEFAULT 'active',
  profile_completion_status ENUM('incomplete','complete') NOT NULL DEFAULT 'incomplete',
  profile_completion_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_students_admission_no (admission_no),
  UNIQUE KEY uq_students_registration_no (registration_no),
  KEY idx_students_name (last_name, first_name),
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guardians (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(160) NOT NULL,
  relationship VARCHAR(80) NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NULL,
  address TEXT NULL,
  occupation VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_guardians_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_guardians (
  student_id BIGINT UNSIGNED NOT NULL,
  guardian_id BIGINT UNSIGNED NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  PRIMARY KEY (student_id, guardian_id),
  CONSTRAINT fk_student_guardians_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_guardians_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  staff_no VARCHAR(50) NOT NULL,
  staff_type ENUM('admin','teacher','accountant','support') NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  middle_name VARCHAR(80) NULL,
  last_name VARCHAR(80) NOT NULL,
  gender ENUM('male','female','other') NULL,
  date_of_birth DATE NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  address TEXT NULL,
  state VARCHAR(100) NULL,
  local_government VARCHAR(100) NULL,
  nationality VARCHAR(80) NULL,
  department_id BIGINT UNSIGNED NULL,
  designation VARCHAR(120) NULL,
  employment_date DATE NULL,
  employment_status ENUM('active','inactive','on_leave','suspended','deleted') DEFAULT 'active',
  qualification VARCHAR(180) NULL,
  specialization VARCHAR(180) NULL,
  years_experience DECIMAL(4,1) DEFAULT 0.0,
  salary_grade VARCHAR(50) NULL,
  contract_type VARCHAR(80) NULL,
  passport_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_staff_no (staff_no),
  KEY idx_staff_type (staff_type),
  KEY idx_staff_name (last_name, first_name),
  CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_staff_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teacher_subjects (
  teacher_id BIGINT UNSIGNED NOT NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (teacher_id, subject_id),
  CONSTRAINT fk_teacher_subjects_teacher FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_subjects_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teacher_classes (
  teacher_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (teacher_id, class_id, section_id),
  CONSTRAINT fk_teacher_classes_teacher FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_classes_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_classes_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_enrollments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  session_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  roll_number VARCHAR(40) NULL,
  status ENUM('active','promoted','repeated','withdrawn') DEFAULT 'active',
  enrolled_at DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_session_enrollment (student_id, session_id),
  KEY idx_enrollments_class_section (class_id, section_id),
  CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_enrollments_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE RESTRICT,
  CONSTRAINT fk_enrollments_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_enrollments_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_history (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  from_session_id BIGINT UNSIGNED NOT NULL,
  to_session_id BIGINT UNSIGNED NOT NULL,
  from_class_id BIGINT UNSIGNED NOT NULL,
  to_class_id BIGINT UNSIGNED NOT NULL,
  from_section_id BIGINT UNSIGNED NULL,
  to_section_id BIGINT UNSIGNED NULL,
  promoted_by BIGINT UNSIGNED NULL,
  promoted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes TEXT NULL,
  KEY idx_promotion_student (student_id),
  CONSTRAINT fk_promotion_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_promotion_from_session FOREIGN KEY (from_session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_promotion_to_session FOREIGN KEY (to_session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_promotion_from_class FOREIGN KEY (from_class_id) REFERENCES classes(id),
  CONSTRAINT fk_promotion_to_class FOREIGN KEY (to_class_id) REFERENCES classes(id),
  CONSTRAINT fk_promotion_from_section FOREIGN KEY (from_section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_promotion_to_section FOREIGN KEY (to_section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_promotion_user FOREIGN KEY (promoted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  document_type VARCHAR(100) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_documents_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_documents_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id BIGINT UNSIGNED NOT NULL,
  document_type VARCHAR(100) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_staff_documents_staff FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
  CONSTRAINT fk_staff_documents_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Calendar, attendance, and timetable
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS school_calendar (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NULL,
  term_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  event_type ENUM('examination','holiday','pta_meeting','staff_meeting','sports','graduation','orientation','other') DEFAULT 'other',
  start_date DATE NOT NULL,
  end_date DATE NULL,
  location VARCHAR(180) NULL,
  status ENUM('scheduled','cancelled','completed') DEFAULT 'scheduled',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_calendar_dates (start_date, end_date),
  CONSTRAINT fk_calendar_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_calendar_term FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE SET NULL,
  CONSTRAINT fk_calendar_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_attendance (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  attendance_date DATE NOT NULL,
  status ENUM('present','absent','late','excused','leave') NOT NULL,
  marked_by BIGINT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_daily_attendance (student_id, attendance_date),
  KEY idx_student_attendance_class_date (class_id, section_id, attendance_date),
  CONSTRAINT fk_student_att_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_att_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_student_att_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_student_att_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_student_att_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_student_att_user FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teacher_attendance (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id BIGINT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  check_in TIME NULL,
  check_out TIME NULL,
  status ENUM('present','absent','late','excused','leave') NOT NULL,
  marked_by BIGINT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_teacher_daily_attendance (staff_id, attendance_date),
  KEY idx_teacher_attendance_date (attendance_date),
  CONSTRAINT fk_teacher_att_staff FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
  CONSTRAINT fk_teacher_att_user FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venues (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  venue_type VARCHAR(80) NULL,
  capacity INT UNSIGNED NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_venues_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS school_periods (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  period_name VARCHAR(80) NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_break TINYINT(1) DEFAULT 0,
  sort_order INT UNSIGNED DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS working_days (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  day_name ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  UNIQUE KEY uq_working_day (day_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS timetable_entries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  teacher_id BIGINT UNSIGNED NOT NULL,
  venue_id BIGINT UNSIGNED NULL,
  day_name ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  status ENUM('draft','published','unpublished') DEFAULT 'draft',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_timetable_class_day (class_id, section_id, day_name),
  KEY idx_timetable_teacher_day (teacher_id, day_name),
  CONSTRAINT fk_timetable_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_timetable_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_timetable_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_timetable_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_timetable_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
  CONSTRAINT fk_timetable_teacher FOREIGN KEY (teacher_id) REFERENCES staff(id),
  CONSTRAINT fk_timetable_venue FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE SET NULL,
  CONSTRAINT fk_timetable_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Results, grading, report cards, and broadsheets
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS grade_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grade VARCHAR(10) NOT NULL,
  min_score DECIMAL(5,2) NOT NULL,
  max_score DECIMAL(5,2) NOT NULL,
  remark VARCHAR(120) NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_grade_settings_grade (grade),
  CHECK (min_score <= max_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS remark_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category ENUM('teacher','principal','general') DEFAULT 'general',
  min_average DECIMAL(5,2) NULL,
  max_average DECIMAL(5,2) NULL,
  remark TEXT NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_remarks_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS result_batches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  teacher_id BIGINT UNSIGNED NULL,
  status ENUM('draft','submitted','approved','published','locked') DEFAULT 'draft',
  submitted_at DATETIME NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  published_by BIGINT UNSIGNED NULL,
  published_at DATETIME NULL,
  locked_by BIGINT UNSIGNED NULL,
  locked_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_result_batch (session_id, term_id, class_id, section_id, subject_id),
  KEY idx_result_batch_status (status),
  CONSTRAINT fk_result_batch_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_result_batch_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_result_batch_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_result_batch_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_result_batch_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
  CONSTRAINT fk_result_batch_teacher FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE SET NULL,
  CONSTRAINT fk_result_batch_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_result_batch_published_by FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_result_batch_locked_by FOREIGN KEY (locked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_results (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  result_batch_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  ca1 DECIMAL(5,2) DEFAULT 0.00,
  ca2 DECIMAL(5,2) DEFAULT 0.00,
  ca3 DECIMAL(5,2) DEFAULT 0.00,
  exam DECIMAL(5,2) DEFAULT 0.00,
  practical DECIMAL(5,2) DEFAULT 0.00,
  total DECIMAL(5,2) DEFAULT 0.00,
  grade VARCHAR(10) NULL,
  remark VARCHAR(120) NULL,
  position_in_subject INT UNSIGNED NULL,
  status ENUM('draft','submitted','approved','published','locked') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_result (result_batch_id, student_id),
  KEY idx_student_results_student (student_id),
  CONSTRAINT fk_student_results_batch FOREIGN KEY (result_batch_id) REFERENCES result_batches(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_results_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CHECK (ca1 >= 0 AND ca2 >= 0 AND ca3 >= 0 AND exam >= 0 AND practical >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS result_scores (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  total_score DECIMAL(7,2) DEFAULT 0.00,
  average_score DECIMAL(5,2) DEFAULT 0.00,
  grade VARCHAR(10) NULL,
  position_in_class INT UNSIGNED NULL,
  teacher_remark TEXT NULL,
  principal_remark TEXT NULL,
  attendance_present INT UNSIGNED DEFAULT 0,
  attendance_absent INT UNSIGNED DEFAULT 0,
  status ENUM('draft','published','locked') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_report_card_score (session_id, term_id, student_id),
  KEY idx_result_scores_class (class_id, section_id),
  CONSTRAINT fk_result_scores_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_result_scores_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_result_scores_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_result_scores_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_result_scores_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- CBT exams, questions, attempts, and answers
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS cbt_exams (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  subject_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  teacher_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  instructions TEXT NULL,
  duration_minutes INT UNSIGNED NOT NULL,
  number_of_questions INT UNSIGNED DEFAULT 0,
  pass_mark DECIMAL(5,2) DEFAULT 40.00,
  maximum_attempts INT UNSIGNED DEFAULT 1,
  randomize_questions TINYINT(1) DEFAULT 0,
  randomize_answers TINYINT(1) DEFAULT 0,
  show_result_immediately TINYINT(1) DEFAULT 1,
  allow_review TINYINT(1) DEFAULT 1,
  status ENUM('draft','published','active','completed','inactive','archived') DEFAULT 'draft',
  created_by BIGINT UNSIGNED NULL,
  published_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cbt_exam_status (status),
  CONSTRAINT fk_cbt_exam_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_cbt_exam_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_cbt_exam_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
  CONSTRAINT fk_cbt_exam_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_cbt_exam_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_cbt_exam_teacher FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE SET NULL,
  CONSTRAINT fk_cbt_exam_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_questions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id BIGINT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  option_a TEXT NOT NULL,
  option_b TEXT NOT NULL,
  option_c TEXT NOT NULL,
  option_d TEXT NOT NULL,
  correct_option ENUM('A','B','C','D') NOT NULL,
  mark DECIMAL(5,2) DEFAULT 1.00,
  sort_order INT UNSIGNED DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cbt_questions_exam (exam_id),
  CONSTRAINT fk_cbt_questions_exam FOREIGN KEY (exam_id) REFERENCES cbt_exams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  started_at DATETIME NOT NULL,
  ended_at DATETIME NULL,
  score DECIMAL(7,2) DEFAULT 0.00,
  percentage DECIMAL(5,2) DEFAULT 0.00,
  grade VARCHAR(10) NULL,
  status ENUM('in_progress','submitted','auto_submitted','cancelled') DEFAULT 'in_progress',
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cbt_attempt_exam_student (exam_id, student_id),
  CONSTRAINT fk_cbt_attempt_exam FOREIGN KEY (exam_id) REFERENCES cbt_exams(id) ON DELETE CASCADE,
  CONSTRAINT fk_cbt_attempt_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_attempt_answers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  selected_option ENUM('A','B','C','D') NULL,
  is_correct TINYINT(1) DEFAULT 0,
  mark_awarded DECIMAL(5,2) DEFAULT 0.00,
  answered_at DATETIME NULL,
  UNIQUE KEY uq_attempt_question (attempt_id, question_id),
  CONSTRAINT fk_attempt_answers_attempt FOREIGN KEY (attempt_id) REFERENCES cbt_attempts(id) ON DELETE CASCADE,
  CONSTRAINT fk_attempt_answers_question FOREIGN KEY (question_id) REFERENCES cbt_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Finance, fees, invoices, payments, receipts, and expenses
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS fee_structures (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  status ENUM('draft','active','inactive','archived') DEFAULT 'draft',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fee_structure (session_id, term_id, class_id, section_id, name),
  CONSTRAINT fk_fee_structure_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_fee_structure_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_fee_structure_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_fee_structure_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_fee_structure_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fee_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fee_item_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fee_structure_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fee_structure_id BIGINT UNSIGNED NOT NULL,
  fee_item_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  is_required TINYINT(1) DEFAULT 1,
  UNIQUE KEY uq_fee_structure_item (fee_structure_id, fee_item_id),
  CONSTRAINT fk_fee_structure_items_structure FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE CASCADE,
  CONSTRAINT fk_fee_structure_items_item FOREIGN KEY (fee_item_id) REFERENCES fee_items(id) ON DELETE RESTRICT,
  CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(60) NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  session_id BIGINT UNSIGNED NOT NULL,
  term_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,
  section_id BIGINT UNSIGNED NULL,
  fee_structure_id BIGINT UNSIGNED NULL,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  due_date DATE NULL,
  status ENUM('unpaid','partial','paid','cancelled') DEFAULT 'unpaid',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invoice_no (invoice_no),
  KEY idx_invoice_student (student_id),
  KEY idx_invoice_status (status),
  CONSTRAINT fk_invoices_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_invoices_session FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
  CONSTRAINT fk_invoices_term FOREIGN KEY (term_id) REFERENCES terms(id),
  CONSTRAINT fk_invoices_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_invoices_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  CONSTRAINT fk_invoices_fee_structure FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE SET NULL,
  CONSTRAINT fk_invoices_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CHECK (total_amount >= 0 AND amount_paid >= 0 AND balance >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT UNSIGNED NOT NULL,
  fee_item_id BIGINT UNSIGNED NULL,
  item_name VARCHAR(120) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  status ENUM('unpaid','partial','paid','waived') DEFAULT 'unpaid',
  CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  CONSTRAINT fk_invoice_items_fee_item FOREIGN KEY (fee_item_id) REFERENCES fee_items(id) ON DELETE SET NULL,
  CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaction_no VARCHAR(60) NOT NULL,
  invoice_id BIGINT UNSIGNED NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  payment_type VARCHAR(100) DEFAULT 'School Fees',
  amount DECIMAL(12,2) NOT NULL,
  payment_method ENUM('cash','bank_transfer','pos','online_payment','cheque') NOT NULL,
  transaction_reference VARCHAR(120) NULL,
  payment_date DATETIME NOT NULL,
  status ENUM('paid','pending','failed','refunded','cancelled') DEFAULT 'paid',
  received_by BIGINT UNSIGNED NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_payment_transaction_no (transaction_no),
  KEY idx_payments_student_date (student_id, payment_date),
  KEY idx_payments_status (status),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
  CONSTRAINT fk_payments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_received_by FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
  CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receipts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(60) NOT NULL,
  payment_id BIGINT UNSIGNED NOT NULL,
  issued_by BIGINT UNSIGNED NULL,
  issued_at DATETIME NOT NULL,
  status ENUM('paid','cancelled','refunded') DEFAULT 'paid',
  footer_message VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_receipt_no (receipt_no),
  KEY idx_receipts_issued_at (issued_at),
  CONSTRAINT fk_receipts_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
  CONSTRAINT fk_receipts_user FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expenses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  expense_no VARCHAR(60) NOT NULL,
  category VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  expense_date DATE NOT NULL,
  payment_method ENUM('cash','bank_transfer','pos','online_payment','cheque') DEFAULT 'cash',
  recorded_by BIGINT UNSIGNED NULL,
  approved_by BIGINT UNSIGNED NULL,
  status ENUM('draft','submitted','approved','rejected','paid') DEFAULT 'draft',
  attachment_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_expense_no (expense_no),
  KEY idx_expenses_date (expense_date),
  KEY idx_expenses_status (status),
  CONSTRAINT fk_expenses_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_expenses_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =========================================================
-- Platform security, auditing, settings, and generated files
-- =========================================================
CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(150) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  was_successful TINYINT(1) NOT NULL DEFAULT 0,
  failure_reason VARCHAR(160) NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_login_attempts_username_time (username, attempted_at),
  KEY idx_login_attempts_ip_time (ip_address, attempted_at),
  CONSTRAINT fk_login_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  session_token_hash CHAR(64) NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_sessions_token (session_token_hash),
  KEY idx_user_sessions_user (user_id),
  KEY idx_user_sessions_expiry (expires_at),
  CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_password_resets_token (token_hash),
  KEY idx_password_resets_user (user_id),
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NULL,
  module VARCHAR(80) NOT NULL,
  action VARCHAR(80) NOT NULL,
  entity_table VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_actor_time (actor_user_id, created_at),
  KEY idx_audit_module_action (module, action),
  KEY idx_audit_entity (entity_table, entity_id),
  CONSTRAINT fk_audit_logs_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS school_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NULL,
  value_type ENUM('string','number','boolean','json','date','time') NOT NULL DEFAULT 'string',
  setting_group VARCHAR(80) NOT NULL DEFAULT 'general',
  is_public TINYINT(1) NOT NULL DEFAULT 0,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_school_settings_key (setting_key),
  KEY idx_school_settings_group (setting_group),
  CONSTRAINT fk_school_settings_user FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS generated_files (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  generated_by BIGINT UNSIGNED NULL,
  module VARCHAR(80) NOT NULL,
  file_type ENUM('report_card','receipt','financial_report','attendance_report','broadsheet','other') NOT NULL DEFAULT 'other',
  entity_table VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_generated_files_module (module, file_type),
  CONSTRAINT fk_generated_files_user FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- Seed data for first development install
-- =========================================================
INSERT INTO roles (id, name, slug, description) VALUES
  (1, 'Super Admin', 'super-admin', 'Full system access'),
  (2, 'Admin', 'admin', 'Administrative school operations'),
  (3, 'Teacher', 'teacher', 'Teaching, attendance, results, and CBT access'),
  (4, 'Accountant', 'accountant', 'Finance and fee management access'),
  (5, 'Student', 'student', 'Student portal access'),
  (6, 'Parent', 'parent', 'Guardian portal access')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

INSERT INTO permissions (module, action, slug, description) VALUES
  ('students','manage','students.manage','Create, update, view, and archive students'),
  ('teachers','manage','teachers.manage','Create, update, view, and archive teachers'),
  ('accountants','manage','accountants.manage','Create, update, view, and archive accountants'),
  ('academic','manage','academic.manage','Manage sessions, terms, classes, sections, departments, subjects, and calendar'),
  ('attendance','manage','attendance.manage','Mark and review student and staff attendance'),
  ('results','manage','results.manage','Enter, approve, publish, lock, and print results'),
  ('cbt','manage','cbt.manage','Create CBT exams, questions, attempts, and results'),
  ('finance','manage','finance.manage','Manage fees, invoices, payments, receipts, expenses, and reports'),
  ('timetable','manage','timetable.manage','Manage timetable periods, venues, and entries'),
  ('settings','manage','settings.manage','Manage school-wide settings'),
  ('audit','view','audit.view','View audit log history'),
  ('roles','manage','roles.manage','Manage roles and permissions')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE slug IN ('students.manage','teachers.manage','accountants.manage','academic.manage','attendance.manage','results.manage','cbt.manage','timetable.manage','settings.manage','audit.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE slug IN ('attendance.manage','results.manage','cbt.manage','timetable.manage');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE slug IN ('finance.manage','students.manage','audit.view');

INSERT INTO academic_sessions (id, name, start_date, end_date, status) VALUES
  (1, '2025/2026', '2025-09-08', '2026-07-24', 'active'),
  (2, '2026/2027', '2026-09-07', '2027-07-23', 'upcoming')
ON DUPLICATE KEY UPDATE start_date = VALUES(start_date), end_date = VALUES(end_date), status = VALUES(status);

INSERT INTO terms (id, session_id, name, start_date, end_date, status) VALUES
  (1, 1, 'First Term', '2025-09-08', '2025-12-12', 'active'),
  (2, 1, 'Second Term', '2026-01-12', '2026-04-03', 'inactive'),
  (3, 1, 'Third Term', '2026-04-27', '2026-07-24', 'inactive')
ON DUPLICATE KEY UPDATE start_date = VALUES(start_date), end_date = VALUES(end_date), status = VALUES(status);

INSERT INTO departments (id, name, description, status) VALUES
  (1, 'Science', 'Science and laboratory subjects', 'active'),
  (2, 'Languages', 'English and language studies', 'active'),
  (3, 'Commercial', 'Business and commercial studies', 'active'),
  (4, 'ICT', 'Computing and technology', 'active'),
  (5, 'Finance', 'Bursary and accounting operations', 'active')
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO classes (id, name, level, status) VALUES
  (1, 'JSS 1', 'junior', 'active'),
  (2, 'JSS 2', 'junior', 'active'),
  (3, 'SS 1', 'senior', 'active'),
  (4, 'SS 2', 'senior', 'active')
ON DUPLICATE KEY UPDATE level = VALUES(level), status = VALUES(status);

INSERT INTO sections (id, class_id, name, capacity, status) VALUES
  (1, 1, 'A', 45, 'active'),
  (2, 2, 'B', 45, 'active'),
  (3, 3, 'Science', 50, 'active'),
  (4, 4, 'Science', 50, 'active')
ON DUPLICATE KEY UPDATE capacity = VALUES(capacity), status = VALUES(status);

INSERT INTO subjects (id, code, name, department_id, subject_type, status) VALUES
  (1, 'MTH', 'Mathematics', 1, 'core', 'active'),
  (2, 'ENG', 'English Language', 2, 'core', 'active'),
  (3, 'PHY', 'Physics', 1, 'elective', 'active'),
  (4, 'CSC', 'Computer Science', 4, 'elective', 'active'),
  (5, 'BIO', 'Biology', 1, 'elective', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name), department_id = VALUES(department_id), subject_type = VALUES(subject_type), status = VALUES(status);

INSERT IGNORE INTO subject_classes (subject_id, class_id) VALUES
  (1,1),(1,2),(1,3),(1,4),(2,1),(2,2),(2,3),(2,4),(3,3),(3,4),(4,1),(4,2),(4,3),(5,3),(5,4);

INSERT INTO users (id, username, email, password_hash, user_type, status) VALUES
  (1, 'admin', 'admin@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
  (2, 'john.musa', 'john.musa@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
  (3, 'john.ibrahim', 'john.ibrahim@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant', 'active'),
  (4, 'REG-2026-001', 'musa.ibrahim@student.school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active')
ON DUPLICATE KEY UPDATE email = VALUES(email), user_type = VALUES(user_type), status = VALUES(status);

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (1,1),(2,3),(3,4),(4,5);

INSERT INTO staff (id, user_id, staff_no, staff_type, first_name, last_name, gender, phone, email, department_id, designation, employment_date, employment_status, qualification, specialization, years_experience, salary_grade, contract_type) VALUES
  (1, 2, 'TCH001', 'teacher', 'John', 'Musa', 'male', '08031234567', 'john.musa@school.test', 1, 'Senior Teacher', '2020-09-14', 'active', 'B.Sc Mathematics', 'Mathematics and Physics', 6.0, 'Grade 08', 'Permanent'),
  (2, 3, 'ACC001', 'accountant', 'John', 'Ibrahim', 'male', '08034561234', 'john.ibrahim@school.test', 5, 'Accountant', '2019-09-02', 'active', 'B.Sc Accounting', 'ICAN', 7.0, 'Grade 08', 'Permanent')
ON DUPLICATE KEY UPDATE staff_type = VALUES(staff_type), email = VALUES(email), department_id = VALUES(department_id), employment_status = VALUES(employment_status);

INSERT INTO students (id, user_id, admission_no, registration_no, first_name, last_name, gender, date_of_birth, phone, email, address, status) VALUES
  (1, 4, 'ADM-2026-001', 'REG-2026-001', 'Musa', 'Ibrahim', 'male', '2010-05-14', '08010000001', 'musa.ibrahim@student.school.test', 'Katsina, Nigeria', 'active')
ON DUPLICATE KEY UPDATE registration_no = VALUES(registration_no), status = VALUES(status);

INSERT INTO guardians (id, full_name, relationship, phone, email, address, occupation) VALUES
  (1, 'Aminu Ibrahim', 'Father', '08020000001', 'aminu.ibrahim@example.com', 'Katsina, Nigeria', 'Civil Servant')
ON DUPLICATE KEY UPDATE phone = VALUES(phone), email = VALUES(email);
INSERT IGNORE INTO student_guardians (student_id, guardian_id, is_primary) VALUES (1, 1, 1);
INSERT INTO student_enrollments (id, student_id, session_id, class_id, section_id, roll_number, status, enrolled_at) VALUES
  (1, 1, 1, 4, 4, 'SS2-SCI-001', 'active', '2025-09-08')
ON DUPLICATE KEY UPDATE class_id = VALUES(class_id), section_id = VALUES(section_id), status = VALUES(status);

INSERT IGNORE INTO teacher_subjects (teacher_id, subject_id) VALUES (1,1),(1,3);
INSERT IGNORE INTO teacher_classes (teacher_id, class_id, section_id) VALUES (1,4,4),(1,3,3);

INSERT INTO grade_settings (id, grade, min_score, max_score, remark, status) VALUES
  (1, 'A', 70, 100, 'Excellent', 'active'),
  (2, 'B', 60, 69.99, 'Very Good', 'active'),
  (3, 'C', 50, 59.99, 'Good', 'active'),
  (4, 'D', 40, 49.99, 'Fair', 'active'),
  (5, 'F', 0, 39.99, 'Needs Improvement', 'active')
ON DUPLICATE KEY UPDATE min_score = VALUES(min_score), max_score = VALUES(max_score), remark = VALUES(remark), status = VALUES(status);

INSERT INTO school_periods (id, period_name, start_time, end_time, sort_order, is_break) VALUES
  (1, 'Period 1', '08:00:00', '08:40:00', 1, 0),
  (2, 'Period 2', '08:40:00', '09:20:00', 2, 0),
  (3, 'Break', '10:00:00', '10:30:00', 3, 1)
ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time), sort_order = VALUES(sort_order), is_break = VALUES(is_break);

INSERT INTO venues (id, name, capacity, status) VALUES
  (1, 'Room 12', 45, 'active'),
  (2, 'Science Laboratory', 50, 'active'),
  (3, 'Computer Lab', 40, 'active')
ON DUPLICATE KEY UPDATE capacity = VALUES(capacity), status = VALUES(status);

INSERT INTO working_days (id, day_name, is_enabled) VALUES
  (1, 'Monday', 1), (2, 'Tuesday', 1), (3, 'Wednesday', 1), (4, 'Thursday', 1), (5, 'Friday', 1)
ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled);

INSERT INTO fee_items (id, name, description, status) VALUES
  (1, 'Tuition', 'Core tuition fee', 'active'),
  (2, 'ICT Levy', 'Technology and CBT support levy', 'active'),
  (3, 'Laboratory Fee', 'Science laboratory consumables', 'active')
ON DUPLICATE KEY UPDATE description = VALUES(description), status = VALUES(status);

INSERT INTO school_settings (setting_key, setting_value, value_type, setting_group, is_public) VALUES
  ('school.name', 'Sample International School', 'string', 'general', 1),
  ('school.email', 'info@school.test', 'string', 'general', 1),
  ('school.phone', '08000000000', 'string', 'general', 1),
  ('result.pass_mark', '50', 'number', 'results', 0),
  ('cbt.default_duration_minutes', '30', 'number', 'cbt', 0),
  ('cbt.randomize_questions', 'true', 'boolean', 'cbt', 0),
  ('timetable.opening_time', '08:00', 'time', 'timetable', 0),
  ('timetable.closing_time', '15:00', 'time', 'timetable', 0)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), value_type = VALUES(value_type), setting_group = VALUES(setting_group), is_public = VALUES(is_public);


-- Persistent remember-me tokens for secure automatic login.
CREATE TABLE IF NOT EXISTS remember_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  selector VARCHAR(64) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  last_used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_remember_tokens_selector (selector),
  KEY idx_remember_tokens_user (user_id),
  KEY idx_remember_tokens_expiry (expires_at),
  CONSTRAINT fk_remember_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 1;


