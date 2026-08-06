<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\Security;
use App\Traits\Auditable;

/**
 * Shared authentication and authorization service for every school portal role.
 */
final class AuthService
{
    use Auditable;

    private const REMEMBER_COOKIE = 'sms_remember';
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_WINDOW_MINUTES = 15;

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

        if ($this->isLockedOut($identifier)) {
            Logger::security('Login blocked by lockout.', ['identifier' => $identifier, 'portal' => $portal]);
            $this->audit(null, 'auth', 'auth.login.locked_out', 'users', null, null, ['identifier' => $identifier, 'portal' => $portal], null, 'failed');
            return ['success' => false, 'message' => sprintf('Too many failed attempts. Please try again in %d minutes.', self::LOCKOUT_WINDOW_MINUTES)];
        }

        $user = $this->findUserByIdentifier($identifier);

        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            $this->recordLoginAttempt($identifier, isset($user['id']) ? (int) $user['id'] : null, false, 'invalid_credentials');
            Logger::security('Failed login attempt.', ['identifier' => $identifier, 'portal' => $portal]);
            $this->audit(null, 'auth', 'auth.login.failed', 'users', isset($user['id']) ? (int) $user['id'] : null, null, ['identifier' => $identifier, 'portal' => $portal, 'reason' => 'invalid_credentials'], null, 'failed');
            return ['success' => false, 'message' => 'Invalid username/email or password.'];
        }

        if (($user['status'] ?? '') !== 'active') {
            $this->recordLoginAttempt($identifier, (int) $user['id'], false, 'inactive_account');
            $this->audit(null, 'auth', 'auth.login.failed', 'users', (int) $user['id'], null, ['identifier' => $identifier, 'portal' => $portal, 'reason' => 'inactive_account'], null, 'failed');
            return ['success' => false, 'message' => 'This account is not active. Please contact the administrator.'];
        }

        $roles = $this->rolesForUser((int) $user['id']);
        $allowedRoles = self::PORTAL_ROLES[$portal] ?? [];

        if (array_intersect($roles, $allowedRoles) === []) {
            $this->recordLoginAttempt($identifier, (int) $user['id'], false, 'wrong_portal');
            Logger::security('Blocked cross-portal login.', ['user_id' => $user['id'], 'portal' => $portal, 'roles' => $roles]);
            $this->audit(null, 'auth', 'auth.login.failed', 'users', (int) $user['id'], null, ['identifier' => $identifier, 'portal' => $portal, 'reason' => 'wrong_portal'], null, 'failed');
            return ['success' => false, 'message' => 'Your account is not allowed to access this portal.'];
        }

        $this->recordLoginAttempt($identifier, (int) $user['id'], true, null);

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
        $this->audit(['id' => (int) $user['id']], 'auth', 'auth.login.success', 'users', (int) $user['id'], null, ['portal' => $portal, 'remember' => $remember]);

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
            $this->audit(['id' => (int) $user['id']], 'auth', 'auth.logout', 'users', (int) $user['id'], null, null);
        }

        $this->revokeCurrentSession();
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
     * Generates a password reset token and emails the reset link. Always
     * returns the same generic message regardless of whether the identifier
     * matched an account, to avoid user enumeration.
     *
     * @return array{success: bool, message: string}
     */
    public function createPasswordReset(string $identifier, string $portal = 'admin'): array
    {
        $identifier = trim($identifier);
        $genericMessage = 'If an account matches those details, a password reset link has been sent to the account email.';

        if ($identifier === '') {
            return ['success' => false, 'message' => 'Enter your username or email address.'];
        }

        $user = $this->findUserByIdentifier($identifier);

        if ($user === null) {
            return ['success' => true, 'message' => $genericMessage];
        }

        $token = Security::randomString(64);
        $this->database->execute(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
            ['user_id' => $user['id'], 'token_hash' => hash('sha256', $token)]
        );

        $email = (string) ($user['email'] ?? '');

        if ($email !== '') {
            $resetUrl = $this->baseUrl('reset-password.php') . '?token=' . urlencode($token) . '&portal=' . urlencode($portal);
            Mailer::send(
                $email,
                'Password reset request',
                "We received a request to reset your password.\n\nReset your password: {$resetUrl}\n\nThis link expires in 1 hour. If you did not request this, you can ignore this email."
            );
        }

        Logger::security('Password reset token generated.', ['user_id' => $user['id']]);
        $this->audit(null, 'auth', 'auth.password_reset.requested', 'users', (int) $user['id'], null, ['portal' => $portal]);

        return ['success' => true, 'message' => $genericMessage];
    }

    /**
     * Completes a password reset from a token minted by createPasswordReset().
     *
     * @return array{success: bool, message: string, errors?: array<string, string>, portal?: string}
     */
    public function resetPassword(string $token, string $password, string $passwordConfirmation): array
    {
        if ($token === '') {
            return ['success' => false, 'message' => 'This reset link is invalid.'];
        }

        $errors = [];

        if (!Security::isStrongPassword($password)) {
            $errors['password'] = Security::passwordPolicyMessage();
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Password confirmation does not match.';
        }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors];
        }

        $reset = $this->database->fetchOne(
            'SELECT * FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
            ['token_hash' => hash('sha256', $token)]
        );

        if ($reset === null) {
            return ['success' => false, 'message' => 'This reset link is invalid or has expired. Please request a new one.'];
        }

        $userId = (int) $reset['user_id'];
        $user = $this->database->fetchOne('SELECT id, username, status FROM users WHERE id = :id LIMIT 1', ['id' => $userId]);

        if ($user === null || ($user['status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'This account is not available for password reset.'];
        }

        $this->database->beginTransaction();

        try {
            $this->database->execute(
                'UPDATE users SET password_hash = :hash, password_must_change = 0, temp_password_created_at = NULL WHERE id = :id',
                ['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $userId]
            );
            $this->database->execute('UPDATE password_resets SET used_at = NOW() WHERE id = :id', ['id' => $reset['id']]);
            $this->database->execute('DELETE FROM remember_tokens WHERE user_id = :user_id', ['user_id' => $userId]);
            $this->database->commit();
        } catch (\Throwable $throwable) {
            $this->database->rollBack();
            Logger::exception($throwable);
            return ['success' => false, 'message' => 'Unable to reset the password right now. Please try again.'];
        }

        Logger::security('Password reset completed.', ['user_id' => $userId]);
        $this->audit(['id' => $userId], 'auth', 'auth.password_reset.completed', 'users', $userId, null, null);

        $roles = $this->rolesForUser($userId);

        return ['success' => true, 'message' => 'Your password has been reset. Please sign in.', 'portal' => $this->portalForRoles($roles)];
    }

    /**
     * Returns true when the identifier has too many recent failed attempts.
     */
    private function isLockedOut(string $identifier): bool
    {
        $cutoff = date('Y-m-d H:i:s', time() - (self::LOCKOUT_WINDOW_MINUTES * 60));
        $row = $this->database->fetchOne(
            'SELECT COUNT(*) AS attempts FROM login_attempts WHERE username = :username AND was_successful = 0 AND attempted_at > :cutoff',
            ['username' => $identifier, 'cutoff' => $cutoff]
        );

        return (int) ($row['attempts'] ?? 0) >= self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Records a login attempt for lockout tracking and audit history.
     */
    private function recordLoginAttempt(string $identifier, ?int $userId, bool $success, ?string $reason): void
    {
        $this->database->execute(
            'INSERT INTO login_attempts (username, user_id, ip_address, user_agent, was_successful, failure_reason) VALUES (:username, :user_id, :ip, :agent, :success, :reason)',
            [
                'username' => $identifier,
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'success' => $success ? 1 : 0,
                'reason' => $reason,
            ]
        );
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
        Session::remove('_csrf_token');
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

        $this->recordUserSession((int) $user['id']);
    }

    /**
     * Records or refreshes this login's row in user_sessions for session
     * management/history. Keyed by a hash of the PHP session ID so a
     * concurrent request never collides on the primary key.
     */
    private function recordUserSession(int $userId): void
    {
        $sessionId = session_id();

        if (!$sessionId) {
            return;
        }

        $lifetime = (int) Application::instance()->config('session.lifetime', 120);
        $expiresAt = date('Y-m-d H:i:s', time() + ($lifetime * 60));

        $this->database->execute(
            'INSERT INTO user_sessions (user_id, session_token_hash, ip_address, user_agent, expires_at)
             VALUES (:user_id, :hash, :ip, :agent, :expires_at)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), ip_address = VALUES(ip_address), user_agent = VALUES(user_agent), expires_at = VALUES(expires_at), revoked_at = NULL',
            [
                'user_id' => $userId,
                'hash' => hash('sha256', $sessionId),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Marks this PHP session's user_sessions row as revoked on logout.
     */
    private function revokeCurrentSession(): void
    {
        $sessionId = session_id();

        if (!$sessionId) {
            return;
        }

        $this->database->execute(
            'UPDATE user_sessions SET revoked_at = NOW() WHERE session_token_hash = :hash AND revoked_at IS NULL',
            ['hash' => hash('sha256', $sessionId)]
        );
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
     * Returns the portal slug for a role set, for redirecting to the right
     * login page after a password reset.
     *
     * @param array<int, string> $roles
     */
    private function portalForRoles(array $roles): string
    {
        if (array_intersect($roles, ['super-admin', 'admin']) !== []) {
            return 'admin';
        }

        $role = $roles[0] ?? '';

        return in_array($role, ['teacher', 'student', 'accountant'], true) ? $role : 'admin';
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