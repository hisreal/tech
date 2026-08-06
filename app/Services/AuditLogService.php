<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Paginator;

/**
 * Read-only query layer over the shared audit_logs table (written to by
 * every service via App\Traits\Auditable, plus AuthService for login/logout/
 * password-reset events). Powers the admin Audit Logs page: filtered,
 * paginated listing with resolved actor name/role and a human-readable
 * description generated automatically from module/action/entity/values.
 */
final class AuditLogService
{
    private const DEFAULT_PER_PAGE = 20;

    /** @var array<string, string> */
    private const ROLE_LABELS = [
        'super-admin' => 'Super Admin',
        'admin' => 'Admin',
        'teacher' => 'Teacher',
        'accountant' => 'Accountant',
        'student' => 'Student',
        'guest' => 'Guest',
    ];

    public function __construct(private ?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * @param array<string,mixed> $filters
     * @return array{data:array<int,array<string,mixed>>,meta:array<string,int>}
     */
    public function list(array $filters = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);

        $sql = "SELECT al.*, u.username,
                    COALESCE(NULLIF(CONCAT(st.first_name, ' ', st.last_name), ' '), NULLIF(CONCAT(s.first_name, ' ', s.last_name), ' '), u.username, 'System') AS actor_name,
                    (SELECT r.slug FROM user_roles ur INNER JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = al.actor_user_id ORDER BY FIELD(r.slug, 'super-admin','admin','teacher','accountant','student') LIMIT 1) AS actor_role
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.actor_user_id
                LEFT JOIN staff st ON st.user_id = u.id
                LEFT JOIN students s ON s.user_id = u.id
                {$whereSql}
                ORDER BY al.created_at DESC, al.id DESC";

        $result = Paginator::paginateQuery($this->db, $sql, $params, $page, $perPage);
        $result['data'] = array_map([$this, 'decorate'], $result['data']);

        return $result;
    }

