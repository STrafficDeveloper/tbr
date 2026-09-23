<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

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
        return self::run($sql, $bindings)->fetchAll();
    }

    /** @param array<string|int,mixed> $bindings @return array<string,mixed>|null */
    public static function selectOne(string $sql, array $bindings = []): ?array
    {
        $row = self::run($sql, $bindings)->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<string|int,mixed> $bindings */
    public static function execute(string $sql, array $bindings = []): int
    {
        return self::run($sql, $bindings)->rowCount();
    }

    /**
     * Binds each value with its real type. execute($array) would send
     * everything as a string, which breaks LIMIT/OFFSET placeholders.
     *
     * @param array<string|int,mixed> $bindings
     */
    private static function run(string $sql, array $bindings): PDOStatement
    {
        $statement = self::connection()->prepare($sql);
        $position = 0;

        foreach ($bindings as $key => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue(is_int($key) ? ++$position : $key, $value, $type);
        }

        $statement->execute();

        return $statement;
    }

    /** @param array<string|int,mixed> $bindings */
    public static function insert(string $sql, array $bindings = []): int
    {
        self::execute($sql, $bindings);

        return (int) self::connection()->lastInsertId();
    }
}
