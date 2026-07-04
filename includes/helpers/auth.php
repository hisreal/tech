<?php
/**
 * Legacy-page authentication bridge backed by the reusable MVC AuthService.
 */

use App\Core\Session;
use App\Helpers\Security;
use App\Services\AuthService;

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../app/bootstrap.php';

if (!function_exists('sms_auth')) {
    /** Returns the shared authentication service. */
    function sms_auth(): AuthService
    {
        static $auth = null;

        if (!$auth instanceof AuthService) {
            $auth = new AuthService();
        }

        return $auth;
    }
}

if (!function_exists('sms_current_user')) {
    /** Returns the authenticated user payload, if available. */
    function sms_current_user(): ?array
    {
        return sms_auth()->user();
    }
}

if (!function_exists('sms_is_authenticated')) {
    /** Returns true when the visitor is authenticated. */
    function sms_is_authenticated(): bool
    {
        return sms_auth()->check();
    }
}

if (!function_exists('sms_require_auth')) {
    /** Protects legacy module pages by role. */
    function sms_require_auth(string|array|null $role = null): void
    {
        $role = $role ?: 'admin';

        if ($role === 'admin') {
            $role = ['super-admin', 'admin'];
        }

        sms_auth()->requireRole($role);
    }
}

if (!function_exists('sms_csrf_token')) {
    /** Returns the current CSRF token. */
    function sms_csrf_token(): string
    {
        return Security::csrfToken();
    }
}

if (!function_exists('sms_verify_csrf')) {
    /** Verifies a CSRF token. */
    function sms_verify_csrf(?string $token): bool
    {
        return Security::verifyCsrf($token);
    }
}

if (!function_exists('sms_flash')) {
    /** Returns and consumes all flash messages. */
    function sms_flash(): array
    {
        return Session::flashes();
    }
}

if (!function_exists('sms_flash_set')) {
    /** Stores a flash message. */
    function sms_flash_set(string $type, string $message): void
    {
        Session::flash($type, $message);
    }
}