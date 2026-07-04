<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\FileUploader;

/** Shared database-backed profile service for all authenticated roles. */
final class ProfileService
{
    private ProfileCompletionService $completionService;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->completionService = new ProfileCompletionService($this->db);
    }

    /** @param array<string,mixed> $authUser @return array<string,mixed> */
    public function getProfile(array $authUser): array
    {
        $user = $this->db->fetchOne('SELECT id AS user_id, username, email AS account_email, status AS account_status, created_at, updated_at, last_login_at, password_must_change FROM users WHERE id = :id', ['id' => (int) $authUser['id']]) ?? [];
        $role = (string) $authUser['role'];
        return $role === 'student'
            ? array_merge($user, $this->student((int) $authUser['id']), ['role_label' => 'Student'])
            : array_merge($user, $this->staff((int) $authUser['id'], $role), ['role_label' => $this->roleLabel($role)]);
    }

    /** @param array<string,mixed> $authUser @param array<string,mixed> $data @param array<string,mixed>|null $file */
    public function updateProfile(array $authUser, array $data, ?array $file = null): array
    {
        $userId = (int) $authUser['id'];
        $errors = $this->profileErrors($data, $userId);
        if ($errors) {
            return ['success' => false, 'message' => 'Please correct the highlighted profile fields.', 'errors' => $errors];
        }

        $userId = (int) $authUser['id'];
        $role = (string) $authUser['role'];
        $name = $this->splitName((string) $data['full_name']);
        $photo = null;
        $email = $this->nullable((string) ($data['email'] ?? ''));

        $this->db->beginTransaction();
        try {
            if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $photo = $this->replacePhoto($userId, $role, $file);
            }
            $this->db->execute('UPDATE users SET email = :email WHERE id = :id', ['email' => $email, 'id' => $userId]);
            $params = $this->params($userId, $data, $name, $photo, $email);
            if ($role === 'student') {
                $sql = 'UPDATE students SET first_name=:first_name,last_name=:last_name,email=:email,phone=:phone,gender=:gender,date_of_birth=:date_of_birth,address=:address,religion=:religion,nationality=:nationality,state=:state,local_government=:local_government,emergency_contact=:emergency_contact' . ($photo ? ',passport_path=:photo' : '') . ' WHERE user_id=:user_id';
            } else {
                $this->ensureStaff($userId, $role, $name);
                $sql = 'UPDATE staff SET first_name=:first_name,last_name=:last_name,email=:email,phone=:phone,gender=:gender,date_of_birth=:date_of_birth,address=:address' . ($photo ? ',passport_path=:photo' : '') . ' WHERE user_id=:user_id';
                unset($params['religion'], $params['nationality'], $params['state'], $params['local_government'], $params['emergency_contact']);
            }
            $this->db->execute($sql, $params);

            if ($role === 'student') {
                $student = $this->db->fetchOne('SELECT * FROM students WHERE user_id = :id LIMIT 1', ['id' => $userId]);
                if ($student) {
                    $this->completionService->sync($student);
                }
            }

            $this->audit($userId, $role, $photo ? 'profile_photo_changed' : 'profile_updated', $data);
            $this->db->commit();
            return ['success' => true, 'message' => $photo ? 'Profile and photo updated successfully.' : 'Profile updated successfully.'];
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update profile right now.'];
        }
    }

    /** @param array<string,mixed> $authUser @param array<string,mixed> $data */
    public function changePassword(array $authUser, array $data): array
    {
        $current = (string) ($data['current_password'] ?? '');
        $next = (string) ($data['new_password'] ?? '');
        $confirm = (string) ($data['confirm_password'] ?? '');
        $errors = [];
        if ($current === '') { $errors['current_password'] = 'Current password is required.'; }
        if (!$this->strongPassword($next)) { $errors['new_password'] = 'Use at least 8 characters with uppercase, lowercase, and a number.'; }
        if ($next !== $confirm) { $errors['confirm_password'] = 'Password confirmation does not match.'; }
        if ($errors) { return ['success' => false, 'message' => 'Please correct the password fields.', 'errors' => $errors]; }

        $row = $this->db->fetchOne('SELECT password_hash FROM users WHERE id=:id', ['id' => (int) $authUser['id']]);
        if (!$row || !password_verify($current, (string) $row['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.', 'errors' => ['current_password' => 'Current password is incorrect.']];
        }
        $this->db->execute('UPDATE users SET password_hash=:hash,password_must_change=0,temp_password_created_at=NULL WHERE id=:id', ['hash' => password_hash($next, PASSWORD_DEFAULT), 'id' => (int) $authUser['id']]);
        $this->audit((int) $authUser['id'], (string) $authUser['role'], 'password_changed', []);
        return ['success' => true, 'message' => 'Password changed successfully.'];
    }

    /** @return array<string,mixed> */
    private function student(int $userId): array
    {
        $row = $this->db->fetchOne("SELECT s.*, CONCAT(s.first_name,' ',s.last_name) full_name, c.name class_name, sec.name section_name, se.enrolled_at admission_date, g.full_name guardian_name, g.phone guardian_phone FROM students s LEFT JOIN student_enrollments se ON se.student_id=s.id AND se.status='active' LEFT JOIN classes c ON c.id=se.class_id LEFT JOIN sections sec ON sec.id=se.section_id LEFT JOIN student_guardians sg ON sg.student_id=s.id AND sg.is_primary=1 LEFT JOIN guardians g ON g.id=sg.guardian_id WHERE s.user_id=:id LIMIT 1", ['id' => $userId]) ?? [];
        $completion = $row ? $this->completionService->sync($row) : ['status' => 'incomplete', 'percentage' => 0, 'missing' => ProfileCompletionService::REQUIRED_STUDENT_FIELDS, 'complete' => false];
        return array_merge($row, ['profile_photo' => $this->photo($row['passport_path'] ?? ''), 'display_id' => $row['registration_no'] ?? '', 'position' => 'Student', 'department' => trim((string) (($row['class_name'] ?? '') . ' ' . ($row['section_name'] ?? ''))), 'assigned_classes' => [], 'subjects' => [], 'profile_completion' => $completion]);
    }

    /** @return array<string,mixed> */
    private function staff(int $userId, string $role): array
    {
        $row = $this->db->fetchOne("SELECT st.*, CONCAT(st.first_name,' ',st.last_name) full_name, d.name department_name FROM staff st LEFT JOIN departments d ON d.id=st.department_id WHERE st.user_id=:id LIMIT 1", ['id' => $userId]) ?? [];
        $staffId = (int) ($row['id'] ?? 0);
        return array_merge($row, ['profile_photo' => $this->photo($row['passport_path'] ?? ''), 'display_id' => $row['staff_no'] ?? strtoupper($role), 'position' => $row['designation'] ?? $this->roleLabel($role), 'department' => $row['department_name'] ?? '', 'assigned_classes' => $staffId ? $this->assignedClasses($staffId) : [], 'subjects' => $staffId ? $this->subjects($staffId) : []]);
    }

    private function ensureStaff(int $userId, string $role, array $name): void
    {
        if ($this->db->fetchOne('SELECT id FROM staff WHERE user_id=:id', ['id' => $userId])) { return; }
        $type = in_array($role, ['super-admin', 'admin'], true) ? 'admin' : $role;
        $this->db->execute('INSERT INTO staff (user_id,staff_no,staff_type,first_name,last_name,employment_status) VALUES (:user_id,:staff_no,:staff_type,:first_name,:last_name,:status)', ['user_id' => $userId, 'staff_no' => 'ADM' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT), 'staff_type' => $type, 'first_name' => $name['first_name'], 'last_name' => $name['last_name'], 'status' => 'active']);
    }

    /** @return array<int,string> */
    private function assignedClasses(int $staffId): array
    {
        return array_map(fn($r) => (string) $r['label'], $this->db->fetchAll("SELECT DISTINCT CONCAT(c.name,COALESCE(CONCAT(' ',sec.name),'')) label FROM teacher_classes tc INNER JOIN classes c ON c.id=tc.class_id LEFT JOIN sections sec ON sec.id=tc.section_id WHERE tc.teacher_id=:id", ['id' => $staffId]));
    }

    /** @return array<int,string> */
    private function subjects(int $staffId): array
    {
        return array_map(fn($r) => (string) $r['name'], $this->db->fetchAll('SELECT DISTINCT s.name FROM teacher_subjects ts INNER JOIN subjects s ON s.id=ts.subject_id WHERE ts.teacher_id=:id', ['id' => $staffId]));
    }

    private function replacePhoto(int $userId, string $role, array $file): string
    {
        $table = $role === 'student' ? 'students' : 'staff';
        $old = $this->db->fetchOne("SELECT passport_path FROM {$table} WHERE user_id=:id", ['id' => $userId]);
        $uploaded = FileUploader::upload($file, 'profiles/' . $role, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        $oldPath = (string) ($old['passport_path'] ?? '');
        if (str_starts_with($oldPath, 'app/Storage/uploads/')) { FileUploader::delete($oldPath); }
        return (string) $uploaded['path'];
    }

    private function audit(int $userId, string $role, string $action, array $new): void
    {
        $this->db->execute('INSERT INTO audit_logs (actor_user_id,module,action,entity_table,entity_id,new_values,ip_address,user_agent) VALUES (:actor,:module,:action,:table_name,:entity_id,:new_values,:ip,:agent)', ['actor' => $userId, 'module' => 'profile', 'action' => $role . '.' . $action, 'table_name' => 'users', 'entity_id' => $userId, 'new_values' => $new ? json_encode($new) : null, 'ip' => $_SERVER['REMOTE_ADDR'] ?? null, 'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
    }

    private function photo(mixed $path): string { $path = (string) $path; return $path !== '' ? '../' . ltrim($path, './') : '../assets/img/avatar/avatar1.jpg'; }
    private function roleLabel(string $role): string { return match ($role) { 'super-admin' => 'Super Administrator', 'admin' => 'Administrator', 'teacher' => 'Teacher', 'accountant' => 'Accountant', default => ucfirst($role) }; }
    private function splitName(string $fullName): array { $parts = preg_split('/\s+/', trim($fullName)) ?: ['']; $first = array_shift($parts) ?: ''; return ['first_name' => $first, 'last_name' => trim(implode(' ', $parts)) ?: $first]; }
    private function strongPassword(string $password): bool { return strlen($password) >= 8 && preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password); }
    private function nullable(string $value): ?string { $value = trim($value); return $value === '' ? null : $value; }

    private function profileErrors(array $data, int $userId): array
    {
        $errors = [];
        if (trim((string) ($data['full_name'] ?? '')) === '') { $errors['full_name'] = 'Full name is required.'; }
        $email = trim((string) ($data['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Enter a valid email address or leave it blank.'; }
        if ($email !== '') {
            $existing = $this->db->fetchOne('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1', ['email' => $email, 'id' => $userId]);
            if ($existing) { $errors['email'] = 'This email address is already in use.'; }
        }
        $phone = trim((string) ($data['phone'] ?? ''));
        if ($phone !== '' && !preg_match('/^[+0-9()\-\s]{7,25}$/', $phone)) { $errors['phone'] = 'Enter a valid phone number.'; }
        return $errors;
    }

    private function params(int $userId, array $data, array $name, ?string $photo, ?string $email): array
    {
        $params = [
            'first_name' => $name['first_name'],
            'last_name' => $name['last_name'],
            'email' => $email,
            'phone' => $this->nullable((string) ($data['phone'] ?? '')),
            'gender' => $this->nullable((string) ($data['gender'] ?? '')),
            'date_of_birth' => $this->nullable((string) ($data['date_of_birth'] ?? '')),
            'address' => $this->nullable((string) ($data['address'] ?? '')),
            'religion' => $this->nullable((string) ($data['religion'] ?? '')),
            'nationality' => $this->nullable((string) ($data['nationality'] ?? '')),
            'state' => $this->nullable((string) ($data['state'] ?? '')),
            'local_government' => $this->nullable((string) ($data['local_government'] ?? '')),
            'emergency_contact' => $this->nullable((string) ($data['emergency_contact'] ?? '')),
            'user_id' => $userId,
        ];
        if ($photo) { $params['photo'] = $photo; }
        return $params;
    }
}
