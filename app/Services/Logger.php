<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;

/**
 * File logger prepared for future audit-log database integration.
 */
final class Logger
{
    /**
     * Writes a general log entry.
     *
     * @param array<string, mixed> $context
     */
    public static function write(string $channel, string $message, array $context = []): void
    {
        $root = Application::instance()->rootPath();
        $dir = $root . '/app/Logs';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = json_encode([
            'timestamp' => date('c'),
            'channel' => $channel,
            'message' => $message,
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        file_put_contents($dir . '/' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }

    /** Logs a login event. */
    public static function login(int|string $userId, string $ipAddress = ''): void
    {
        self::write('login', 'User logged in.', ['user_id' => $userId, 'ip_address' => $ipAddress]);
    }

    /** Logs a logout event. */
    public static function logout(int|string $userId): void
    {
        self::write('logout', 'User logged out.', ['user_id' => $userId]);
    }

    /** Logs a future CRUD/audit activity. */
    public static function activity(string $module, string $action, array $context = []): void
    {
        self::write('activity', $module . ':' . $action, $context);
    }

    /** Logs a security event. */
    public static function security(string $message, array $context = []): void
    {
        self::write('security', $message, $context);
    }

    /** Logs an exception. */
    public static function exception(\Throwable $throwable): void
    {
        self::write('exception', $throwable->getMessage(), [
            'class' => $throwable::class,
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ]);
    }
}
