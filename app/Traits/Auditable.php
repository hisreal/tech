<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\Logger;

/**
 * Adds audit-ready activity logging to services, controllers, and models.
 */
trait Auditable
{
    /**
     * Writes a structured activity entry for future audit-log persistence.
     *
     * @param array<string, mixed> $context
     */
    protected function audit(string $module, string $action, array $context = []): void
    {
        Logger::activity($module, $action, $context);
    }
}
