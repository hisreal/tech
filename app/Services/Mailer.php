<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Outbound mail service. Sends real mail over SMTP once MAIL_HOST/
 * MAIL_USERNAME are configured in .env; until then (or if sending fails)
 * it falls back to writing to app/Logs/mail-*.log (already denied from web
 * access), so password resets and other notifications never silently
 * disappear in an unconfigured environment.
 *
 * Callers only depend on send(), so nothing else needs to change when mail
 * settings are filled in.
 */
final class Mailer
{
    /**
     * Set whenever send() returns false, so callers that need to explain
     * *why* delivery failed (e.g. showing the real reason in a UI instead
     * of a generic message) can read it immediately after calling send().
     */
    public static ?string $lastError = null;

    /**
     * Sends an email. Returns true when the message was actually delivered
     * over SMTP (or logged, on the log driver).
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        self::$lastError = null;

        if (trim($to) === '') {
            self::$lastError = 'No recipient email address was provided.';

            return false;
        }

        $config = (array) Application::instance()->config('mail', []);
        $configured = !empty($config['host']) && $config['host'] !== 'localhost' && !empty($config['username']);

        if ($configured && self::deliverSmtp($to, $subject, $body, $config)) {
            return true;
        }

        if ($configured && self::$lastError === null) {
            self::$lastError = 'SMTP delivery failed for an unknown reason.';
        }

        $logged = self::deliverLog($to, $subject, $body, $config);

        if (!$logged && self::$lastError === null) {
            self::$lastError = 'Could not write to the mail fallback log (check app/Logs permissions).';
        }

        return $logged;
    }

    /** @param array<string,mixed> $config */
    private static function deliverSmtp(string $to, string $subject, string $body, array $config): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = (string) $config['host'];
            $mail->Port = (int) ($config['port'] ?? 587);
            $mail->SMTPAuth = true;
            $mail->Username = (string) $config['username'];
            $mail->Password = (string) $config['password'];
            $mail->SMTPSecure = (string) ($config['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $mail->setFrom((string) ($config['from_address'] ?? 'noreply@school.test'), (string) ($config['from_name'] ?? 'School Management System'));
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(false);
            $mail->Body = $body;

            $mail->send();

            return true;
        } catch (\Throwable $exception) {
            // Catches PHPMailer's own Exception as well as things like a
            // missing class (e.g. vendor/ not deployed) so a broken mail
            // config can never take down the whole request uncaught.
            Logger::exception($exception);
            self::$lastError = $exception->getMessage();

            return false;
        }
    }

    /** @param array<string,mixed> $config */
    private static function deliverLog(string $to, string $subject, string $body, array $config): bool
    {
        $root = Application::instance()->rootPath();
        $dir = $root . '/app/Logs';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = json_encode([
            'timestamp' => date('c'),
            'from' => $config['from_address'] ?? 'noreply@school.test',
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        return file_put_contents($dir . '/mail-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX) !== false;
    }
}
