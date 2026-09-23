<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class HallOfFameRepository
{
    private const COLUMNS = 'id, name, slug, handle, headline, summary, avatar, cover_image,
                             facebook_url, instagram_url, tiktok_url, featured_month';

    /** @return array<string,mixed>|null the most recently featured hero of the month */
    public function heroOfMonth(): ?array
    {
        return Database::selectOne(
            'SELECT ' . self::COLUMNS . '
             FROM hof_profiles
             WHERE status = ? AND is_hero_of_month = 1
             ORDER BY featured_month DESC, id DESC
             LIMIT 1',
            ['published'],
        );
    }

    /** @return array<string,mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        return Database::selectOne(
            'SELECT ' . self::COLUMNS . ' FROM hof_profiles WHERE slug = ? AND status = ? LIMIT 1',
            [$slug, 'published'],
        );
    }

    /** @return list<array<string,mixed>> Biodata tab blocks */
    public function sections(int $profileId): array
    {
        return Database::select(
            'SELECT heading, body FROM hof_sections WHERE hof_profile_id = ? ORDER BY sort_order, id',
            [$profileId],
        );
    }

    /** @return list<array<string,mixed>> Galeri tab photos */
    public function images(int $profileId): array
    {
        return Database::select(
            'SELECT id, path, alt_text FROM hof_images WHERE hof_profile_id = ? ORDER BY sort_order, id',
            [$profileId],
        );
    }
}
