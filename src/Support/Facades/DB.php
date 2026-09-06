<?php
declare(strict_types=1);
namespace Sierra\Support\Facades;

use Sierra\Database\Connection;

/**
 * @method static \Sierra\Database\QueryBuilder table(string $table)
 * @method static array select(string $query, array $bindings = [])
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static bool beginTransaction()
 * @method static bool commit()
 * @method static bool rollBack()
 * @method static \PDO getPdo()
 */
class DB
{
    public static function __callStatic(string $method, array $args): mixed
    {
        global $sierraApp;
        if (!$sierraApp) throw new \RuntimeException("Application not booted");

        $connection = $sierraApp->getContainer()->get(Connection::class);
        return $connection->{$method}(...$args);
    }
}
