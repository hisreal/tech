<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Thrown by BaseController::authorize() when the current user lacks the
 * required role or permission. Rendered as a 403 by ExceptionHandler.
 */
final class AuthorizationException extends \RuntimeException
{
}
