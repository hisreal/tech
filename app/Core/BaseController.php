<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Validator;

/**
 * Base controller with shared rendering, redirects, JSON, validation, and flash helpers.
 */
abstract class BaseController
{
    /**
     * Renders a view inside an optional layout.
     *
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = [], ?string $layout = null): Response
    {
        $content = $this->renderFile(Application::instance()->rootPath('app/Views/' . str_replace('.', '/', $view) . '.php'), $data);

        if ($layout !== null) {
            $content = $this->renderFile(Application::instance()->rootPath('app/Views/' . str_replace('.', '/', $layout) . '.php'), array_merge($data, ['content' => $content]));
        }

        return new Response($content);
    }

    /**
     * Creates a redirect response.
     */
    protected function redirect(string $url): Response
    {
        return Response::redirect($url);
    }

    /**
     * Creates a JSON response.
     *
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    /**
     * Validates data with shared validator rules.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @return array<string, array<int, string>>
     */
    protected function validate(array $data, array $rules): array
    {
        return Validator::make($data, $rules)->errors();
    }

    /**
     * Instantiates a model by class name.
     */
    protected function loadModel(string $modelClass): object
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException(sprintf('Model %s was not found.', $modelClass));
        }

        return new $modelClass();
    }

    /**
     * Instantiates a service by class name.
     */
    protected function service(string $serviceClass): object
    {
        if (!class_exists($serviceClass)) {
            throw new \InvalidArgumentException(sprintf('Service %s was not found.', $serviceClass));
        }

        return new $serviceClass();
    }

    /**
     * Throws AuthorizationException unless the current user holds at least
     * one of the given permission slugs (rendered as a 403 by ExceptionHandler).
     *
     * @param string|array<int, string> $permission
     */
    protected function authorize(string|array $permission): void
    {
        $required = (array) $permission;
        $granted = (array) Session::get('permissions', []);

        if (array_intersect($required, $granted) === []) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }

    /**
     * Returns a single value from input flashed by the previous request.
     */
    protected function old(string $key, mixed $default = null): mixed
    {
        return Session::old($key, $default);
    }

    /**
     * Returns validation errors flashed by the previous request.
     *
     * @return array<string, mixed>
     */
    protected function errors(): array
    {
        return Session::errors();
    }

    /**
     * Redirects back to the referring page, flashing input and errors for
     * the next request to read via old()/errors().
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $errors
     */
    protected function backWithErrors(array $input, array $errors): Response
    {
        Session::flashInput($input);
        Session::flashErrors($errors);

        return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    /**
     * Stores a success flash message.
     */
    protected function successMessage(string $message): void
    {
        Session::flash('success', $message);
    }

    /**
     * Stores an error flash message.
     */
    protected function errorMessage(string $message): void
    {
        Session::flash('error', $message);
    }

    /**
     * Renders a PHP view file and returns its buffer.
     *
     * @param array<string, mixed> $data
     */
    private function renderFile(string $path, array $data): string
    {
        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('View file not found: %s', $path));
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}
