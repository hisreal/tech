<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Reusable active-record-like base model for common database operations.
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    /** @var array<int, string> */
    protected array $fillable = [];

    /**
     * Column => cast type ('int', 'float', 'bool', 'json') applied to
     * results returned from find/findAll/where/first.
     *
     * @var array<string, string>
     */
    protected array $casts = [];

    /**
     * When true, delete() sets $deletedAtColumn instead of removing the
     * row, and find/findAll/where/first/count/exists automatically exclude
     * soft-deleted rows. Opt-in per model; the underlying table must have
     * the column. Off by default so existing subclasses are unaffected.
     */
    protected bool $softDeletes = false;

    protected string $deletedAtColumn = 'deleted_at';

    public function __construct(?Database $database = null)
    {
        $this->db = $database ?? Database::getInstance();
    }

    /**
     * Finds one record by primary key.
     *
     * @return array<string, mixed>|null
     */
    public function find(int|string $id): ?array
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = :id%s LIMIT 1', $this->table, $this->primaryKey, $this->scopeClause('AND'));
        $row = $this->db->fetchOne($sql, ['id' => $id]);

        return $row === null ? null : $this->applyCasts($row);
    }

    /**
     * Finds one record by primary key or throws ModelNotFoundException.
     *
     * @return array<string, mixed>
     */
    public function findOrFail(int|string $id): array
    {
        $row = $this->find($id);

        if ($row === null) {
            throw new ModelNotFoundException(sprintf('No record found in `%s` for %s = %s.', $this->table, $this->primaryKey, (string) $id));
        }

        return $row;
    }

    /**
     * Returns all rows with an optional limit.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $sql = sprintf('SELECT * FROM `%s`%s', $this->table, $this->scopeClause('WHERE'));
        $params = [];

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params = ['limit' => $limit, 'offset' => $offset];
        }

        return $this->applyCastsToAll($this->db->fetchAll($sql, $params));
    }

    /**
     * Creates a new row and returns its ID.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): string
    {
        $data = $this->onlyFillable($data);
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $this->db->execute($sql, $data);

        return $this->db->lastInsertId();
    }

    /**
     * Updates a row by primary key.
     *
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data): bool
    {
        $data = $this->onlyFillable($data);
        $assignments = array_map(static fn (string $column): string => sprintf('`%s` = :%s', $column, $column), array_keys($data));
        $data['__id'] = $id;

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :__id',
            $this->table,
            implode(', ', $assignments),
            $this->primaryKey
        );

        return $this->db->execute($sql, $data);
    }

    /**
     * Deletes a row by primary key. Soft-deletes (sets $deletedAtColumn)
     * when $softDeletes is enabled; otherwise removes the row.
     */
    public function delete(int|string $id): bool
    {
        if ($this->softDeletes) {
            return $this->db->execute(
                sprintf('UPDATE `%s` SET `%s` = NOW() WHERE `%s` = :id', $this->table, $this->deletedAtColumn, $this->primaryKey),
                ['id' => $id]
            );
        }

        return $this->db->execute(
            sprintf('DELETE FROM `%s` WHERE `%s` = :id', $this->table, $this->primaryKey),
            ['id' => $id]
        );
    }

    /**
     * Permanently removes a row regardless of $softDeletes.
     */
    public function forceDelete(int|string $id): bool
    {
        return $this->db->execute(
            sprintf('DELETE FROM `%s` WHERE `%s` = :id', $this->table, $this->primaryKey),
            ['id' => $id]
        );
    }

    /**
     * Clears a soft-delete marker, restoring the row.
     */
    public function restore(int|string $id): bool
    {
        return $this->db->execute(
            sprintf('UPDATE `%s` SET `%s` = NULL WHERE `%s` = :id', $this->table, $this->deletedAtColumn, $this->primaryKey),
            ['id' => $id]
        );
    }

    /**
     * Returns rows where a column matches a value.
     *
     * @return array<int, array<string, mixed>>
     */
    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $this->guardOperator($operator);

        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` %s :value%s', $this->table, $column, $operator, $this->scopeClause('AND'));

        return $this->applyCastsToAll($this->db->fetchAll($sql, ['value' => $value]));
    }

    /**
     * Returns the first row where a column matches a value.
     *
     * @return array<string, mixed>|null
     */
    public function first(string $column, mixed $value, string $operator = '='): ?array
    {
        $this->guardOperator($operator);

        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` %s :value%s LIMIT 1', $this->table, $column, $operator, $this->scopeClause('AND'));
        $row = $this->db->fetchOne($sql, ['value' => $value]);

        return $row === null ? null : $this->applyCasts($row);
    }

    /**
     * Returns the first row matching every given column/value pair, or
     * creates one from $attributes merged with $values if none exists.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function firstOrCreate(array $attributes, array $values = []): array
    {
        $existing = $this->findWhereAll($attributes);

        if ($existing !== null) {
            return $existing;
        }

        $id = $this->create(array_merge($attributes, $values));

        return $this->findOrFail($id);
    }

    /**
     * Updates the first row matching every given column/value pair, or
     * creates one from $attributes merged with $values if none exists.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function updateOrCreate(array $attributes, array $values = []): array
    {
        $existing = $this->findWhereAll($attributes);

        if ($existing === null) {
            $id = $this->create(array_merge($attributes, $values));

            return $this->findOrFail($id);
        }

        $this->update($existing[$this->primaryKey], $values);

        return $this->findOrFail($existing[$this->primaryKey]);
    }

    /**
     * Returns the first row matching every given column/value pair.
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>|null
     */
    private function findWhereAll(array $attributes): ?array
    {
        if ($attributes === []) {
            return null;
        }

        $conditions = [];
        $params = [];

        foreach ($attributes as $column => $value) {
            $placeholder = 'w_' . $column;
            $conditions[] = sprintf('`%s` = :%s', $column, $placeholder);
            $params[$placeholder] = $value;
        }

        $sql = sprintf('SELECT * FROM `%s` WHERE %s%s LIMIT 1', $this->table, implode(' AND ', $conditions), $this->scopeClause('AND'));
        $row = $this->db->fetchOne($sql, $params);

        return $row === null ? null : $this->applyCasts($row);
    }

    /**
     * Returns paginated records and pagination metadata.
     *
     * @return array<string, mixed>
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $total = $this->count();
        $offset = ($page - 1) * $perPage;

        return [
            'data' => $this->findAll($perPage, $offset),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Counts all rows in the model table.
     */
    public function count(): int
    {
        $row = $this->db->fetchOne(sprintf('SELECT COUNT(*) AS aggregate FROM `%s`%s', $this->table, $this->scopeClause('WHERE')));

        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * Checks whether a row exists by column.
     */
    public function exists(string $column, mixed $value): bool
    {
        $row = $this->db->fetchOne(
            sprintf('SELECT 1 FROM `%s` WHERE `%s` = :value%s LIMIT 1', $this->table, $column, $this->scopeClause('AND')),
            ['value' => $value]
        );

        return $row !== null;
    }

    /**
     * Returns the soft-delete scope fragment (" AND `deleted_at` IS NULL"
     * style), or an empty string when soft deletes are disabled.
     *
     * @param 'WHERE'|'AND' $joiner
     */
    private function scopeClause(string $joiner): string
    {
        if (!$this->softDeletes) {
            return '';
        }

        return sprintf(' %s `%s` IS NULL', $joiner, $this->deletedAtColumn);
    }

    /**
     * Casts configured columns on a single row.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function applyCasts(array $row): array
    {
        foreach ($this->casts as $column => $type) {
            if (!array_key_exists($column, $row) || $row[$column] === null) {
                continue;
            }

            $row[$column] = match ($type) {
                'int', 'integer' => (int) $row[$column],
                'float', 'double' => (float) $row[$column],
                'bool', 'boolean' => (bool) $row[$column],
                'json', 'array' => json_decode((string) $row[$column], true),
                default => $row[$column],
            };
        }

        return $row;
    }

    /**
     * Casts configured columns across a result set.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function applyCastsToAll(array $rows): array
    {
        return $this->casts === [] ? $rows : array_map([$this, 'applyCasts'], $rows);
    }

    /**
     * Filters data down to fillable columns.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function onlyFillable(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Allows only safe SQL comparison operators.
     */
    private function guardOperator(string $operator): void
    {
        if (!in_array($operator, ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE'], true)) {
            throw new \InvalidArgumentException('Invalid SQL operator.');
        }
    }
}
