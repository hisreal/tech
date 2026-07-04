<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SettingsService;

final class SettingsController
{
    public function __construct(private ?SettingsService $service = null)
    {
        $this->service = $service ?? new SettingsService();
    }

    /** @return array{settings:array<string,mixed>,sessions:array<int,array<string,mixed>>,terms:array<int,array<string,mixed>>} */
    public function index(): array
    {
        return $this->service->pageData();
    }

    /** @param array<string,mixed> $post @param array<string,mixed>|null $file @param array<string,mixed>|null $actor */
    public function update(string $section, array $post, ?array $file, ?array $actor): array
    {
        return $this->service->save($section, $post, $file, $actor);
    }

    public function logoUrl(?string $path): string
    {
        return $this->service->logoUrl($path);
    }
}
