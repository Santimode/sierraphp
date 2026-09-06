<?php
declare(strict_types=1);
namespace Sierra\Database;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    protected PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = $this->createPdoInstance($config);
    }

    protected function createPdoInstance(array $config): PDO
    {
        $driver = $config['driver'] ?? 'mysql';
        
        if ($driver === 'sqlite') {
            $dsn = "sqlite:{$config['database']}";
        } else {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $database = $config['database'] ?? '';
            $charset = $config['charset'] ?? 'utf8mb4';
            $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
        }

        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function select(string $query, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public function insert(string $query, array $bindings = []): bool
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($bindings);
    }

    public function update(string $query, array $bindings = []): int
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->update($query, $bindings);
    }

    public function statement(string $query, array $bindings = []): bool
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($bindings);
    }

    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }
}
