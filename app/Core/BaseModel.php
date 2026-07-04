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
        return $this->db->fetchOne(
            sprintf('SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1', $this->table, $this->primaryKey),
            ['id' => $id]
        );
    }

    /**
     * Returns all rows with an optional limit.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $sql = sprintf('SELECT * FROM `%s`', $this->table);
        $params = [];

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params = ['limit' => $limit, 'offset' => $offset];
        }

        return $this->db->fetchAll($sql, $params);
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
     * Deletes a row by primary key.
     */
    public function delete(int|string $id): bool
    {
        return $this->db->execute(
            sprintf('DELETE FROM `%s` WHERE `%s` = :id', $this->table, $this->primaryKey),
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

        return $this->db->fetchAll(
            sprintf('SELECT * FROM `%s` WHERE `%s` %s :value', $this->table, $column, $operator),
            ['value' => $value]
        );
    }

    /**
     * Returns the first row where a column matches a value.
     *
     * @return array<string, mixed>|null
     */
    public function first(string $column, mixed $value, string $operator = '='): ?array
    {
        $this->guardOperator($operator);

        return $this->db->fetchOne(
            sprintf('SELECT * FROM `%s` WHERE `%s` %s :value LIMIT 1', $this->table, $column, $operator),
            ['value' => $value]
        );
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
        $row = $this->db->fetchOne(sprintf('SELECT COUNT(*) AS aggregate FROM `%s`', $this->table));

        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * Checks whether a row exists by column.
     */
    public function exists(string $column, mixed $value): bool
    {
        $row = $this->db->fetchOne(
            sprintf('SELECT 1 FROM `%s` WHERE `%s` = :value LIMIT 1', $this->table, $column),
            ['value' => $value]
        );

        return $row !== null;
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
