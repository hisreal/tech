<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Coordinates the request lifecycle for the MVC application.
 */
final class Application
{
    private static ?self $instance = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private string $rootPath,
        private array $config,
        private Router $router
    ) {
        self::$instance = $this;
    }

    /**
     * Returns the active application instance.
     */
    public static function instance(): self
    {
        if (!self::$instance instanceof self) {
            throw new \RuntimeException('Application has not been initialized.');
        }

        return self::$instance;
    }

    /**
     * Runs the router and sends the generated response.
     */
    public function run(): void
    {
        Session::start($this->config['session'] ?? []);
        $request = Request::capture();
        $response = $this->router->dispatch($request);
        $response->send();
    }

    /**
     * Returns the configured router instance.
     */
    public function router(): Router
    {
        return $this->router;
    }

    /**
     * Returns a configuration value using dot notation.
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Returns the application root path.
     */
    public function rootPath(string $path = ''): string
    {
        return rtrim($this->rootPath . '/' . ltrim($path, '/'), '/');
    }
}
