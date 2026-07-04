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
