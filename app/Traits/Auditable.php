<?php

declare(strict_types=1);

namespace App\Traits;

use App\Core\Database;

/**
 * Adds a shared audit_logs writer to services. This is the single audit
 * mechanism new services should use instead of hand-rolling their own
 * private audit() method (as SettingsModel/ProfileService/StudentImportService/
 * AcademicService currently each do independently).
 */
trait Auditable
{
    /**
     * Records a row in audit_logs.
     *
     * @param array<string, mixed>|null $actor Authenticated user payload (uses 'id'), or null for system actions.
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    protected function audit(
        ?array $actor,
        string $module,
        string $action,
        ?string $entityTable = null,
        int|string|null $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Database $database = null,
        string $status = 'success'
    ): void {
        $db = $database ?? Database::getInstance();

        $db->execute(
            'INSERT INTO audit_logs (actor_user_id, module, action, status, entity_table, entity_id, old_values, new_values, ip_address, user_agent) VALUES (:actor, :module, :action, :status, :entity_table, :entity_id, :old_values, :new_values, :ip, :agent)',
            [
                'actor' => isset($actor['id']) ? (int) $actor['id'] : null,
                'module' => $module,
                'action' => $action,
                'status' => $status,
                'entity_table' => $entityTable,
                'entity_id' => $entityId,
                'old_values' => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values' => $newValues !== null ? json_encode($newValues) : null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
    }
}
