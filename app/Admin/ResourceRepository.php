<?php

declare(strict_types=1);

namespace App\Admin;

use App\Core\Database;

/**
 * Generic reads and writes for any admin Resource. Identifiers come from the
 * Resource definition (validated in its constructor); values are always bound.
 */
final class ResourceRepository
{
    /** @return list<array<string,mixed>> */
    public function list(Resource $resource, ?int $parentId, string $search, int $limit, int $offset): array
    {
        [$where, $bindings] = $this->where($resource, $parentId, $search);

        return Database::select(
            "SELECT * FROM `{$resource->table}` WHERE {$where} ORDER BY {$resource->orderBy} LIMIT ? OFFSET ?",
            [...$bindings, $limit, $offset],
        );
    }

    public function count(Resource $resource, ?int $parentId, string $search): int
    {
        [$where, $bindings] = $this->where($resource, $parentId, $search);
        $row = Database::selectOne("SELECT COUNT(*) AS n FROM `{$resource->table}` WHERE {$where}", $bindings);

        return (int) ($row['n'] ?? 0);
    }

    /** @return array<string,mixed>|null */
    public function find(Resource $resource, int $id): ?array
    {
        return Database::selectOne("SELECT * FROM `{$resource->table}` WHERE id = ? LIMIT 1", [$id]);
    }

    /** @param array<string,mixed> $data column => value, columns from the Resource only */
    public function insert(Resource $resource, array $data): int
    {
        $columns = array_keys($data);
        $this->assertColumns($resource, $columns);

        return Database::insert(
            sprintf(
                'INSERT INTO `%s` (`%s`) VALUES (%s)',
                $resource->table,
                implode('`, `', $columns),
                implode(', ', array_fill(0, count($columns), '?')),
            ),
            array_values($data),
        );
    }

    /** @param array<string,mixed> $data */
    public function update(Resource $resource, int $id, array $data): void
    {
        if ($data === []) {
            return;
        }

        $this->assertColumns($resource, array_keys($data));
        $assignments = implode(', ', array_map(static fn (string $c): string => "`{$c}` = ?", array_keys($data)));

        Database::execute("UPDATE `{$resource->table}` SET {$assignments} WHERE id = ?", [...array_values($data), $id]);
    }

    public function delete(Resource $resource, int $id): void
    {
        Database::execute("DELETE FROM `{$resource->table}` WHERE id = ?", [$id]);
    }

    public function slugTaken(Resource $resource, string $slug, ?int $exceptId): bool
    {
        $sql = "SELECT id FROM `{$resource->table}` WHERE slug = ?";
        $bindings = [$slug];

        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $bindings[] = $exceptId;
        }

        return Database::selectOne($sql . ' LIMIT 1', $bindings) !== null;
    }

    /** @return list<array<string,mixed>> child rows, e.g. to delete their files before the parent goes */
    public function children(Resource $child, int $parentId): array
    {
        return Database::select("SELECT * FROM `{$child->table}` WHERE `{$child->foreignKey}` = ?", [$parentId]);
    }

    public function childCount(Resource $child, int $parentId): int
    {
        $row = Database::selectOne("SELECT COUNT(*) AS n FROM `{$child->table}` WHERE `{$child->foreignKey}` = ?", [$parentId]);

        return (int) ($row['n'] ?? 0);
    }

    /** @return array{0:string,1:list<string|int>} */
    private function where(Resource $resource, ?int $parentId, string $search): array
    {
        $where = ['1 = 1'];
        $bindings = [];

        if ($resource->foreignKey !== null) {
            $where[] = "`{$resource->foreignKey}` = ?";
            $bindings[] = (int) $parentId;
        }

        if ($search !== '' && $resource->search !== []) {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where[] = '(' . implode(' OR ', array_map(static fn (string $c): string => "`{$c}` LIKE ?", $resource->search)) . ')';
            array_push($bindings, ...array_fill(0, count($resource->search), $like));
        }

        return [implode(' AND ', $where), $bindings];
    }

    /** @param list<string> $columns */
    private function assertColumns(Resource $resource, array $columns): void
    {
        $allowed = array_map(static fn (Field $f): string => $f->name, $resource->fields);

        if ($resource->foreignKey !== null) {
            $allowed[] = $resource->foreignKey;
        }

        foreach ($columns as $column) {
            if (!in_array($column, $allowed, true)) {
                throw new \LogicException("Column {$column} is not part of admin resource {$resource->key}");
            }
        }
    }
}
