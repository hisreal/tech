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

    /**
     * Registers PHP error and exception handlers.
     *
     * @param array<string, mixed> $config
     */
    public static function register(array $config): void
    {
        self::$config = $config;

        set_exception_handler(static function (\Throwable $throwable): void {
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
