<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\ExceptionHandler;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Security;

/**
 * Verifies the CSRF token on state-changing router-dispatched requests.
 * Opt in per route/group via the middleware array; legacy pages continue
 * to call Security::verifyCsrf()/sms_verify_csrf() directly.
 */
final class CsrfMiddleware
{
    /** @var array<int, string> */
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request): ?Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return null;
        }

        $token = $request->input('_token');

        if (!Security::verifyCsrf(is_string($token) ? $token : null)) {
            return ExceptionHandler::renderStatus(419, 'Your session has expired. Please refresh and try again.');
        }

        return null;
    }
}
