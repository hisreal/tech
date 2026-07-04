<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Singleton PDO database layer with safe query helpers and transactions.
 */
final class Database
{
    private static ?self $instance = null;
    private ?PDO $pdo = null;
    private ?PDOStatement $statement = null;

    /** @param array<string, mixed> $config */
    private function __construct(private array $config)
    {
    }

    /**
     * Returns the singleton database instance.
     *
     * @param array<string, mixed>|null $config
     */
    public static function getInstance(?array $config = null): self
    {
        if (!self::$instance instanceof self) {
            if ($config === null) {
                $config = Application::instance()->config('database', []);
            }

            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Returns an active PDO connection.
     */
    public function connection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $this->config['host'] ?? '127.0.0.1',
                $this->config['name'] ?? 'school_management',
                $this->config['charset'] ?? 'utf8mb4'
            );

            $this->pdo = new PDO($dsn, (string) ($this->config['user'] ?? 'root'), (string) ($this->config['password'] ?? ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new \RuntimeException('Database connection failed.', 0, $exception);
        }

        return $this->pdo;
    }

    /**
     * Prepares and executes a parameterized query.
     *
     * @param array<string|int, mixed> $params
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $this->statement = $this->connection()->prepare($sql);
        $this->statement->execute($params);

        return $this->statement;
    }

    /**
     * Executes a statement and returns success status.
     *
     * @param array<string|int, mixed> $params
     */
    public function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params)->rowCount() >= 0;
    }

    /**
     * Fetches the first row for a query.
     *
     * @param array<string|int, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Fetches all rows for a query.
     *
     * @param array<string|int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Starts a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->connection()->beginTransaction();
    }

    /**
     * Commits a transaction.
     */
    public function commit(): bool
    {
        return $this->connection()->commit();
    }

    /**
     * Rolls back a transaction if active.
     */
    public function rollBack(): bool
    {
        return $this->connection()->inTransaction() && $this->connection()->rollBack();
    }

    /**
     * Runs a callback inside a database transaction.
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (\Throwable $throwable) {
            $this->rollBack();
            throw $throwable;
        }
    }

    /**
     * Returns the last inserted auto-increment ID.
     */
    public function lastInsertId(): string
    {
        return $this->connection()->lastInsertId();
    }

    /**
     * Returns the row count for the most recent statement.
     */
    public function rowCount(): int
    {
        return $this->statement?->rowCount() ?? 0;
    }
}
