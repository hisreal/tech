<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Session;

/**
 * Convenience wrapper for one-time flash notifications.
 */
final class Flash
{
    /** Stores a success message. */
    public static function success(string $message): void
    {
        Session::flash('success', $message);
    }

    /** Stores an error message. */
    public static function error(string $message): void
    {
        Session::flash('error', $message);
    }

    /** Stores a warning message. */
    public static function warning(string $message): void
    {
        Session::flash('warning', $message);
    }

    /** Stores an info message. */
    public static function info(string $message): void
    {
        Session::flash('info', $message);
    }
}
