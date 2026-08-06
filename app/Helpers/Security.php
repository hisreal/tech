<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Session;

/**
 * Security utility methods for CSRF, escaping, passwords, and random strings.
 */
final class Security
{
    /** Generates or returns the current CSRF token. */
    public static function csrfToken(): string
    {
        $token = Session::get('_csrf_token');

        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }

        return $token;
    }

    /** Verifies a submitted CSRF token. */
    public static function verifyCsrf(?string $token): bool
    {
        return is_string($token) && hash_equals((string) Session::get('_csrf_token', ''), $token);
    }

    /** Escapes output for safe HTML rendering. */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /** Creates a secure password hash. */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /** Verifies a password against a hash. */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** Returns random URL-safe text. */
    public static function randomString(int $length = 32): string
    {
        return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }

    /** Generates a random temporary password for newly created portal accounts. */
    public static function temporaryPassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@$%';
        $password = '';
        $max = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Returns true when a password meets the app-wide minimum strength
     * policy: at least 8 characters, one uppercase, one lowercase, one digit.
     */
    public static function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1;
    }

    /** Returns the standard message describing the password policy. */
    public static function passwordPolicyMessage(): string
    {
        return 'Use at least 8 characters with uppercase, lowercase, and a number.';
    }
}
