<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SettingsModel
{
    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /** @return array<string,array{value:mixed,type:string,group:string}> */
    public function all(): array
    {
        $rows = $this->db->fetchAll('SELECT setting_key, setting_value, value_type, setting_group FROM school_settings ORDER BY setting_group, setting_key');
        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['setting_key']] = [
                'value' => $this->cast((string) ($row['setting_value'] ?? ''), (string) $row['value_type']),
                'type' => (string) $row['value_type'],
                'group' => (string) $row['setting_group'],
            ];
        }
        return $settings;
    }

    /** @param array<string,array{value:mixed,type:string,group:string,public?:bool}> $settings */
    public function upsertMany(array $settings, ?int $userId = null): void
    {
        foreach ($settings as $key => $setting) {
            $this->db->execute(
                'INSERT INTO school_settings (setting_key, setting_value, value_type, setting_group, is_public, updated_by) VALUES (:setting_key, :setting_value, :value_type, :setting_group, :is_public, :updated_by) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), value_type = VALUES(value_type), setting_group = VALUES(setting_group), is_public = VALUES(is_public), updated_by = VALUES(updated_by)',
                [
                    'setting_key' => $key,
                    'setting_value' => $this->serialize($setting['value'], $setting['type']),
                    'value_type' => $setting['type'],
                    'setting_group' => $setting['group'],
                    'is_public' => !empty($setting['public']) ? 1 : 0,
                    'updated_by' => $userId,
                ]
            );
        }
    }

    /** @return array<int,array<string,mixed>> */
    public function academicSessions(): array
    {
        return $this->db->fetchAll('SELECT id, name, status FROM academic_sessions ORDER BY status = "active" DESC, start_date DESC, id DESC');
    }

    /** @return array<int,array<string,mixed>> */
    public function terms(?int $sessionId = null): array
    {
        if ($sessionId) {
            return $this->db->fetchAll('SELECT id, session_id, name, status FROM terms WHERE session_id = :session_id ORDER BY id', ['session_id' => $sessionId]);
        }
        return $this->db->fetchAll('SELECT id, session_id, name, status FROM terms ORDER BY session_id, id');
    }

    /** @param array<string,mixed>|null $old @param array<string,mixed> $new */
    public function audit(?array $actor, string $category, array $old, array $new): void
    {
        $this->db->execute(
            'INSERT INTO audit_logs (actor_user_id, module, action, entity_table, old_values, new_values, ip_address, user_agent) VALUES (:actor, :module, :action, :entity_table, :old_values, :new_values, :ip, :agent)',
            [
                'actor' => isset($actor['id']) ? (int) $actor['id'] : null,
                'module' => 'settings',
                'action' => 'settings.' . $category . '.updated',
                'entity_table' => 'school_settings',
                'old_values' => json_encode($old),
                'new_values' => json_encode($new),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
    }

    private function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'number' => is_numeric($value) ? (float) $value : 0,
            'boolean' => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    private function serialize(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
