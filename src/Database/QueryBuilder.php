<?php
declare(strict_types=1);
namespace Sierra\Database;

class QueryBuilder
{
    protected Connection $connection;
    protected string $table;
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $columns = ['*'];
    protected ?int $limit = null;
    protected ?string $orderBy = null;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function select(array|string $columns): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value');
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
        $this->orderBy = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function get(): array
    {
        return $this->connection->select($this->toSql(), $this->bindings);
    }

    public function first(): mixed
    {
        $this->limit(1);
        $results = $this->get();
        return count($results) > 0 ? $results[0] : null;
    }

    public function insert(array $values): bool
    {
        if (empty($values)) return false;

        $columns = array_keys($values);
        $placeholders = array_fill(0, count($values), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        return $this->connection->insert($sql, array_values($values));
    }
    
    public function insertGetId(array $values): string|false
    {
        if ($this->insert($values)) {
            return $this->connection->lastInsertId();
        }
        return false;
    }

    public function update(array $values): int
    {
        if (empty($values)) return 0;

        $columns = [];
        $updateBindings = [];
        foreach ($values as $key => $value) {
            $columns[] = "{$key} = ?";
            $updateBindings[] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s %s',
            $this->table,
            implode(', ', $columns),
            $this->compileWheres()
        );

        $bindings = array_merge($updateBindings, $this->bindings);
        return $this->connection->update($sql, $bindings);
    }

    public function delete(): int
    {
        $sql = sprintf('DELETE FROM %s %s', $this->table, $this->compileWheres());
        return $this->connection->delete($sql, $this->bindings);
    }

    public function toSql(): string
    {
        $sql = sprintf(
            'SELECT %s FROM %s %s %s %s',
            implode(', ', $this->columns),
            $this->table,
            $this->compileWheres(),
            $this->compileOrderBy(),
            $this->compileLimit()
        );

        return trim(preg_replace('/\s+/', ' ', $sql));
    }

    protected function compileWheres(): string
    {
        if (empty($this->wheres)) return '';

        $sql = [];
        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? 'WHERE' : 'AND';
            $sql[] = "{$boolean} {$where['column']} {$where['operator']} ?";
        }

        return implode(' ', $sql);
    }

    protected function compileOrderBy(): string
    {
        return $this->orderBy ? "ORDER BY {$this->orderBy}" : '';
    }

    protected function compileLimit(): string
    {
        return $this->limit ? "LIMIT {$this->limit}" : '';
    }
}
