<?php
/**
 * Legacy-page authorization helpers backed by the real session permission
 * and role data that AuthService::loginUser() already computes and stores.
 */

use App\Core\Session;

require_once __DIR__ . '/../../app/bootstrap.php';

if (!function_exists('sms_can')) {
    /** Returns true when the current user holds the given permission slug. */
    function sms_can(string $permission): bool
    {
        return in_array($permission, (array) Session::get('permissions', []), true);
    }
}

if (!function_exists('sms_has_role')) {
    /** Returns true when the current user holds any of the given role(s). */
    function sms_has_role(string|array $role): bool
    {
        $roles = array_map('strtolower', (array) Session::get('roles', []));
        $required = array_map('strtolower', (array) $role);

        return array_intersect($required, $roles) !== [];
    }
}
