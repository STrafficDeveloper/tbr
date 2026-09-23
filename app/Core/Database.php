<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            (string) Config::get('database.host'),
            (string) Config::get('database.port'),
            (string) Config::get('database.name'),
            (string) Config::get('database.charset'),
        );

        self::$connection = new PDO(
            $dsn,
            (string) Config::get('database.user'),
            (string) Config::get('database.pass'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ],
        );

        return self::$connection;
    }

    /** @param array<string|int,mixed> $bindings @return list<array<string,mixed>> */
    public static function select(string $sql, array $bindings = []): array
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchAll();
    }

    /** @param array<string|int,mixed> $bindings @return array<string,mixed>|null */
    public static function selectOne(string $sql, array $bindings = []): ?array
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($bindings);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<string|int,mixed> $bindings */
    public static function execute(string $sql, array $bindings = []): int
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    /** @param array<string|int,mixed> $bindings */
    public static function insert(string $sql, array $bindings = []): int
    {
        self::execute($sql, $bindings);

        return (int) self::connection()->lastInsertId();
    }
}