    /** @return array<string,mixed> */
    public function stats(): array
    {
        $total = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM audit_logs')['c'] ?? 0);
        $today = (int) ($this->db->fetchOne('SELECT COUNT(*) c FROM audit_logs WHERE DATE(created_at) = CURDATE()')['c'] ?? 0);
        $failedLogins = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) c FROM audit_logs WHERE module = 'auth' AND action LIKE 'auth.login%' AND status = 'failed'"
        )['c'] ?? 0);
        $activeUsers = (int) ($this->db->fetchOne(
            'SELECT COUNT(DISTINCT actor_user_id) c FROM audit_logs WHERE actor_user_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
        )['c'] ?? 0);

        return ['total' => $total, 'today' => $today, 'failed_logins' => $failedLogins, 'active_users' => $activeUsers];
    }

    /** @return array<int,string> */
    public function modulesForSelect(): array
    {
        return array_column($this->db->fetchAll('SELECT DISTINCT module FROM audit_logs ORDER BY module ASC'), 'module');
    }

    /** @return array<int,string> */
    public function actionsForSelect(?string $module = null): array
    {
        if ($module) {
            return array_column(
                $this->db->fetchAll('SELECT DISTINCT action FROM audit_logs WHERE module = :module ORDER BY action ASC', ['module' => $module]),
                'action'
            );
        }

        return array_column($this->db->fetchAll('SELECT DISTINCT action FROM audit_logs ORDER BY action ASC'), 'action');
    }

    /** @return array<string,string> */
    public function rolesForSelect(): array
    {
        return self::ROLE_LABELS;
    }

    private function intFilter(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    /**
     * @param array<string,mixed> $filters
     * @return array{0:string,1:array<string,mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $where = ['1=1'];
        $params = [];

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $where[] = 'al.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $where[] = 'al.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $actorUserId = $this->intFilter($filters['actor_user_id'] ?? 0);
        if ($actorUserId !== null) {
            $where[] = 'al.actor_user_id = :actor_user_id';
            $params['actor_user_id'] = $actorUserId;
        }

        $module = trim((string) ($filters['module'] ?? ''));
        if ($module !== '') {
            $where[] = 'al.module = :module';
            $params['module'] = $module;
        }

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $where[] = 'al.action = :action';
            $params['action'] = $action;
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if (in_array($status, ['success', 'failed', 'warning'], true)) {
            $where[] = 'al.status = :status';
            $params['status'] = $status;
        }

        $role = trim((string) ($filters['role'] ?? ''));
        if ($role === 'guest') {
            $where[] = 'al.actor_user_id IS NULL';
        } elseif ($role === 'admin') {
            $where[] = "EXISTS (SELECT 1 FROM user_roles ur2 INNER JOIN roles r2 ON r2.id = ur2.role_id WHERE ur2.user_id = al.actor_user_id AND r2.slug IN ('super-admin','admin'))";
        } elseif (in_array($role, ['teacher', 'accountant', 'student'], true)) {
            $where[] = 'EXISTS (SELECT 1 FROM user_roles ur2 INNER JOIN roles r2 ON r2.id = ur2.role_id WHERE ur2.user_id = al.actor_user_id AND r2.slug = :role)';
            $params['role'] = $role;
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = "(u.username LIKE :search1 OR st.first_name LIKE :search2 OR st.last_name LIKE :search3 OR s.first_name LIKE :search4 OR s.last_name LIKE :search5 OR al.module LIKE :search6 OR al.action LIKE :search7 OR al.entity_table LIKE :search8)";
            $like = '%' . $search . '%';
            foreach (range(1, 8) as $i) {
                $params['search' . $i] = $like;
            }
        }

        return [' WHERE ' . implode(' AND ', $where), $params];
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function decorate(array $row): array
    {
        $role = (string) ($row['actor_role'] ?? '');
        if ($row['actor_user_id'] === null) {
            $role = 'guest';
            $row['actor_name'] = 'Guest';
        }

        $ua = (string) ($row['user_agent'] ?? '');
        $agent = $this->parseUserAgent($ua);

        return array_merge($row, [
            'actor_role' => $role,
            'actor_role_label' => self::ROLE_LABELS[$role] ?? ucfirst($role ?: 'guest'),
            'description' => $this->describe($row),
            'browser' => $agent['browser'],
            'os' => $agent['os'],
            'device' => $agent['device'],
        ]);
    }

    /** @param array<string,mixed> $row */
    private function describe(array $row): string
    {
        $action = (string) $row['action'];
        $label = ucwords(str_replace(['.', '_'], ' ', $action));

        $entity = '';
        if (!empty($row['entity_table'])) {
            $tableLabel = ucwords(str_replace('_', ' ', (string) $row['entity_table']));
            $entity = ' - ' . $tableLabel . (!empty($row['entity_id']) ? ' #' . $row['entity_id'] : '');
        }

        $extra = '';
        $newValues = $row['new_values'] ? json_decode((string) $row['new_values'], true) : null;
        if (is_array($newValues) && $newValues !== []) {
            $parts = [];
            foreach ($newValues as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $parts[] = ucwords(str_replace('_', ' ', (string) $key)) . ': ' . $value;
                if (count($parts) >= 3) {
                    break;
                }
            }
            if ($parts !== []) {
                $extra = ' (' . implode(', ', $parts) . ')';
            }
        }

        return $label . $entity . $extra;
    }

    /** @return array{browser:string,os:string,device:string} */
    private function parseUserAgent(string $ua): array
    {
        if ($ua === '') {
            return ['browser' => 'Unknown', 'os' => 'Unknown', 'device' => 'Unknown'];
        }

        $browser = 'Other';
        foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'] as $needle => $name) {
            if (str_contains($ua, $needle)) {
                $browser = $name;
                break;
            }
        }

        $os = 'Other';
        foreach (['Windows' => 'Windows', 'Mac OS' => 'macOS', 'Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iOS', 'Linux' => 'Linux'] as $needle => $name) {
            if (str_contains($ua, $needle)) {
                $os = $name;
                break;
            }
        }

        $device = (str_contains($ua, 'Mobile') || $os === 'Android' || $os === 'iOS') ? 'Mobile' : 'Desktop';

        return ['browser' => $browser, 'os' => $os, 'device' => $device];
    }
}
