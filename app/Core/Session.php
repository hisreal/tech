<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure session manager with flash message support.
 */
final class Session
{
    /**
     * Starts the PHP session with configured cookie options.
     *
     * @param array<string, mixed> $config
     */
    public static function start(array $config = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => ((int) ($config['lifetime'] ?? 120)) * 60,
            'path' => '/',
            'secure' => (bool) ($config['secure'] ?? false),
            'httponly' => (bool) ($config['http_only'] ?? true),
            'samesite' => (string) ($config['same_site'] ?? 'Lax'),
        ]);

        session_start();
        self::ageFlashMessages();
    }

    /**
     * Stores a session value.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Returns a session value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Removes a session value.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destroys the current session.
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
    }

    /**
     * Regenerates the session ID to prevent fixation.
     */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Stores a flash message for the next request.
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = ['message' => $message, 'fresh' => true];
    }

    /**
     * Returns flash messages and marks them for removal.
     *
     * @return array<string, array<int, string>>
     */
    public static function flashes(): array
    {
        $messages = [];

        foreach ($_SESSION['_flash'] ?? [] as $type => $items) {
            foreach ($items as $item) {
                $messages[$type][] = $item['message'];
            }
        }

        unset($_SESSION['_flash']);

        return $messages;
    }

    /**
     * Removes stale flash messages after they have survived one request.
     */
    private static function ageFlashMessages(): void
    {
        foreach ($_SESSION['_flash'] ?? [] as $type => $items) {
            foreach ($items as $index => $item) {
                if (($item['fresh'] ?? false) === false) {
                    unset($_SESSION['_flash'][$type][$index]);
                } else {
                    $_SESSION['_flash'][$type][$index]['fresh'] = false;
                }
            }
        }
    }
}
