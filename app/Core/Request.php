<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish wrapper around PHP superglobals for incoming HTTP requests.
 */
final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    private function __construct(
        private string $method,
        private string $path,
        private array $query,
        private array $body,
        private array $files,
        private array $server
    ) {
    }

    /**
     * Captures the current PHP request.
     */
    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $override = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;

        if (is_string($override) && in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'], true)) {
            $method = strtoupper($override);
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $path = '/' . trim($uri, '/');

        if ($scriptDir !== '/' && $scriptDir !== '.' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir)) ?: '/';
        }

        return new self($method, '/' . trim($path, '/'), $_GET, $_POST, $_FILES, $_SERVER);
    }

    /**
     * Returns the HTTP method.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Returns the normalized path.
     */
    public function path(): string
    {
        return $this->path === '//' ? '/' : $this->path;
    }

    /**
     * Returns all request input merged from query and body.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Returns a single input value.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Returns uploaded file data by field name.
     */
    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Returns a server value.
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}
