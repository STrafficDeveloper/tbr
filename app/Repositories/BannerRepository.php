<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class BannerRepository
{
    /** @return list<array<string,mixed>> live banners for a slot, in admin-set order */
    public function forPlacement(string $placement): array
    {
        return Database::select(
            'SELECT id, title, body, image_desktop, image_mobile, alt_text, link_url, cta_label
             FROM banners
             WHERE placement = ?
               AND status = ?
               AND (starts_on IS NULL OR starts_on <= CURDATE())
               AND (ends_on IS NULL OR ends_on >= CURDATE())
             ORDER BY sort_order, id',
            [$placement, 'published'],
        );
    }
}
