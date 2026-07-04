<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\Security;

/**
 * Shared authentication and authorization service for every school portal role.
 */
final class AuthService
{
    private const REMEMBER_COOKIE = 'sms_remember';

    /** @var array<string, string> */
    private const ROLE_DASHBOARDS = [
        'super-admin' => 'admin/dashboard.php',
        'admin' => 'admin/dashboard.php',
        'teacher' => 'teacher/dashboard.php',
        'student' => 'student/dashboard.php',
        'accountant' => 'accountant/dashboard.php',
    ];

    /** @var array<string, array<int, string>> */
    private const PORTAL_ROLES = [
        'admin' => ['super-admin', 'admin'],
        'teacher' => ['teacher'],
        'student' => ['student'],
        'accountant' => ['accountant'],
    ];

    public function __construct(private ?Database $database = null)
    {
        $this->database = $database ?? Database::getInstance();
    }

    /**
     * Attempts to authenticate a user for a given portal.
     *
     * @return array{success: bool, message: string, redirect?: string, errors?: array<string, array<int, string>>}
     */
    public function attempt(string $identifier, string $password, string $portal, bool $remember = false): array
    {
        $identifier = trim($identifier);
        $errors = $this->validateLoginInput($identifier, $password);

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted errors.', 'errors' => $errors];
        }

