<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class HallOfFameRepository
{
    /** @return array<string,mixed>|null the most recently featured hero of the month */
    public function heroOfMonth(): ?array
    {
        return Database::selectOne(
            'SELECT id, name, slug, handle, headline, summary, avatar, cover_image
             FROM hof_profiles
             WHERE status = ? AND is_hero_of_month = 1
             ORDER BY featured_month DESC, id DESC
             LIMIT 1',
            ['published'],
        );
    }
}
