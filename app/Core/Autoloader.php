<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal PSR-4 autoloader used before Composer dependencies are installed.
 */
final class Autoloader
{
    /**
     * Registers the App namespace against the application root path.
     */
    public static function register(string $rootPath): void
    {
        spl_autoload_register(static function (string $class) use ($rootPath): void {
            $prefix = 'App\\';

            if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $rootPath . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}
