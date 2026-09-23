<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class VideoRepository
{
    /** @return list<array<string,mixed>> newest first */
    public function latest(string $section, int $limit): array
    {
        return Database::select(
            'SELECT id, title, slug, provider, video_id, video_url, thumbnail, description,
                    duration_seconds, views_count, published_at
             FROM videos
             WHERE section = ? AND status = ?
             ORDER BY published_at DESC, sort_order, id
             LIMIT ?',
            [$section, 'published', $limit],
        );
    }
}
