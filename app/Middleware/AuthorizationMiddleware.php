<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\ExceptionHandler;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Restricts routes by role and/or permission slugs.
 */
final class AuthorizationMiddleware
{
    /**
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function __construct(private array $roles = [], private array $permissions = [])
    {
    }

    /**
     * Blocks requests that lack the required role or permission.
     */
    public function handle(Request $request): ?Response
    {
        $userRoles = array_map('strtolower', (array) Session::get('roles', []));
        $userPermissions = (array) Session::get('permissions', []);

        if ($this->roles !== []) {
            $allowedRoles = array_map('strtolower', $this->roles);

            if (array_intersect($allowedRoles, $userRoles) === []) {
                return ExceptionHandler::renderStatus(403);
            }
        }

        foreach ($this->permissions as $permission) {
            if (!in_array($permission, $userPermissions, true)) {
                return ExceptionHandler::renderStatus(403);
            }
        }

        return null;
    }
}
