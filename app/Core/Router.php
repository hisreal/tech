<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight router supporting verbs, route parameters, groups, names, and middleware.
 */
final class Router
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $namedRoutes = [];

    /** @var array<string, mixed> */
    private array $group = ['prefix' => '', 'middleware' => []];

    /**
     * Registers a GET route.
     */
    public function get(string $uri, mixed $handler, array $options = []): self
    {
        return $this->add('GET', $uri, $handler, $options);
    }

    /**
     * Registers a POST route.
     */
    public function post(string $uri, mixed $handler, array $options = []): self
    {
        return $this->add('POST', $uri, $handler, $options);
    }

    /**
     * Registers a PUT route.
     */
    public function put(string $uri, mixed $handler, array $options = []): self
    {
        return $this->add('PUT', $uri, $handler, $options);
    }

    /**
     * Registers a PATCH route.
     */
    public function patch(string $uri, mixed $handler, array $options = []): self
    {
        return $this->add('PATCH', $uri, $handler, $options);
    }

    /**
     * Registers a DELETE route.
     */
    public function delete(string $uri, mixed $handler, array $options = []): self
    {
        return $this->add('DELETE', $uri, $handler, $options);
    }

    /**
     * Registers routes inside a shared prefix and middleware group.
     *
     * @param array{prefix?: string, middleware?: array<int, mixed>} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $previous = $this->group;
        $this->group = [
            'prefix' => rtrim($previous['prefix'] . '/' . trim((string) ($attributes['prefix'] ?? ''), '/'), '/'),
            'middleware' => array_merge($previous['middleware'], $attributes['middleware'] ?? []),
        ];

        $callback($this);
        $this->group = $previous;
    }

    /**
     * Builds a URL for a named route.
     *
     * @param array<string, string|int> $params
     */
    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException(sprintf('Named route %s is not registered.', $name));
        }

        $uri = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
        }

        return $uri;
    }

    /**
     * Dispatches a request to the matching route.
     */
    public function dispatch(Request $request): Response
    {
        foreach ($this->routes[$request->method()] ?? [] as $route) {
            $params = $this->match($route['uri'], $request->path());

            if ($params === null) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                $result = $this->runMiddleware($middleware, $request);

                if ($result instanceof Response) {
                    return $result;
                }
            }

            return $this->call($route['handler'], $params, $request);
        }

        return ExceptionHandler::renderStatus(404);
    }

    /**
     * Adds a route definition.
     */
    private function add(string $method, string $uri, mixed $handler, array $options): self
    {
        $uri = '/' . trim(rtrim($this->group['prefix'] . '/' . trim($uri, '/'), '/'), '/');
        $uri = $uri === '//' ? '/' : $uri;
        $middleware = array_merge($this->group['middleware'], $options['middleware'] ?? []);

        $this->routes[$method][] = [
            'uri' => $uri,
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        if (isset($options['name'])) {
            $this->namedRoutes[(string) $options['name']] = $uri;
        }

        return $this;
    }

    /**
     * Matches a route URI against a request path.
     *
     * @return array<string, string>|null
     */
    private function match(string $uri, string $path): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $uri) ?: $uri;
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $path, $matches)) {
            return null;
        }

        return array_filter($matches, static fn (string|int $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Runs route middleware.
     */
    private function runMiddleware(mixed $middleware, Request $request): ?Response
    {
        if (is_string($middleware)) {
            $middleware = new $middleware();
        }

        if (!is_object($middleware) || !method_exists($middleware, 'handle')) {
            throw new \InvalidArgumentException('Invalid route middleware.');
        }

        return $middleware->handle($request);
    }

    /**
     * Calls a route handler.
     *
     * @param array<string, string> $params
     */
    private function call(mixed $handler, array $params, Request $request): Response
    {
        if (is_callable($handler)) {
            $result = $handler($request, ...array_values($params));
        } elseif (is_array($handler) && isset($handler[0], $handler[1])) {
            $controller = new $handler[0]();
            $result = $controller->{$handler[1]}($request, ...array_values($params));
        } else {
            throw new \InvalidArgumentException('Invalid route handler.');
        }

        if ($result instanceof Response) {
            return $result;
        }

        return new Response((string) $result);
    }
}
