<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class PortRiderRepository
{
    /** @return list<array<string,mixed>> */
    public function search(?string $state, ?string $term, int $limit, int $offset = 0): array
    {
        [$where, $bindings] = $this->filters($state, $term);

        return Database::select(
            "SELECT id, name, slug, category, address, city, state, postcode, phone, maps_url,
                    latitude, longitude, image, likes_count, views_count
             FROM port_riders
             WHERE {$where}
             ORDER BY name
             LIMIT ? OFFSET ?",
            [...$bindings, $limit, $offset],
        );
    }

    public function count(?string $state, ?string $term): int
    {
        [$where, $bindings] = $this->filters($state, $term);
        $row = Database::selectOne("SELECT COUNT(*) AS total FROM port_riders WHERE {$where}", $bindings);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * State keys that have at least one live listing, so the filter never
     * offers a state that would come back empty.
     *
     * @return list<string>
     */
    public function statesWithListings(): array
    {
        $rows = Database::select(
            'SELECT DISTINCT state FROM port_riders WHERE status = ? ORDER BY state',
            ['published'],
        );

        return array_column($rows, 'state');
    }

    /** @return array{0:string,1:list<string|int>} */
    private function filters(?string $state, ?string $term): array
    {
        $where = ['status = ?'];
        $bindings = ['published'];

        if ($state !== null && $state !== '') {
            $where[] = 'state = ?';
            $bindings[] = $state;
        }

        if ($term !== null && $term !== '') {
            // Escape LIKE wildcards so a search for "50%" matches literally.
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $where[] = '(name LIKE ? OR city LIKE ? OR address LIKE ?)';
            array_push($bindings, $like, $like, $like);
        }

        return [implode(' AND ', $where), $bindings];
    }
}
