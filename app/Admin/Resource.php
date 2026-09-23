<?php

declare(strict_types=1);

namespace App\Admin;

use Closure;
use InvalidArgumentException;

/**
 * Describes one kind of content the admin panel manages: its table, its form
 * fields and its list columns. The generic admin controller does the rest.
 *
 * Table and column names only ever come from these definitions, never from
 * the request, which is what makes building SQL from them safe.
 */
final class Resource
{
    /**
     * @param list<Field> $fields
     * @param array<string,array{0:string,1?:string}> $columns column => [label, format]
     *        formats: text (default), date, datetime, status, bool, image
     * @param list<string> $search columns matched by the list's search box
     * @param list<string> $children keys of resources edited from this one's form
     * @param Closure(array<string,mixed>):?string|null $deleteGuard returns a reason to refuse deletion
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $singular,
        public readonly string $table,
        public readonly array $fields,
        public readonly array $columns,
        public readonly string $orderBy = 'id DESC',
        public readonly array $search = [],
        public readonly ?string $parentKey = null,
        public readonly ?string $foreignKey = null,
        public readonly array $children = [],
        public readonly ?string $publicPath = null,
        public readonly ?string $bulkImageField = null,
        public readonly ?Closure $deleteGuard = null,
        public readonly ?string $titleColumn = null,
    ) {
        foreach ([$table, $foreignKey, ...array_keys($columns), ...$search, ...array_map(static fn (Field $f): string => $f->name, $fields)] as $identifier) {
            if ($identifier !== null && preg_match('/^[a-z_]+$/', $identifier) !== 1) {
                throw new InvalidArgumentException("Unsafe identifier in admin resource {$key}: {$identifier}");
            }
        }

        // Letters, underscores, spaces and commas only: no quotes, brackets or ";".
        if (preg_match('/^[a-z_ ,]+$/i', $orderBy) !== 1) {
            throw new InvalidArgumentException("Unsafe ORDER BY in admin resource {$key}");
        }
    }

    public function field(string $name): ?Field
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }

        return null;
    }

    /** @return list<Field> */
    public function imageFields(): array
    {
        return array_values(array_filter($this->fields, static fn (Field $f): bool => $f->type === 'image'));
    }

    public function isChild(): bool
    {
        return $this->parentKey !== null;
    }

    /** A human title for a row: its title/name column, or "#id". */
    public function titleOf(array $row): string
    {
        $column = $this->titleColumn ?? (array_key_exists('title', $row) ? 'title' : (array_key_exists('name', $row) ? 'name' : null));

        return $column !== null && !empty($row[$column]) ? (string) $row[$column] : '#' . $row['id'];
    }

    public function publicUrl(array $row): ?string
    {
        if ($this->publicPath === null || empty($row['slug']) || ($row['status'] ?? 'published') === 'draft') {
            return null;
        }

        return str_replace('{slug}', rawurlencode((string) $row['slug']), $this->publicPath);
    }
}