        $user = $this->findUserByIdentifier($identifier);

        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            Logger::security('Failed login attempt.', ['identifier' => $identifier, 'portal' => $portal]);
            return ['success' => false, 'message' => 'Invalid username/email or password.'];
        }

        if (($user['status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'This account is not active. Please contact the administrator.'];
        }

        $roles = $this->rolesForUser((int) $user['id']);
        $allowedRoles = self::PORTAL_ROLES[$portal] ?? [];

        if (array_intersect($roles, $allowedRoles) === []) {
            Logger::security('Blocked cross-portal login.', ['user_id' => $user['id'], 'portal' => $portal, 'roles' => $roles]);
            return ['success' => false, 'message' => 'Your account is not allowed to access this portal.'];
        }

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            $this->database->execute(
                'UPDATE users SET password_hash = :password_hash WHERE id = :id',
                ['password_hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]
            );
        }

        $this->loginUser($user, $roles);
        $this->database->execute('UPDATE users SET last_login_at = NOW() WHERE id = :id', ['id' => $user['id']]);

        if ($remember) {
            $this->createRememberToken((int) $user['id']);
        } else {
            $this->forgetRememberToken();
        }

        Logger::login((int) $user['id'], $_SERVER['REMOTE_ADDR'] ?? '');

        return [
            'success' => true,
            'message' => 'Login successful.',
            'redirect' => $this->dashboardForRoles($roles),
        ];
    }

    /**
     * Logs the current user out and clears persistent login state.
     */
    public function logout(): void
    {
        $user = $this->user();

        if ($user !== null) {
            Logger::logout((int) $user['id']);
        }

        $this->forgetRememberToken();
        Session::destroy();
    }

    /**
     * Returns the authenticated session user, if any.
     *
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        $user = Session::get('auth_user');

        if (is_array($user)) {
            return $user;
        }

        return $this->loginFromRememberToken();
    }

    /**
     * Returns true when an authenticated user exists.
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Protects a legacy page and redirects/blocks as needed.
     *
     * @param string|array<int, string> $roles
     */
    public function requireRole(string|array $roles): void
    {
        $roles = array_map('strtolower', (array) $roles);
        $user = $this->user();

        if ($user === null) {
            $this->redirect($this->loginUrlForRoles($roles));
        }

        $userRole = strtolower((string) ($user['role'] ?? ''));

        if (!in_array($userRole, $roles, true)) {
            $this->redirect($this->baseUrl('403.php'));
        }
    }

    /**
     * Generates and stores a password reset token.
     *
     * @return array{success: bool, message: string, token?: string}
     */
    public function createPasswordReset(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return ['success' => false, 'message' => 'Enter your username or email address.'];
        }

        $user = $this->findUserByIdentifier($identifier);

        if ($user === null) {
            return ['success' => false, 'message' => 'No account was found for those details.'];
        }

        $token = Security::randomString(64);
        $this->database->execute(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
            ['user_id' => $user['id'], 'token_hash' => hash('sha256', $token)]
        );

        Logger::security('Password reset token generated.', ['user_id' => $user['id']]);

        return ['success' => true, 'message' => 'Password reset request created. Email delivery will be connected later.', 'token' => $token];
    }

    /**
     * Finds a user by username or email with profile details.
     *
     * @return array<string, mixed>|null
     */
    private function findUserByIdentifier(string $identifier): ?array
    {
        return $this->database->fetchOne(
            "SELECT
                u.*,
                COALESCE(CONCAT(st.first_name, ' ', st.last_name), CONCAT(s.first_name, ' ', s.last_name), u.username) AS full_name,
                COALESCE(st.passport_path, s.passport_path, '') AS profile_photo
             FROM users u
             LEFT JOIN staff st ON st.user_id = u.id
             LEFT JOIN students s ON s.user_id = u.id
             WHERE u.username = :username OR u.email = :email
             LIMIT 1",
            ['username' => $identifier, 'email' => $identifier]
        );
    }

    /**
     * Returns normalized role slugs for a user.
     *
     * @return array<int, string>
     */
    private function rolesForUser(int $userId): array
    {
        $rows = $this->database->fetchAll(
            'SELECT r.slug FROM roles r INNER JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = :user_id AND r.status = :status',
            ['user_id' => $userId, 'status' => 'active']
        );

        return array_map(static fn (array $row): string => strtolower((string) $row['slug']), $rows);
    }

    /**
     * Persists authenticated user details to session.
     *
     * @param array<string, mixed> $user
     * @param array<int, string> $roles
     */
    private function loginUser(array $user, array $roles): void
    {
        Session::regenerate();
        $primaryRole = $this->primaryRole($roles);
        $payload = [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'full_name' => (string) ($user['full_name'] ?? $user['username']),
            'role' => $primaryRole,
            'roles' => $roles,
            'profile_photo' => (string) ($user['profile_photo'] ?: 'assets/img/avatar/avatar1.jpg'),
            'last_login_at' => $user['last_login_at'] ?? null,
        ];

        Session::set('auth_user', $payload);
        Session::set('user', $payload);
        Session::set('roles', $roles);
        Session::set('permissions', $this->permissionsForRoles($roles));
        Session::set('expires_at', time() + ((int) Application::instance()->config('session.lifetime', 120) * 60));
    }

    /**
     * Returns permissions granted to any of the user's roles.
     *
     * @param array<int, string> $roles
     * @return array<int, string>
     */
    private function permissionsForRoles(array $roles): array
    {
        if ($roles === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $rows = $this->database->fetchAll(
            "SELECT DISTINCT p.slug FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN roles r ON r.id = rp.role_id
             WHERE r.slug IN ({$placeholders})",
            $roles
        );

        return array_map(static fn (array $row): string => (string) $row['slug'], $rows);
    }

    /**
     * Creates a persistent remember-me cookie and token row.
     */
    private function createRememberToken(int $userId): void
    {
        $selector = Security::randomString(24);
        $validator = Security::randomString(64);
        $days = (int) (getenv('REMEMBER_ME_DAYS') ?: 30);
        $expires = time() + ($days * 86400);

        $this->database->execute('DELETE FROM remember_tokens WHERE user_id = :user_id', ['user_id' => $userId]);
        $this->database->execute(
            'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at, created_at) VALUES (:user_id, :selector, :token_hash, FROM_UNIXTIME(:expires_at), NOW())',
            ['user_id' => $userId, 'selector' => $selector, 'token_hash' => hash('sha256', $validator), 'expires_at' => $expires]
        );

        setcookie(self::REMEMBER_COOKIE, $selector . ':' . $validator, [
            'expires' => $expires,
            'path' => '/',
            'secure' => (bool) Application::instance()->config('session.secure', false),
            'httponly' => true,
            'samesite' => (string) Application::instance()->config('session.same_site', 'Lax'),
        ]);
    }

    /**
     * Clears persistent remember-me token state.
     */
    private function forgetRememberToken(): void
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? '';

        if (is_string($cookie) && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            $this->database->execute('DELETE FROM remember_tokens WHERE selector = :selector', ['selector' => $selector]);
        }

        setcookie(self::REMEMBER_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => (bool) Application::instance()->config('session.secure', false),
            'httponly' => true,
            'samesite' => (string) Application::instance()->config('session.same_site', 'Lax'),
        ]);
    }

    /**
     * Attempts automatic login from a valid remember-me cookie.
     *
     * @return array<string, mixed>|null
     */
    private function loginFromRememberToken(): ?array
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? '';

        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        $token = $this->database->fetchOne(
            'SELECT * FROM remember_tokens WHERE selector = :selector AND expires_at > NOW() LIMIT 1',
            ['selector' => $selector]
        );

        if ($token === null || !hash_equals((string) $token['token_hash'], hash('sha256', $validator))) {
            $this->forgetRememberToken();
            return null;
        }

        $user = $this->database->fetchOne(
            "SELECT u.*, COALESCE(CONCAT(st.first_name, ' ', st.last_name), CONCAT(s.first_name, ' ', s.last_name), u.username) AS full_name, COALESCE(st.passport_path, s.passport_path, '') AS profile_photo
             FROM users u
             LEFT JOIN staff st ON st.user_id = u.id
             LEFT JOIN students s ON s.user_id = u.id
             WHERE u.id = :id AND u.status = 'active'
             LIMIT 1",
            ['id' => $token['user_id']]
        );

        if ($user === null) {
            $this->forgetRememberToken();
            return null;
        }

        $roles = $this->rolesForUser((int) $user['id']);
        $this->loginUser($user, $roles);
        $this->database->execute('UPDATE remember_tokens SET last_used_at = NOW() WHERE id = :id', ['id' => $token['id']]);

        return $this->user();
    }

    /**
     * Validates login input before database access.
     *
     * @return array<string, array<int, string>>
     */
    private function validateLoginInput(string $identifier, string $password): array
    {
        $errors = [];

        if ($identifier === '') {
            $errors['username'][] = 'Username or email is required.';
        }

        if ($password === '') {
            $errors['password'][] = 'Password is required.';
        }

        return $errors;
    }

    /**
     * Chooses the highest-priority role for session display and redirection.
     *
     * @param array<int, string> $roles
     */
    private function primaryRole(array $roles): string
    {
        foreach (['super-admin', 'admin', 'teacher', 'accountant', 'student'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return $roles[0] ?? '';
    }

    /**
     * Returns a dashboard URL for the user's role.
     *
     * @param array<int, string> $roles
     */
    private function dashboardForRoles(array $roles): string
    {
        return $this->baseUrl(self::ROLE_DASHBOARDS[$this->primaryRole($roles)] ?? 'login.php');
    }

    /**
     * Returns the login URL for the requested roles.
     *
     * @param array<int, string> $roles
     */
    private function loginUrlForRoles(array $roles): string
    {
        if (array_intersect($roles, ['super-admin', 'admin']) !== []) {
            return $this->baseUrl('admin/login.php');
        }

        $role = $roles[0] ?? '';

        return $this->baseUrl(in_array($role, ['teacher', 'student', 'accountant'], true) ? $role . '/login.php' : 'login.php');
    }

    /**
     * Redirects and exits.
     */
    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Builds an absolute application URL.
     */
    private function baseUrl(string $path): string
    {
        return rtrim((string) Application::instance()->config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}