<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class PitStopEventRepository
{
    /**
     * Published events that have not started yet, soonest first, each with
     * how many riders have registered (rejected registrations excluded).
     *
     * @return list<array<string,mixed>>
     */
    public function upcoming(int $limit): array
    {
        return Database::select(
            'SELECT e.id, e.title, e.slug, e.state, e.location_name, e.starts_at, e.banner_image,
                    (SELECT COUNT(*) FROM pitstop_registrations r
                      WHERE r.event_id = e.id AND r.status <> ?) AS registrations_count
             FROM pitstop_events e
             WHERE e.status = ? AND e.starts_at >= NOW()
             ORDER BY e.starts_at, e.sort_order
             LIMIT ?',
            ['rejected', 'published', $limit],
        );
    }
}
