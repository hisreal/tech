<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\Logger;

/**
 * Centralized exception and error renderer for browser-friendly failures.
 */
final class ExceptionHandler
{
    /** @var array<string, mixed> */
    private static array $config = [];

    /** @var array<int, int> PHP error levels severe enough to treat as a thrown exception. */
    private const FATAL_ERROR_LEVELS = [E_USER_ERROR, E_RECOVERABLE_ERROR];

    /** @var array<int, int> PHP error levels only catchable via a shutdown function. */
    private const SHUTDOWN_FATAL_LEVELS = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    /**
     * Registers PHP error, exception, and fatal-shutdown handlers, mirroring
     * Laravel's Handler triad. Non-fatal levels (warnings/notices/deprecated)
     * are logged, not thrown, so existing legacy pages that already tolerate
     * them keep rendering exactly as before.
     *
     * @param array<string, mixed> $config
     */
    public static function register(array $config): void
    {
        self::$config = $config;

        set_error_handler(static function (int $level, string $message, string $file = '', int $line = 0): bool {
            if (!(error_reporting() & $level)) {
                return false;
            }

            if (in_array($level, self::FATAL_ERROR_LEVELS, true)) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }

            Logger::warning($message, ['file' => $file, 'line' => $line, 'level' => $level]);

            return true;
        });

        set_exception_handler(static function (\Throwable $throwable): void {
            Logger::exception($throwable);
            self::renderThrowable($throwable)->send();
        });

        register_shutdown_function(static function (): void {
            $error = error_get_last();

            if ($error === null || !in_array($error['type'], self::SHUTDOWN_FATAL_LEVELS, true)) {
                return;
            }

            $throwable = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            Logger::exception($throwable);
            self::renderThrowable($throwable)->send();
        });
    }

    /**
     * Renders a status page response.
     */
    public static function renderStatus(int $status, string $message = ''): Response
    {
        $view = Application::instance()->rootPath('app/Views/errors/' . $status . '.php');
        $message = $message !== '' ? $message : self::defaultMessage($status);

        if (is_file($view)) {
            ob_start();
            require $view;
            $content = (string) ob_get_clean();
        } else {
            $content = sprintf('<h1>%d</h1><p>%s</p>', $status, htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        }

        return new Response($content, $status);
    }

    /**
     * Converts exceptions into safe responses.
     */
    private static function renderThrowable(\Throwable $throwable): Response
    {
        if ($throwable instanceof AuthorizationException) {
            return self::renderStatus(403, $throwable->getMessage());
        }

        if ($throwable instanceof ModelNotFoundException) {
            return self::renderStatus(404);
        }

        $debug = (bool) (self::$config['app']['debug'] ?? false);

        if ($debug) {
            $content = '<h1>Application Error</h1><pre>' . htmlspecialchars((string) $throwable, ENT_QUOTES, 'UTF-8') . '</pre>';

            return new Response($content, 500);
        }

        return self::renderStatus(500);
    }

    /**
     * Returns default messages for known HTTP statuses.
     */
    private static function defaultMessage(int $status): string
    {
        return match ($status) {
            403 => 'You do not have permission to access this page.',
            404 => 'The page you requested could not be found.',
            default => 'Something went wrong. Please try again later.',
        };
    }
}
