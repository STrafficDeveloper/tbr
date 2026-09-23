<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class VideoRepository
{
    private const COLUMNS = 'id, title, slug, provider, video_id, video_url, thumbnail, description,
                             duration_seconds, views_count, published_at';

    /** @return list<array<string,mixed>> newest first */
    public function latest(string $section, int $limit, int $offset = 0): array
    {
        return Database::select(
            'SELECT ' . self::COLUMNS . '
             FROM videos
             WHERE section = ? AND status = ?
             ORDER BY published_at DESC, sort_order, id
             LIMIT ? OFFSET ?',
            [$section, 'published', $limit, $offset],
        );
    }

    public function count(string $section): int
    {
        $row = Database::selectOne('SELECT COUNT(*) AS n FROM videos WHERE section = ? AND status = ?', [$section, 'published']);

        return (int) ($row['n'] ?? 0);
    }

    /** @return list<array<string,mixed>> a Hall of Fame profile's videos, in admin-set order */
    public function forProfile(int $profileId): array
    {
        return Database::select(
            'SELECT ' . self::COLUMNS . '
             FROM videos
             WHERE hof_profile_id = ? AND section = ? AND status = ?
             ORDER BY sort_order, id',
            [$profileId, 'hall_of_fame', 'published'],
        );
    }
}
