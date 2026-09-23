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

    /** @return array<string,mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        return Database::selectOne(
            'SELECT id, name, slug, likes_count, views_count FROM port_riders WHERE slug = ? AND status = ? LIMIT 1',
            [$slug, 'published'],
        );
    }

    /**
     * Likes if not yet liked, unlikes otherwise. The like row and the cached
     * counter change in one transaction so they can never drift apart.
     *
     * @return array{liked:bool,count:int}
     */
    public function toggleLike(int $placeId, ?int $userId, ?string $visitorHash): array
    {
        [$column, $owner] = $userId !== null ? ['user_id', $userId] : ['visitor_hash', $visitorHash];

        return Database::transaction(function () use ($placeId, $column, $owner): array {
            $removed = Database::execute(
                "DELETE FROM port_rider_likes WHERE port_rider_id = ? AND {$column} = ?",
                [$placeId, $owner],
            );

            if ($removed > 0) {
                Database::execute(
                    'UPDATE port_riders SET likes_count = likes_count - 1 WHERE id = ? AND likes_count > 0',
                    [$placeId],
                );
                $liked = false;
            } else {
                // IGNORE: a double-click racing this one hits the unique key instead of duplicating.
                $added = Database::execute(
                    "INSERT IGNORE INTO port_rider_likes (port_rider_id, {$column}) VALUES (?, ?)",
                    [$placeId, $owner],
                );

                if ($added > 0) {
                    Database::execute('UPDATE port_riders SET likes_count = likes_count + 1 WHERE id = ?', [$placeId]);
                }
                $liked = true;
            }

            $row = Database::selectOne('SELECT likes_count FROM port_riders WHERE id = ?', [$placeId]);

            return ['liked' => $liked, 'count' => (int) ($row['likes_count'] ?? 0)];
        });
    }

    /**
     * Which of these listings the current viewer has liked, to draw filled hearts.
     *
     * @param list<int> $placeIds
     * @return list<int>
     */
    public function likedIds(array $placeIds, ?int $userId, ?string $visitorHash): array
    {
        if ($placeIds === [] || ($userId === null && $visitorHash === null)) {
            return [];
        }

        [$column, $owner] = $userId !== null ? ['user_id', $userId] : ['visitor_hash', $visitorHash];
        $placeholders = implode(',', array_fill(0, count($placeIds), '?'));

        $rows = Database::select(
            "SELECT port_rider_id FROM port_rider_likes WHERE {$column} = ? AND port_rider_id IN ({$placeholders})",
            [$owner, ...$placeIds],
        );

        return array_map('intval', array_column($rows, 'port_rider_id'));
    }

    public function incrementViews(int $placeId): void
    {
        Database::execute('UPDATE port_riders SET views_count = views_count + 1 WHERE id = ?', [$placeId]);
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
