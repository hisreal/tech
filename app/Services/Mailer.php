<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;

/**
 * Minimal outbound mail service. Ships with a "log" driver that writes
 * messages to app/Logs/mail-*.log (already denied from web access) instead
 * of requiring an SMTP library or a configured mail server, so password
 * resets and other notifications work out of the box in any environment.
 *
 * Swap deliver() for a real SMTP/API client later; callers only depend on
 * send(), so nothing else needs to change when that happens.
 */
final class Mailer
{
    /**
     * Sends an email. Returns true when the message was handed off
     * successfully (or logged, on the log driver).
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        if (trim($to) === '') {
            return false;
        }

        return self::deliver($to, $subject, $body);
    }

    private static function deliver(string $to, string $subject, string $body): bool
    {
        $root = Application::instance()->rootPath();
        $dir = $root . '/app/Logs';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = json_encode([
            'timestamp' => date('c'),
            'from' => Application::instance()->config('mail.from_address', 'noreply@school.test'),
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        return file_put_contents($dir . '/mail-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX) !== false;
    }
}
