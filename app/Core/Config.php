<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Loads environment variables and exposes normalized application configuration.
 */
final class Config
{
    /**
     * Loads .env values and returns a nested configuration array.
     *
     * @return array<string, mixed>
     */
    public static function load(string $rootPath): array
    {
        self::loadEnv($rootPath . '/.env');

        $timezone = self::env('APP_TIMEZONE', 'Africa/Lagos');
        date_default_timezone_set((string) $timezone);

        return [
            'root_path' => $rootPath,
            'app' => [
                'name' => self::env('APP_NAME', 'School Management System'),
                'url' => rtrim((string) self::env('APP_URL', 'http://localhost/tech'), '/'),
                'debug' => self::bool('APP_DEBUG', false),
                'timezone' => $timezone,
            ],
            'database' => self::loadDatabaseConfig($rootPath),
            'mail' => [
                'host' => self::env('MAIL_HOST', 'localhost'),
                'port' => (int) self::env('MAIL_PORT', 1025),
                'username' => self::env('MAIL_USERNAME', ''),
                'password' => self::env('MAIL_PASSWORD', ''),
                'from_address' => self::env('MAIL_FROM_ADDRESS', 'noreply@school.test'),
                'from_name' => self::env('MAIL_FROM_NAME', 'School Management System'),
            ],
            'session' => [
                'lifetime' => (int) self::env('SESSION_LIFETIME', 120),
                'secure' => self::bool('SESSION_SECURE', false),
                'http_only' => self::bool('SESSION_HTTP_ONLY', true),
                'same_site' => self::env('SESSION_SAME_SITE', 'Lax'),
            ],
            'uploads' => [
                'dir' => self::env('UPLOAD_DIR', 'app/Storage/uploads'),
                'max_size' => (int) self::env('MAX_UPLOAD_SIZE', 5242880),
            ],
        ];
    }

    /**
     * Loads the single database configuration file used by all DB consumers.
     *
     * To deploy on another server, edit the DB_* values in .env. The
     * app/Config/database.php file reads those values and supplies defaults
     * for local development.
     *
     * @return array<string, mixed>
     */
    private static function loadDatabaseConfig(string $rootPath): array
    {
        $configPath = $rootPath . '/app/Config/database.php';

        if (!is_file($configPath)) {
            throw new \RuntimeException('Database configuration file is missing: app/Config/database.php');
        }

        $config = require $configPath;

        if (!is_array($config)) {
            throw new \RuntimeException('Database configuration file must return an array.');
        }

        return $config;
    }

    /**
     * Reads key-value pairs from an environment file.
     */
    private static function loadEnv(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, " \t\n\r\0\x0B\"'");

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    /**
     * Returns an environment value with a fallback.
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        return $value === false || $value === null ? $default : $value;
    }

    /**
     * Casts an environment value to boolean.
     */
    private static function bool(string $key, bool $default): bool
    {
        $value = self::env($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
