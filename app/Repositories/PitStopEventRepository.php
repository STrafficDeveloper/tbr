<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class PitStopEventRepository
{
    /** Registrations that hold a slot: everything except rejected ones. */
    private const TAKEN_SLOTS = '(SELECT COUNT(*) FROM pitstop_registrations r
                                  WHERE r.event_id = e.id AND r.status <> \'rejected\')';

    private const COLUMNS = 'e.id, e.title, e.slug, e.state, e.location_name, e.address, e.maps_url,
                             e.latitude, e.longitude, e.starts_at, e.ends_at, e.capacity, e.description,
                             e.banner_image, e.status';

    /**
     * Published events that have not started yet, soonest first.
     *
     * @return list<array<string,mixed>>
     */
    public function upcoming(int $limit): array
    {
        return Database::select(
            'SELECT ' . self::COLUMNS . ', ' . self::TAKEN_SLOTS . ' AS registrations_count
             FROM pitstop_events e
             WHERE e.status = ? AND e.starts_at >= NOW()
             ORDER BY e.starts_at, e.sort_order
             LIMIT ?',
            ['published', $limit],
        );
    }

    /**
     * A public event page: published or closed (closed ones stay visible,
     * they just stop taking registrations). Drafts are not public.
     *
     * @return array<string,mixed>|null
     */
    public function findPublicBySlug(string $slug): ?array
    {
        return Database::selectOne(
            'SELECT ' . self::COLUMNS . ', ' . self::TAKEN_SLOTS . ' AS registrations_count
             FROM pitstop_events e
             WHERE e.slug = ? AND e.status IN (?, ?)
             LIMIT 1',
            [$slug, 'published', 'closed'],
        );
    }

    /**
     * Events a member can still book: published, not started, not full.
     *
     * @return list<array<string,mixed>>
     */
    public function openForRegistration(): array
    {
        return Database::select(
            'SELECT ' . self::COLUMNS . ', ' . self::TAKEN_SLOTS . ' AS registrations_count
             FROM pitstop_events e
             WHERE e.status = ? AND e.starts_at > NOW()
               AND (e.capacity IS NULL OR ' . self::TAKEN_SLOTS . ' < e.capacity)
             ORDER BY e.starts_at, e.sort_order',
            ['published'],
        );
    }

    /** @param array<string,mixed> $event */
    public static function isOpen(array $event): bool
    {
        $hasRoom = $event['capacity'] === null || (int) $event['registrations_count'] < (int) $event['capacity'];

        return $event['status'] === 'published' && strtotime((string) $event['starts_at']) > time() && $hasRoom;
    }
}
