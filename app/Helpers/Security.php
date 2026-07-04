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
}
