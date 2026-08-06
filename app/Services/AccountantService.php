<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\FileUploader;
use App\Helpers\Paginator;
use App\Helpers\Security;
use App\Models\StaffModel;
use App\Traits\Auditable;

/**
 * Backing service for Accountant Management: list/create/update/delete + role permissions display.
 */
final class AccountantService
{
    use Auditable;

    private const GENDERS = ['male', 'female', 'other'];
    private const STATUSES = ['active', 'inactive', 'on_leave', 'suspended', 'deleted'];
    private const DEFAULT_PER_PAGE = 10;

    private StaffModel $staff;

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
        $this->staff = new StaffModel($this->db);
    }

    /** @param array<string,mixed> $filters @return array{data:array<int,array<string,mixed>>,meta:array<string,int>} */
    public function list(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $where = ["s.staff_type = 'accountant'"];
        $params = [];

        if (($search = trim((string) ($filters['search'] ?? ''))) !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.staff_no LIKE :search3)';
            $like = '%' . $search . '%';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }
        if (($deptId = $this->intFilter($filters['department_id'] ?? 0)) !== null) {
            $where[] = 's.department_id = :department_id';
            $params['department_id'] = $deptId;
        }

        $status = $this->enumFilter($filters['status'] ?? '', self::STATUSES);
        if ($status !== null) {
            $where[] = 's.employment_status = :status';
            $params['status'] = $status;
        } else {
            $where[] = "s.employment_status <> 'deleted'";
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT s.*, d.name AS department_name
                 FROM staff s
                 LEFT JOIN departments d ON d.id = s.department_id
                 {$whereSql}
                 ORDER BY s.last_name ASC, s.first_name ASC";

        return Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
    }

    /** @return array<string,mixed>|null */
    public function findByUserId(int $userId): ?array
    {
        $row = $this->db->fetchOne('SELECT id FROM staff WHERE user_id = :uid AND staff_type = "accountant"', ['uid' => $userId]);

        return $row ? $this->find((int) $row['id']) : null;
    }

    public function find(int $id): ?array
    {
        $staff = $this->staff->find($id);

        if ($staff === null || $staff['staff_type'] !== 'accountant') {
            return null;
        }

        $staff['department_name'] = null;
        if (!empty($staff['department_id'])) {
            $dept = $this->db->fetchOne('SELECT name FROM departments WHERE id = :id', ['id' => $staff['department_id']]);
            $staff['department_name'] = $dept['name'] ?? null;
        }

        $staff['documents'] = $this->db->fetchAll(
            'SELECT * FROM staff_documents WHERE staff_id = :id ORDER BY uploaded_at DESC',
            ['id' => $id]
        );

        if (!empty($staff['user_id'])) {
            $staff['payments_processed'] = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM payments WHERE received_by = :id', ['id' => $staff['user_id']])['c'] ?? 0);
            $staff['receipts_generated'] = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM receipts WHERE issued_by = :id', ['id' => $staff['user_id']])['c'] ?? 0);
            $staff['expenses_recorded'] = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM expenses WHERE recorded_by = :id', ['id' => $staff['user_id']])['c'] ?? 0);
        } else {
            $staff['payments_processed'] = 0;
            $staff['receipts_generated'] = 0;
            $staff['expenses_recorded'] = 0;
        }

        return $staff;
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<string,mixed>> $files
     * @param array<string,mixed>|null $actor
     * @return array{success:bool,message:string,errors?:array<string,string>,credentials?:array<string,string>,id?:int}
     */
    public function create(array $data, array $files, ?array $actor): array
    {
        $fields = $this->normalizeFields($data);
        $errors = $this->validate($fields, null, true);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO users (username, email, password_hash, user_type, status, password_must_change, temp_password_created_at) VALUES (:username, :email, :password_hash, :user_type, :status, 1, NOW())',
                [
                    'username' => $fields['username'],
                    'email' => $fields['email'] !== '' ? $fields['email'] : null,
                    'password_hash' => password_hash($fields['password'], PASSWORD_DEFAULT),
                    'user_type' => 'accountant',
                    'status' => 'active',
                ]
            );
            $userId = (int) $this->db->lastInsertId();

            $roleRow = $this->db->fetchOne("SELECT id FROM roles WHERE slug = 'accountant' LIMIT 1");
            if ($roleRow) {
                $this->db->execute('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)', ['user_id' => $userId, 'role_id' => $roleRow['id']]);
            }

            $staffNo = $this->generateStaffNo();
            $photoFile = $this->resolvePhotoFile($files);
            $photoPath = $photoFile ? FileUploader::upload($photoFile, 'staff', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024)['path'] : null;

            $staffPayload = array_merge($this->staffPayload($fields, $photoPath), [
                'user_id' => $userId,
                'staff_no' => $staffNo,
                'staff_type' => 'accountant',
            ]);
            $staffId = (int) $this->staff->create($staffPayload);

            $this->syncDocuments($staffId, $files, $actor);

            $this->audit($actor, 'staff', 'accountant.created', 'staff', $staffId, null, $staffPayload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to create the accountant right now.'];
        }

        return [
            'success' => true,
            'message' => "Accountant created successfully. Staff ID: {$staffNo}.",
            'id' => $staffId,
            'credentials' => ['username' => $fields['username'], 'staff_no' => $staffNo],
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,array<string,mixed>> $files
     * @param array<string,mixed>|null $actor
     */
    public function update(int $id, array $data, array $files, ?array $actor): array
    {
        $before = $this->staff->find($id);

        if ($before === null || $before['staff_type'] !== 'accountant') {
            return ['success' => false, 'message' => 'Accountant not found.'];
        }

        $fields = $this->normalizeFields($data, $before);
        $errors = $this->validate($fields, $id, false);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $photoFile = $this->resolvePhotoFile($files);
            $photoPath = null;
            if ($photoFile !== null) {
                $photoPath = FileUploader::upload($photoFile, 'staff', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024)['path'];
                if (!empty($before['passport_path']) && str_starts_with((string) $before['passport_path'], 'app/Storage/uploads/')) {
                    FileUploader::delete((string) $before['passport_path']);
                }
            }

            $staffPayload = $this->staffPayload($fields, $photoPath);
            $this->staff->update($id, $staffPayload);

            $this->syncDocuments($id, $files, $actor);

            if ($fields['email'] !== '' && !empty($before['user_id'])) {
                $this->db->execute('UPDATE users SET email = :email WHERE id = :id', ['email' => $fields['email'], 'id' => $before['user_id']]);
            }

            $this->audit($actor, 'staff', 'accountant.updated', 'staff', $id, $before, $staffPayload);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to update the accountant right now.'];
        }

        return ['success' => true, 'message' => 'Accountant updated successfully.'];
    }

    /**
     * Soft-deletes an accountant (employment_status = 'deleted') and deactivates
     * the linked login, rather than removing the row - payment/receipt/expense
     * records they touched should not silently lose their actor reference.
     *
     * @param array<string,mixed>|null $actor
     */
    public function delete(int $id, ?array $actor): array
    {
        $before = $this->staff->find($id);

        if ($before === null || $before['staff_type'] !== 'accountant') {
            return ['success' => false, 'message' => 'Accountant not found.'];
        }
        if ($before['employment_status'] === 'deleted') {
            return ['success' => false, 'message' => 'This accountant has already been deleted.'];
        }

        $this->db->beginTransaction();
        try {
            $this->staff->update($id, ['employment_status' => 'deleted']);
            if (!empty($before['user_id'])) {
                $this->db->execute('UPDATE users SET status = "inactive" WHERE id = :id', ['id' => $before['user_id']]);
            }
            $this->audit($actor, 'staff', 'accountant.deleted', 'staff', $id, $before, ['employment_status' => 'deleted']);
            $this->db->commit();
        } catch (\Throwable $throwable) {
            $this->db->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to delete this accountant right now.'];
        }

        return ['success' => true, 'message' => 'Accountant deleted successfully.'];
    }

    /** @return array<int,array<string,mixed>> */
    public function departmentsForSelect(): array
    {
        return $this->db->fetchAll('SELECT id, name FROM departments WHERE status = "active" ORDER BY name ASC');
    }

    /**
     * Real read-only permission set granted to the 'accountant' role, sourced
     * from roles/role_permissions/permissions - replaces the old hardcoded
     * placeholder list that had no relationship to actual access control.
     *
     * @return array<int,array<string,mixed>>
     */
    public function permissionsForRole(string $roleSlug = 'accountant'): array
    {
        return $this->db->fetchAll(
            'SELECT p.module, p.action, p.slug, p.description
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN roles r ON r.id = rp.role_id
             WHERE r.slug = :slug
             ORDER BY p.module ASC',
            ['slug' => $roleSlug]
        );
    }

    public function generateStaffNo(): string
    {
        $prefix = 'ACC' . date('Y') . '-';
        $count = (int) ($this->db->fetchOne("SELECT COUNT(*) c FROM staff WHERE staff_no LIKE :prefix", ['prefix' => $prefix . '%'])['c'] ?? 0);

        for ($attempt = $count + 1; $attempt < $count + 100; $attempt++) {
            $candidate = $prefix . str_pad((string) $attempt, 3, '0', STR_PAD_LEFT);
            if (!$this->db->fetchOne('SELECT 1 FROM staff WHERE staff_no = :no', ['no' => $candidate])) {
                return $candidate;
            }
        }

        return $prefix . Security::randomString(6);
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $existing Current staff row, used to preserve fields the
     *     calling form doesn't submit (edit-accountant.php omits gender/DOB/nationality/
     *     state/LGA/employment_date) instead of silently nulling them out on update.
     * @return array<string,mixed>
     */
    private function normalizeFields(array $data, array $existing = []): array
    {
        $pick = static function (string $dataKey, string $existingKey = '') use ($data, $existing): string {
            if (array_key_exists($dataKey, $data)) {
                return trim((string) $data[$dataKey]);
            }
            return $existingKey !== '' ? trim((string) ($existing[$existingKey] ?? '')) : '';
        };

        return [
            'first_name' => $pick('first_name', 'first_name'),
            'middle_name' => $pick('middle_name', 'middle_name'),
            'last_name' => $pick('last_name', 'last_name'),
            'gender' => strtolower($pick('gender', 'gender')),
            'date_of_birth' => $pick('date_of_birth', 'date_of_birth'),
            'nationality' => $pick('nationality', 'nationality'),
            'state' => $pick('state', 'state'),
            'local_government' => $pick('local_government', 'local_government'),
            'address' => $pick('address', 'address'),
            'phone' => $pick('phone', 'phone'),
            'email' => $pick('email', 'email'),
            'department_id' => (int) (array_key_exists('department', $data) || array_key_exists('department_id', $data)
                ? ($data['department'] ?? $data['department_id'] ?? 0)
                : ($existing['department_id'] ?? 0)),
            'designation' => $pick('designation', 'designation') ?: 'Accountant',
            'employment_date' => $pick('employment_date', 'employment_date'),
            'employment_status' => array_key_exists('status', $data) || array_key_exists('employment_status', $data)
                ? strtolower(str_replace(' ', '_', trim((string) ($data['status'] ?? $data['employment_status'] ?? 'active'))))
                : strtolower((string) ($existing['employment_status'] ?? 'active')),
            'qualification' => $pick('qualification', 'qualification'),
            'certification' => $pick('certification', 'specialization'),
            'experience' => $pick('experience', 'years_experience'),
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => (string) ($data['password'] ?? ''),
            'confirm_password' => (string) ($data['confirm_password'] ?? ''),
        ];
    }

    /** @return array<string,string> */
    private function validate(array $f, ?int $excludeId, bool $requireAccount): array
    {
        $errors = [];

        if ($f['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        }
        if ($f['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        }
        if ($f['gender'] !== '' && !in_array($f['gender'], self::GENDERS, true)) {
            $errors['gender'] = 'Choose a valid gender.';
        }
        if ($f['date_of_birth'] !== '' && (strtotime($f['date_of_birth']) === false || strtotime($f['date_of_birth']) > time())) {
            $errors['date_of_birth'] = 'Enter a valid date of birth.';
        }
        if ($f['phone'] === '') {
            $errors['phone'] = 'Phone number is required.';
        }
        if ($f['email'] === '' || !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        } elseif ($this->duplicateExists('staff', 'email', $f['email'], $excludeId)) {
            $errors['email'] = 'An accountant with this email already exists.';
        }
        if ($f['department_id'] < 0) {
            $errors['department_id'] = 'Choose a valid department.';
        }
        if (!in_array($f['employment_status'], self::STATUSES, true)) {
            $errors['employment_status'] = 'Choose a valid employment status.';
        }
        if ($f['experience'] !== '' && (!is_numeric($f['experience']) || (float) $f['experience'] < 0)) {
            $errors['experience'] = 'Years of experience must be a positive number.';
        }

        if ($requireAccount) {
            if ($f['username'] === '') {
                $errors['username'] = 'Username is required.';
            } elseif ($this->duplicateExists('users', 'username', $f['username'], null)) {
                $errors['username'] = 'This username is already taken.';
            }
            if (!Security::isStrongPassword($f['password'])) {
                $errors['password'] = Security::passwordPolicyMessage();
            } elseif ($f['password'] !== $f['confirm_password']) {
                $errors['confirm_password'] = 'Password confirmation does not match.';
            }
        }

        return $errors;
    }

    /** @return array<string,mixed> */
    private function staffPayload(array $f, ?string $photoPath): array
    {
        $payload = [
            'first_name' => $f['first_name'],
            'middle_name' => $f['middle_name'] !== '' ? $f['middle_name'] : null,
            'last_name' => $f['last_name'],
            'gender' => $f['gender'] !== '' ? $f['gender'] : null,
            'date_of_birth' => $f['date_of_birth'] !== '' ? $f['date_of_birth'] : null,
            'nationality' => $f['nationality'] !== '' ? $f['nationality'] : null,
            'state' => $f['state'] !== '' ? $f['state'] : null,
            'local_government' => $f['local_government'] !== '' ? $f['local_government'] : null,
            'address' => $f['address'] !== '' ? $f['address'] : null,
            'phone' => $f['phone'] !== '' ? $f['phone'] : null,
            'email' => $f['email'] !== '' ? $f['email'] : null,
            'department_id' => $f['department_id'] ?: null,
            'designation' => $f['designation'] !== '' ? $f['designation'] : 'Accountant',
            'employment_date' => $f['employment_date'] !== '' ? $f['employment_date'] : null,
            'employment_status' => $f['employment_status'],
            'qualification' => $f['qualification'] !== '' ? $f['qualification'] : null,
            'specialization' => $f['certification'] !== '' ? $f['certification'] : null,
            'years_experience' => $f['experience'] !== '' ? (float) $f['experience'] : 0,
        ];

        if ($photoPath !== null) {
            $payload['passport_path'] = $photoPath;
        }

        return $payload;
    }

    private function resolvePhotoFile(array $files): ?array
    {
        foreach (['passport', 'profile_photo', 'passport_photo'] as $field) {
            $file = $files[$field] ?? null;
            if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                return $file;
            }
        }

        return null;
    }

    /** @param array<string,array<string,mixed>> $files @param array<string,mixed>|null $actor */
    private function syncDocuments(int $staffId, array $files, ?array $actor): void
    {
        $documentFields = [
            'cv' => 'CV / Resume',
            'certificates' => 'Certificate',
            'appointment_letter' => 'Appointment Letter',
            'id_document' => 'Identification Document',
        ];

        foreach ($documentFields as $field => $label) {
            $fileEntry = $files[$field] ?? null;

            if ($fileEntry === null) {
                continue;
            }

            $entries = is_array($fileEntry['name'] ?? null) ? $this->splitMultiFile($fileEntry) : [$fileEntry];

            foreach ($entries as $entry) {
                if ((int) ($entry['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                try {
                    $uploaded = FileUploader::upload($entry, 'staff/documents', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'], 5 * 1024 * 1024);
                    $this->db->execute(
                        'INSERT INTO staff_documents (staff_id, document_type, file_path, uploaded_by) VALUES (:staff_id, :type, :path, :by)',
                        ['staff_id' => $staffId, 'type' => $label, 'path' => $uploaded['path'], 'by' => isset($actor['id']) ? (int) $actor['id'] : null]
                    );
                } catch (\Throwable $throwable) {
                    Logger::exception($throwable);
                }
            }
        }
    }

    /** @param array<string,mixed> $fileEntry @return array<int,array<string,mixed>> */
    private function splitMultiFile(array $fileEntry): array
    {
        $count = count((array) $fileEntry['name']);
        $entries = [];

        for ($i = 0; $i < $count; $i++) {
            $entries[] = [
                'name' => $fileEntry['name'][$i],
                'type' => $fileEntry['type'][$i],
                'tmp_name' => $fileEntry['tmp_name'][$i],
                'error' => $fileEntry['error'][$i],
                'size' => $fileEntry['size'][$i],
            ];
        }

        return $entries;
    }

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function enumFilter(mixed $value, array $allowed): ?string
    {
        $value = strtolower(str_replace(' ', '_', trim((string) $value)));

        return in_array($value, $allowed, true) ? $value : null;
    }

    private function duplicateExists(string $table, string $column, string $value, ?int $excludeId): bool
    {
        $sql = "SELECT 1 FROM `{$table}` WHERE LOWER(`{$column}`) = LOWER(:value)";
        $params = ['value' => $value];

        if ($excludeId) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        return $this->db->fetchOne($sql . ' LIMIT 1', $params) !== null;
    }
}
