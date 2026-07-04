<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Represents an HTTP response that can be sent to the browser.
 */
final class Response
{
    /** @var array<string, string> */
    private array $headers = [];

    public function __construct(private string $content = '', private int $status = 200)
    {
    }

    /**
     * Creates a JSON response.
     *
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): self
    {
        return (new self((string) json_encode($data, JSON_THROW_ON_ERROR), $status))
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Creates a redirect response.
     */
    public static function redirect(string $url, int $status = 302): self
    {
        return (new self('', $status))->header('Location', $url);
    }

    /**
     * Adds a response header.
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Sends response headers and content.
     */
    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
