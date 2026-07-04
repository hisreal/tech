<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Response;
use App\Helpers\Security;

if (!function_exists('app_config')) {
    /** Returns a configuration value by dot notation. */
    function app_config(string $key, mixed $default = null): mixed
    {
        return Application::instance()->config($key, $default);
    }
}

if (!function_exists('e')) {
    /** Escapes text for HTML output. */
    function e(mixed $value): string
    {
        return Security::e($value);
    }
}

if (!function_exists('url')) {
    /** Generates an absolute application URL. */
    function url(string $path = ''): string
    {
        return rtrim((string) app_config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** Generates an absolute asset URL. */
    function asset(string $path): string
    {
        return url($path);
    }
}

if (!function_exists('redirect')) {
    /** Creates a redirect response. */
    function redirect(string $path): Response
    {
        return Response::redirect(url($path));
    }
}

if (!function_exists('format_date')) {
    /** Formats a date for display. */
    function format_date(string|DateTimeInterface|null $date, string $format = 'M d, Y'): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $date = $date instanceof DateTimeInterface ? $date : new DateTimeImmutable($date);

        return $date->format($format);
    }
}

if (!function_exists('format_currency')) {
    /** Formats a Nigerian Naira amount. */
    function format_currency(float|int|string $amount, string $symbol = 'NGN '): string
    {
        return $symbol . number_format((float) $amount, 2);
    }
}

if (!function_exists('random_string')) {
    /** Generates random text. */
    function random_string(int $length = 32): string
    {
        return Security::randomString($length);
    }
}
